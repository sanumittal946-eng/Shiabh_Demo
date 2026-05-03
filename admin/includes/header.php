<?php
// admin/includes/header.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

if (empty($_SESSION['admin_access'])) {
    header("Location: login.php");
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Sahib Classes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root { --primary: #1A3C6E; --accent: #F4A226; --sidebar-width: 250px; }
        body { font-family: 'Inter', sans-serif; background-color: #f4f6f9; }
        .sidebar { width: var(--sidebar-width); background: var(--primary); height: 100vh; position: fixed; color: #fff; z-index: 1000; display: flex; flex-direction: column; }
        .sidebar-nav { flex-grow: 1; overflow-y: auto; scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.2) transparent; }
        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 10px; }
        .main-content { margin-left: var(--sidebar-width); padding: 20px; }
        .nav-link { color: rgba(255,255,255,0.8); }
        .nav-link:hover, .nav-link.active { color: #fff; background: rgba(255,255,255,0.2); border-radius: 5px;}
        .stat-card { border-left: 4px solid var(--accent); }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar shadow-sm">
        <div class="p-3">
            <a href="index.php" class="d-flex align-items-center text-white text-decoration-none">
                <span class="fs-4 fw-bold">Sahib <span style="color:var(--accent)">Classes</span></span>
            </a>
            <hr class="mb-0">
        </div>

        <div class="sidebar-nav px-3">
            <ul class="nav nav-pills flex-column gap-1 py-3">
                <li class="nav-item">
                    <a href="index.php" class="nav-link <?= $current_page == 'index.php' ? 'active' : '' ?>"><i class="fa-solid fa-gauge me-2"></i> Dashboard</a>
                </li>
                <li><a href="students.php" class="nav-link <?= $current_page == 'students.php' ? 'active' : '' ?>"><i class="fa-solid fa-users me-2"></i> Students</a></li>
                <li><a href="courses.php" class="nav-link <?= $current_page == 'courses.php' ? 'active' : '' ?>"><i class="fa-solid fa-book me-2"></i> Courses</a></li>
                <li><a href="batches.php" class="nav-link <?= $current_page == 'batches.php' ? 'active' : '' ?>"><i class="fa-solid fa-clock me-2"></i> Upcoming Batches</a></li>
                <li><a href="enquiries.php" class="nav-link <?= $current_page == 'enquiries.php' ? 'active' : '' ?>"><i class="fa-regular fa-envelope me-2"></i> Enquiries</a></li>
                <li><a href="faculty.php" class="nav-link <?= $current_page == 'faculty.php' ? 'active' : '' ?>"><i class="fa-solid fa-chalkboard-user me-2"></i> Faculty</a></li>
                <li><a href="news.php" class="nav-link <?= $current_page == 'news.php' ? 'active' : '' ?>"><i class="fa-solid fa-newspaper me-2"></i> News & CMS</a></li>
                <hr class="text-secondary mx-3 my-1">
                <li><a href="materials.php" class="nav-link <?= $current_page == 'materials.php' ? 'active' : '' ?>"><i class="fa-solid fa-folder-open me-2"></i> Materials & Videos</a></li>
                <li><a href="timetable_admin.php" class="nav-link <?= $current_page == 'timetable_admin.php' ? 'active' : '' ?>"><i class="fa-solid fa-calendar-days me-2"></i> Timetable</a></li>
                <li><a href="lectures.php" class="nav-link <?= $current_page == 'lectures.php' ? 'active' : '' ?>"><i class="fa-solid fa-video me-2"></i> Lectures Manager</a></li>
                <li><a href="doubts.php" class="nav-link <?= $current_page == 'doubts.php' ? 'active' : '' ?>"><i class="fa-solid fa-comments me-2"></i> Doubt Resolution</a></li>
                <li><a href="tests.php" class="nav-link <?= $current_page == 'tests.php' || $current_page == 'test_submissions.php' ? 'active' : '' ?>"><i class="fa-solid fa-file-signature me-2"></i> Tests & Checking</a></li>
                <li><a href="notices.php" class="nav-link <?= $current_page == 'notices.php' ? 'active' : '' ?>"><i class="fa-solid fa-bullhorn me-2"></i> News Ticker</a></li>
                <li><a href="settings.php" class="nav-link <?= $current_page == 'settings.php' ? 'active' : '' ?>"><i class="fa-solid fa-gear me-2"></i> Site Settings</a></li>
            </ul>
        </div>

        <div class="p-3 mt-auto">
            <hr class="mt-0">
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle border p-2 rounded" data-bs-toggle="dropdown">
                    <i class="fa-solid fa-user-shield me-2"></i>
                    <strong><?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></strong>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                    <li><a class="dropdown-item" href="logout.php">Sign out</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="main-content">
