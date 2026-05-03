<?php
// includes/functions.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

/**
 * Fetch all site settings from DB once.
 */
function getSiteSettings() {
    global $siteSettings;
    if (!isset($siteSettings) || !is_array($siteSettings)) {
        $siteSettings = [];
    }
    
    if (empty($siteSettings)) {
        try {
            $db = getDB();
            if ($db) {
                $stmt = $db->query("SELECT setting_key, setting_value FROM site_settings");
                if ($stmt) {
                    $rows = $stmt->fetchAll();
                    foreach ($rows as $row) {
                        $siteSettings[$row['setting_key']] = $row['setting_value'];
                    }
                }
            }
        } catch (\Throwable $e) {
            // Silently fail if table doesn't exist yet
            return [];
        }
    }
    return is_array($siteSettings) ? $siteSettings : [];
}

/**
 * Get a specific setting value
 */
function getSetting($key, $default = '') {
    $settings = getSiteSettings();
    return $settings[$key] ?? $default;
}

/**
 * Sanitize user input securely
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Check CSRF token
 */
function verifyCSRF($token) {
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

/**
 * Render standard CSRF field
 */
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
}

/**
 * Verify Student is Logged In
 */
function checkStudentAuth() {
    if (empty($_SESSION['student_id'])) {
        header("Location: login.php");
        exit();
    }
}

/**
 * Check Honeypot for forms
 */
function checkHoneypot($fieldValue) {
    if (!empty($fieldValue)) {
        die("Invalid request"); // Bot triggered
    }
}
?>
