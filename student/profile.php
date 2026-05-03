<?php
// profile.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/student_header.php';

$db = getDB();
$student_id = $_SESSION['student_id'];
$err = $msg = "";

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        $err = "Invalid security token.";
    } else {
        $phone = trim($_POST['phone']);
        $email = trim($_POST['email']);
        
        if (empty($email) || empty($phone)) {
            $err = "Email and Phone are required.";
        } else {
            $stmt = $db->prepare("UPDATE students SET phone = :phone, email = :email WHERE id = :id");
            if ($stmt->execute([':phone' => $phone, ':email' => $email, ':id' => $student_id])) {
                $msg = "Profile updated successfully!";
            } else {
                $err = "Error updating profile. Email might already be in use.";
            }
        }
    }
}

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        $err = "Invalid security token.";
    } else {
        $current = $_POST['current_password'];
        $new = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];
        
        $chk = $db->prepare("SELECT password_hash FROM students WHERE id = :id");
        $chk->execute([':id' => $student_id]);
        $current_hash = $chk->fetchColumn();
        
        if (!password_verify($current, $current_hash)) {
            $err = "Current password is incorrect.";
        } elseif ($new !== $confirm) {
            $err = "New passwords do not match.";
        } elseif (strlen($new) < 6) {
            $err = "Password must be at least 6 characters.";
        } else {
            $new_hash = password_hash($new, PASSWORD_DEFAULT);
            $db->prepare("UPDATE students SET password_hash = :hash WHERE id = :id")->execute([':hash' => $new_hash, ':id' => $student_id]);
            $msg = "Password updated successfully!";
        }
    }
}

// Fetch current student data
$stmt = $db->prepare("SELECT s.*, c.name as course_name FROM students s LEFT JOIN courses c ON s.course_id = c.id WHERE s.id = :id");
$stmt->execute([':id' => $student_id]);
$student = $stmt->fetch();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 text-dark fw-bold">My Profile Settings</h4>
</div>

<?php if (!empty($msg)): ?>
    <div class="alert alert-success"><i class="fa-solid fa-check-circle me-2"></i><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<?php if (!empty($err)): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($err) ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Personal Information -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 p-4">
                <h5 class="mb-0 fw-bold text-primary">Personal Details</h5>
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <?= csrfField() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">Full Name</label>
                            <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($student['name'] ?? '') ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">Enrolled Course</label>
                            <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($student['course_name'] ?? 'N/A') ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">Email Address *</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($student['email'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">Phone Number *</label>
                            <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($student['phone'] ?? '') ?>" required>
                        </div>
                        <div class="col-12 mt-4 text-end">
                            <button type="submit" name="update_profile" class="btn btn-primary px-4"><i class="fa-solid fa-save me-1"></i> Update Details</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Registered Info (ReadOnly) -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                 <h5 class="mb-3 fw-bold small text-uppercase text-muted border-bottom pb-2">Academic Information</h5>
                 <div class="row text-center g-3">
                     <div class="col-4">
                         <div class="small text-muted">Student ID</div>
                         <div class="fw-bold fs-5 text-primary">#<?= $student['id'] ?></div>
                     </div>
                     <div class="col-4">
                         <div class="small text-muted">Enroll Date</div>
                         <div class="fw-bold"><?= date('M Y', strtotime($student['created_at'])) ?></div>
                     </div>
                     <div class="col-4">
                         <div class="small text-muted">Batch</div>
                         <div class="fw-bold"><?= htmlspecialchars($student['batch'] ?? 'General') ?></div>
                     </div>
                 </div>
            </div>
        </div>
    </div>

    <!-- Security Settings -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm border-top border-4 border-accent">
            <div class="card-header bg-white border-0 p-4">
                <h5 class="mb-0 fw-bold text-dark">Change Password</h5>
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">New Password</label>
                        <input type="password" name="new_password" class="form-control" required minlength="6">
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <div class="d-grid shadow-sm">
                        <button type="submit" name="change_password" class="btn btn-accent text-white fw-bold">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="mt-4 p-3 bg-white rounded shadow-sm">
            <div class="d-flex align-items-center">
                <div class="bg-danger bg-opacity-10 p-3 rounded-circle me-3">
                    <i class="fa-solid fa-shield-halved text-danger"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold text-dark">Account Status</h6>
                    <small class="text-success fw-bold">Active & Verified</small>
                </div>
            </div>
        </div>
    </div>
</div>

        </div> <!-- End p-4 p-md-5 -->
    </div> <!-- End main-content -->
</div> <!-- End d-flex -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
