<?php
// results.php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

$db = getDB();

// Fetch Toppers
$toppersStmt = $db->query("SELECT * FROM results WHERE is_topper = 1 ORDER BY year DESC, score DESC");
$toppers = $toppersStmt->fetchAll();

// Fetch Competitive Results (Table)
$compStmt = $db->query("SELECT student_name, exam_type, rank, year FROM results WHERE is_topper = 0 ORDER BY year DESC, rank ASC");
$compResults = $compStmt->fetchAll();

// Fetch available years for filter
$yearsStmt = $db->query("SELECT DISTINCT year FROM results ORDER BY year DESC");
$years = $yearsStmt->fetchAll(PDO::FETCH_COLUMN);

// Analytics Data for Chart.js
$chartData = [
    'labels' => ['2019', '2020', '2021', '2022', '2023'],
    'data' => [88, 91, 93, 96, 98] // Mock progression
];

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<section class="banner-section bg-primary text-white py-5 position-relative" style="background-image: url('https://www.transparenttextures.com/patterns/clean-gray-paper.png'); border-bottom: 5px solid var(--accent-color);">
    <div class="container text-center py-5 position-relative" style="z-index: 2;">
        <h1 class="display-4 fw-bold text-white mb-3" data-aos="fade-down">Results & Achievements</h1>
        <p class="lead text-light opacity-75 mx-auto" style="max-width: 600px;">Our track record speaks for itself. Discover the phenomenal performances of our proud students year on year.</p>
        <nav aria-label="breadcrumb" class="mt-3">
            <ol class="breadcrumb justify-content-center mb-0">
                <li class="breadcrumb-item"><a href="index.php" class="text-white text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active text-accent" aria-current="page">Results</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Section 1: Animated Stats -->
<section class="py-5" style="background-color: var(--primary-color);">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="100">
                <div class="display-4 fw-bold text-accent mb-1"><span class="counter" data-target="98">0</span>%</div>
                <p class="text-white fw-semibold mb-0">Overall Pass Rate</p>
            </div>
            <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="200">
                <div class="display-4 fw-bold text-accent mb-1"><span class="counter" data-target="150">0</span>+</div>
                <p class="text-white fw-semibold mb-0">City Toppers</p>
            </div>
            <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="300">
                <div class="display-4 fw-bold text-accent mb-1"><span class="counter" data-target="500">0</span>+</div>
                <p class="text-white fw-semibold mb-0">Competitive Selections</p>
            </div>
            <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="400">
                <div class="display-4 fw-bold text-accent mb-1"><span class="counter" data-target="25">0</span>+</div>
                <p class="text-white fw-semibold mb-0">National Awards</p>
            </div>
        </div>
    </div>
</section>

<!-- Section 2 & 3: Filter & Toppers Grid -->
<section class="section-padding bg-light-grey border-bottom">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-5 border-bottom pb-4" data-aos="fade-up">
            <h2 class="text-primary fw-bold mb-3 mb-md-0">Our Hall of Fame</h2>
            
            <div class="btn-group shadow-sm" role="group" aria-label="Year Filter">
                <button type="button" class="btn btn-primary active filter-btn" data-filter="all">All Time</button>
                <?php foreach($years as $yr): ?>
                    <button type="button" class="btn btn-outline-primary filter-btn" data-filter="<?= $yr ?>"><?= $yr ?></button>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="row justify-content-center text-center g-4">
            <?php foreach($toppers as $top): ?>
            <div class="col-lg-3 col-md-4 col-sm-6 filter-item" data-category="<?= htmlspecialchars($top['year']) ?>" data-aos="fade-up">
                <div class="card h-100 border-0 shadow-sm pt-4 pb-3 px-3 rounded-4 position-relative overflow-hidden">
                    <div class="position-absolute bg-accent text-white fw-bold px-3 py-1 top-0 start-50 translate-middle-x" style="border-bottom-left-radius: 8px; border-bottom-right-radius: 8px; font-size: 14px;">
                        <?= htmlspecialchars($top['year']) ?>
                    </div>
                    
                    <img src="<?= !empty($top['photo']) ? htmlspecialchars($top['photo']) : 'https://via.placeholder.com/120x120?text=Photo' ?>" class="rounded-circle mx-auto mb-3 border border-3 border-primary" style="width: 100px; height: 100px; object-fit: cover;" alt="<?= htmlspecialchars($top['student_name']) ?>">
                    
                    <h5 class="text-primary fw-bold mb-1 fs-6"><?= htmlspecialchars($top['student_name']) ?></h5>
                    <p class="text-muted small mb-2"><?= htmlspecialchars($top['course']) ?></p>
                    
                    <div class="mt-auto pt-3 border-top">
                        <span class="d-block fw-bold text-dark fs-5 text-accent"><?= htmlspecialchars($top['score']) ?></span>
                        <span class="badge bg-primary"><?= htmlspecialchars($top['rank']) ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Section 4 & 5: Table and Chart -->
<section class="section-padding bg-white">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-6" data-aos="fade-right">
                <h3 class="text-primary fw-bold mb-4">Competitive Selections</h3>
                <div class="table-responsive rounded shadow-sm border border-light">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th class="border-0">Student Name</th>
                                <th class="border-0">Exam</th>
                                <th class="border-0">Rank</th>
                                <th class="border-0">Year</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($compResults as $res): ?>
                            <tr>
                                <td class="fw-bold py-3"><?= htmlspecialchars($res['student_name']) ?></td>
                                <td><?= htmlspecialchars($res['exam_type']) ?></td>
                                <td class="text-accent fw-bold"><?= htmlspecialchars($res['rank']) ?></td>
                                <td class="text-muted"><?= htmlspecialchars($res['year']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($compResults)): ?>
                            <tr><td colspan="4" class="text-center py-4 text-muted">Records will be updated soon.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="col-lg-6" data-aos="fade-left">
                <h3 class="text-primary fw-bold mb-4">Consistent Growth (Graph)</h3>
                <div class="card border-0 shadow-sm h-100 p-4">
                    <canvas id="resultsChart" style="min-height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section 6: Awards Gallery -->
<section class="section-padding bg-light-grey border-top">
    <div class="container text-center">
        <h3 class="text-primary fw-bold mb-5" data-aos="fade-up">Awards & Recognitions</h3>
        
        <div class="row justify-content-center g-4 text-muted">
            <div class="col-lg-2 col-md-3 col-6" data-aos="zoom-in" data-aos-delay="100">
                <i class="fa-solid fa-trophy display-4 mb-3 text-secondary"></i>
                <h6 class="fw-bold fs-6">Best Institute 2023</h6>
            </div>
            <div class="col-lg-2 col-md-3 col-6" data-aos="zoom-in" data-aos-delay="200">
                <i class="fa-solid fa-medal display-4 mb-3 text-secondary"></i>
                <h6 class="fw-bold fs-6">Top Educator Award</h6>
            </div>
             <div class="col-lg-2 col-md-3 col-6" data-aos="zoom-in" data-aos-delay="300">
                <i class="fa-solid fa-star display-4 mb-3 text-secondary"></i>
                <h6 class="fw-bold fs-6">Excellence in Ed 2022</h6>
            </div>
             <div class="col-lg-2 col-md-3 col-6" data-aos="zoom-in" data-aos-delay="400">
                <i class="fa-solid fa-shield-halved display-4 mb-3 text-secondary"></i>
                <h6 class="fw-bold fs-6">Trusted Brand 2021</h6>
            </div>
        </div>
    </div>
</section>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter
    const filterBtns = document.querySelectorAll('.filter-btn');
    const items = document.querySelectorAll('.filter-item');
    
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
        });
    });
    
    // Animation Counter
    const counters = document.querySelectorAll('.counter');
    counters.forEach(counter => {
        const updateCount = () => {
            const target = +counter.getAttribute('data-target');
            const count = +counter.innerText;
            const inc = target / 100;
            if (count < target) {
                counter.innerText = Math.ceil(count + inc);
                setTimeout(updateCount, 20);
            } else {
                counter.innerText = target;
            }
        };
        updateCount();
    });

    // Chart Init
    const ctx = document.getElementById('resultsChart').getContext('2d');
    const dbData = <?= json_encode($chartData) ?>;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: dbData.labels,
            datasets: [{
                label: 'Pass Percentage',
                data: dbData.data,
                borderColor: '#F4A226',
                backgroundColor: 'rgba(244, 162, 38, 0.2)',
                borderWidth: 3,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: false,
                    min: 80,
                    max: 100
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
