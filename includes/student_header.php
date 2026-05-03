<?php
// includes/student_header.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
checkStudentAuth();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal | Edu Institute</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a365d;
            --accent-color: #fb8500;
            --text-dark: #2b2b2b;
            --light-bg: #f8f9fa;
        }
        body { font-family: 'Inter', sans-serif; background-color: #f4f6f9; }
        h1, h2, h3, h4, h5, h6, .brand-font { font-family: 'Outfit', sans-serif; }
        .sidebar { min-height: 100vh; background-color: var(--primary-color); }
        .sidebar .nav-link { color: rgba(255,255,255,.8); font-weight: 500; transition: all 0.2s; border-radius: 5px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: var(--accent-color); color: white; }
        .sidebar-brand { color: white; text-decoration: none; font-size: 1.5rem; font-weight: bold; }
        .main-content { min-height: 100vh; padding-bottom: 2rem; }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar p-3 d-flex flex-column" style="width: 280px;">
            <a href="../" class="sidebar-brand mb-4 text-center">
                <i class="fa-solid fa-graduation-cap text-accent me-2"></i>Sahib Classes
            </a>
            
            <ul class="nav nav-pills flex-column mb-auto gap-1">
                <li><a href="dashboard.php" class="nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>"><i class="fa-solid fa-house me-2"></i> Dashboard</a></li>
                <li><a href="lectures.php" class="nav-link <?= $current_page == 'lectures.php' ? 'active' : '' ?>"><i class="fa-solid fa-play-circle me-2"></i> Lectures & Videos</a></li>
                <li><a href="materials.php" class="nav-link <?= $current_page == 'materials.php' ? 'active' : '' ?>"><i class="fa-solid fa-book-open-reader me-2"></i> Study Materials</a></li>
                <li><a href="timetable.php" class="nav-link <?= $current_page == 'timetable.php' ? 'active' : '' ?>"><i class="fa-solid fa-calendar-days me-2"></i> My Timetable</a></li>
                <li><a href="doubts.php" class="nav-link <?= $current_page == 'doubts.php' ? 'active' : '' ?>"><i class="fa-solid fa-circle-question me-2"></i> Ask a Doubt</a></li>
                <li><a href="tests.php" class="nav-link <?= $current_page == 'tests.php' ? 'active' : '' ?>"><i class="fa-solid fa-graduation-cap me-2"></i> Tests & Results</a></li>
                <li><a href="submit-review.php" class="nav-link <?= $current_page == 'submit-review.php' ? 'active' : '' ?>"><i class="fa-solid fa-star me-2"></i> Write a Review</a></li>
                <hr class="text-white opacity-25 mx-2 my-2">
                <li><a href="profile.php" class="nav-link <?= $current_page == 'profile.php' ? 'active' : '' ?>"><i class="fa-solid fa-user me-2"></i> My Profile</a></li>
            </ul>
            <hr>
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle border p-2 rounded" data-bs-toggle="dropdown">
                    <i class="fa-solid fa-user-graduate me-2"></i>
                    <strong><?= htmlspecialchars($_SESSION['student_name'] ?? 'Student') ?></strong>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                    <li><a class="dropdown-item" href="profile.php">Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="../logout.php">Sign out</a></li>
                </ul>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="main-content flex-grow-1 bg-light">
            <!-- Topbar mobile friendly -->
            <div class="d-md-none bg-primary text-white p-3 d-flex justify-content-between align-items-center mb-3">
                <span class="fs-4 fw-bold"><i class="fa-solid fa-graduation-cap text-accent me-2"></i>Sahib Classes</span>
                <a href="../logout.php" class="btn btn-sm btn-outline-light"><i class="fa-solid fa-power-off"></i></a>
            </div>
            
            <div class="p-4 p-md-5">
