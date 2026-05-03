<?php
// courses.php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';

$db = getDB();

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Fetch all categories for filter
$catStmt = $db->query("SELECT DISTINCT category FROM courses ORDER BY category ASC");
$categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);

// Search Query
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$whereClause = "";
$queryParams = [];

if (!empty($search)) {
    $whereClause = "WHERE name LIKE :search OR description LIKE :search";
    $queryParams[':search'] = "%$search%";
}

// Get total for pagination
$totalStmt = $db->prepare("SELECT COUNT(*) FROM courses $whereClause");
$totalStmt->execute($queryParams);
$total = $totalStmt->fetchColumn();
$pages = ceil($total / $limit);

// Fetch courses
$coursesStmt = $db->prepare("SELECT * FROM courses $whereClause ORDER BY sort_order ASC, id DESC LIMIT :start, :limit");
// PDO bind limit workaround
foreach($queryParams as $k => $v){
    $coursesStmt->bindValue($k, $v);
}
$coursesStmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
$coursesStmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
$coursesStmt->execute();
$courses = $coursesStmt->fetchAll();
?>

<!-- Page Hero -->
<section class="banner-section bg-primary text-white py-5 position-relative" style="background-image: url('https://www.transparenttextures.com/patterns/black-mamba.png');">
    <div class="container text-center py-5 position-relative" style="z-index: 2;">
        <h1 class="display-4 fw-bold text-white mb-3" data-aos="fade-down">Our Courses</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-center mb-0">
                <li class="breadcrumb-item"><a href="index.php" class="text-white text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active text-accent" aria-current="page">Courses</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Course List Section -->
<section class="section-padding bg-light-grey">
    <div class="container">
        
        <!-- Controls: Filter & Search -->
        <div class="row mb-5 align-items-center">
            <div class="col-lg-8 mb-3 mb-lg-0" data-aos="fade-right">
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-primary active filter-btn" data-filter="all">All</button>
                    <?php foreach($categories as $cat): ?>
                        <button class="btn btn-outline-primary filter-btn" data-filter="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></button>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-lg-4" data-aos="fade-left">
                <form action="courses.php" method="GET" class="d-flex">
                    <input type="text" id="live-search" name="search" class="form-control rounded-start-pill" placeholder="Search courses..." value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-accent rounded-end-pill text-white px-3" type="submit"><i class="fa-solid fa-search"></i></button>
                    <?php if(!empty($search)): ?>
                        <a href="courses.php" class="btn btn-outline-secondary ms-2 rounded-pill"><i class="fa-solid fa-xmark"></i></a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Course Cards -->
        <div class="row g-4" id="course-list">
            <?php if(empty($courses)): ?>
                <div class="col-12 text-center py-5">
                    <h4 class="text-muted">No courses found matching your criteria.</h4>
                </div>
            <?php else: ?>
                <?php foreach($courses as $course): ?>
                <div class="col-lg-4 col-md-6 filter-item" data-category="<?= htmlspecialchars($course['category']) ?>" data-aos="fade-up">
                    <div class="card h-100 p-0 border-0 flex-column d-flex overflow-hidden course-card">
                        <div class="p-4 bg-white flex-grow-1">
                            <span class="badge bg-secondary mb-2"><?= htmlspecialchars($course['category']) ?></span>
                            <h4 class="mb-3 fs-5 text-primary fw-bold"><?= htmlspecialchars($course['name']) ?></h4>
                            <p class="text-muted small mb-4" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;"><?= htmlspecialchars($course['description']) ?></p>
                            
                            <ul class="list-unstyled text-muted small mb-0 border-top pt-3">
                                <li class="mb-2 d-flex justify-content-between"><span><i class="fa-regular fa-clock me-2 text-accent"></i>Duration</span> <span class="fw-bold"><?= htmlspecialchars($course['duration']) ?></span></li>
                                <li class="mb-2 d-flex justify-content-between"><span><i class="fa-solid fa-laptop-house me-2 text-accent"></i>Mode</span> <span class="fw-bold"><?= htmlspecialchars($course['mode']) ?></span></li>
                                <li class="mb-0 d-flex justify-content-between"><span><i class="fa-solid fa-indian-rupee-sign me-2 text-accent"></i>Fee</span> <span class="text-primary fw-bold">₹<?= number_format($course['fee']) ?></span></li>
                            </ul>
                        </div>
                        <div class="card-footer bg-light p-3 border-top-0 d-flex justify-content-between align-items-center">
                            <a href="course-detail.php?id=<?= $course['id'] ?>" class="btn btn-sm btn-outline-primary fw-bold">View Details</a>
                            <a href="admission.php?course_id=<?= $course['id'] ?>" class="btn btn-sm btn-accent fw-bold px-3">Enrol Now</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if($pages > 1): ?>
        <nav aria-label="Course pagination" class="mt-5" data-aos="fade-up">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page-1 ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>">Previous</a>
                </li>
                <?php for($i = 1; $i <= $pages; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a class="page-link <?= ($i == $page) ? 'bg-primary border-primary' : '' ?>" href="?page=<?= $i ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?= ($page >= $pages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page+1 ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>">Next</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>

    </div>
</section>

<!-- JS for Live Search filtering out rendered items instantly (optional enhancement) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const liveSearch = document.getElementById('live-search');
    const items = document.querySelectorAll('.filter-item');
    
    liveSearch.addEventListener('keyup', function(e) {
        // Only do live filter if no server search is happening, or just enhance it.
        const val = e.target.value.toLowerCase();
        items.forEach(item => {
            const text = item.innerText.toLowerCase();
            if(text.includes(val)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
