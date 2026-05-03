<?php
// admin/tests.php
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$action = $_GET['action'] ?? 'list';
$msg = $err = "";

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->prepare("DELETE FROM test_submissions WHERE test_id = :id")->execute([':id' => $id]);
    $db->prepare("DELETE FROM tests WHERE id = :id")->execute([':id' => $id]);
    $msg = "Test deleted successfully.";
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_test'])) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        $err = "Invalid security token.";
    } else {
        $title = trim($_POST['title']);
        $course_id = (int)$_POST['course_id'];
        $total_marks = (int)$_POST['total_marks'];
        $passing_marks = (int)$_POST['passing_marks'];
        $description = trim($_POST['description']);
        
        if (empty($title) || empty($course_id) || $total_marks <= 0) {
            $err = "Title, Course, and Total Marks are required.";
        } else {
            $file_path = "";
            
            // File Upload Handle
            if (isset($_FILES['test_file']) && $_FILES['test_file']['error'] == 0) {
                $uploadDir = __DIR__ . '/../uploads/tests/';
                $fileName = time() . '_qp_' . basename($_FILES['test_file']['name']);
                $uploadFile = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['test_file']['tmp_name'], $uploadFile)) {
                    $file_path = 'uploads/tests/' . $fileName;
                }
            }
            
            $stmt = $db->prepare("INSERT INTO tests (course_id, title, description, file_path, total_marks, passing_marks) VALUES (:cid, :title, :desc, :fp, :tm, :pm)");
            if ($stmt->execute([
                ':cid' => $course_id,
                ':title' => $title,
                ':desc' => $description,
                ':fp' => $file_path,
                ':tm' => $total_marks,
                ':pm' => $passing_marks
            ])) {
                $msg = "Test assignment created successfully!";
                $action = 'list';
            } else {
                $err = "Database error.";
            }
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 shadow-sm rounded">
    <h4 class="mb-0 text-dark fw-bold">Test & Assignment Hub</h4>
    <?php if ($action == 'list'): ?>
        <a href="tests.php?action=add" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i> Create Test / Assignment</a>
    <?php else: ?>
        <a href="tests.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Back to Hub</a>
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
            <form method="POST" action="tests.php?action=add" enctype="multipart/form-data">
                <?= csrfField() ?>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Test Title *</label>
                        <input type="text" name="title" class="form-control" required placeholder="e.g. Physics Grand Mock Test 1">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Target Course *</label>
                        <select name="course_id" class="form-select" required>
                            <option value="">Choose Course...</option>
                            <?php
                            $courses = $db->query("SELECT id, name FROM courses")->fetchAll();
                            foreach($courses as $c) { echo "<option value='{$c['id']}'>".htmlspecialchars($c['name'])."</option>"; }
                            ?>
                        </select>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Total Marks *</label>
                        <input type="number" name="total_marks" class="form-control" required value="100">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Passing Marks *</label>
                        <input type="number" name="passing_marks" class="form-control" required value="40">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Upload Question Paper (PDF)</label>
                        <input type="file" name="test_file" class="form-control" accept=".pdf">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Instructions for Students</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Time limit: 2 hours. Upload answers in a single PDF."></textarea>
                </div>
                
                <div class="d-flex justify-content-end">
                    <button type="submit" name="save_test" class="btn btn-success px-4"><i class="fa-solid fa-save me-1"></i> Publish Test</button>
                </div>
            </form>
        </div>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Test Title & Course</th>
                            <th>Marks (Pas / Tot)</th>
                            <th>Created</th>
                            <th>Submissions</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $tests = $db->query("
                            SELECT t.*, c.name as course_name, 
                            (SELECT COUNT(*) FROM test_submissions ts WHERE ts.test_id = t.id) as sub_count,
                            (SELECT COUNT(*) FROM test_submissions ts WHERE ts.test_id = t.id AND status='submitted') as pending_count
                            FROM tests t 
                            LEFT JOIN courses c ON t.course_id = c.id 
                            ORDER BY t.id DESC
                        ")->fetchAll();
                        
                        if (empty($tests)):
                        ?>
                            <tr><td colspan="5" class="text-center text-muted">No tests created yet.</td></tr>
                        <?php else: foreach($tests as $t): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-primary"><?= htmlspecialchars($t['title']) ?></div>
                                    <small class="badge bg-secondary"><?= htmlspecialchars($t['course_name'] ?? 'Unknown Course') ?></small>
                                </td>
                                <td><span class="text-success fw-bold"><?= $t['passing_marks'] ?></span> / <?= $t['total_marks'] ?></td>
                                <td><?= date('M d, Y', strtotime($t['created_at'])) ?></td>
                                <td>
                                    <div class="fw-bold"><?= $t['sub_count'] ?> Total</div>
                                    <?php if($t['pending_count'] > 0): ?>
                                        <small class="text-danger fw-bold"><i class="fa-solid fa-bell me-1"></i><?= $t['pending_count'] ?> need checking!</small>
                                    <?php else: ?>
                                        <small class="text-muted">All checked.</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="test_submissions.php?test_id=<?= $t['id'] ?>" class="btn btn-sm btn-primary me-1"><i class="fa-solid fa-users-viewfinder me-1"></i> Submissions</a>
                                    <a href="tests.php?delete=<?= $t['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this test and all its submissions?');"><i class="fa-solid fa-trash"></i></a>
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
