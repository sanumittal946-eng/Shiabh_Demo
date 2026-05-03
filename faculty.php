<?php
// faculty.php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';

$db = getDB();

// Fetch all subjects for filter
$subStmt = $db->query("SELECT DISTINCT subject FROM faculty ORDER BY subject ASC");
$subjects = $subStmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch faculty
$facStmt = $db->query("SELECT * FROM faculty ORDER BY sort_order ASC, name ASC");
$facultyList = $facStmt->fetchAll();

// Separate HODs and Regular
$hods = [];
$regular = [];
foreach($facultyList as $fac) {
    if (stripos($fac['designation'], 'head') !== false || stripos($fac['designation'], 'hod') !== false) {
        $hods[] = $fac;
    } else {
        $regular[] = $fac;
    }
}
?>

<!-- Page Hero -->
<section class="banner-section bg-primary text-white py-5 position-relative" style="background-image: url('https://www.transparenttextures.com/patterns/black-linen-2.png'); border-bottom: 5px solid var(--accent-color);">
    <div class="container text-center py-5 position-relative" style="z-index: 2;">
        <h1 class="display-4 fw-bold text-white mb-3" data-aos="fade-down">Our Faculty</h1>
        <p class="lead text-light opacity-75 mx-auto" style="max-width: 600px;">Meet our team of experienced educators, industry experts and mentors dedicated to your success.</p>
        <nav aria-label="breadcrumb" class="mt-3">
            <ol class="breadcrumb justify-content-center mb-0">
                <li class="breadcrumb-item"><a href="index.php" class="text-white text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active text-accent" aria-current="page">Faculty</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Faculty Filter -->
<div class="bg-white py-4 border-bottom shadow-sm sticky-top" style="top: 76px; z-index: 1000;">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-center gap-2">
            <button class="btn btn-primary active filter-btn rounded-pill px-4" data-filter="all">All Departments</button>
            <?php foreach($subjects as $sub): ?>
                <button class="btn btn-outline-primary filter-btn rounded-pill px-4" data-filter="<?= htmlspecialchars($sub) ?>"><?= htmlspecialchars($sub) ?></button>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<section class="section-padding bg-light-grey">
    <div class="container">
        
        <!-- Department Heads -->
        <?php if(!empty($hods)): ?>
        <div class="mb-5">
            <h3 class="text-center text-primary fw-bold mb-4">Department Heads</h3>
            <div class="row justify-content-center g-4">
                <?php foreach($hods as $fac): ?>
                <div class="col-lg-4 col-md-6 filter-item" data-category="<?= htmlspecialchars($fac['subject']) ?>" data-aos="fade-up">
                    <div class="card h-100 border-0 shadow-sm border-top border-4 border-accent text-center p-4">
                        <div class="mx-auto mb-3">
                            <img src="<?= !empty($fac['photo']) ? htmlspecialchars($fac['photo']) : 'https://via.placeholder.com/150x150?text=Photo' ?>" class="rounded-circle object-fit-cover shadow" style="width: 120px; height: 120px;" alt="<?= htmlspecialchars($fac['name']) ?>">
                        </div>
                        <h4 class="text-primary fw-bold mb-1"><?= htmlspecialchars($fac['name']) ?></h4>
                        <p class="text-accent fw-semibold small mb-2"><?= htmlspecialchars($fac['designation']) ?></p>
                        <span class="badge bg-secondary mb-3"><?= htmlspecialchars($fac['subject']) ?></span>
                        
                        <p class="small text-muted mb-4 text-truncate"><?= htmlspecialchars($fac['bio']) ?></p>
                        
                        <button class="btn btn-outline-primary rounded-pill btn-sm d-inline-flex align-items-center justify-content-center mx-auto" style="width: 150px;" data-bs-toggle="modal" data-bs-target="#facModal<?= $fac['id'] ?>">View Profile</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Regular Faculty -->
        <?php if(!empty($regular)): ?>
        <div>
            <h3 class="text-center text-primary fw-bold mb-4">Expert Educators</h3>
            <div class="row g-4">
                <?php foreach($regular as $fac): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 filter-item" data-category="<?= htmlspecialchars($fac['subject']) ?>" data-aos="fade-up">
                    <div class="card h-100 border-0 shadow-sm text-center p-3">
                         <div class="mx-auto mb-3">
                            <img src="<?= !empty($fac['photo']) ? htmlspecialchars($fac['photo']) : 'https://via.placeholder.com/100x100?text=Photo' ?>" class="rounded-circle object-fit-cover" style="width: 90px; height: 90px;" alt="<?= htmlspecialchars($fac['name']) ?>">
                        </div>
                        <h5 class="text-primary fw-bold mb-1 fs-6"><?= htmlspecialchars($fac['name']) ?></h5>
                        <p class="text-muted small mb-1"><?= htmlspecialchars($fac['designation']) ?></p>
                        <span class="badge bg-light text-dark border mb-3 w-100"><?= htmlspecialchars($fac['subject']) ?></span>
                        
                        <button class="btn btn-light border rounded btn-sm d-inline-flex w-100 mt-auto" data-bs-toggle="modal" data-bs-target="#facModal<?= $fac['id'] ?>">Profile</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</section>

<!-- Modals for all faculty -->
<?php foreach($facultyList as $fac): ?>
<div class="modal fade" id="facModal<?= $fac['id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold">Faculty Profile</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0">
                    <div class="col-md-4 bg-light text-center p-4">
                        <img src="<?= !empty($fac['photo']) ? htmlspecialchars($fac['photo']) : 'https://via.placeholder.com/200x200?text=Photo' ?>" class="rounded-circle object-fit-cover shadow-sm mb-3 border border-3 border-white" style="width: 150px; height: 150px;" alt="<?= htmlspecialchars($fac['name']) ?>">
                        <h4 class="text-primary fw-bold mb-0"><?= htmlspecialchars($fac['name']) ?></h4>
                        <p class="text-accent small"><?= htmlspecialchars($fac['designation']) ?></p>
                        
                        <?php if(!empty($fac['linkedin_url'])): ?>
                            <a href="<?= htmlspecialchars($fac['linkedin_url']) ?>" target="_blank" class="btn btn-outline-primary btn-sm rounded-circle mt-2" style="width: 35px; height: 35px; padding: 5px;"><i class="fa-brands fa-linkedin-in"></i></a>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-8 p-4">
                        <h5 class="text-primary border-bottom pb-2 mb-3">About</h5>
                        <p class="text-muted small mb-4"><?= nl2br(htmlspecialchars($fac['bio'])) ?></p>
                        
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <h6 class="text-dark fw-bold mb-1"><i class="fa-solid fa-graduation-cap text-accent me-2"></i> Qualification</h6>
                                <p class="text-muted small"><?= htmlspecialchars($fac['qualification']) ?></p>
                            </div>
                            <div class="col-sm-6">
                                <h6 class="text-dark fw-bold mb-1"><i class="fa-solid fa-clock-rotate-left text-accent me-2"></i> Experience</h6>
                                <p class="text-muted small"><?= htmlspecialchars($fac['experience_years']) ?>+ Years</p>
                            </div>
                            <div class="col-sm-6">
                                <h6 class="text-dark fw-bold mb-1"><i class="fa-solid fa-book text-accent me-2"></i> Subject Taught</h6>
                                <p class="text-muted small"><?= htmlspecialchars($fac['subject']) ?></p>
                            </div>
                            <div class="col-sm-6">
                                <h6 class="text-dark fw-bold mb-1"><i class="fa-solid fa-calendar-check text-accent me-2"></i> Contact Hours</h6>
                                <p class="text-muted small">Mon - Fri, 4 PM - 5 PM</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
