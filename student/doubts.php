<?php
// doubts.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/student_header.php';

$db = getDB();
$student_id = $_SESSION['student_id'];
$err = $msg = "";

// Handle New Doubt Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ask_doubt'])) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        $err = "Invalid security token.";
    } else {
        $question = trim($_POST['question']);
        
        // Get student course
        $sStmt = $db->prepare("SELECT course_id FROM students WHERE id = :id");
        $sStmt->execute([':id' => $student_id]);
        $course_id = $sStmt->fetchColumn();

        if (empty($question)) {
            $err = "Please enter your question.";
        } else {
            $stmt = $db->prepare("INSERT INTO doubts (student_id, course_id, question) VALUES (:sid, :cid, :q)");
            if ($stmt->execute([':sid' => $student_id, ':cid' => $course_id, ':q' => $question])) {
                $msg = "Doubt submitted! A teacher will respond soon.";
            } else {
                $err = "Database error.";
            }
        }
    }
}

// Fetch Student Doubts
$stmt = $db->prepare("SELECT * FROM doubts WHERE student_id = :sid ORDER BY id DESC");
$stmt->execute([':sid' => $student_id]);
$doubts = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 text-dark fw-bold">Ask & Solve Doubts</h4>
</div>

<?php if (!empty($msg)): ?>
    <div class="alert alert-success"><i class="fa-solid fa-check-circle me-2"></i><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<?php if (!empty($err)): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($err) ?></div>
<?php endif; ?>

<div class="row g-4">
    <!-- Form -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm sticky-top" style="top: 1rem;">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3">Ask a Question</h5>
                <form method="POST">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Your Doubt / Query</label>
                        <textarea name="question" class="form-control" rows="5" placeholder="Be specific about the topic, chapter or question number..." required></textarea>
                    </div>
                    <div class="d-grid">
                        <button type="submit" name="ask_doubt" class="btn btn-primary py-2 fw-bold">Submit Question</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- History -->
    <div class="col-lg-8">
        <h5 class="fw-bold mb-3 small text-uppercase text-muted">My Doubt History</h5>
        
        <?php if(empty($doubts)): ?>
            <div class="card border-0 shadow-sm p-5 text-center">
                <i class="fa-solid fa-comment-dots fs-1 text-muted opacity-25 mb-3"></i>
                <h6>No questions asked yet.</h6>
            </div>
        <?php else: foreach($doubts as $d): ?>
            <div class="card border-0 shadow-sm mb-3 <?= $d['status'] == 'open' ? 'border-start border-4 border-warning' : 'border-start border-4 border-success' ?>">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge <?= $d['status'] == 'open' ? 'bg-warning text-dark' : 'bg-success' ?> mb-2"><?= ucfirst($d['status']) ?></span>
                        <small class="text-muted"><?= date('d M, Y', strtotime($d['created_at'])) ?></small>
                    </div>
                    <p class="mb-3 fw-bold text-dark"><?= nl2br(htmlspecialchars($d['question'])) ?></p>
                    
                    <?php 
                    if($d['status'] == 'resolved'):
                        // Fetch reply
                        $rStmt = $db->prepare("SELECT * FROM doubt_replies WHERE doubt_id = :did ORDER BY id DESC LIMIT 1");
                        $rStmt->execute([':did' => $d['id']]);
                        $reply = $rStmt->fetch();
                        if($reply):
                    ?>
                        <div class="bg-success bg-opacity-10 p-3 rounded border border-success border-opacity-25 mt-3">
                            <div class="d-flex align-items-center mb-1">
                                <i class="fa-solid fa-reply text-success me-2"></i>
                                <span class="small fw-bold text-success"><?= htmlspecialchars($reply['replied_by']) ?> (Teacher)</span>
                            </div>
                            <p class="mb-0 small text-dark opacity-75"><?= nl2br(htmlspecialchars($reply['reply'])) ?></p>
                        </div>
                    <?php endif; else: ?>
                        <div class="small text-muted"><i class="fa-solid fa-clock me-1"></i> Awaiting teacher's response...</div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>

        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
