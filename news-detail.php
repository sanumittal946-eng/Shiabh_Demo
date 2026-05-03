<?php
// news-detail.php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    header("Location: news.php");
    exit();
}

$db = getDB();

// Fetch article
$stmt = $db->prepare("SELECT * FROM news WHERE id = :id AND status = 'published'");
$stmt->execute([':id' => $id]);
$article = $stmt->fetch();

if (!$article) {
    die("Article not found.");
}

// Fetch related
$relStmt = $db->prepare("SELECT id, title, published_at FROM news WHERE category = :cat AND id != :id AND status='published' LIMIT 3");
$relStmt->execute([':cat' => $article['category'], ':id' => $id]);
$related = $relStmt->fetchAll();

// Construct current URL for share buttons
$current_url = urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<section class="banner-section bg-primary text-white py-5 position-relative" style="background-image: url('https://www.transparenttextures.com/patterns/black-mamba.png'); border-bottom: 5px solid var(--accent-color);">
    <div class="container text-center py-5 position-relative" style="z-index: 2;">
        <span class="badge bg-accent px-3 py-2 mb-3 rounded-pill"><?= htmlspecialchars($article['category']) ?></span>
        <h1 class="display-5 fw-bold text-white mb-3" data-aos="fade-down"><?= htmlspecialchars($article['title']) ?></h1>
        <ul class="list-inline text-light opacity-75 mb-0" data-aos="fade-up">
            <li class="list-inline-item me-4"><i class="fa-regular fa-calendar text-accent me-2"></i><?= date('F d, Y', strtotime($article['published_at'])) ?></li>
            <li class="list-inline-item"><i class="fa-solid fa-user-pen text-accent me-2"></i><?= htmlspecialchars($article['author'] ?? 'Admin') ?></li>
        </ul>
    </div>
</section>

<section class="section-padding bg-light-grey">
    <div class="container">
        <div class="row justify-content-center">
            
            <div class="col-lg-8" data-aos="fade-up">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
                    
                    <?php if(!empty($article['featured_image'])): ?>
                    <img src="<?= htmlspecialchars($article['featured_image']) ?>" class="card-img-top w-100 object-fit-cover" style="max-height: 400px;" alt="<?= htmlspecialchars($article['title']) ?>">
                    <?php endif; ?>
                    
                    <div class="card-body p-5">
                        <div class="article-content lh-lg text-dark" style="font-size: 1.1rem;">
                            <?= nl2br(htmlspecialchars($article['content'] ?? $article['excerpt'])) ?>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-light p-4 border-top d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold text-primary me-2">Share:</span>
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $current_url ?>" target="_blank" class="btn btn-primary btn-sm rounded-circle shadow-sm" style="width:35px; height:35px; padding:6px;"><i class="fa-brands fa-facebook-f"></i></a>
                            <a href="https://api.whatsapp.com/send?text=<?= urlencode($article['title']) ?> <?= $current_url ?>" target="_blank" class="btn btn-success btn-sm rounded-circle shadow-sm" style="width:35px; height:35px; padding:6px;"><i class="fa-brands fa-whatsapp"></i></a>
                            <button onclick="navigator.clipboard.writeText(window.location.href); alert('Link copied!');" class="btn btn-secondary btn-sm rounded-circle shadow-sm" style="width:35px; height:35px; padding:6px;"><i class="fa-solid fa-link"></i></button>
                        </div>
                        <a href="news.php" class="btn btn-outline-primary rounded-pill btn-sm px-4"><i class="fa-solid fa-arrow-left me-2"></i> Back to News</a>
                    </div>
                </div>
                
                <!-- Related Posts -->
                <?php if(!empty($related)): ?>
                <div class="mb-5">
                    <h4 class="text-primary fw-bold mb-4 border-bottom pb-2">Related Articles</h4>
                    <div class="row g-4">
                        <?php foreach($related as $rel): ?>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm border-start border-4 border-accent h-100 p-3">
                                <h6><a href="news-detail.php?id=<?= $rel['id'] ?>" class="text-primary text-decoration-none lh-base"><?= htmlspecialchars($rel['title']) ?></a></h6>
                                <span class="small text-muted mt-auto pt-2"><i class="fa-regular fa-clock me-1"></i> <?= date('M d, Y', strtotime($rel['published_at'])) ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</section>

<!-- Include required specific styling for article -->
<style>
.article-content img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 20px 0;
}
.article-content h2, .article-content h3 {
    color: var(--primary-color);
    margin-top: 30px;
    margin-bottom: 15px;
}
.article-content blockquote {
    border-left: 5px solid var(--accent-color);
    padding: 15px 20px;
    background-color: var(--light-grey);
    font-style: italic;
    border-radius: 4px;
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
