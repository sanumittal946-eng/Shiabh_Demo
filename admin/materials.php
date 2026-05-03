<?php
// admin/materials.php
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$action = $_GET['action'] ?? 'list';
$msg = "";
$err = "";

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $db->prepare("SELECT file_path, type FROM materials WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $mat = $stmt->fetch();
    
    if ($mat) {
        // If it's a file, try to delete it
        if ($mat['type'] != 'Videos' && file_exists(__DIR__ . '/../' . $mat['file_path'])) {
            @unlink(__DIR__ . '/../' . $mat['file_path']);
        }
        $db->prepare("DELETE FROM materials WHERE id = :id")->execute([':id' => $id]);
        $msg = "Material deleted successfully.";
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_material'])) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        $err = "Invalid security token.";
    } else {
        $title = trim($_POST['title']);
        $subject = trim($_POST['subject']);
        $type = $_POST['type']; // 'Notes','Videos','Practice Papers','Previous Years'
        $course_id = (int)$_POST['course_id'];
        
        if (empty($title) || empty($subject) || empty($course_id) || empty($type)) {
            $err = "All fields are required.";
        } else {
            $file_path = "";
            $file_size = "0 KB";
            
            if ($type == 'Videos') {
                $file_path = trim($_POST['video_url']);
                if (empty($file_path)) $err = "Video URL is required.";
            } else {
                // File Upload Handle
                if (isset($_FILES['material_file']) && $_FILES['material_file']['error'] == 0) {
                    $uploadDir = __DIR__ . '/../uploads/materials/';
                    $fileName = time() . '_' . basename($_FILES['material_file']['name']);
                    $uploadFile = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES['material_file']['tmp_name'], $uploadFile)) {
                        $file_path = 'uploads/materials/' . $fileName;
                        $sizeHelper = $_FILES['material_file']['size'] / 1024;
                        $file_size = ($sizeHelper > 1024) ? round($sizeHelper/1024, 2) . " MB" : round($sizeHelper, 2) . " KB";
                    } else {
                        $err = "Failed to upload the file.";
                    }
                } else {
                    $err = "Please select a valid file to upload.";
                }
            }
            
            if (empty($err)) {
                $stmt = $db->prepare("INSERT INTO materials (title, subject, type, file_path, file_size, course_id) VALUES (:title, :sub, :type, :path, :size, :cid)");
                if ($stmt->execute([
                    ':title' => $title,
                    ':sub' => $subject,
                    ':type' => $type,
                    ':path' => $file_path,
                    ':size' => $file_size,
                    ':cid' => $course_id
                ])) {
                    $msg = "Material uploaded successfully!";
                    $action = 'list';
                } else {
                    $err = "Database error saving material.";
                }
            }
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 shadow-sm rounded">
    <h4 class="mb-0 text-dark fw-bold">Materials & Video Hub</h4>
    <?php if ($action == 'list'): ?>
        <a href="materials.php?action=add" class="btn btn-primary"><i class="fa-solid fa-cloud-arrow-up me-1"></i> Upload Material</a>
    <?php else: ?>
        <a href="materials.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Back to Hub</a>
    <?php endif; ?>
</div>

<?php if (!empty($msg)): ?>
    <div class="alert alert-success"><i class="fa-solid fa-check-circle me-2"></i><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<?php if (!empty($err)): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($err) ?></div>
<?php endif; ?>

<?php if ($action == 'add'): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="materials.php?action=add" enctype="multipart/form-data">
                <?= csrfField() ?>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Title *</label>
                        <input type="text" name="title" class="form-control" required placeholder="e.g. Chapter 1: Kinematics Notes">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Target Course *</label>
                        <select name="course_id" class="form-select" required>
                            <option value="">Choose Course...</option>
                            <?php
                            $courses = $db->query("SELECT id, name FROM courses")->fetchAll();
                            foreach($courses as $c) { echo "<option value='{$c['id']}'>".htmlspecialchars($c['name'])."</option>"; }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Subject / Unit *</label>
                        <input type="text" name="subject" class="form-control" required placeholder="Physics">
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold" id="typeLabel">Material Type *</label>
                        <select name="type" class="form-select" id="materialType" required onchange="toggleInputs()">
                            <option value="Notes">PDF Notes / Syllabus</option>
                            <option value="Videos">Video Link</option>
                            <option value="Practice Papers">Practice Assignment</option>
                            <option value="Previous Years">Previous Year Paper</option>
                        </select>
                    </div>
                    
                    <div class="col-md-8" id="fileInputContainer">
                        <label class="form-label fw-bold">Upload PDF / Document</label>
                        <input type="file" name="material_file" class="form-control" accept=".pdf,.doc,.docx">
                    </div>
                    
                    <div class="col-md-8" id="urlInputContainer" style="display:none;">
                        <label class="form-label fw-bold">Video URL (YouTube/Vimeo)</label>
                        <input type="url" name="video_url" class="form-control" placeholder="https://youtube.com/watch?v=...">
                    </div>
                </div>
                
                <div class="d-flex justify-content-end">
                    <button type="submit" name="save_material" class="btn btn-success px-4"><i class="fa-solid fa-save me-1"></i> Save Material</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        function toggleInputs() {
            let type = document.getElementById('materialType').value;
            if (type === 'Videos') {
                document.getElementById('fileInputContainer').style.display = 'none';
                document.getElementById('urlInputContainer').style.display = 'block';
            } else {
                document.getElementById('fileInputContainer').style.display = 'block';
                document.getElementById('urlInputContainer').style.display = 'none';
            }
        }
    </script>
<?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <!-- Filter -->
            <form method="GET" class="mb-4 d-flex gap-2">
                <select name="filter_course" class="form-select" style="max-width: 250px;" onchange="this.form.submit()">
                    <option value="">All Courses</option>
                    <?php
                    $courses = $db->query("SELECT id, name FROM courses")->fetchAll();
                    $filter_c = $_GET['filter_course'] ?? '';
                    foreach($courses as $c) { 
                        $sel = ($c['id'] == $filter_c) ? 'selected' : '';
                        echo "<option value='{$c['id']}' $sel>".htmlspecialchars($c['name'])."</option>"; 
                    }
                    ?>
                </select>
                <select name="filter_type" class="form-select" style="max-width: 200px;" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="Notes" <?= ($_GET['filter_type'] ?? '') == 'Notes' ? 'selected' : '' ?>>Notes</option>
                    <option value="Videos" <?= ($_GET['filter_type'] ?? '') == 'Videos' ? 'selected' : '' ?>>Videos</option>
                    <option value="Practice Papers" <?= ($_GET['filter_type'] ?? '') == 'Practice Papers' ? 'selected' : '' ?>>Practice Papers</option>
                </select>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Type</th>
                            <th>Title & Subject</th>
                            <th>Course</th>
                            <th>Format</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT m.*, c.name as course_name FROM materials m LEFT JOIN courses c ON m.course_id = c.id WHERE 1=1";
                        $params = [];
                        if (!empty($_GET['filter_course'])) {
                            $sql .= " AND m.course_id = :cid";
                            $params[':cid'] = $_GET['filter_course'];
                        }
                        if (!empty($_GET['filter_type'])) {
                            $sql .= " AND m.type = :type";
                            $params[':type'] = $_GET['filter_type'];
                        }
                        $sql .= " ORDER BY m.id DESC";
                        
                        $stmt = $db->prepare($sql);
                        $stmt->execute($params);
                        $mats = $stmt->fetchAll();
                        
                        if (empty($mats)):
                        ?>
                            <tr><td colspan="5" class="text-center text-muted">No materials found.</td></tr>
                        <?php else: foreach($mats as $m): ?>
                            <tr>
                                <td>
                                    <?php if($m['type'] == 'Videos'): ?>
                                        <i class="fa-solid fa-video text-danger fs-4"></i>
                                    <?php else: ?>
                                        <i class="fa-regular fa-file-pdf text-danger fs-4"></i>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($m['title']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($m['subject']) ?></small>
                                </td>
                                <td><span class="badge bg-primary"><?= htmlspecialchars($m['course_name']) ?></span></td>
                                <td>
                                    <?php if($m['type'] == 'Videos'): ?>
                                        <a href="<?= htmlspecialchars($m['file_path']) ?>" target="_blank" class="small text-decoration-none"><i class="fa-solid fa-link me-1"></i>View Link</a>
                                    <?php else: ?>
                                        <div class="small fw-bold"><?= $m['file_size'] ?></div>
                                        <a href="../<?= htmlspecialchars($m['file_path']) ?>" target="_blank" class="small text-decoration-none">Download</a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="materials.php?delete=<?= $m['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this material?');"><i class="fa-solid fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
