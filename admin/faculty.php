<?php
// admin/faculty.php
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$msg = "";
$err = "";

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $db->prepare("DELETE FROM faculty WHERE id = :id");
    if ($stmt->execute([':id' => $id])) {
        $msg = "Faculty member deleted successfully.";
    }
}

// Handle Add / Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_faculty'])) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        $err = "Invalid security token.";
    } else {
        $id               = (int)($_POST['faculty_id'] ?? 0);
        $name             = trim($_POST['name']);
        $designation      = trim($_POST['designation']);
        $subject          = trim($_POST['subject']);
        $qualification    = trim($_POST['qualification']);
        $experience_years = (int)$_POST['experience_years'];

        if (empty($name) || empty($designation) || empty($subject)) {
            $err = "Name, Designation, and Subject are required.";
        } else {
            if ($id > 0) {
                $stmt = $db->prepare("UPDATE faculty SET name=:name, designation=:desig, subject=:sub, qualification=:qual, experience_years=:exp WHERE id=:id");
                $ok = $stmt->execute([':name'=>$name,':desig'=>$designation,':sub'=>$subject,':qual'=>$qualification,':exp'=>$experience_years,':id'=>$id]);
                $msg = $ok ? "Faculty updated successfully!" : "Database error.";
            } else {
                $stmt = $db->prepare("INSERT INTO faculty (name, designation, subject, qualification, experience_years) VALUES (:name, :desig, :sub, :qual, :exp)");
                if ($stmt->execute([':name'=>$name,':desig'=>$designation,':sub'=>$subject,':qual'=>$qualification,':exp'=>$experience_years])) {
                    $msg = "Faculty added successfully!";
                } else {
                    $err = "Database error occurred.";
                }
            }
        }
    }
}

$faculty = $db->query("SELECT * FROM faculty ORDER BY id DESC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 shadow-sm rounded">
    <h4 class="mb-0 text-dark fw-bold">Manage Faculty</h4>
    <button class="btn btn-primary" onclick="openFacultyModal()"><i class="fa-solid fa-plus me-1"></i> Add Faculty</button>
</div>

<?php if (!empty($msg)): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-check-circle me-2"></i><?= htmlspecialchars($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (!empty($err)): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($err) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Name &amp; Designation</th>
                        <th>Subject</th>
                        <th>Qualifications</th>
                        <th>Exp.</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($faculty)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No faculty members found.</td></tr>
                    <?php else: foreach($faculty as $f): ?>
                        <tr>
                            <td><?= $f['id'] ?></td>
                            <td>
                                <div class="fw-bold text-primary"><?= htmlspecialchars($f['name']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($f['designation']) ?></small>
                            </td>
                            <td><span class="badge bg-info text-dark"><?= htmlspecialchars($f['subject']) ?></span></td>
                            <td><?= htmlspecialchars($f['qualification']) ?></td>
                            <td><?= $f['experience_years'] ?> yrs</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1" onclick='editFaculty(<?= json_encode($f) ?>)'><i class="fa-solid fa-pen"></i></button>
                                <a href="faculty.php?delete=<?= $f['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this faculty member?');"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add / Edit Modal -->
<div class="modal fade" id="facultyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="faculty_id" id="faculty_id" value="0">
                <div class="modal-header">
                    <h5 class="modal-title" id="facultyModalTitle">Add Faculty</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Full Name *</label>
                            <input type="text" name="name" id="f_name" class="form-control" required placeholder="Dr. XYZ">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Designation *</label>
                            <input type="text" name="designation" id="f_designation" class="form-control" required placeholder="Senior Lecturer">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Subject / Department *</label>
                            <input type="text" name="subject" id="f_subject" class="form-control" required placeholder="Physics">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Qualifications</label>
                            <input type="text" name="qualification" id="f_qualification" class="form-control" placeholder="M.Sc, B.Ed">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Years of Experience</label>
                            <input type="number" name="experience_years" id="f_experience" class="form-control" placeholder="10" min="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_faculty" class="btn btn-success px-4"><i class="fa-solid fa-save me-1"></i> Save Faculty</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const facultyModal = new bootstrap.Modal(document.getElementById('facultyModal'));

    window.openFacultyModal = function() {
        document.getElementById('faculty_id').value = 0;
        document.getElementById('facultyModalTitle').innerText = 'Add Faculty';
        document.getElementById('f_name').value = '';
        document.getElementById('f_designation').value = '';
        document.getElementById('f_subject').value = '';
        document.getElementById('f_qualification').value = '';
        document.getElementById('f_experience').value = '';
        facultyModal.show();
    }

    window.editFaculty = function(f) {
        document.getElementById('faculty_id').value = f.id;
        document.getElementById('facultyModalTitle').innerText = 'Edit Faculty';
        document.getElementById('f_name').value = f.name || '';
        document.getElementById('f_designation').value = f.designation || '';
        document.getElementById('f_subject').value = f.subject || '';
        document.getElementById('f_qualification').value = f.qualification || '';
        document.getElementById('f_experience').value = f.experience_years || 0;
        facultyModal.show();
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
