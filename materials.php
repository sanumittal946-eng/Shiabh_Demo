<?php
// materials.php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Auth Protection
checkStudentAuth();

$db = getDB();
$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];

// Track Download via URL parameter
if (isset($_GET['download']) && is_numeric($_GET['download'])) {
    $mat_id = (int)$_GET['download'];
    $upStmt = $db->prepare("UPDATE materials SET download_count = download_count + 1 WHERE id = :id");
    $upStmt->execute([':id' => $mat_id]);
    
    $fetchUrl = $db->prepare("SELECT file_path FROM materials WHERE id = :id");
    $fetchUrl->execute([':id' => $mat_id]);
    $url = $fetchUrl->fetchColumn();
    if($url) {
        header("Location: " . $url);
        exit();
    }
}

// Fetch subjects for filter
$subStmt = $db->query("SELECT DISTINCT subject FROM materials ORDER BY subject ASC");
$subjects = $subStmt->fetchAll(PDO::FETCH_COLUMN);

// Search Query
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$whereClause = "";
$queryParams = [];

if (!empty($search)) {
    $whereClause = "WHERE title LIKE :search OR subject LIKE :search";
    $queryParams[':search'] = "%$search%";
}

// Fetch all materials
$stmt = $db->prepare("SELECT * FROM materials $whereClause ORDER BY uploaded_at DESC");
$stmt->execute($queryParams);
$allMaterials = $stmt->fetchAll();

$materialsByType = [
    'Notes' => [],
    'Videos' => [],
    'Practice Papers' => [],
    'Previous Years' => []
];

foreach ($allMaterials as $m) {
    if(isset($materialsByType[$m['type']])) {
        $materialsByType[$m['type']][] = $m;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<section class="banner-section bg-primary text-white py-4 position-relative" style="background-image: url('https://www.transparenttextures.com/patterns/black-mamba.png'); border-bottom: 5px solid var(--accent-color);">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center py-4 position-relative" style="z-index: 2;">
        <div>
            <h1 class="display-6 fw-bold text-white mb-1"><i class="fa-solid fa-book-open-reader me-3 text-accent"></i>Study Materials</h1>
            <p class="text-light opacity-75 mb-0">Welcome back, <span class="fw-bold text-white"><?= htmlspecialchars($student_name) ?></span>!</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="logout.php" class="btn btn-outline-light rounded-pill btn-sm"><i class="fa-solid fa-power-off me-2"></i>Logout</a>
        </div>
    </div>
</section>

<!-- Content Area -->
<section class="section-padding bg-light-grey min-vh-100">
    <div class="container">
        
        <!-- Controls -->
        <div class="bg-white p-4 rounded shadow-sm mb-5 d-flex flex-column flex-md-row justify-content-between gap-3" data-aos="fade-up">
            <div class="w-100">
                <form action="materials.php" method="GET" class="d-flex">
                    <input type="text" id="live-search" name="search" class="form-control" placeholder="Search by title or subject..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn btn-primary px-4 ms-2"><i class="fa-solid fa-magnifying-glass"></i></button>
                    <?php if(!empty($search)): ?>
                        <a href="materials.php" class="btn btn-outline-secondary ms-2"><i class="fa-solid fa-xmark"></i></a>
                    <?php endif; ?>
                </form>
            </div>
            
            <div class="d-flex gap-2 flex-shrink-0 align-items-center">
                <span class="text-muted small fw-bold"><i class="fa-solid fa-filter me-1"></i> Filter:</span>
                <select id="subject-filter" class="form-select form-select-sm" style="width: auto;">
                    <option value="all">All Subjects</option>
                    <?php foreach($subjects as $sub): ?>
                        <option value="<?= htmlspecialchars($sub) ?>"><?= htmlspecialchars($sub) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <!-- Tabs -->
        <ul class="nav nav-pills mb-4 nav-justified bg-white rounded shadow-sm p-1" id="materialTabs" role="tablist" data-aos="fade-up" data-aos-delay="100">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold border-0" data-bs-toggle="pill" data-bs-target="#tab-notes" type="button" role="tab"><i class="fa-solid fa-file-pdf me-2"></i> PDF Notes</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold border-0" data-bs-toggle="pill" data-bs-target="#tab-videos" type="button" role="tab"><i class="fa-solid fa-circle-play me-2"></i> Video Lessons</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold border-0" data-bs-toggle="pill" data-bs-target="#tab-practice" type="button" role="tab"><i class="fa-solid fa-pen-to-square me-2"></i> Practice Papers</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold border-0" data-bs-toggle="pill" data-bs-target="#tab-prev" type="button" role="tab"><i class="fa-solid fa-clock-rotate-left me-2"></i> Previous Years</button>
            </li>
        </ul>
        
        <style>
            .nav-pills .nav-link.active {
                background-color: var(--primary-color);
                color: white !important;
            }
            .nav-pills .nav-link {
                color: var(--text-dark);
                border-radius: .25rem;
            }
        </style>
        
        <!-- Tab Content -->
        <div class="tab-content" id="materialTabsContent">
            
            <?php 
            $tabs = [
                ['id' => 'tab-notes', 'key' => 'Notes', 'icon' => 'fa-file-pdf', 'color' => 'danger'],
                ['id' => 'tab-videos', 'key' => 'Videos', 'icon' => 'fa-youtube', 'color' => 'danger'],
                ['id' => 'tab-practice', 'key' => 'Practice Papers', 'icon' => 'fa-file-word', 'color' => 'primary'],
                ['id' => 'tab-prev', 'key' => 'Previous Years', 'icon' => 'fa-file-zipper', 'color' => 'warning']
            ];
            
            foreach($tabs as $index => $tab): 
                $items = $materialsByType[$tab['key']];
            ?>
            <div class="tab-pane fade <?= $index == 0 ? 'show active' : '' ?>" id="<?= $tab['id'] ?>" role="tabpanel">
                
                <?php if(empty($items)): ?>
                    <div class="text-center py-5 bg-white rounded shadow-sm border border-light">
                        <i class="fa-regular fa-folder-open fs-1 text-muted opacity-50 mb-3"></i>
                        <h5 class="text-muted">No materials found in this category.</h5>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach($items as $item): ?>
                        <div class="col-lg-4 col-md-6 mat-card" data-subject="<?= htmlspecialchars($item['subject']) ?>">
                            <div class="card h-100 border-0 shadow-sm border-top border-3 border-<?= $tab['color'] ?> p-0">
                                
                                <?php if($tab['key'] === 'Videos'): ?>
                                    <div class="ratio ratio-16x9">
                                        <!-- Assuming file_path holds youtube embed url -->
                                        <iframe src="<?= htmlspecialchars($item['file_path']) ?>" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="card-body p-4">
                                    <span class="badge bg-light text-dark border mb-2 px-2 py-1"><?= htmlspecialchars($item['subject']) ?></span>
                                    <h5 class="card-title text-primary fw-bold mb-3 fs-6 lh-base"><?= htmlspecialchars($item['title']) ?></h5>
                                    
                                    <div class="d-flex justify-content-between text-muted small mb-0">
                                        <span><i class="fa-regular fa-calendar me-1"></i> <?= date('M d, Y', strtotime($item['uploaded_at'])) ?></span>
                                        <?php if($tab['key'] !== 'Videos'): ?>
                                            <span><i class="fa-solid fa-server me-1"></i> <?= htmlspecialchars($item['file_size'] ?? '1.2 MB') ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <?php if($tab['key'] !== 'Videos'): ?>
                                <div class="card-footer bg-white p-3 border-top d-flex justify-content-between align-items-center">
                                    <span class="small text-muted"><i class="fa-solid fa-download me-1"></i> <?= $item['download_count'] ?></span>
                                    <a href="materials.php?download=<?= $item['id'] ?>" target="_blank" class="btn btn-sm btn-outline-<?= $tab['color'] ?> fw-bold px-3">Download <i class="fa-solid fa-cloud-arrow-down ms-1"></i></a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
            </div>
            <?php endforeach; ?>
            
        </div>
    </div>
</section>

<!-- Subject Filter JS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const subjectFilter = document.getElementById('subject-filter');
    const matCards = document.querySelectorAll('.mat-card');
    
    subjectFilter.addEventListener('change', function(e) {
        const val = e.target.value.toLowerCase();
        
        matCards.forEach(card => {
            if(val === 'all') {
                card.style.display = 'block';
            } else {
                const sub = card.getAttribute('data-subject').toLowerCase();
                if(sub === val) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
