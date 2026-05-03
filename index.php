<?php
// index.php
require_once __DIR__ . '/includes/config.php';
echo "<!-- DEBUG: CONFIG LOADED -->";
require_once __DIR__ . '/includes/db.php';
echo "<!-- DEBUG: DB LOADED -->";
require_once __DIR__ . '/includes/header.php';
echo "<!-- DEBUG: HEADER LOADED -->";

$db = getDB();

// Fetch Data with fallback for empty/missing tables
try {
    $notices = $db->query("SELECT title, link, icon FROM notices WHERE is_active = 1 ORDER BY sort_order ASC, id DESC LIMIT 10")->fetchAll();
} catch (\Throwable $e) { $notices = []; }

try {
    $rawStats = $db->query("SELECT stat_key, stat_value FROM site_stats")->fetchAll();
} catch (\Throwable $e) { $rawStats = []; }

$stats = [];
foreach ($rawStats as $row) {
    preg_match_all('/\d+/', $row['stat_value'], $matches);
    $num = $matches[0][0] ?? 0;
    $stats[$row['stat_key']] = ['num' => $num, 'suffix' => str_replace($num, '', $row['stat_value'])];
}

try {
    $featuredCourses = $db->query("SELECT id, name, category, duration, fee FROM courses WHERE is_featured = 1 ORDER BY sort_order ASC LIMIT 6")->fetchAll();
} catch (\Throwable $e) { $featuredCourses = []; }

try {
    $batches = $db->query("SELECT b.start_date, b.timing, b.mode, c.name as course_name FROM batches b JOIN courses c ON b.course_id = c.id WHERE b.is_active = 1 AND b.start_date >= CURDATE() ORDER BY b.start_date ASC LIMIT 3")->fetchAll();
} catch (\Throwable $e) { $batches = []; }

try {
    $testimonials = $db->query("SELECT name, course, review, rating, photo FROM testimonials WHERE is_active = 1 LIMIT 3")->fetchAll();
} catch (\Throwable $e) { $testimonials = []; }

try {
    $newsItems = $db->query("SELECT id, title, published_at FROM news WHERE status = 'published' ORDER BY published_at DESC LIMIT 3")->fetchAll();
} catch (\Throwable $e) { $newsItems = []; }

// Why Choose Us Data
$why_us = [
    ['icon' => 'fa-user-tie', 'title' => 'Expert Faculty', 'desc' => 'Learn from top educators with extensive teaching experience.'],
    ['icon' => 'fa-users', 'title' => 'Small Batches', 'desc' => 'Personalized attention by maintaining a low student-teacher ratio.'],
    ['icon' => 'fa-trophy', 'title' => 'Proven Results', 'desc' => 'Consistent track record of producing toppers year on year.'],
    ['icon' => 'fa-wallet', 'title' => 'Affordable Fees', 'desc' => 'Quality education at highly competitive pricing structures.'],
    ['icon' => 'fa-laptop-house', 'title' => 'Flexible Learning', 'desc' => 'Choose full classroom teaching or join our hybrid online batches.'],
    ['icon' => 'fa-hand-holding-heart', 'title' => 'Personal Attention', 'desc' => 'Regular doubt clearing sessions and parent-teacher meetings.']
];

// Enrol Steps Data
$steps = [
    ['n' => 1, 't' => 'Register Online', 'd' => 'Fill the admission form on our website with valid details.'],
    ['n' => 2, 't' => 'Counseling', 'd' => 'Our expert counsellors will suggest the right batch for you.'],
    ['n' => 3, 't' => 'Fee Payment', 'd' => 'Submit documents and complete the payment process.'],
    ['n' => 4, 't' => 'Start Learning', 'd' => 'Get access to materials and attend your first class!']
];
?>

<!-- Hero -->
<section class="hero-section">
    <div class="container">
        <div class="row"><div class="col-lg-8 hero-content" data-aos="fade-up">
            <span class="badge bg-accent px-3 py-2 mb-3 fs-6 rounded-pill">Welcome to <?= getSetting('site_name') ?></span>
            <h1 class="mb-4">Shape Your Destiny With <br><span class="text-accent">Expert Guidance</span></h1>
            <p class="lead mb-4 opacity-75">Join the best minds. We provide comprehensive coaching for board exams and competitive entrance tests with a track record of producing toppers.</p>
            <div class="d-flex flex-wrap gap-3">
                <a href="admission" class="btn btn-accent btn-lg rounded-pill px-4 fw-bold">Enrol Now</a>
                <a href="courses" class="btn btn-outline-light btn-lg rounded-pill px-4 fw-bold background-transparent border-2 border-white">View Courses</a>
            </div>
        </div></div>
    </div>
</section>

<!-- Announcement Bar -->
<?php if ($notices): ?>
<div class="announcement-bar d-flex align-items-center">
    <div class="container d-flex overflow-hidden">
        <div class="badge bg-dark me-3 rounded-0 px-3 py-2 text-uppercase" style="z-index: 2; white-space:nowrap;">Latest Updates</div>
        <marquee behavior="scroll" direction="left" onmouseover="this.stop();" onmouseout="this.start();" class="pt-1">
            <?php foreach ($notices as $n): ?>
                <a href="<?= $n['link'] ?: '#' ?>"><i class="fa-solid <?= $n['icon'] ?: 'fa-bell' ?> me-1"></i><?= htmlspecialchars($n['title']) ?></a>&nbsp;&nbsp;&nbsp;
            <?php endforeach; ?>
        </marquee>
    </div>
</div>
<?php endif; ?>

<!-- Stats Counter -->
<section class="section-padding bg-white">
    <div class="container"><div class="row g-4 text-center">
        <?php foreach ($stat_items as $idx => $item): ?>
        <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="<?= ($idx+1)*100 ?>">
            <div class="display-4 fw-bold text-primary mb-2">
                <span class="counter" data-target="<?= $stats[$item['key']]['num'] ?? 0 ?>">0</span><?= $stats[$item['key']]['suffix'] ?? '+' ?>
            </div>
            <p class="text-muted fw-semibold"><?= $item['label'] ?></p>
        </div>
        <?php endforeach; ?>
    </div></div>
</section>

<!-- Featured Courses -->
<section class="section-padding bg-light-grey">
    <div class="container">
        <div class="section-title"><h2 data-aos="fade-up">Featured Courses</h2><p class="text-muted" data-aos="fade-up" data-aos-delay="100">Explore our most popular and successful programs</p></div>
        <div class="row g-4">
            <?php foreach ($featuredCourses as $course): ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up">
                <div class="card h-100 p-4 border-0 shadow-sm">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary text-white p-3 rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;"><i class="fa-solid fa-book-open fs-4"></i></div>
                        <div class="ms-3"><span class="badge bg-secondary mb-1"><?= htmlspecialchars($course['category']) ?></span><h4 class="mb-0 fs-5"><?= htmlspecialchars($course['name']) ?></h4></div>
                    </div>
                    <ul class="list-unstyled text-muted small mb-4">
                        <li class="mb-2"><i class="fa-regular fa-clock me-2 text-accent"></i>Duration: <?= htmlspecialchars($course['duration']) ?></li>
                        <li class="mb-2"><i class="fa-solid fa-indian-rupee-sign me-2 text-accent"></i>Fee: ₹<?= number_format($course['fee']) ?></li>
                    </ul>
                    <div class="d-flex mt-auto justify-content-between">
                        <a href="course-detail?id=<?= $course['id'] ?>" class="btn btn-outline-primary btn-sm">Details</a>
                        <a href="admission?course_id=<?= $course['id'] ?>" class="btn btn-primary btn-sm">Enrol</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5"><a href="courses" class="btn btn-accent rounded-pill px-4">See All Courses</a></div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="section-padding bg-white">
    <div class="container">
        <div class="section-title"><h2 data-aos="fade-up">Why Choose Us?</h2><p class="text-muted" data-aos="fade-up" data-aos-delay="100">What makes Sahib Classes the right choice for your child's future</p></div>
        <div class="row g-4 text-center">
            <?php foreach ($why_us as $idx => $item): ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?= $idx*100 ?>">
                <div class="p-4 shadow-sm rounded border h-100"><i class="fa-solid <?= $item['icon'] ?> display-4 text-accent mb-3"></i><h4 class="fs-5 mb-3 text-primary"><?= $item['title'] ?></h4><p class="text-muted small mb-0"><?= $item['desc'] ?></p></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- How To Enrol -->
<section class="section-padding bg-primary text-white">
    <div class="container">
        <div class="section-title"><h2 class="text-white" data-aos="fade-up">How To Enrol</h2><p class="opacity-75" data-aos="fade-up" data-aos-delay="100">Join our institute in 4 simple steps</p></div>
        <div class="row text-center position-relative">
            <div class="d-none d-lg-block position-absolute" style="top: 25%; left: 10%; right: 10%; height: 2px; background-color: rgba(255,255,255,0.2); z-index: 1;"></div>
            <?php foreach ($steps as $idx => $s): ?>
            <div class="col-lg-3 col-md-6 mb-4 mb-lg-0" data-aos="fade-down" data-aos-delay="<?= ($idx+1)*100 ?>">
                <div class="bg-dark mx-auto rounded-circle d-flex align-items-center justify-content-center border border-accent border-3 position-relative" style="width: 80px; height: 80px; z-index: 2;"><span class="fs-3 fw-bold text-accent"><?= $s['n'] ?></span></div>
                <h5 class="mt-4 text-white"><?= $s['t'] ?></h5><p class="small opacity-75"><?= $s['d'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Upcoming Batches -->
<section class="section-padding bg-light-grey">
    <div class="container"><div class="row align-items-center">
        <div class="col-lg-5 mb-4 mb-lg-0" data-aos="fade-right">
            <h2 class="mb-4 text-primary fw-bold">Upcoming <span class="text-accent">Batches</span></h2>
            <p class="text-muted mb-4">Don't miss out! Secure your seat in our upcoming batches before they fill up.</p>
            <a href="timetable" class="btn btn-outline-primary rounded-pill">View Timetable</a>
        </div>
        <div class="col-lg-7" data-aos="fade-left">
            <div class="table-responsive bg-white rounded shadow-sm">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-primary text-white"><tr><th>Course</th><th>Start Date</th><th>Timing</th><th>Mode</th></tr></thead>
                    <tbody>
                        <?php if (empty($batches)): ?><tr><td colspan="4" class="text-center py-4 text-muted">No upcoming batches.</td></tr>
                        <?php else: foreach ($batches as $b): ?>
                        <tr><td class="py-3 fw-semibold text-primary"><?= htmlspecialchars($b['course_name']) ?></td><td><?= date('d M, Y', strtotime($b['start_date'])) ?></td><td><?= htmlspecialchars($b['timing']) ?></td><td><span class="badge <?= $b['mode'] == 'Online' ? 'bg-info' : 'bg-success' ?>"><?= $b['mode'] ?></span></td></tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div></div>
</section>

<!-- Testimonials -->
<section class="section-padding bg-white">
    <div class="container">
        <div class="section-title"><h2 data-aos="fade-up">Student Success</h2></div>
        <div class="row justify-content-center"><div class="col-lg-8" data-aos="fade-up">
            <div id="test-carousel" class="carousel slide pb-5" data-bs-ride="carousel">
                <div class="carousel-inner text-center">
                    <?php foreach ($testimonials as $idx => $t): ?>
                    <div class="carousel-item <?= $idx == 0 ? 'active' : '' ?>">
                        <i class="fa-solid fa-quote-left fs-1 text-light opacity-25 mb-3"></i>
                        <p class="fs-5 text-dark mb-4">"<?= htmlspecialchars($t['review']) ?>"</p>
                        <div class="d-flex align-items-center justify-content-center">
                            <img src="<?= $t['photo'] ?: 'https://via.placeholder.com/60' ?>" class="rounded-circle shadow-sm me-3" style="width:60px; height:60px; object-fit:cover;">
                            <div class="text-start"><h6 class="mb-0 text-primary fw-bold"><?= htmlspecialchars($t['name']) ?></h6><small class="text-muted"><?= htmlspecialchars($t['course']) ?></small>
                                <div class="text-warning small mt-1"><?php for($i=1;$i<=5;$i++) echo '<i class="fa-'.($i<=$t['rating']?'solid':'regular').' fa-star"></i>'; ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="carousel-indicators mb-0" style="bottom:-20px;">
                    <?php foreach ($testimonials as $idx => $t): ?><button type="button" data-bs-target="#test-carousel" data-bs-slide-to="<?= $idx ?>" class="<?= $idx==0?'active':'' ?> bg-primary"></button><?php endforeach; ?>
                </div>
            </div>
        </div></div>
    </div>
</section>

<!-- News Strip -->
<section class="py-4 bg-light border-top border-bottom">
    <div class="container"><div class="row align-items-center">
        <div class="col-md-3 mb-3 mb-md-0"><h5 class="mb-0 text-primary fw-bold"><i class="fa-regular fa-newspaper me-2 text-accent"></i> Latest News</h5></div>
        <div class="col-md-9"><div class="d-flex flex-column flex-md-row justify-content-md-end gap-3">
            <?php foreach ($newsItems as $n): ?>
            <div class="d-flex align-items-center"><span class="badge bg-secondary me-2"><?= date('d M', strtotime($n['published_at'])) ?></span><a href="news-detail?id=<?= $n['id'] ?>" class="text-dark small text-truncate" style="max-width:200px;"><?= htmlspecialchars($n['title']) ?></a></div>
            <?php endforeach; ?>
            <a href="news" class="small text-accent fw-bold ms-md-3">View All <i class="fa-solid fa-arrow-right"></i></a>
        </div></div>
    </div></div>
</section>

<!-- CTA -->
<section class="section-padding bg-accent text-center text-white" style="background-image: url('https://www.transparenttextures.com/patterns/cubes.png');">
    <div class="container" data-aos="zoom-in">
        <h2 class="display-5 fw-bold text-white mb-4">Ready to Start? Enrol Today.</h2>
        <p class="lead mb-5 opacity-75">Take the first step towards a bright career. Let Sahib Classes guide you.</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="contact" class="btn btn-outline-light btn-lg rounded-pill px-4 border-2 fw-bold">Contact Us</a>
            <a href="admission" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow">Apply Online</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const counters = document.querySelectorAll('.counter');
    const observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting) {
            counters.forEach(c => {
                const target = +c.dataset.target, animate = () => {
                    const cur = +c.innerText, inc = target / 200;
                    if (cur < target) { c.innerText = Math.ceil(cur + inc); setTimeout(animate, 20); }
                    else c.innerText = target;
                }; animate();
            });
            observer.disconnect();
        }
    }, { threshold: 0.5 });
    if (counters.length) observer.observe(counters[0]);
});
</script>