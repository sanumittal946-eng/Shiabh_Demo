<?php
// includes/config.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Detect Environment
$is_railway = getenv('RAILWAY_ENVIRONMENT') !== false || getenv('MYSQLHOST') !== false;
$is_local = !$is_railway && ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['SERVER_ADDR'] === '127.0.0.1');

if ($is_railway) {
    // --- RAILWAY (LIVE) ---
    define('DB_HOST', getenv('MYSQLHOST') ?: 'sql207.infinityfree.com'); 
    define('DB_NAME', getenv('MYSQLDATABASE') ?: 'if0_41820914_XXX'); 
    define('DB_USER', getenv('MYSQLUSER') ?: 'if0_41820914'); 
    define('DB_PASS', getenv('MYSQLPASSWORD') ?: 'YqQOYtWuz3e'); 
    define('DB_PORT', getenv('MYSQLPORT') ?: '3306');
    
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    // --- LOCAL (XAMPP) ---
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'edu_focus');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_PORT', '3306');
    
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

define('SITE_NAME', 'Sahib Classes');

if (!defined('BASE_URL')) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    if ($is_local) {
        $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        $root = rtrim(str_replace('\\', '/', $scriptDir), '/');
        if (preg_match('#/(admin|student)$#', $root)) { $root = dirname($root); }
        define('BASE_URL', $scheme . '://' . $host . $root);
    } else {
        define('BASE_URL', $scheme . '://' . $host);
    }
}

date_default_timezone_set('Asia/Kolkata');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>