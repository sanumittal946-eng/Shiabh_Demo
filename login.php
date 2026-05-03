<?php
// login.php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect if already logged in
if (!empty($_SESSION['student_id'])) {
    header("Location: student/dashboard.php");
    exit();
}

$db = getDB();
$err = $msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    
    // CSRF Check
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        $err = "Invalid security token. Please refresh and try again.";
    } else {
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        
        if (empty($email) || empty($password)) {
            $err = "Please enter email and password.";
        } else {
            $stmt = $db->prepare("SELECT id, name, password_hash, is_active FROM students WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $student = $stmt->fetch();
            
            if ($student && password_verify($password, $student['password_hash'])) {
                if ($student['is_active'] == 1) {
                    // Start Session
                    $_SESSION['student_id'] = $student['id'];
                    $_SESSION['student_name'] = $student['name'];
                    
                    // Update last login
                    $update = $db->prepare("UPDATE students SET last_login = CURRENT_TIMESTAMP WHERE id = :id");
                    $update->execute([':id' => $student['id']]);
                    
                    header("Location: student/dashboard.php");
                    exit();
                } else {
                    $err = "Your account is inactive. Please contact administration.";
                }
            } else {
                $err = "Invalid email or password.";
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="section-padding bg-light-grey" style="min-height: calc(100vh - 400px);">
    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7" data-aos="zoom-in">
                
                <div class="card shadow-lg border-0 rounded overflow-hidden">
                    <div class="card-header bg-primary text-white text-center p-4 py-5 border-0 position-relative" style="background-image: url('https://www.transparenttextures.com/patterns/cubes.png');">
                        <div class="bg-white rounded-circle d-inline-flex justify-content-center align-items-center shadow position-absolute" style="width: 70px; height: 70px; top: -35px; left: 50%; transform: translateX(-50%);">
                            <i class="fa-solid fa-user-graduate fs-2 text-primary"></i>
                        </div>
                        <h4 class="mb-1 mt-3">Student Portal</h4>
                        <p class="mb-0 text-light opacity-75 small">Login to access study materials and resources</p>
                    </div>
                    
                    <div class="card-body p-5">
                        <?php if(!empty($err)): ?>
                            <div class="alert alert-danger small py-2"><i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($err) ?></div>
                        <?php endif; ?>
                        
                        <form action="login.php" method="POST">
                            <?= csrfField() ?>
                            
                            <div class="mb-4">
                                <label class="form-label text-muted fw-bold small">Email Address</label>
                                <div class="input-group text-dark bg-white rounded border">
                                    <span class="input-group-text bg-transparent border-0"><i class="fa-regular fa-envelope text-muted"></i></span>
                                    <input type="email" name="email" class="form-control border-0 bg-transparent" placeholder="Enter your registered email" required autofocus>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label text-muted fw-bold small mb-0">Password</label>
                                    <a href="#!" class="small text-accent text-decoration-none" onclick="alert('Please contact admin to reset password.')">Forgot Password?</a>
                                </div>
                                <div class="input-group text-dark bg-white rounded border">
                                    <span class="input-group-text bg-transparent border-0"><i class="fa-solid fa-lock text-muted"></i></span>
                                    <input type="password" name="password" class="form-control border-0 bg-transparent" placeholder="••••••••" required>
                                </div>
                            </div>
                            
                            <div class="mb-4 form-check">
                                <input type="checkbox" class="form-check-input" id="rememberMe">
                                <label class="form-check-label small text-muted" for="rememberMe">Remember me on this device</label>
                            </div>
                            
                            <div class="d-grid mt-4">
                                <button type="submit" name="login" class="btn btn-primary btn-lg rounded-pill fw-bold text-uppercase shadow-sm">Secure Login <i class="fa-solid fa-arrow-right-to-bracket ms-2"></i></button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="card-footer bg-light text-center py-3 border-top-0">
                        <p class="text-muted small mb-0">Don't have an account? <span class="fw-bold text-primary">Registration is managed by admin.</span></p>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
