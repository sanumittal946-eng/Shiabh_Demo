<?php
// about.php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$milestones = $db->query("SELECT year, title, description, icon FROM milestones ORDER BY year ASC")->fetchAll();

// Data for Loops
$core_values = [
    ['icon' => 'fa-handshake', 'title' => 'Integrity', 'desc' => 'Upholding honesty and transparency in all relationships.'],
    ['icon' => 'fa-star', 'title' => 'Excellence', 'desc' => 'Striving for the highest quality in teaching and administration.'],
    ['icon' => 'fa-people-carry-box', 'title' => 'Student-Centric', 'desc' => 'Putting the needs and growth of our students first always.'],
    ['icon' => 'fa-brain', 'title' => 'Innovation', 'desc' => 'Adopting creative approaches to make learning engaging.'],
    ['icon' => 'fa-users', 'title' => 'Inclusivity', 'desc' => 'Fostering an environment that respects everybody.'],
    ['icon' => 'fa-clipboard-check', 'title' => 'Accountability', 'desc' => 'Taking ownership of our actions and fulfilling our promises.']
];

$philosophy = [
    ['icon' => 'fa-chess-knight', 'title' => 'Strategic Learning', 'desc' => 'We map out complex topics into bite-sized, logical sequences making it easier to absorb and retain.'],
    ['icon' => 'fa-comments', 'title' => 'Interactive Sessions', 'desc' => 'Monologues are boring. Our classes thrive on two-way communication and open debates on subject matter.'],
    ['icon' => 'fa-chart-line', 'title' => 'Continuous Evaluation', 'desc' => 'Daily practice papers, weekly tests, and detailed analytical feedback keep performance on track.']
];

$infra = [
    ['id' => 'class', 'title' => 'Classrooms', 'img' => 'https://images.unsplash.com/photo-1544531586-fde5298cdd40?auto=format&fit=crop&w=600&q=80', 'desc' => 'Spacious, well-ventilated, and equipped with modern teaching aids.'],
    ['id' => 'lib', 'title' => 'Smart Library', 'img' => 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=600&q=80', 'desc' => 'Over 5,000 reference books, quiet reading zones, and digital access.'],
    ['id' => 'lab', 'title' => 'Digital Labs', 'img' => 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?auto=format&fit=crop&w=600&q=80', 'desc' => 'High-speed internet and latest software for practical learning.']
];
?>

<!-- Page Hero -->
<section class="banner-section bg-primary text-white py-5 position-relative" style="background-image: url('https://www.transparenttextures.com/patterns/black-scales.png');">
    <div class="container text-center py-5 position-relative" style="z-index: 2;">
        <h1 class="display-4 fw-bold mb-3" data-aos="fade-down">About Us</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-center mb-0">
                <li class="breadcrumb-item"><a href="./" class="text-white text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active text-accent">About Us</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Intro -->
<section class="section-padding bg-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0" data-aos="fade-right">
                <h5 class="text-accent fw-bold text-uppercase mb-2">Our Story</h5>
                <h2 class="text-primary mb-4 fw-bold">Empowering Students Since 2010</h2>
                <p class="text-muted mb-4"><?= getSetting('site_name') ?> was founded with a clear vision: to provide high-quality education and mentorship to students aspiring to succeed in competitive and board exams.</p>
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fa-solid fa-check text-accent me-2"></i> Comprehensive study materials</li>
                    <li class="mb-2"><i class="fa-solid fa-check text-accent me-2"></i> Regular mock tests and analysis</li>
                </ul>
            </div>
            <div class="col-lg-6 position-relative" data-aos="fade-left">
                <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=800&q=80" alt="Institute" class="img-fluid rounded shadow-lg border border-4 border-white">
                <div class="position-absolute bg-accent text-white p-4 rounded text-center shadow" style="bottom: -20px; left: -20px; max-width: 150px;">
                    <div class="display-5 fw-bold mb-0">15+</div>
                    <div class="small text-uppercase fw-semibold">Years of Excellence</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mission & Vision -->
<section class="section-padding bg-light-grey">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-6" data-aos="fade-up">
                <div class="card h-100 p-5 border-0 border-bottom border-4 border-primary shadow-sm">
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-primary-subtle text-primary p-3 rounded-circle me-4"><i class="fa-solid fa-bullseye fs-1"></i></div>
                        <h3 class="mb-0 text-primary">Our Mission</h3>
                    </div>
                    <p class="text-muted fs-5">To deliver inclusive and value-based education that builds confidence, ignites curiosity, and enables students to achieve academic brilliance.</p>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 p-5 border-0 border-bottom border-4 border-accent shadow-sm">
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-warning-subtle text-accent p-3 rounded-circle me-4"><i class="fa-regular fa-lightbulb fs-1"></i></div>
                        <h3 class="mb-0 text-primary">Our Vision</h3>
                    </div>
                    <p class="text-muted fs-5">To be recognized globally as an institution shaping the leaders of tomorrow through innovative and holistic learning methods.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Core Values -->
<section class="section-padding bg-white">
    <div class="container">
        <div class="section-title text-center mb-5">
            <h2 data-aos="fade-up">Core Values</h2>
            <p class="text-muted" data-aos="fade-up" data-aos-delay="100">The principles that guide our everyday actions</p>
        </div>
        <div class="row g-4 text-center">
            <?php foreach ($core_values as $idx => $v): ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?= $idx * 100 ?>">
                <div class="card h-100 p-4 bg-light-grey border-0 shadow-sm hover-up">
                    <i class="fa-solid <?= $v['icon'] ?> display-5 text-accent mb-3"></i>
                    <h5 class="text-primary mb-3"><?= $v['title'] ?></h5>
                    <p class="text-muted small mb-0"><?= $v['desc'] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Journey -->
<section class="section-padding bg-primary text-white position-relative" style="background-image: url('https://www.transparenttextures.com/patterns/cubes.png');">
    <div class="container">
        <div class="section-title text-center">
            <h2 class="text-white border-accent mb-5" data-aos="fade-up">Our Journey</h2>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="timeline position-relative ps-4" style="border-left: 2px solid rgba(255,255,255,0.2);">
                    <?php foreach ($milestones as $idx => $ms): ?>
                    <div class="timeline-item mb-5 position-relative" data-aos="fade-right" data-aos-delay="<?= $idx * 100 ?>">
                        <div class="timeline-icon position-absolute rounded-circle bg-accent d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; left: -44px; top: 0;">
                            <i class="fa-solid <?= $ms['icon'] ?: 'fa-flag' ?> text-white small"></i>
                        </div>
                        <h3 class="text-accent fw-bold mb-1"><?= $ms['year'] ?></h3>
                        <h4 class="text-white fs-5 mb-2"><?= $ms['title'] ?></h4>
                        <p class="text-light opacity-75 mb-0"><?= $ms['description'] ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Philosophy -->
<section class="section-padding bg-white border-bottom">
    <div class="container text-center">
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8" data-aos="fade-up">
                <i class="fa-solid fa-quote-left display-3 text-light opacity-25 mb-3"></i>
                <h2 class="text-primary fw-bold mb-3">Our Teaching Philosophy</h2>
                <p class="lead text-muted">Education is not the learning of facts, but the training of the mind to think. We focus on the 'why' before the 'how'.</p>
            </div>
        </div>
        <div class="row g-4 text-start">
            <?php foreach ($philosophy as $idx => $p): ?>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="<?= $idx * 100 ?>">
                <div class="d-flex align-items-start">
                    <i class="fa-solid <?= $p['icon'] ?> fs-2 text-accent me-3 mt-1"></i>
                    <div><h5 class="text-primary"><?= $p['title'] ?></h5><p class="text-muted small"><?= $p['desc'] ?></p></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Leadership -->
<section class="section-padding bg-light-grey">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-5 mb-4 mb-lg-0" data-aos="fade-right">
                <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=600&q=80" alt="Director" class="img-fluid rounded shadow object-fit-cover" style="max-height: 400px; width: 100%;">
            </div>
            <div class="col-lg-7 ps-lg-5" data-aos="fade-left">
                <h5 class="text-accent fw-bold text-uppercase mb-2">Message from the Director</h5>
                <h2 class="text-primary fw-bold mb-3">Mr. Sahib Sir</h2>
                <p class="text-muted mb-4">"Education is the most powerful weapon which you can use to change the world. At Sahib Classes, we don't just prepare students for exams; we prepare them for life."</p>
                <div class="mb-4 text-muted border-start border-3 border-accent ps-3">
                    <small>With over 20 years of experience, Mr. Sahib Sir has transformed the learning trajectories of thousands of students.</small>
                </div>
                <img src="https://upload.wikimedia.org/wikipedia/commons/f/fa/Signature_of_John_Hancock.svg" alt="Signature" style="height: 40px; opacity:0.6;">
            </div>
        </div>
    </div>
</section>

<!-- Infrastructure -->
<section class="section-padding bg-white">
    <div class="container">
        <div class="section-title text-center mb-5"><h2 data-aos="fade-up">World-Class Infrastructure</h2></div>
        <div class="row">
            <div class="col-lg-3">
                <div class="nav flex-column nav-pills me-3" id="infra-tab" role="tablist" data-aos="fade-right">
                    <?php foreach ($infra as $idx => $i): ?>
                    <button class="nav-link <?= $idx === 0 ? 'active' : '' ?> text-start mb-2 border rounded" data-bs-toggle="pill" data-bs-target="#<?= $i['id'] ?>" type="button"><?= $i['title'] ?></button>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-lg-9">
                <div class="tab-content border rounded p-4 bg-light-grey" data-aos="fade-left">
                    <?php foreach ($infra as $idx => $i): ?>
                    <div class="tab-pane fade <?= $idx === 0 ? 'show active' : '' ?>" id="<?= $i['id'] ?>" role="tabpanel">
                        <div class="row align-items-center">
                            <div class="col-md-6"><h4 class="text-primary mb-3"><?= $i['title'] ?></h4><p class="text-muted"><?= $i['desc'] ?></p></div>
                            <div class="col-md-6"><img src="<?= $i['img'] ?>" alt="<?= $i['title'] ?>" class="img-fluid rounded shadow-sm"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-4 bg-accent text-center text-white">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center">
        <h4 class="mb-3 mb-md-0 fw-bold">Want to learn more about our methodologies?</h4>
        <a href="contact.php" class="btn btn-dark rounded-pill px-4 fw-bold shadow-sm">Contact Us Today</a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>