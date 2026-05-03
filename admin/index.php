<?php
// admin/index.php — Redesigned Admin Dashboard
require_once __DIR__ . '/includes/header.php';
$db = getDB();

// Core stats
$student_count  = $db->query("SELECT COUNT(*) FROM students WHERE is_active=1")->fetchColumn();
$course_count   = $db->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$enquiry_count  = $db->query("SELECT COUNT(*) FROM enquiries WHERE status='pending'")->fetchColumn();
$faculty_count  = $db->query("SELECT COUNT(*) FROM faculty")->fetchColumn();
$doubt_pending  = $db->query("SELECT COUNT(*) FROM doubts WHERE status='open'")->fetchColumn();
$sub_pending    = $db->query("SELECT COUNT(*) FROM test_submissions WHERE status='submitted'")->fetchColumn();
$live_upcoming  = $db->query("SELECT COUNT(*) FROM live_lectures WHERE is_active=1 AND scheduled_at >= NOW()")->fetchColumn();
$recorded_total = $db->query("SELECT COUNT(*) FROM recorded_lectures WHERE is_active=1")->fetchColumn();

// Enrollment by course (for mini bar)
$courseStats = $db->query("SELECT c.name, COUNT(s.id) as cnt FROM courses c LEFT JOIN students s ON s.course_id=c.id GROUP BY c.id ORDER BY cnt DESC LIMIT 6")->fetchAll();

// Recent enquiries
$recent_enq = $db->query("SELECT * FROM enquiries ORDER BY created_at DESC LIMIT 6")->fetchAll();

// Recent students
$recent_stu = $db->query("SELECT s.name, s.email, c.name as cname, s.created_at FROM students s LEFT JOIN courses c ON s.course_id=c.id ORDER BY s.created_at DESC LIMIT 5")->fetchAll();

// Next live class
$nextLive = $db->query("SELECT l.*, c.name as cname FROM live_lectures l LEFT JOIN courses c ON l.course_id=c.id WHERE l.is_active=1 AND l.scheduled_at >= NOW() ORDER BY l.scheduled_at ASC LIMIT 1")->fetch();

$hour = (int)date('G');
$greet = $hour<12?'Good Morning':'($hour<17?\'Good Afternoon\':\'Good Evening\')';
?>
<style>
.dash-header{background:linear-gradient(135deg,#1a365d 0%,#1e3a5f 60%,#2d6a4f 100%);border-radius:18px;color:#fff;padding:2rem;position:relative;overflow:hidden;margin-bottom:1.5rem;}
.dash-header::before{content:'';position:absolute;top:-50px;right:-50px;width:200px;height:200px;border-radius:50%;background:rgba(255,255,255,.05);}
.dash-header::after{content:'';position:absolute;bottom:-70px;right:120px;width:160px;height:160px;border-radius:50%;background:rgba(251,133,0,.08);}
.kpi-card{border-radius:16px;border:none;transition:transform .2s,box-shadow .2s;}
.kpi-card:hover{transform:translateY(-4px);box-shadow:0 12px 32px rgba(0,0,0,.12)!important;}
.kpi-icon{width:52px;height:52px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0;}
.alert-kpi{background:#fff7ed;border-left:4px solid #fb8500;border-radius:0 12px 12px 0;}
.bar-track{height:8px;background:#e2e8f0;border-radius:10px;overflow:hidden;}
.bar-fill{height:100%;border-radius:10px;background:linear-gradient(90deg,#1a365d,#3b82f6);transition:width .8s ease;}
.section-card{border-radius:16px;border:none;box-shadow:0 2px 16px rgba(0,0,0,.07);}
.activity-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0;margin-top:4px;}
.live-pulse{animation:pulseLive 1.3s infinite;}
@keyframes pulseLive{0%,100%{box-shadow:0 0 0 0 rgba(239,68,68,.4);}50%{box-shadow:0 0 0 7px rgba(239,68,68,0);}}
.quick-action{border-radius:12px;border:1.5px solid #e2e8f0;color:#1a365d;font-weight:600;padding:.7rem 1rem;transition:.18s;text-align:left;background:#fff;}
.quick-action:hover{border-color:#1a365d;background:#eff6ff;color:#1a365d;}
</style>

<!-- HEADER -->
<div class="dash-header">
  <div class="row align-items-center position-relative" style="z-index:2">
    <div class="col-md-8">
      <div style="font-size:.82rem;opacity:.65;font-weight:500"><?= date('l, d F Y') ?> · Admin Dashboard</div>
      <h3 class="fw-bold mt-1 mb-1">Welcome back, <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?>! 👋</h3>
      <p class="mb-3 opacity-70 small">Here's what's happening at your institute today.</p>
      <div class="d-flex gap-2 flex-wrap">
        <a href="../index.php" target="_blank" class="btn btn-sm px-3" style="background:rgba(255,255,255,.15);color:#fff;border-radius:8px;border:1px solid rgba(255,255,255,.3)"><i class="fa-solid fa-arrow-up-right-from-square me-1"></i>View Website</a>
        <a href="lectures.php" class="btn btn-sm px-3" style="background:#fb8500;color:#fff;border-radius:8px"><i class="fa-solid fa-video me-1"></i>Manage Lectures</a>
      </div>
    </div>
    <div class="col-md-4 text-end d-none d-md-block">
      <div style="font-size:4rem;line-height:1">🏫</div>
    </div>
  </div>
</div>

<!-- ALERT BADGES for pending actions -->
<?php if($enquiry_count > 0 || $doubt_pending > 0 || $sub_pending > 0): ?>
<div class="row g-2 mb-4">
  <?php if($enquiry_count > 0): ?>
  <div class="col-md-4"><div class="alert-kpi p-3 d-flex align-items-center gap-3"><i class="fa-solid fa-bell text-warning fs-4"></i><div><div class="fw-bold small"><?= $enquiry_count ?> Pending Enquiries</div><a href="enquiries.php" class="small text-primary">Review now →</a></div></div></div>
  <?php endif; ?>
  <?php if($doubt_pending > 0): ?>
  <div class="col-md-4"><div class="alert-kpi p-3 d-flex align-items-center gap-3"><i class="fa-solid fa-circle-question text-warning fs-4"></i><div><div class="fw-bold small"><?= $doubt_pending ?> Unanswered Doubts</div><a href="doubts.php" class="small text-primary">Reply now →</a></div></div></div>
  <?php endif; ?>
  <?php if($sub_pending > 0): ?>
  <div class="col-md-4"><div class="alert-kpi p-3 d-flex align-items-center gap-3"><i class="fa-solid fa-file-circle-check text-warning fs-4"></i><div><div class="fw-bold small"><?= $sub_pending ?> Tests Need Grading</div><a href="tests.php" class="small text-primary">Grade now →</a></div></div></div>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- KPI CARDS -->
<div class="row g-3 mb-4">
<?php
$kpis = [
  ['label'=>'Active Students','val'=>$student_count,'icon'=>'fa-user-graduate','color'=>'#3b82f6','bg'=>'#eff6ff','link'=>'students.php'],
  ['label'=>'Courses','val'=>$course_count,'icon'=>'fa-book-open','color'=>'#10b981','bg'=>'#ecfdf5','link'=>'courses.php'],
  ['label'=>'Faculty','val'=>$faculty_count,'icon'=>'fa-chalkboard-user','color'=>'#8b5cf6','bg'=>'#f5f3ff','link'=>'faculty.php'],
  ['label'=>'Live Sessions','val'=>$live_upcoming,'icon'=>'fa-broadcast-tower','color'=>'#ef4444','bg'=>'#fff1f2','link'=>'lectures.php'],
  ['label'=>'Recorded Videos','val'=>$recorded_total,'icon'=>'fa-film','color'=>'#f59e0b','bg'=>'#fffbeb','link'=>'lectures.php'],
  ['label'=>'Pending Doubts','val'=>$doubt_pending,'icon'=>'fa-comments','color'=>'#06b6d4','bg'=>'#ecfeff','link'=>'doubts.php'],
  ['label'=>'Need Grading','val'=>$sub_pending,'icon'=>'fa-file-pen','color'=>'#f97316','bg'=>'#fff7ed','link'=>'test_submissions.php'],
  ['label'=>'Pending Enquiries','val'=>$enquiry_count,'icon'=>'fa-envelope','color'=>'#dc2626','bg'=>'#fff1f2','link'=>'enquiries.php'],
];
foreach($kpis as $k): ?>
<div class="col-6 col-md-3">
  <a href="<?= $k['link'] ?>" class="kpi-card card p-3 shadow-sm text-decoration-none">
    <div class="d-flex align-items-center gap-3">
      <div class="kpi-icon" style="background:<?= $k['bg'] ?>;color:<?= $k['color'] ?>"><i class="fa-solid <?= $k['icon'] ?>"></i></div>
      <div>
        <div class="fw-bold fs-4 mb-0 lh-1" style="color:<?= $k['color'] ?>"><?= $k['val'] ?></div>
        <div class="text-muted small"><?= $k['label'] ?></div>
      </div>
    </div>
  </a>
</div>
<?php endforeach; ?>
</div>

<div class="row g-4">
  <!-- NEXT LIVE SESSION -->
  <div class="col-12">
    <?php if($nextLive): $dt = strtotime($nextLive['scheduled_at']); $diff = $dt - time(); ?>
    <div class="section-card card p-4 mb-1" style="background:linear-gradient(135deg,#0f172a,#1e293b);color:#fff">
      <div class="row align-items-center">
        <div class="col-md-8">
          <div class="d-flex align-items-center gap-3 mb-2">
            <span class="badge bg-danger live-pulse px-3 py-2"><i class="fa-solid fa-circle me-1" style="font-size:.45rem;vertical-align:middle"></i> NEXT LIVE SESSION</span>
            <span class="badge" style="background:#1e293b;color:#7dd3fc"><?= strtoupper($nextLive['platform']) ?></span>
          </div>
          <h5 class="fw-bold text-white mb-1"><?= htmlspecialchars($nextLive['title']) ?></h5>
          <div class="d-flex gap-4 mt-2 flex-wrap" style="font-size:.82rem;color:#94a3b8">
            <span><i class="fa-regular fa-calendar me-1"></i><?= date('d M Y, h:i A', $dt) ?></span>
            <span><i class="fa-regular fa-clock me-1"></i><?= $nextLive['duration_mins'] ?> mins</span>
            <span><i class="fa-solid fa-users me-1"></i><?= htmlspecialchars($nextLive['cname'] ?? 'All Courses') ?></span>
          </div>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
          <div class="text-warning fw-bold mb-2"><?= $diff < 3600 ? '🔴 Starting in '.round($diff/60).' mins' : '⏰ '.gmdate('H\h i\m', $diff).' away' ?></div>
          <a href="<?= htmlspecialchars($nextLive['join_url']) ?>" target="_blank" class="btn fw-bold px-4" style="background:#fb8500;color:#fff;border-radius:10px"><i class="fa-solid fa-video me-2"></i>Join / Preview</a>
        </div>
      </div>
    </div>
    <?php else: ?>
    <div class="section-card card p-3 mb-1 d-flex align-items-center gap-3" style="background:#f8fafc;border:1.5px dashed #cbd5e1">
      <i class="fa-regular fa-calendar-xmark fs-4 text-muted"></i>
      <span class="text-muted">No upcoming live sessions. <a href="lectures.php">Schedule one →</a></span>
    </div>
    <?php endif; ?>
  </div>

  <!-- ENROLLMENT BY COURSE -->
  <div class="col-lg-5">
    <div class="section-card card p-4 h-100">
      <h6 class="fw-bold mb-4"><i class="fa-solid fa-chart-bar text-primary me-2"></i>Enrollment by Course</h6>
      <?php if(empty($courseStats)): ?>
        <div class="text-muted small text-center py-3">No enrollment data.</div>
      <?php else:
        $maxVal = max(array_column($courseStats,'cnt')) ?: 1;
        foreach($courseStats as $cs):
          $pct = round(($cs['cnt']/$maxVal)*100);
      ?>
      <div class="mb-3">
        <div class="d-flex justify-content-between small mb-1">
          <span class="fw-semibold text-truncate me-2"><?= htmlspecialchars($cs['name']) ?></span>
          <span class="fw-bold text-primary"><?= $cs['cnt'] ?></span>
        </div>
        <div class="bar-track"><div class="bar-fill" style="width:<?= $pct ?>%"></div></div>
      </div>
      <?php endforeach; endif; ?>
    </div>
  </div>

  <!-- RECENT ENQUIRIES -->
  <div class="col-lg-7">
    <div class="section-card card h-100">
      <div class="card-header bg-white border-0 px-4 py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="fa-solid fa-envelope text-primary me-2"></i>Recent Enquiries</h6>
        <a href="enquiries.php" class="small text-primary text-decoration-none">View all →</a>
      </div>
      <div class="card-body p-0">
        <?php if(empty($recent_enq)): ?>
          <div class="text-muted text-center py-4 small">No enquiries yet.</div>
        <?php else: foreach($recent_enq as $enq): ?>
        <div class="d-flex align-items-center gap-3 px-4 py-3 border-bottom">
          <div style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,#3b82f6,#1a365d);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.9rem;flex-shrink:0"><?= strtoupper(substr($enq['name'],0,1)) ?></div>
          <div class="flex-grow-1 min-w-0">
            <div class="fw-bold small text-truncate"><?= htmlspecialchars($enq['name']) ?></div>
            <div class="text-muted" style="font-size:.75rem"><?= htmlspecialchars($enq['email']) ?> · <?= date('d M', strtotime($enq['created_at'])) ?></div>
          </div>
          <?php $bc = ['pending'=>'bg-warning text-dark','contacted'=>'bg-primary','resolved'=>'bg-success']; ?>
          <span class="badge <?= $bc[$enq['status']] ?? 'bg-secondary' ?>"><?= ucfirst($enq['status']) ?></span>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>

  <!-- RECENT STUDENTS + QUICK ACTIONS -->
  <div class="col-lg-7">
    <div class="section-card card">
      <div class="card-header bg-white border-0 px-4 py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="fa-solid fa-user-plus text-success me-2"></i>Recently Enrolled Students</h6>
        <a href="students.php" class="small text-primary text-decoration-none">View all →</a>
      </div>
      <div class="card-body p-0">
        <?php if(empty($recent_stu)): ?>
          <div class="text-muted text-center py-4 small">No students yet.</div>
        <?php else: foreach($recent_stu as $s): ?>
        <div class="d-flex align-items-center gap-3 px-4 py-3 border-bottom">
          <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#10b981,#065f46);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;flex-shrink:0"><?= strtoupper(substr($s['name'],0,1)) ?></div>
          <div class="flex-grow-1 min-w-0">
            <div class="fw-bold small"><?= htmlspecialchars($s['name']) ?></div>
            <div class="text-muted" style="font-size:.75rem"><?= htmlspecialchars($s['cname'] ?? 'No course') ?></div>
          </div>
          <div class="text-muted" style="font-size:.72rem"><?= date('d M', strtotime($s['created_at'])) ?></div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>

  <!-- QUICK ACTIONS -->
  <div class="col-lg-5">
    <div class="section-card card p-4 h-100">
      <h6 class="fw-bold mb-3"><i class="fa-solid fa-bolt text-warning me-2"></i>Quick Actions</h6>
      <div class="d-grid gap-2">
        <a href="students.php" class="quick-action btn"><i class="fa-solid fa-user-plus me-2 text-success"></i>Enroll New Student</a>
        <a href="lectures.php" class="quick-action btn"><i class="fa-solid fa-video me-2 text-danger"></i>Manage Lectures</a>
        <a href="batches.php" class="quick-action btn"><i class="fa-solid fa-clock me-2 text-primary"></i>Upcoming Batches</a>
        <a href="tests.php" class="quick-action btn"><i class="fa-solid fa-file-pen me-2 text-orange"></i>Create Test</a>
        <a href="doubts.php" class="quick-action btn"><i class="fa-solid fa-comments me-2 text-info"></i>Reply to Doubts</a>
        <a href="materials.php" class="quick-action btn"><i class="fa-solid fa-folder-open me-2" style="color:#8b5cf6"></i>Upload Materials</a>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
