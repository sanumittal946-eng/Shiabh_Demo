<?php
// timetable.php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

$db = getDB();

// Fetch distinct batches
$batchesStmt = $db->query("SELECT DISTINCT batch FROM timetable ORDER BY batch ASC");
$batches = $batchesStmt->fetchAll(PDO::FETCH_COLUMN);

// Pre-select batch
$selected_batch = isset($_GET['batch']) ? $_GET['batch'] : (!empty($batches) ? $batches[0] : '');

// Fetch timetable for selected batch
$tt = [];
if ($selected_batch) {
    // Generate a structured timetable array. 
    // Usually timetable table is normalized. For this demo we assume days and periods 1-6.
    $stmt = $db->prepare("
        SELECT t.*, f.name as fac_name 
        FROM timetable t 
        LEFT JOIN faculty f ON t.faculty_id = f.id 
        WHERE t.batch = :b 
        ORDER BY t.period ASC
    ");
    $stmt->execute([':b' => $selected_batch]);
    $ttData = $stmt->fetchAll();
    
    foreach($ttData as $row){
        $tt[$row['day']][$row['period']] = $row;
    }
}
// Array mapping for rendering table Mon-Sat, periods 1-5
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$periods = [1, 2, 3, 4, 5];

// Fetch Upcoming tests/exams mock data
$upcomingExams = [
    ['name' => 'Monthly Unit Test 1', 'date' => date('d M, Y', strtotime('+5 days'))],
    ['name' => 'JEE Mock Paper 4', 'date' => date('d M, Y', strtotime('+12 days'))],
    ['name' => 'Board Revision Test', 'date' => date('d M, Y', strtotime('+20 days'))]
];

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<section class="banner-section bg-primary text-white py-5 position-relative" style="background-image: url('https://www.transparenttextures.com/patterns/black-linen.png'); border-bottom: 5px solid var(--accent-color);">
    <div class="container text-center py-5 position-relative" style="z-index: 2;">
        <h1 class="display-4 fw-bold text-white mb-3" data-aos="fade-down">Timetable & Schedule</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-center mb-0">
                <li class="breadcrumb-item"><a href="index.php" class="text-white text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active text-accent" aria-current="page">Timetable</li>
            </ol>
        </nav>
    </div>
</section>

<section class="section-padding bg-light-grey">
    <div class="container">
        
        <!-- Batch Selector -->
        <div class="row justify-content-center mb-5" data-aos="fade-up">
            <div class="col-lg-6 col-md-8 text-center bg-white p-4 rounded shadow-sm">
                <form action="timetable.php" method="GET" class="d-flex align-items-center justify-content-center gap-3" id="batchForm">
                    <label class="fw-bold text-primary mb-0 flex-shrink-0">Select Batch:</label>
                    <select name="batch" class="form-select w-50" onchange="document.getElementById('batchForm').submit()">
                        <?php if(empty($batches)): ?>
                            <option>No batches available</option>
                        <?php else: ?>
                            <?php foreach($batches as $b): ?>
                                <option value="<?= htmlspecialchars($b) ?>" <?= $selected_batch == $b ? 'selected' : '' ?>><?= htmlspecialchars($b) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </form>
            </div>
        </div>

        <div class="row g-5">
            <!-- Weekly Timetable -->
            <div class="col-lg-8" data-aos="fade-right">
                <div class="card border-0 shadow-sm rounded overflow-hidden">
                    <div class="card-header bg-primary text-white p-3 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 fs-5"><i class="fa-regular fa-calendar-days me-2"></i> Weekly Schedule - <?= htmlspecialchars($selected_batch) ?></h4>
                        <button onclick="window.print()" class="btn btn-sm btn-outline-light d-print-none"><i class="fa-solid fa-file-pdf me-1"></i> Save as PDF</button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive printable-area">
                            <!-- Title only shows in print -->
                            <h2 class="d-none d-print-block text-center mt-3 mb-4 border-bottom pb-2">Sahib Classes Weekly Timetable: <?= htmlspecialchars($selected_batch) ?></h2>
                            
                            <table class="table table-bordered table-hover text-center align-middle mb-0" style="min-width: 700px;">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="py-3 bg-secondary text-white opacity-75">Day / Period</th>
                                        <?php foreach($periods as $p): ?>
                                            <th class="py-3 text-primary">Period <?= $p ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($days as $day): ?>
                                    <tr>
                                        <td class="fw-bold bg-light py-4"><?= $day ?></td>
                                        <?php foreach($periods as $p): ?>
                                            <?php if(isset($tt[$day][$p])): ?>
                                                <?php $cell = $tt[$day][$p]; ?>
                                                <td class="bg-white">
                                                    <div class="fw-bold text-dark rounded px-2 py-1 mb-1 shadow-sm d-inline-block" style="background-color: var(--light-grey);"><?= htmlspecialchars($cell['subject']) ?></div>
                                                    <div class="small text-muted"><i class="fa-solid fa-user-tie text-accent me-1"></i> <?= htmlspecialchars($cell['fac_name'] ?? 'TBA') ?></div>
                                                    <div class="small text-muted"><i class="fa-solid fa-door-open text-accent me-1"></i> Room <?= htmlspecialchars($cell['room'] ?? '-') ?></div>
                                                    <div class="small text-accent mt-1" style="font-size: 11px;"><?= date('H:i', strtotime($cell['time_start'])) ?> - <?= date('H:i', strtotime($cell['time_end'])) ?></div>
                                                </td>
                                            <?php else: ?>
                                                <td class="text-muted small bg-light opacity-50">- Free -</td>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar: Exams & Holidays -->
            <div class="col-lg-4 d-print-none" data-aos="fade-left">
                <!-- Upcoming Exams -->
                <div class="card border-0 shadow-sm mb-4 border-top border-4 border-accent">
                    <div class="card-body p-4">
                        <h4 class="text-primary mb-4 fw-bold fs-5"><i class="fa-solid fa-file-pen me-2"></i> Upcoming Exams</h4>
                        <ul class="list-group list-group-flush">
                            <?php foreach($upcomingExams as $exam): ?>
                            <li class="list-group-item px-0 py-3 border-bottom d-flex align-items-center">
                                <div class="bg-primary text-white p-2 rounded text-center me-3 shadow-sm" style="min-width: 60px;">
                                    <span class="d-block fw-bold fs-5" style="line-height:1;"><?= date('d', strtotime($exam['date'])) ?></span>
                                    <span class="d-block small"><?= date('M', strtotime($exam['date'])) ?></span>
                                </div>
                                <div>
                                    <h6 class="mb-1 text-dark fw-bold"><?= htmlspecialchars($exam['name']) ?></h6>
                                    <small class="text-muted">Mandatory test</small>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <!-- Holiday Calendar Simple UI -->
                <div class="card border-0 shadow-sm bg-primary text-white">
                    <div class="card-body p-4 text-center">
                        <h4 class="mb-4 fw-bold fs-5 text-accent"><i class="fa-solid fa-umbrella-beach me-2"></i> Upcoming Holidays</h4>
                        <div class="d-flex justify-content-between border-bottom border-secondary pb-2 mb-2">
                            <span>Summer Break</span>
                            <span class="text-accent fw-bold">May 15 - Jun 05</span>
                        </div>
                         <div class="d-flex justify-content-between border-bottom border-secondary pb-2 mb-2">
                            <span>Independence Day</span>
                            <span class="text-accent fw-bold">Aug 15</span>
                        </div>
                         <div class="d-flex justify-content-between">
                            <span>Diwali Holidays</span>
                            <span class="text-accent fw-bold">Oct 24 - Oct 27</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<!-- Make it look good when printed as PDF -->
<style>
@media print {
    body * {
        visibility: hidden;
    }
    .printable-area, .printable-area * {
        visibility: visible;
    }
    .printable-area {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    .bg-light {
        background-color: #f8f9fa !important;
        -webkit-print-color-adjust: exact; 
    }
    .bg-secondary {
        background-color: #6c757d !important;
        color: white !important;
        -webkit-print-color-adjust: exact; 
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
