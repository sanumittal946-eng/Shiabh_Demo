<?php
// includes/config.sample.php
// Rename this file to config.php and update the credentials

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'edu_focus');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application Constraints
define('SITE_NAME', 'Sahib Classes');

// --- Dynamic BASE_URL: works on localhost, localtunnel, live server ---
if (!defined('BASE_URL')) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    $root = rtrim(str_replace('\\', '/', $scriptDir), '/');
    if (preg_match('#/(admin|student)$#', $root)) {
        $root = dirname($root);
    }
    define('BASE_URL', $scheme . '://' . $host . $root);
}

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Error reporting mapping
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$siteSettings = [];
?>
