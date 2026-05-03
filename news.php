<?php
// news.php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

$db = getDB();

$limit = 6;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Category Filter
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$whereClause = "WHERE status = 'published'";
$queryParams = [];

if (!empty($category) && $category != 'all') {
    $whereClause .= " AND category = :cat";
    $queryParams[':cat'] = $category;
}

// Total
$totalStmt = $db->prepare("SELECT COUNT(*) FROM news $whereClause");
$totalStmt->execute($queryParams);
$total = $totalStmt->fetchColumn();
$pages = ceil($total / $limit);

// Fetch News
$newsStmt = $db->prepare("SELECT * FROM news $whereClause ORDER BY published_at DESC LIMIT :start, :limit");
foreach($queryParams as $k => $v) {
    $newsStmt->bindValue($k, $v);
}
$newsStmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
$newsStmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
$newsStmt->execute();
$newsItems = $newsStmt->fetchAll();

// Fetch Recent Posts for sidebar
$recentStmt = $db->query("SELECT id, title, published_at FROM news WHERE status = 'published' ORDER BY published_at DESC LIMIT 5");
$recentPosts = $recentStmt->fetchAll();

// Fetch Categories for sidebar
$catStmt = $db->query("SELECT category, COUNT(*) as cat_count FROM news WHERE status='published' GROUP BY category");
$categories = $catStmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<section class="banner-section bg-primary text-white py-5 position-relative" style="background-image: url('https://www.transparenttextures.com/patterns/black-linen-2.png'); border-bottom: 5px solid var(--accent-color);">
    <div class="container text-center py-5 position-relative" style="z-index: 2;">
        <h1 class="display-4 fw-bold text-white mb-3" data-aos="fade-down">News & Events</h1>
        <p class="lead text-light opacity-75 mx-auto" style="max-width: 600px;">Stay updated with the latest happenings, announcements and educational blogs.</p>
        <nav aria-label="breadcrumb" class="mt-3">
            <ol class="breadcrumb justify-content-center mb-0">
                <li class="breadcrumb-item"><a href="index.php" class="text-white text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active text-accent" aria-current="page">News</li>
            </ol>
        </nav>
    </div>
</section>

<section class="section-padding bg-light-grey">
    <div class="container">
        <div class="row g-5">
            <!-- Main Content -->
            <div class="col-lg-8" data-aos="fade-right">
                
                <!-- Horizontal Category Tabs (Mobile Friendly view of categories) -->
                <div class="d-flex overflow-auto pb-3 mb-4 d-lg-none gap-2" style="white-space: nowrap;">
                    <a href="news.php" class="btn <?= empty($category) ? 'btn-primary' : 'btn-outline-primary' ?> rounded-pill btn-sm px-3">All Posts</a>
                    <?php foreach($categories as $c): ?>
                        <a href="news.php?category=<?= urlencode($c['category']) ?>" class="btn <?= ($category == $c['category']) ? 'btn-primary' : 'btn-outline-primary' ?> rounded-pill btn-sm px-3"><?= htmlspecialchars($c['category']) ?></a>
                    <?php endforeach; ?>
                </div>

                <div class="row g-4">
                    <?php if(empty($newsItems)): ?>
                        <div class="col-12 py-5 text-center">
                            <h5 class="text-muted">No articles found in this category.</h5>
                        </div>
                    <?php else: ?>
                        <?php foreach($newsItems as $news): ?>
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                                <?php if(!empty($news['featured_image'])): ?>
                                    <div class="ratio ratio-16x9">
                                        <img src="<?= htmlspecialchars($news['featured_image']) ?>" class="object-fit-cover w-100 h-100" alt="<?= htmlspecialchars($news['title']) ?>">
                                    </div>
                                <?php else: ?>
                                    <div class="ratio ratio-16x9 bg-secondary d-flex align-items-center justify-content-center">
                                        <i class="fa-regular fa-image text-white opacity-50 display-1 position-absolute top-50 start-50 translate-middle"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="card-body p-4 position-relative">
                                    <span class="badge bg-accent position-absolute top-0 translate-middle-y shadow-sm px-3 py-2"><?= htmlspecialchars($news['category']) ?></span>
                                    
                                    <h5 class="card-title text-primary fw-bold mb-3 mt-2 lh-base" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><a href="news-detail.php?id=<?= $news['id'] ?>" class="text-primary text-decoration-none"><?= htmlspecialchars($news['title']) ?></a></h5>
                                    
                                    <p class="text-muted small mb-4" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;"><?= htmlspecialchars($news['excerpt']) ?></p>
                                    
                                    <div class="d-flex justify-content-between align-items-center border-top pt-3">
                                        <span class="small text-muted"><i class="fa-regular fa-calendar text-accent me-1"></i> <?= date('M d, Y', strtotime($news['published_at'])) ?></span>
                                        <a href="news-detail.php?id=<?= $news['id'] ?>" class="text-accent fw-bold text-decoration-none small text-uppercase">Read More <i class="fa-solid fa-arrow-right ms-1"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if($pages > 1): ?>
                <nav aria-label="News pagination" class="mt-5">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page-1 ?><?= !empty($category) ? '&category='.urlencode($category) : '' ?>">Prev</a>
                        </li>
                        <?php for($i = 1; $i <= $pages; $i++): ?>
                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?><?= !empty($category) ? '&category='.urlencode($category) : '' ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($page >= $pages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page+1 ?><?= !empty($category) ? '&category='.urlencode($category) : '' ?>">Next</a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4" data-aos="fade-left">
                <!-- Search Box -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h5 class="text-primary border-bottom pb-2 mb-3 fw-bold">Search</h5>
                        <form action="news.php" method="GET">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Keywords..." name="q">
                                <button class="btn btn-accent text-white" type="submit"><i class="fa-solid fa-search"></i></button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Categories -->
                <div class="card border-0 shadow-sm mb-4 d-none d-lg-block">
                    <div class="card-body p-4">
                        <h5 class="text-primary border-bottom pb-2 mb-3 fw-bold">Categories</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <a href="news.php" class="text-decoration-none <?= empty($category) ? 'text-accent fw-bold' : 'text-dark' ?>">All Posts</a>
                            </li>
                            <?php foreach($categories as $c): ?>
                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <a href="news.php?category=<?= urlencode($c['category']) ?>" class="text-decoration-none <?= ($category == $c['category']) ? 'text-accent fw-bold' : 'text-dark' ?>"><?= htmlspecialchars($c['category']) ?></a>
                                <span class="badge bg-light text-muted rounded-pill"><?= $c['cat_count'] ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <!-- Recent Posts -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h5 class="text-primary border-bottom pb-2 mb-3 fw-bold">Recent Posts</h5>
                        <?php foreach($recentPosts as $idx => $rp): ?>
                            <div class="d-flex align-items-center mb-3 <?= $idx < count($recentPosts)-1 ? 'border-bottom pb-3' : '' ?>">
                                <div>
                                    <h6 class="mb-1"><a href="news-detail.php?id=<?= $rp['id'] ?>" class="text-primary text-decoration-none lh-sm d-block" style="font-size: 0.95rem;"><?= htmlspecialchars($rp['title']) ?></a></h6>
                                    <small class="text-muted"><i class="fa-regular fa-clock me-1 text-accent"></i> <?= date('M d, Y', strtotime($rp['published_at'])) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
