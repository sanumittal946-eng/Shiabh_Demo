<?php
// materials.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/student_header.php';

$db = getDB();
$student_id = $_SESSION['student_id'];

// Get Student Course
$sStmt = $db->prepare("SELECT course_id FROM students WHERE id = :id");
$sStmt->execute([':id' => $student_id]);
$course_id = $sStmt->fetchColumn();

// Fetch Materials for this course only
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where = "WHERE course_id = :cid";
$params = [':cid' => $course_id];

if(!empty($search)) {
    $where .= " AND (title LIKE :search OR subject LIKE :search)";
    $params[':search'] = "%$search%";
}

$stmt = $db->prepare("SELECT * FROM materials $where ORDER BY uploaded_at DESC");
$stmt->execute($params);
$mats = $stmt->fetchAll();

$mByType = [
    'Notes' => [],
    'Videos' => [],
    'Practice Papers' => [],
    'Previous Years' => []
];
foreach($mats as $m) {
    if(isset($mByType[$m['type']])) $mByType[$m['type']][] = $m;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 text-dark fw-bold">Resources & Study Library</h4>
</div>

<div class="bg-white p-3 rounded shadow-sm mb-4 d-flex gap-3">
    <form class="d-flex flex-grow-1">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
            <input type="text" name="search" class="form-control border-start-0" placeholder="Search notes, topics or videos..." value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-primary px-4" type="submit">Search</button>
        </div>
    </form>
</div>

<div class="row g-4">
    <?php foreach(['Notes' => 'danger', 'Videos' => 'primary', 'Practice Papers' => 'success'] as $type => $color): 
        $items = $mByType[$type];
    ?>
    <div class="col-12">
        <h5 class="fw-bold mb-3 border-start border-4 border-<?= $color ?> ps-3 text-dark"><?= $type ?></h5>
        <div class="row g-3">
            <?php if(empty($items)): ?>
                <div class="col-12"><p class="text-muted small ps-3">No <?= strtolower($type) ?> available for your course yet.</p></div>
            <?php else: foreach($items as $i): ?>
                <div class="col-md-4 col-lg-3">
                    <div class="card border-0 shadow-sm h-100">
                        <?php if($type == 'Videos'): ?>
                            <div class="ratio ratio-16x9">
                                <iframe src="<?= htmlspecialchars($i['file_path']) ?>" allowfullscreen></iframe>
                            </div>
                        <?php endif; ?>
                        <div class="card-body p-3">
                            <div class="small fw-bold text-<?= $color ?> text-uppercase mb-1" style="font-size: 0.7rem;"><?= htmlspecialchars($i['subject']) ?></div>
                            <h6 class="fw-bold small mb-2 lh-base"><?= htmlspecialchars($i['title']) ?></h6>
                            <?php if($type != 'Videos'): ?>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="smaller text-muted"><?= $i['file_size'] ?></span>
                                    <a href="<?= htmlspecialchars($i['file_path']) ?>" target="_blank" class="btn btn-xs btn-outline-<?= $color ?> py-1 px-2 small" style="font-size: 0.75rem;">Download <i class="fa-solid fa-download ms-1"></i></a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>
        <hr class="my-4 opacity-50">
    </div>
    <?php endforeach; ?>
</div>

        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
