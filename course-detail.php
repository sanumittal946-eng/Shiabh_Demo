<?php
// course-detail.php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    header("Location: courses.php");
    exit();
}

$db = getDB();

// Fetch course details
$stmt = $db->prepare("SELECT * FROM courses WHERE id = :id");
$stmt->execute([':id' => $id]);
$course = $stmt->fetch();

if (!$course) {
    echo "Course not found.";
    exit();
}

// Use dummy json if missing to ensure UI works
$syllabusArray = [];
if (!empty($course['syllabus_json'])) {
    $syllabusArray = json_decode($course['syllabus_json'], true);
}
if (empty($syllabusArray)) {
    $syllabusArray = [
        ['title' => 'Module 1: Introduction to Concepts', 'content' => 'Basics, fundamentals and background theory.'],
        ['title' => 'Module 2: Advanced Topics', 'content' => 'Deep dive into complex equations and logical reasoning.'],
        ['title' => 'Module 3: Practice & Revision', 'content' => 'Applying concepts to test papers and past exam patterns.']
    ];
}

// Fetch related courses
$relatedStmt = $db->prepare("SELECT id, name, fee, duration FROM courses WHERE category = :cat AND id != :id LIMIT 3");
$relatedStmt->execute([':cat' => $course['category'], ':id' => $id]);
$related = $relatedStmt->fetchAll();

// Include header after setting meta description if needed
require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<section class="banner-section bg-primary text-white py-5 position-relative" style="background-image: url('https://www.transparenttextures.com/patterns/diagmonds-light.png');">
    <div class="container py-4 position-relative" style="z-index: 2;">
        <span class="badge bg-accent mb-3 px-3 py-2 ms-1 rounded-pill"><?= htmlspecialchars($course['category']) ?></span>
        <h1 class="display-5 fw-bold text-white mb-3" data-aos="fade-right"><?= htmlspecialchars($course['name']) ?></h1>
        <ul class="list-inline text-light opacity-75 mb-0" data-aos="fade-up">
            <li class="list-inline-item me-4"><i class="fa-regular fa-clock me-2"></i><?= htmlspecialchars($course['duration']) ?></li>
            <li class="list-inline-item me-4"><i class="fa-solid fa-laptop-house me-2"></i><?= htmlspecialchars($course['mode']) ?></li>
            <li class="list-inline-item"><i class="fa-solid fa-indian-rupee-sign me-2"></i><?= number_format($course['fee']) ?></li>
        </ul>
    </div>
</section>

<!-- Content Area -->
<section class="section-padding bg-light-grey">
    <div class="container">
        <div class="row g-5">
            <!-- Left Main Content -->
            <div class="col-lg-8" data-aos="fade-right">
                <div class="card p-4 border-0 shadow-sm mb-4">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs mb-4 border-bottom-0 gap-2" id="courseTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active rounded border-0 bg-light" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">Overview</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded border-0 bg-light" id="syllabus-tab" data-bs-toggle="tab" data-bs-target="#syllabus" type="button" role="tab">Syllabus</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded border-0 bg-light" id="schedule-tab" data-bs-toggle="tab" data-bs-target="#schedule" type="button" role="tab">Schedule</button>
                        </li>
                    </ul>
                    <!-- Tab panes -->
                    <div class="tab-content text-muted" id="courseTabsContent">
                        <!-- Overview -->
                        <div class="tab-pane fade show active" id="overview" role="tabpanel">
                            <h4 class="text-primary mb-3">Course Description</h4>
                            <p><?= nl2br(htmlspecialchars($course['description'] ?? 'No description available for this course.')) ?></p>
                            
                            <h5 class="text-primary mt-4 mb-3">Who is this course for?</h5>
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="fa-solid fa-angle-right text-accent me-2"></i> Students preparing for <?= htmlspecialchars($course['category']) ?> exams.</li>
                                <li class="mb-2"><i class="fa-solid fa-angle-right text-accent me-2"></i> Those looking for absolute conceptual clarity.</li>
                                <li class="mb-2"><i class="fa-solid fa-angle-right text-accent me-2"></i> Candidates aiming for top ranks and premium colleges.</li>
                            </ul>
                        </div>

                        <!-- Syllabus -->
                        <div class="tab-pane fade" id="syllabus" role="tabpanel">
                            <h4 class="text-primary mb-4">Course Syllabus</h4>
                            <div class="accordion" id="syllabusAccordion">
                                <?php foreach($syllabusArray as $i => $mod): ?>
                                <div class="accordion-item border-0 mb-3 bg-light rounded shadow-sm overflow-hidden">
                                    <h2 class="accordion-header" id="heading<?= $i ?>">
                                        <button class="accordion-button <?= $i != 0 ? 'collapsed' : '' ?> bg-white fw-bold text-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $i ?>" aria-expanded="<?= $i == 0 ? 'true' : 'false' ?>">
                                            <?= htmlspecialchars($mod['title']) ?>
                                        </button>
                                    </h2>
                                    <div id="collapse<?= $i ?>" class="accordion-collapse collapse <?= $i == 0 ? 'show' : '' ?>" data-bs-parent="#syllabusAccordion">
                                        <div class="accordion-body bg-white border-top">
                                            <?= nl2br(htmlspecialchars($mod['content'])) ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Schedule -->
                        <div class="tab-pane fade" id="schedule" role="tabpanel">
                             <h4 class="text-primary mb-3">Batch Strategy</h4>
                             <p>Batch Timings: <?= htmlspecialchars($course['batch_timing'] ?? 'Flexible / Check with Admin') ?></p>
                             <p>This program is designed perfectly balancing school/college timings and self-study hours. Connect with our admission desk for current schedules.</p>
                             <a href="timetable.php" class="btn btn-outline-primary btn-sm mt-2">View Full Timetable</a>
                        </div>
                    </div>
                </div>

                <!-- Related Courses -->
                <?php if(!empty($related)): ?>
                <h4 class="text-primary fw-bold mb-3 mt-5">Related Courses</h4>
                <div class="row g-3">
                    <?php foreach($related as $rel): ?>
                    <div class="col-md-6">
                        <div class="card p-3 border-0 shadow-sm h-100">
                            <h5 class="fs-6 text-primary mb-2"><?= htmlspecialchars($rel['name']) ?></h5>
                            <div class="d-flex justify-content-between align-items-center mb-0 text-muted small">
                                <span><i class="fa-regular fa-clock me-1 text-accent"></i> <?= htmlspecialchars($rel['duration']) ?></span>
                                <span><i class="fa-solid fa-indian-rupee-sign me-1 text-accent"></i> <?= number_format($rel['fee']) ?></span>
                            </div>
                            <a href="course-detail.php?id=<?= $rel['id'] ?>" class="stretched-link"></a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right Sidebar -->
            <div class="col-lg-4" data-aos="fade-left">
                <!-- Action Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4 text-center">
                        <h2 class="text-primary fw-bold mb-1">₹<?= number_format($course['fee']) ?></h2>
                        <p class="text-muted small mb-4">One-time payment</p>
                        
                        <a href="admission.php?course_id=<?= $course['id'] ?>" class="btn btn-accent w-100 fw-bold py-2 mb-3 shadow text-white">Enrol Now</a>
                        
                        <?php if(!empty($course['brochure_pdf'])): ?>
                            <a href="<?= htmlspecialchars($course['brochure_pdf']) ?>" target="_blank" class="btn btn-outline-primary w-100 fw-bold py-2"><i class="fa-solid fa-download me-2"></i> Download Brochure</a>
                        <?php else: ?>
                            <button class="btn btn-outline-primary w-100 fw-bold py-2" onclick="alert('Brochure is not available for this course yet.')"><i class="fa-solid fa-download me-2"></i> Download Brochure</button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Inquiry Form -->
                <div class="card border-0 shadow-sm bg-primary text-white">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold mb-3 text-accent"><i class="fa-regular fa-circle-question me-2"></i> Have Questions?</h5>
                        <p class="small opacity-75 mb-4">Fill this quick form and our experts will call you back.</p>
                        
                        <form action="contact.php" method="GET">
                            <input type="hidden" name="course_interest" value="<?= htmlspecialchars($course['name']) ?>">
                            <div class="mb-3">
                                <input type="text" class="form-control form-control-sm border-0" placeholder="Your Name" required>
                            </div>
                            <div class="mb-3">
                                <input type="tel" class="form-control form-control-sm border-0" placeholder="Phone Number" required>
                            </div>
                            <button type="submit" class="btn btn-accent btn-sm w-100 text-white fw-bold">Request Callback</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Include style for the tab buttons active state -->
<style>
.nav-tabs .nav-link.active {
    background-color: var(--primary-color) !important;
    color: white !important;
}
.nav-tabs .nav-link {
    color: var(--text-dark);
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
