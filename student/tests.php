<?php
// tests.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/student_header.php';

$db = getDB();
$student_id = $_SESSION['student_id'];
$err = $msg = "";

// Get Student Course
$sStmt = $db->prepare("SELECT course_id FROM students WHERE id = :id");
$sStmt->execute([':id' => $student_id]);
$course_id = $sStmt->fetchColumn();

// Handle Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_submission'])) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        $err = "Invalid security token.";
    } else {
        $test_id = (int)$_POST['test_id'];
        
        // File Upload
        if (isset($_FILES['answer_file']) && $_FILES['answer_file']['error'] == 0) {
            $uploadDir = __DIR__ . '/uploads/submissions/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            $fileName = time() . '_s' . $student_id . '_' . basename($_FILES['answer_file']['name']);
            $target = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['answer_file']['tmp_name'], $target)) {
                $filePath = 'uploads/submissions/' . $fileName;
                
                // Track submission in DB
                $ins = $db->prepare("INSERT INTO test_submissions (test_id, student_id, file_path) VALUES (:tid, :sid, :fp)");
                if ($ins->execute([':tid' => $test_id, ':sid' => $student_id, ':fp' => $filePath])) {
                    $msg = "Assignment uploaded successfully!";
                } else {
                    $err = "Database error logging submission.";
                }
            } else {
                $err = "Failed to upload file.";
            }
        } else {
            $err = "Please select a valid PDF file.";
        }
    }
}

// Fetch Available Tests
$stmt = $db->prepare("
    SELECT t.*, 
    (SELECT status FROM test_submissions WHERE test_id = t.id AND student_id = :sid LIMIT 1) as sub_status,
    (SELECT score FROM test_submissions WHERE test_id = t.id AND student_id = :sid2 LIMIT 1) as sub_score
    FROM tests t 
    WHERE t.course_id = :cid AND t.is_active = 1 
    ORDER BY t.id DESC
");
$stmt->execute([':sid' => $student_id, ':sid2' => $student_id, ':cid' => $course_id]);
$tests = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 text-dark fw-bold">My Tests & Evaluations</h4>
</div>

<?php if (!empty($msg)): ?>
    <div class="alert alert-success"><i class="fa-solid fa-check-circle me-2"></i><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<?php if (!empty($err)): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($err) ?></div>
<?php endif; ?>

<div class="row g-4">
    <?php if(empty($tests)): ?>
        <div class="col-12"><div class="card p-5 text-center text-muted"><h6>No tests scheduled for your course.</h6></div></div>
    <?php else: foreach($tests as $t): ?>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-3">
                        <h5 class="fw-bold text-primary mb-0"><?= htmlspecialchars($t['title']) ?></h5>
                        <div class="small fw-bold">Marks: <?= $t['total_marks'] ?></div>
                    </div>
                    <p class="small text-muted mb-4"><?= nl2br(htmlspecialchars($t['description'])) ?></p>
                    
                    <div class="bg-light p-3 rounded mb-4 d-flex justify-content-between align-items-center border">
                        <div>
                            <div class="smaller text-uppercase fw-bold text-muted" style="font-size: 0.65rem;">Question Paper</div>
                            <div class="small fw-bold">Master_QP.pdf</div>
                        </div>
                        <a href="<?= htmlspecialchars($t['file_path']) ?>" target="_blank" class="btn btn-sm btn-dark"><i class="fa-solid fa-file-pdf me-1"></i> Download QP</a>
                    </div>

                    <?php if(!$t['sub_status']): ?>
                        <form method="POST" enctype="multipart/form-data">
                            <?= csrfField() ?>
                            <input type="hidden" name="test_id" value="<?= $t['id'] ?>">
                            <label class="form-label small fw-bold text-muted">Upload Answer Sheet (PDF)</label>
                            <div class="input-group">
                                <input type="file" name="answer_file" class="form-control form-control-sm" accept=".pdf" REQUIRED>
                                <button type="submit" name="upload_submission" class="btn btn-sm btn-primary">Submit Test</button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="border-top pt-3 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge <?= $t['sub_status'] == 'checked' ? 'bg-success' : 'bg-info' ?>"><?= ucfirst($t['sub_status']) ?></span>
                                <small class="text-muted ms-1">Submitted on <?= date('d M') ?></small>
                            </div>
                            <?php if($t['sub_status'] == 'checked'): ?>
                                <div class="text-end">
                                    <div class="smaller text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Your Score</div>
                                    <div class="h4 mb-0 fw-bold <?= $t['sub_score'] >= $t['passing_marks'] ? 'text-success' : 'text-danger' ?>"><?= $t['sub_score'] ?> / <?= $t['total_marks'] ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; endif; ?>
</div>

        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
