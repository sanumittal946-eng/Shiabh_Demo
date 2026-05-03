<?php
// testimonials.php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

$db = getDB();

// Fetch all active testimonials
$stmt = $db->query("SELECT * FROM testimonials WHERE is_active = 1 ORDER BY rating DESC, id DESC");
$testimonials = $stmt->fetchAll();

// Calculate Average
$totalRating = 0;
$count = count($testimonials);
foreach($testimonials as $t) {
    if($t['type'] != 'video') { // Video ones might not have literal stars
        $totalRating += $t['rating'];
    }
}
$average = $count > 0 ? round($totalRating / $count, 1) : 0;

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<section class="banner-section bg-primary text-white py-5 position-relative" style="background-image: url('https://www.transparenttextures.com/patterns/black-linen.png'); border-bottom: 5px solid var(--accent-color);">
    <div class="container text-center py-5 position-relative" style="z-index: 2;">
        <h1 class="display-4 fw-bold text-white mb-3" data-aos="fade-down">What People Say</h1>
        <p class="lead text-light opacity-75 mx-auto" style="max-width: 600px;">Real stories. Real results. Read how Sahib Classes has transformed the lives of students and brought smiles to parents.</p>
        <nav aria-label="breadcrumb" class="mt-3">
            <ol class="breadcrumb justify-content-center mb-0">
                <li class="breadcrumb-item"><a href="index.php" class="text-white text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active text-accent" aria-current="page">Testimonials</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Stats Header -->
<section class="py-4 bg-white border-bottom shadow-sm z-index-2 position-relative pt-5 pb-5 mt-n3 rounded-bottom" style="z-index: 3;">
    <div class="container text-center" data-aos="zoom-in">
        <h2 class="display-1 fw-bold text-primary mb-0"><?= $average ?></h2>
        <div class="fs-4 text-warning mb-2">
            <?php 
                $fullStars = floor($average);
                $halfStar = ($average - $fullStars) >= 0.5 ? 1 : 0;
                $emptyStars = 5 - ($fullStars + $halfStar);
                
                for($i=0; $i<$fullStars; $i++) echo '<i class="fa-solid fa-star"></i>';
                if($halfStar) echo '<i class="fa-solid fa-star-half-stroke"></i>';
                for($i=0; $i<$emptyStars; $i++) echo '<i class="fa-regular fa-star"></i>';
            ?>
        </div>
        <p class="fw-bold text-muted text-uppercase small">Average Rating from <?= $count ?>+ Verified Reviews</p>
    </div>
</section>

<section class="section-padding bg-light-grey">
    <div class="container">
        
        <!-- Filter Controls -->
        <div class="d-flex flex-wrap justify-content-center gap-3 mb-5" data-aos="fade-up">
            <button class="btn btn-primary active filter-btn rounded-pill px-4" data-filter="all">All Reviews</button>
            <button class="btn btn-outline-primary filter-btn rounded-pill px-4" data-filter="student">Students</button>
            <button class="btn btn-outline-primary filter-btn rounded-pill px-4" data-filter="parent">Parents</button>
            <button class="btn btn-outline-primary filter-btn rounded-pill px-4" data-filter="video">Video Experiences</button>
        </div>

        <!-- Masonry Grid -->
        <div class="row" data-masonry='{"percentPosition": true }'>
            <?php foreach($testimonials as $t): ?>
            <div class="col-lg-4 col-md-6 filter-item mb-4" data-category="<?= htmlspecialchars($t['type']) ?>" data-rating="<?= $t['rating'] ?>">
                <div class="card border-0 shadow-sm h-100 rounded-4">
                    <div class="card-body p-4">
                        
                        <?php if($t['type'] == 'video' && !empty($t['video_url'])): ?>
                            <!-- Video Embed -->
                            <div class="ratio ratio-16x9 mb-3 rounded overflow-hidden">
                                <iframe src="<?= htmlspecialchars($t['video_url']) ?>" title="Testimonial" allowfullscreen></iframe>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($t['type'] != 'video'): ?>
                            <!-- Star Rating -->
                            <div class="text-warning small mb-3">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <i class="fa-<?= $i <= $t['rating'] ? 'solid' : 'regular' ?> fa-star"></i>
                                <?php endfor; ?>
                            </div>
                            
                            <!-- Review Text -->
                            <p class="text-dark fs-6" style="line-height: 1.6;">"<?= nl2br(htmlspecialchars($t['review'])) ?>"</p>
                        <?php endif; ?>
                        
                    </div>
                    <div class="card-footer bg-transparent border-0 px-4 pb-4 pt-1 d-flex align-items-center">
                        <img src="<?= !empty($t['photo']) ? htmlspecialchars($t['photo']) : 'https://via.placeholder.com/60x60?text=User' ?>" class="rounded-circle me-3 object-fit-cover shadow-sm" style="width: 50px; height: 50px;" alt="<?= htmlspecialchars($t['name']) ?>">
                        <div>
                            <h6 class="mb-0 fw-bold text-primary"><?= htmlspecialchars($t['name']) ?></h6>
                            <span class="small text-muted"><?= ucfirst($t['type']) ?> <?= !empty($t['course']) ? ' - ' . htmlspecialchars($t['course']) : '' ?></span>
                        </div>
                        <i class="fa-solid fa-quote-right fs-1 text-light opacity-50 ms-auto pt-2"></i>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if(empty($testimonials)): ?>
                <div class="col-12 text-center text-muted py-5">No testimonials available.</div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Masonry layout script -->
<script src="https://cdn.jsdelivr.net/npm/masonry-layout@4.2.2/dist/masonry.pkgd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Basic Filtering logic. Since masonry needs re-layout, we do that if possible.
    const filterBtns = document.querySelectorAll('.filter-btn');
    const items = document.querySelectorAll('.filter-item');
    
    // Attempt re-init of masonry on filter
    var msnry = null;
    if (typeof Masonry !== 'undefined') {
        const grid = document.querySelector('[data-masonry]');
        if (grid) msnry = Masonry.data(grid);
    }
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => { b.classList.remove('active', 'btn-primary'); b.classList.add('btn-outline-primary'); });
            this.classList.remove('btn-outline-primary');
            this.classList.add('active', 'btn-primary');
            
            const filter = this.getAttribute('data-filter');
            
            items.forEach(item => {
                if(filter === 'all' || filter === item.getAttribute('data-category')) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
            
            if (msnry) setTimeout(() => msnry.layout(), 100);
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
