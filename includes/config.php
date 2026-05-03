<?php
// includes/config.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Detect Environment (Local vs Live)
$is_local = ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['SERVER_ADDR'] === '127.0.0.1' || strpos($_SERVER['HTTP_HOST'], '192.168') !== false);

if ($is_local) {
    // --- LOCAL SETTINGS (XAMPP) ---
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'edu_focus');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    // --- LIVE SETTINGS (INFINITYFREE) ---
    define('DB_HOST', 'sql207.infinityfree.com'); 
    define('DB_NAME', 'if0_41820914_XXX'); 
    define('DB_USER', 'if0_41820914'); 
    define('DB_PASS', 'YqQOYtWuz3e'); 
    
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Application Constraints
define('SITE_NAME', 'Sahib Classes');

// --- Dynamic BASE_URL: handles local subfolders and live domains automatically ---
if (!defined('BASE_URL')) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // For local XAMPP, we might be in /edu_website/
    if ($is_local) {
        $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        $root = rtrim(str_replace('\\', '/', $scriptDir), '/');
        // Walk up if in subdirectories
        if (preg_match('#/(admin|student)$#', $root)) {
            $root = dirname($root);
        }
        define('BASE_URL', $scheme . '://' . $host . $root);
    } else {
        // For live, we usually use the domain root
        define('BASE_URL', $scheme . '://' . $host);
    }
}

// Timezone
date_default_timezone_set('Asia/Kolkata');

// CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$siteSettings = [];
?>