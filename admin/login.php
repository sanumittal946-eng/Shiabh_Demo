<?php
// admin/login.php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// If already logged in, go to dashboard
if (!empty($_SESSION['admin_access'])) {
    header("Location: index.php");
    exit();
}

$err = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admin_login'])) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        $err = "Invalid security token.";
    } else {
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        if (empty($email) || empty($password)) {
            $err = "Please enter email and password.";
        } else {
            $db = getDB();
            $stmt = $db->prepare("SELECT id, username, password_hash FROM admins WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                $_SESSION['admin_access'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                header("Location: index.php");
                exit;
            } else {
                $err = "Invalid email or password.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Sahib Classes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>body { font-family: 'Inter', sans-serif; background-color: #f4f6f9; }</style>
</head>
<body>
    <div class="d-flex align-items-center justify-content-center vh-100 bg-light">
        <div class="card p-5 shadow border-0" style="max-width: 400px; width:100%;">
            <div class="text-center mb-4">
                <i class="fa-solid fa-shield-halved text-primary fs-1"></i>
                <h4 class="mt-3 text-primary fw-bold">Admin Portal</h4>
            </div>
            <?php if(!empty($err)): ?>
                <div class="alert alert-danger small py-2"><i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($err) ?></div>
            <?php endif; ?>
            <form method="POST" action="login.php">
                <?= csrfField() ?>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="admin@example.com" required>
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
                <button type="submit" name="admin_login" class="btn btn-primary w-100 fw-bold">Secure Login</button>
            </form>
            <div class="text-center mt-3">
                <a href="../index.php" class="text-muted small text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i> Back to Website</a>
            </div>
        </div>
    </div>
</body>
</html>
