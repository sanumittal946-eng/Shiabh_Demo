<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/functions.php';

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= getSetting('tagline', 'Educational and Coaching Institute') ?>">
    <?php echo "<!-- DEBUG: TAGLINE CHECK -->"; ?>
    <title><?= getSetting('site_name', 'Sahib Classes') ?> -
        <?= ucfirst($currentPage == 'index' ? 'Home' : $currentPage) ?></title>
    <?php echo "<!-- DEBUG: TITLE CHECK -->"; ?>

    <!-- Open Graph Tags -->
    <meta property="og:title" content="<?= getSetting('site_name', 'Sahib Classes') ?>">
    <meta property="og:description" content="<?= getSetting('tagline', 'Empowering Your Future Through Education') ?>">
    <meta property="og:type" content="website">

    <!-- Canonical URL -->
    <link rel="canonical" href="<?= BASE_URL . '/' . basename($_SERVER['PHP_SELF']) ?>">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        crossorigin="anonymous">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@500;600;700;800&display=swap"
        rel="stylesheet">
    <!-- AOS Animation CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>

<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm py-3">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <span class="text-primary"><i
                        class="fa-solid fa-graduation-cap me-2"></i><?= getSetting('site_name', 'Sahib Classes') ?></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage == 'index' ? 'active' : '' ?>" href="./">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage == 'about' ? 'active' : '' ?>" href="about">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage == 'courses' ? 'active' : '' ?>"
                            href="courses">Courses</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage == 'admission' ? 'active' : '' ?>"
                            href="admission">Admission</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage == 'faculty' ? 'active' : '' ?>"
                            href="faculty">Faculty</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            More
                        </a>
                        <ul class="dropdown-menu border-0 shadow" aria-labelledby="navbarDropdown">
                            <?php if (!empty($_SESSION['student_id'])): ?>
                                <li><a class="dropdown-item fw-bold text-primary" href="student/dashboard"><i class="fa-solid fa-gauge me-2"></i>My Dashboard</a></li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item <?= $currentPage == 'timetable' ? 'active' : '' ?>"
                                    href="timetable">Timetable</a></li>
                            <li><a class="dropdown-item <?= $currentPage == 'results' ? 'active' : '' ?>"
                                    href="results">Results</a></li>
                            <li><a class="dropdown-item <?= $currentPage == 'testimonials' ? 'active' : '' ?>"
                                    href="testimonials">Testimonials</a></li>
                            <li><a class="dropdown-item <?= $currentPage == 'news' ? 'active' : '' ?>"
                                    href="news">News & Events</a></li>
                            <li><a class="dropdown-item <?= $currentPage == 'materials' ? 'active' : '' ?>"
                                    href="materials">Study Materials</a></li>
                            <li><a class="dropdown-item <?= $currentPage == 'faq' ? 'active' : '' ?>"
                                    href="faq">FAQ</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage == 'contact' ? 'active' : '' ?>" href="contact">Contact
                            Us</a>
                    </li>
                </ul>
                <div class="d-flex ms-lg-3 mt-3 mt-lg-0">
                    <a href="admission"
                        class="btn btn-accent rounded-pill px-4 fw-semibold text-white shadow-sm">Enrol Now <i
                            class="fa-solid fa-arrow-right ms-2 btn-icon"></i></a>
                </div>
                <?php if (empty($_SESSION['student_id'])): ?>
                    <div class="d-flex ms-lg-2 mt-2 mt-lg-0">
                        <a href="login" class="btn btn-outline-primary rounded-pill px-3 fw-semibold">Login</a>
                    </div>
                <?php else: ?>
                    <div class="d-flex ms-lg-2 mt-2 mt-lg-0">
                        <a href="logout" class="btn btn-outline-danger rounded-pill px-3 fw-semibold">Logout</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>