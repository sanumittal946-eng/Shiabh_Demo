<?php
// admin/doubts.php
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$msg = $err = "";

// Handle Reply
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_reply'])) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        $err = "Invalid security token.";
    } else {
        $doubt_id = (int)$_POST['doubt_id'];
        $reply_text = trim($_POST['reply']);
        
        if (!empty($reply_text)) {
            $stmt = $db->prepare("INSERT INTO doubt_replies (doubt_id, reply, replied_by) VALUES (:did, :rep, :by)");
            if ($stmt->execute([
                ':did' => $doubt_id,
                ':rep' => $reply_text,
                ':by' => $_SESSION['admin_username'] ?? 'Admin'
            ])) {
                // Update doubt status to resolved
                $db->prepare("UPDATE doubts SET status='resolved' WHERE id=:did")->execute([':did' => $doubt_id]);
                $msg = "Reply sent and doubt assigned as resolved.";
            }
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 shadow-sm rounded">
    <h4 class="mb-0 text-dark fw-bold">Doubt Resolution Hub</h4>
</div>

<?php if (!empty($msg)): ?>
    <div class="alert alert-success"><i class="fa-solid fa-check-circle me-2"></i><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<?php if (!empty($err)): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($err) ?></div>
<?php endif; ?>

<div class="row g-4">
    <?php
    $doubts = $db->query("
        SELECT d.*, s.name as student_name, c.name as course_name 
        FROM doubts d 
        LEFT JOIN students s ON d.student_id = s.id 
        LEFT JOIN courses c ON d.course_id = c.id 
        ORDER BY d.status ASC, d.id DESC
    ")->fetchAll();
    
    if (empty($doubts)):
    ?>
        <div class="col-12"><div class="card p-5 text-center text-muted"><p>No doubts pending resolution! Relax.</p></div></div>
    <?php else: foreach($doubts as $d): ?>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100 <?= $d['status'] == 'open' ? 'border-start border-4 border-warning' : 'border-start border-4 border-success' ?>">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <div>
                            <h6 class="fw-bold mb-0"><?= htmlspecialchars($d['student_name'] ?? 'Unknown Student') ?></h6>
                            <small class="text-muted"><?= htmlspecialchars($d['course_name'] ?? 'General') ?> &bull; <?= date('M d, Y', strtotime($d['created_at'])) ?></small>
                        </div>
                        <div>
                            <?php if($d['status'] == 'open'): ?>
                                <span class="badge bg-warning text-dark">Awaiting Reply</span>
                            <?php else: ?>
                                <span class="badge bg-success">Resolved</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="bg-light p-3 rounded mb-3 border">
                        <?= nl2br(htmlspecialchars($d['question'])) ?>
                    </div>
                    
                    <?php if($d['status'] == 'open'): ?>
                        <form method="POST" action="doubts.php">
                            <?= csrfField() ?>
                            <input type="hidden" name="doubt_id" value="<?= $d['id'] ?>">
                            <div class="mb-2">
                                <textarea name="reply" class="form-control" rows="2" placeholder="Write your professional explanation here..." required></textarea>
                            </div>
                            <button type="submit" name="send_reply" class="btn btn-sm btn-primary w-100"><i class="fa-solid fa-paper-plane me-1"></i> Send Reply & Resolve</button>
                        </form>
                    <?php else: 
                        // Fetch the reply
                        $stmt = $db->prepare("SELECT * FROM doubt_replies WHERE doubt_id = :did ORDER BY id DESC LIMIT 1");
                        $stmt->execute([':did' => $d['id']]);
                        $rep = $stmt->fetch();
                        if ($rep):
                    ?>
                        <div class="bg-success bg-opacity-10 p-3 rounded border border-success border-opacity-25">
                            <small class="fw-bold text-success"><i class="fa-solid fa-reply me-1"></i> <?= htmlspecialchars($rep['replied_by']) ?> replied:</small>
                            <p class="mb-0 mt-1 small"><?= nl2br(htmlspecialchars($rep['reply'])) ?></p>
                        </div>
                    <?php endif; endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
