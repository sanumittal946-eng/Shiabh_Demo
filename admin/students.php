<?php
// admin/students.php
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$msg = "";
$err = "";

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $db->prepare("DELETE FROM students WHERE id = :id");
    if ($stmt->execute([':id' => $id])) {
        $msg = "Student deleted successfully.";
    }
}

// Handle Add / Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_student'])) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        $err = "Invalid security token.";
    } else {
        $id        = (int)($_POST['student_id'] ?? 0);
        $name      = trim($_POST['name']);
        $email     = trim($_POST['email']);
        $phone     = trim($_POST['phone']);
        $password  = $_POST['password'];
        $course_id = $_POST['course_id'] ?: null;
        $batch     = $_POST['batch'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (empty($name) || empty($email)) {
            $err = "Name and Email are required.";
        } else {
            if ($id > 0) {
                // UPDATE
                if (!empty($password)) {
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("UPDATE students SET name=:name, email=:email, phone=:phone, password_hash=:pass, course_id=:cid, batch=:batch, is_active=:ia WHERE id=:id");
                    $params = [':name'=>$name,':email'=>$email,':phone'=>$phone,':pass'=>$password_hash,':cid'=>$course_id,':batch'=>$batch,':ia'=>$is_active,':id'=>$id];
                } else {
                    $stmt = $db->prepare("UPDATE students SET name=:name, email=:email, phone=:phone, course_id=:cid, batch=:batch, is_active=:ia WHERE id=:id");
                    $params = [':name'=>$name,':email'=>$email,':phone'=>$phone,':cid'=>$course_id,':batch'=>$batch,':ia'=>$is_active,':id'=>$id];
                }
                if ($stmt->execute($params)) {
                    $msg = "Student updated successfully!";
                } else {
                    $err = "Database error occurred.";
                }
            } else {
                // INSERT — check duplicate email
                $check = $db->prepare("SELECT id FROM students WHERE email = :email");
                $check->execute([':email' => $email]);
                if ($check->fetch()) {
                    $err = "A student with this email already exists.";
                } else {
                    $password_hash = password_hash(empty($password) ? 'password123' : $password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("INSERT INTO students (name, email, phone, password_hash, course_id, batch, is_active) VALUES (:name, :email, :phone, :pass, :cid, :batch, :ia)");
                    if ($stmt->execute([':name'=>$name,':email'=>$email,':phone'=>$phone,':pass'=>$password_hash,':cid'=>$course_id,':batch'=>$batch,':ia'=>$is_active])) {
                        $msg = "Student added successfully! Default password is 'password123' unless specified.";
                    } else {
                        $err = "Database error occurred.";
                    }
                }
            }
        }
    }
}

$courses = $db->query("SELECT id, name FROM courses")->fetchAll();
$students = $db->query("SELECT s.*, c.name as course_name FROM students s LEFT JOIN courses c ON s.course_id = c.id ORDER BY s.id DESC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 shadow-sm rounded">
    <h4 class="mb-0 text-dark fw-bold">Manage Students</h4>
    <button class="btn btn-primary" onclick="openStudentModal()"><i class="fa-solid fa-plus me-1"></i> Add Student</button>
</div>

<?php if (!empty($msg)): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-check-circle me-2"></i><?= htmlspecialchars($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (!empty($err)): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($err) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<!-- Students Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Name &amp; Email</th>
                        <th>Phone</th>
                        <th>Course</th>
                        <th>Batch</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No students enrolled yet.</td></tr>
                    <?php else: foreach($students as $s): ?>
                        <tr>
                            <td><?= $s['id'] ?></td>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($s['name']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($s['email']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($s['phone'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($s['course_name'] ?? 'Not Assigned') ?></td>
                            <td><?= htmlspecialchars($s['batch'] ?? '—') ?></td>
                            <td>
                                <?php if($s['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1" onclick='editStudent(<?= htmlspecialchars(json_encode($s), ENT_QUOTES, "UTF-8") ?>)'><i class="fa-solid fa-pen"></i></button>
                                <a href="students.php?delete=<?= $s['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this student?');"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add / Edit Modal -->
<div class="modal fade" id="studentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="student_id" id="student_id" value="0">
                <div class="modal-header">
                    <h5 class="modal-title" id="studentModalTitle">Add Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Full Name *</label>
                            <input type="text" name="name" id="s_name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Email Address *</label>
                            <input type="email" name="email" id="s_email" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Phone Number</label>
                            <input type="tel" name="phone" id="s_phone" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Password <span class="text-muted fw-normal small">(leave blank to keep current)</span></label>
                            <input type="text" name="password" id="s_password" class="form-control" placeholder="password123">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Assign Course</label>
                            <select name="course_id" id="s_course" class="form-select">
                                <option value="">-- None --</option>
                                <?php foreach($courses as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Batch</label>
                            <input type="text" name="batch" id="s_batch" class="form-control" placeholder="e.g. Batch A">
                        </div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="s_active" checked>
                        <label class="form-check-label fw-bold">Active Account</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_student" class="btn btn-success px-4"><i class="fa-solid fa-save me-1"></i> Save Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const studentModal = new bootstrap.Modal(document.getElementById('studentModal'));

    window.openStudentModal = function() {
        document.getElementById('student_id').value = 0;
        document.getElementById('studentModalTitle').innerText = 'Add Student';
        document.getElementById('s_name').value = '';
        document.getElementById('s_email').value = '';
        document.getElementById('s_phone').value = '';
        document.getElementById('s_password').value = '';
        document.getElementById('s_course').value = '';
        document.getElementById('s_batch').value = '';
        document.getElementById('s_active').checked = true;
        studentModal.show();
    }

    window.editStudent = function(s) {
        document.getElementById('student_id').value = s.id;
        document.getElementById('studentModalTitle').innerText = 'Edit Student';
        document.getElementById('s_name').value = s.name || '';
        document.getElementById('s_email').value = s.email || '';
        document.getElementById('s_phone').value = s.phone || '';
        document.getElementById('s_password').value = '';
        document.getElementById('s_course').value = s.course_id || '';
        document.getElementById('s_batch').value = s.batch || '';
        document.getElementById('s_active').checked = s.is_active == 1;
        studentModal.show();
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
