<?php
// student/submit-review.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/student_header.php';

$db = getDB();
$student_id = $_SESSION['student_id'];
$err = $msg = "";

// Fetch student info
$sStmt = $db->prepare("SELECT s.name, c.name as course_name FROM students s LEFT JOIN courses c ON s.course_id = c.id WHERE s.id = :id");
$sStmt->execute([':id' => $student_id]);
$student = $sStmt->fetch();

// Check if already reviewed
$already = $db->prepare("SELECT id FROM testimonials WHERE name = :name AND type = 'student'");
$already->execute([':name' => $student['name']]);
$hasReviewed = $already->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        $err = "Invalid security token.";
    } else {
        $review = trim($_POST['review']);
        $rating = min(5, max(1, (int)$_POST['rating']));
        $course = $student['course_name'] ?? 'General';
        $name   = $student['name'];

        if (empty($review)) {
            $err = "Please write your review before submitting.";
        } elseif (strlen($review) < 20) {
            $err = "Review is too short. Please write at least 20 characters.";
        } else {
            $stmt = $db->prepare("INSERT INTO testimonials (name, course, review, rating, type, is_active) VALUES (:name, :course, :review, :rating, 'student', 0)");
            if ($stmt->execute([':name' => $name, ':course' => $course, ':review' => $review, ':rating' => $rating])) {
                $msg = "Your review has been submitted successfully!";
                $hasReviewed = true;
            } else {
                $err = "Something went wrong. Please try again.";
            }
        }
    }
}
?>

<style>
.review-wrap { max-width: 680px; margin: 0 auto; }
.review-hero { background: linear-gradient(135deg, #1a365d 0%, #2d6a4f 100%); border-radius: 18px; padding: 2rem; color: #fff; margin-bottom: 1.5rem; position: relative; overflow: hidden; }
.review-hero::after { content: ''; position: absolute; top: -40px; right: -40px; width: 150px; height: 150px; border-radius: 50%; background: rgba(255,255,255,.07); }
.review-card { border-radius: 18px; border: none; box-shadow: 0 4px 24px rgba(0,0,0,.09); }
.star-wrap { display: flex; gap: 8px; }
.star-btn { font-size: 2rem; cursor: pointer; color: #d1d5db; transition: transform .15s, color .15s; line-height: 1; }
.star-btn.active, .star-btn:hover { color: #f59e0b; transform: scale(1.2); }
.star-btn.active ~ .star-btn { color: #d1d5db; }
.rating-label { font-size: .82rem; font-weight: 700; color: #f59e0b; margin-top: 6px; min-height: 20px; }
.char-count { font-size: .75rem; color: #94a3b8; text-align: right; margin-top: 4px; }
.review-textarea { border-radius: 12px; border: 1.5px solid #e2e8f0; font-size: .95rem; resize: vertical; transition: border-color .2s; }
.review-textarea:focus { border-color: #1a365d; box-shadow: 0 0 0 3px rgba(26,54,93,.08); }
.submit-btn { background: linear-gradient(135deg, #1a365d, #2563eb); color: #fff; border: none; border-radius: 10px; padding: .55rem 1.6rem; font-weight: 700; font-size: .92rem; transition: opacity .2s, transform .15s; }
.submit-btn:hover { opacity: .92; transform: translateY(-1px); color: #fff; }
.student-chip { background: #eff6ff; color: #1d4ed8; border-radius: 20px; padding: 4px 14px; font-size: .78rem; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; }
.success-box { background: linear-gradient(135deg,#ecfdf5,#d1fae5); border: 1.5px solid #6ee7b7; border-radius: 18px; padding: 2.5rem; text-align: center; }
.tip-box { background: #f8fafc; border-left: 3px solid #3b82f6; border-radius: 0 10px 10px 0; padding: .7rem 1rem; font-size: .82rem; color: #475569; }
</style>

<div class="review-wrap">

  <!-- HERO -->
  <div class="review-hero">
    <div class="d-flex align-items-center gap-3 position-relative" style="z-index:2">
      <div style="font-size:2.5rem;line-height:1">⭐</div>
      <div>
        <h4 class="fw-bold mb-1">Write a Review</h4>
        <p class="mb-0 opacity-75 small">Your feedback helps us improve and inspires fellow students.</p>
      </div>
    </div>
  </div>

  <?php if (!empty($msg)): ?>
  <!-- SUCCESS STATE -->
  <div class="success-box">
    <div style="font-size:3.5rem;line-height:1" class="mb-3">🎉</div>
    <h5 class="fw-bold text-success mb-1">Thank You, <?= htmlspecialchars($student['name']) ?>!</h5>
    <p class="text-muted mb-4 small">Your review has been submitted and is pending admin approval. It will appear on the testimonials page soon.</p>
    <a href="dashboard.php" class="btn submit-btn px-4">
      <i class="fa-solid fa-house me-1"></i> Back to Dashboard
    </a>
  </div>

  <?php elseif ($hasReviewed): ?>
  <!-- ALREADY REVIEWED -->
  <div class="review-card card p-4 text-center">
    <div style="font-size:3rem" class="mb-3">✅</div>
    <h5 class="fw-bold mb-1">You've Already Submitted a Review</h5>
    <p class="text-muted small mb-4">Thank you! Your review is under review by our admin team.</p>
    <a href="dashboard.php" class="btn submit-btn px-4"><i class="fa-solid fa-house me-1"></i> Back to Dashboard</a>
  </div>

  <?php else: ?>
  <!-- FORM -->
  <div class="review-card card">
    <div class="card-body p-4">

      <!-- Student chip -->
      <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div class="fw-bold" style="font-size:.95rem">Share your experience</div>
        <div>
          <span class="student-chip"><i class="fa-solid fa-user-graduate"></i><?= htmlspecialchars($student['name']) ?></span>
          <span class="student-chip ms-1" style="background:#f5f3ff;color:#7c3aed"><i class="fa-solid fa-book"></i><?= htmlspecialchars($student['course_name'] ?? 'General') ?></span>
        </div>
      </div>

      <?php if ($err): ?>
      <div class="alert d-flex align-items-center gap-2 py-2 mb-3" style="background:#fff1f2;border:1px solid #fecdd3;border-radius:10px;font-size:.85rem;color:#be123c">
        <i class="fa-solid fa-circle-exclamation"></i><?= htmlspecialchars($err) ?>
      </div>
      <?php endif; ?>

      <form method="POST" id="reviewForm">
        <?= csrfField() ?>

        <!-- STAR RATING -->
        <div class="mb-4">
          <label class="form-label fw-bold small text-uppercase text-muted tracking-wide">Your Rating *</label>
          <input type="hidden" name="rating" id="ratingValue" value="5">
          <div class="star-wrap" id="starWrap">
            <?php for($i=1;$i<=5;$i++): ?>
              <i class="fa-solid fa-star star-btn <?= $i<=5?'active':'' ?>" data-value="<?= $i ?>"></i>
            <?php endfor; ?>
          </div>
          <div class="rating-label" id="ratingLabel">⭐ Excellent</div>
        </div>

        <!-- REVIEW TEXT -->
        <div class="mb-3">
          <label class="form-label fw-bold small text-uppercase text-muted">Your Review *</label>
          <textarea name="review" id="reviewText" class="form-control review-textarea" rows="5"
            placeholder="Tell us about your learning experience — the teachers, study material, doubt sessions, results..."
            maxlength="1000" required></textarea>
          <div class="char-count"><span id="charCount">0</span>/1000 characters</div>
        </div>

        <!-- TIPS -->
        <div class="tip-box mb-4">
          <i class="fa-solid fa-lightbulb text-primary me-1"></i>
          <strong>Tip:</strong> Mention your subject, batch, and what you liked most. Honest reviews help other students choose better!
        </div>

        <!-- SUBMIT -->
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
          <a href="dashboard.php" class="btn btn-sm btn-outline-secondary px-3" style="border-radius:8px">
            <i class="fa-solid fa-arrow-left me-1"></i> Cancel
          </a>
          <button type="submit" name="submit_review" class="submit-btn btn">
            <i class="fa-solid fa-paper-plane me-1"></i> Submit Review
          </button>
        </div>
      </form>
    </div>
  </div>
  <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const stars  = document.querySelectorAll('.star-btn');
    const input  = document.getElementById('ratingValue');
    const label  = document.getElementById('ratingLabel');
    const labels = ['', '😞 Poor', '😐 Fair', '🙂 Good', '😊 Very Good', '⭐ Excellent'];

    function setRating(val) {
        input.value = val;
        label.textContent = labels[val] || '';
        stars.forEach((s, idx) => {
            if (idx < val) { s.classList.add('active'); }
            else           { s.classList.remove('active'); }
        });
    }

    stars.forEach(star => {
        star.addEventListener('click',      function() { setRating(parseInt(this.dataset.value)); });
        star.addEventListener('mouseenter', function() {
            const hval = parseInt(this.dataset.value);
            stars.forEach((s, idx) => s.style.color = idx < hval ? '#f59e0b' : '');
        });
        star.addEventListener('mouseleave', () => stars.forEach(s => s.style.color = ''));
    });
    setRating(5); // default 5 stars

    // Character counter
    const ta = document.getElementById('reviewText');
    const cc = document.getElementById('charCount');
    if (ta && cc) {
        ta.addEventListener('input', () => {
            cc.textContent = ta.value.length;
            cc.style.color = ta.value.length > 900 ? '#ef4444' : '#94a3b8';
        });
    }
});
</script>

<?php include __DIR__ . '/../includes/student_footer.php'; ?>
