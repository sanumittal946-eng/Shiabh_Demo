<?php
// admin/courses.php
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$msg = "";
$err = "";

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $db->prepare("DELETE FROM courses WHERE id = :id");
    if ($stmt->execute([':id' => $id])) {
        $msg = "Course deleted successfully.";
    }
}

// Handle Add / Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_course'])) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        $err = "Invalid security token.";
    } else {
        $id          = (int)($_POST['course_id'] ?? 0);
        $name        = trim($_POST['name']);
        $category    = trim($_POST['category']);
        $duration    = trim($_POST['duration']);
        $fee         = trim($_POST['fee']);
        $mode        = trim($_POST['mode']);
        $description = trim($_POST['description']);

        if (empty($name) || empty($category)) {
            $err = "Name and Category are required.";
        } else {
            if ($id > 0) {
                // UPDATE
                $stmt = $db->prepare("UPDATE courses SET name=:name, category=:cat, duration=:dur, fee=:fee, mode=:mod, description=:desc WHERE id=:id");
                $ok = $stmt->execute([':name'=>$name,':cat'=>$category,':dur'=>$duration,':fee'=>empty($fee)?0:$fee,':mod'=>$mode,':desc'=>$description,':id'=>$id]);
                $msg = $ok ? "Course updated successfully!" : "Database error.";
            } else {
                // INSERT
                $stmt = $db->prepare("INSERT INTO courses (name, category, duration, fee, mode, description) VALUES (:name, :cat, :dur, :fee, :mod, :desc)");
                $ok = $stmt->execute([':name'=>$name,':cat'=>$category,':dur'=>$duration,':fee'=>empty($fee)?0:$fee,':mod'=>$mode,':desc'=>$description]);
                $msg = $ok ? "Course added successfully!" : "Database error.";
                if (!$ok) $err = "Database error occurred.";
            }
        }
    }
}

$courses = $db->query("SELECT * FROM courses ORDER BY id DESC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 shadow-sm rounded">
    <h4 class="mb-0 text-dark fw-bold">Manage Courses</h4>
    <button class="btn btn-primary" onclick="openCourseModal()"><i class="fa-solid fa-plus me-1"></i> Add Course</button>
</div>

<?php if (!empty($msg)): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-check-circle me-2"></i><?= htmlspecialchars($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (!empty($err)): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($err) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<!-- Courses Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Course Info</th>
                        <th>Duration &amp; Fee</th>
                        <th>Mode</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($courses)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No courses available.</td></tr>
                    <?php else: foreach($courses as $c): ?>
                        <tr>
                            <td><?= $c['id'] ?></td>
                            <td>
                                <div class="fw-bold text-primary"><?= htmlspecialchars($c['name']) ?></div>
                                <span class="badge bg-secondary"><?= htmlspecialchars($c['category']) ?></span>
                            </td>
                            <td>
                                <div><i class="fa-regular fa-clock text-muted"></i> <?= htmlspecialchars($c['duration']) ?></div>
                                <div><i class="fa-solid fa-indian-rupee-sign text-muted"></i> <?= number_format((float)$c['fee'], 2) ?></div>
                            </td>
                            <td><?= htmlspecialchars($c['mode']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1" onclick='editCourse(<?= htmlspecialchars(json_encode($c), ENT_QUOTES, "UTF-8") ?>)'><i class="fa-solid fa-pen"></i></button>
                                <a href="courses.php?delete=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this course?');"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add / Edit Modal -->
<div class="modal fade" id="courseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="course_id" id="course_id_hidden" value="0">
                <div class="modal-header">
                    <h5 class="modal-title" id="courseModalTitle">Add Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Course Title *</label>
                            <input type="text" name="name" id="c_name" class="form-control" required placeholder="e.g. JEE Mains Crash Course">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Category *</label>
                            <select name="category" id="c_category" class="form-select" required>
                                <option value="">Choose...</option>
                                <option value="Competitive">Competitive (JEE/NEET)</option>
                                <option value="School">School (6-10)</option>
                                <option value="Board">Boards (11-12)</option>
                                <option value="Language">Language &amp; Spoken</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Duration</label>
                            <input type="text" name="duration" id="c_duration" class="form-control" placeholder="e.g. 6 Months">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Fee (in Rs)</label>
                            <input type="number" step="0.01" name="fee" id="c_fee" class="form-control" placeholder="e.g. 15000">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Mode</label>
                            <select name="mode" id="c_mode" class="form-select">
                                <option value="Offline">Offline</option>
                                <option value="Online">Online</option>
                                <option value="Hybrid">Hybrid</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" id="c_description" class="form-control" rows="4" placeholder="Brief outline of the course..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_course" class="btn btn-success px-4"><i class="fa-solid fa-save me-1"></i> Save Course</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const courseModal = new bootstrap.Modal(document.getElementById('courseModal'));

    window.openCourseModal = function() {
        document.getElementById('course_id_hidden').value = 0;
        document.getElementById('courseModalTitle').innerText = 'Add Course';
        document.getElementById('c_name').value = '';
        document.getElementById('c_category').value = '';
        document.getElementById('c_duration').value = '';
        document.getElementById('c_fee').value = '';
        document.getElementById('c_mode').value = 'Offline';
        document.getElementById('c_description').value = '';
        courseModal.show();
    }

    window.editCourse = function(c) {
        document.getElementById('course_id_hidden').value = c.id;
        document.getElementById('courseModalTitle').innerText = 'Edit Course';
        document.getElementById('c_name').value = c.name || '';
        document.getElementById('c_category').value = c.category || '';
        document.getElementById('c_duration').value = c.duration || '';
        document.getElementById('c_fee').value = c.fee || '';
        document.getElementById('c_mode').value = c.mode || 'Offline';
        document.getElementById('c_description').value = c.description || '';
        courseModal.show();
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
