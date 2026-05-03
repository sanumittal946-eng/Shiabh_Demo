<?php
// admin/test_submissions.php
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$test_id = (int)($_GET['test_id'] ?? 0);
$msg = $err = "";

// Fetch Test Detail
$stmt = $db->prepare("SELECT t.*, c.name as course_name FROM tests t LEFT JOIN courses c ON t.course_id = c.id WHERE t.id = :id");
$stmt->execute([':id' => $test_id]);
$test = $stmt->fetch();

if (!$test) {
    die("Invalid Test Selected. <a href='tests.php'>Go Back</a>");
}

// Handle Grading
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_grade'])) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        $err = "Invalid security token.";
    } else {
        $sub_id = (int)$_POST['submission_id'];
        $score = (int)$_POST['score'];
        $feedback = trim($_POST['feedback']);
        
        if ($score > $test['total_marks']) {
            $err = "Score cannot be greater than Total Marks ({$test['total_marks']}).";
        } else {
            $upd = $db->prepare("UPDATE test_submissions SET score = :sc, feedback = :fb, status = 'checked', checked_at = CURRENT_TIMESTAMP WHERE id = :id");
            if ($upd->execute([':sc' => $score, ':fb' => $feedback, ':id' => $sub_id])) {
                $msg = "Submission graded successfully!";
            } else {
                $err = "Database error grading.";
            }
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 shadow-sm rounded">
    <div>
        <h4 class="mb-0 text-dark fw-bold">Submissions Tracker</h4>
        <div class="text-muted small mt-1">Viewing submissions for: <span class="fw-bold text-primary"><?= htmlspecialchars($test['title']) ?></span> (<?= htmlspecialchars($test['course_name']) ?>)</div>
    </div>
    <a href="tests.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Back to Tests</a>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-4 border-info">
            <div class="card-body py-2">
                <small class="text-muted fw-bold text-uppercase">Total Marks</small>
                <div class="fs-4 fw-bold text-dark"><?= $test['total_marks'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-4 border-danger">
            <div class="card-body py-2">
                <small class="text-muted fw-bold text-uppercase">Passing Marks</small>
                <div class="fs-4 fw-bold text-dark"><?= $test['passing_marks'] ?></div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($msg)): ?>
    <div class="alert alert-success"><i class="fa-solid fa-check-circle me-2"></i><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<?php if (!empty($err)): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($err) ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Student Name</th>
                        <th>Submitted At</th>
                        <th>File Answer</th>
                        <th>Status & Score</th>
                        <th>Check / Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $subs = $db->prepare("
                        SELECT ts.*, s.name as student_name 
                        FROM test_submissions ts 
                        LEFT JOIN students s ON ts.student_id = s.id 
                        WHERE ts.test_id = :tid 
                        ORDER BY ts.status ASC, ts.submitted_at DESC
                    ");
                    $subs->execute([':tid' => $test_id]);
                    $records = $subs->fetchAll();
                    
                    if (empty($records)):
                    ?>
                        <tr><td colspan="5" class="text-center text-muted">No submissions yet.</td></tr>
                    <?php else: foreach($records as $sub): ?>
                        <tr>
                            <td class="fw-bold text-primary"><?= htmlspecialchars($sub['student_name'] ?? 'Unknown Student') ?></td>
                            <td><?= date('M d, Y h:i A', strtotime($sub['submitted_at'])) ?></td>
                            <td>
                                <a href="../<?= htmlspecialchars($sub['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-info"><i class="fa-solid fa-download me-1"></i> View Answers</a>
                            </td>
                            <td>
                                <?php if($sub['status'] == 'submitted'): ?>
                                    <span class="badge bg-warning text-dark"><i class="fa-solid fa-clock me-1"></i>Pending Check</span>
                                <?php else: ?>
                                    <span class="badge bg-success mb-1">Checked</span>
                                    <div class="fw-bold <?= $sub['score'] >= $test['passing_marks'] ? 'text-success' : 'text-danger' ?>">
                                        Score: <?= $sub['score'] ?> / <?= $test['total_marks'] ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <!-- Grading Dropdown Form -->
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-<?= $sub['status'] == 'checked' ? 'outline-secondary' : 'primary' ?> dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                                        <?= $sub['status'] == 'checked' ? 'Update Grade' : 'Grade Now' ?>
                                    </button>
                                    <div class="dropdown-menu p-3 shadow" style="min-width: 250px;">
                                        <form method="POST" action="test_submissions.php?test_id=<?= $test_id ?>">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="submission_id" value="<?= $sub['id'] ?>">
                                            <div class="mb-2">
                                                <label class="form-label small fw-bold">Score (out of <?= $test['total_marks'] ?>)</label>
                                                <input type="number" name="score" class="form-control form-control-sm" required min="0" max="<?= $test['total_marks'] ?>" value="<?= $sub['score'] ?? '' ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Feedback Remarks</label>
                                                <textarea name="feedback" class="form-control form-control-sm" rows="2" placeholder="Great job / Need improvement on Q3..."><?= htmlspecialchars($sub['feedback'] ?? '') ?></textarea>
                                            </div>
                                            <button class="btn btn-sm btn-success w-100" type="submit" name="save_grade">Submit Evaluation</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
