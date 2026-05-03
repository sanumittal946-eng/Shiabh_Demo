<?php
// student/dashboard.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/student_header.php';

$db = getDB();
$student_id = $_SESSION['student_id'];

$stmt = $db->prepare("SELECT s.*, c.name as course_name FROM students s LEFT JOIN courses c ON s.course_id = c.id WHERE s.id = :id");
$stmt->execute([':id' => $student_id]);
$student = $stmt->fetch();

$cid = $student['course_id'];

// Stats
$total_materials  = $db->prepare("SELECT COUNT(*) FROM materials WHERE course_id = :cid"); $total_materials->execute([':cid'=>$cid]); $total_materials = $total_materials->fetchColumn();
$pending_doubts   = $db->prepare("SELECT COUNT(*) FROM doubts WHERE student_id = :sid AND status = 'open'"); $pending_doubts->execute([':sid'=>$student_id]); $pending_doubts = $pending_doubts->fetchColumn();
$total_tests      = $db->prepare("SELECT COUNT(*) FROM tests WHERE course_id = :cid"); $total_tests->execute([':cid'=>$cid]); $total_tests = $total_tests->fetchColumn();
$recorded_count   = $db->prepare("SELECT COUNT(*) FROM recorded_lectures WHERE (course_id = :cid OR course_id IS NULL) AND is_active=1"); $recorded_count->execute([':cid'=>$cid]); $recorded_count = $recorded_count->fetchColumn();

// Next Live Class — filtered by student's course
$live = $db->prepare("SELECT l.*, c.name as cname FROM live_lectures l LEFT JOIN courses c ON l.course_id = c.id WHERE (l.course_id = :cid OR l.course_id IS NULL) AND l.is_active = 1 AND l.scheduled_at >= NOW() ORDER BY l.scheduled_at ASC LIMIT 1");
$live->execute([':cid' => $cid]);
$nextLive = $live->fetch();

// Today's timetable
$today = date('l');
$batch = $student['batch'] ?? 'General';
$tt = $db->prepare("SELECT t.*, f.name as faculty_name FROM timetable t LEFT JOIN faculty f ON t.faculty_id = f.id WHERE t.batch = :batch AND t.day = :day ORDER BY t.time_start ASC");
$tt->execute([':batch'=>$batch, ':day'=>$today]);
$classes = $tt->fetchAll();

// Latest 3 recorded lectures
$recStmt = $db->prepare("SELECT * FROM recorded_lectures WHERE (course_id = :cid OR course_id IS NULL) AND is_active=1 ORDER BY created_at DESC LIMIT 3");
$recStmt->execute([':cid' => $cid]);
$recentVideos = $recStmt->fetchAll();

// Latest notices
$notices = $db->query("SELECT title, content, created_at FROM notices WHERE is_active=1 ORDER BY created_at DESC LIMIT 4")->fetchAll();
?>
<style>
:root{--grad1: linear-gradient(135deg,#1a365d 0%,#2d6a4f 100%);--grad2: linear-gradient(135deg,#fb8500,#ffb703);--card-radius:16px;}
.dash-hero{background:var(--grad1);border-radius:var(--card-radius);color:#fff;padding:2.2rem 2rem;position:relative;overflow:hidden;}
.dash-hero::before{content:'';position:absolute;top:-60px;right:-60px;width:220px;height:220px;border-radius:50%;background:rgba(255,255,255,.06);}
.dash-hero::after{content:'';position:absolute;bottom:-80px;right:80px;width:180px;height:180px;border-radius:50%;background:rgba(255,255,255,.05);}
.stat-card{border-radius:var(--card-radius);border:none;transition:transform .2s,box-shadow .2s;cursor:default;}
.stat-card:hover{transform:translateY(-4px);box-shadow:0 12px 28px rgba(0,0,0,.12)!important;}
.stat-icon{width:56px;height:56px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;}
.live-badge{background:linear-gradient(90deg,#e63946,#c1121f);animation:pulseLive 1.4s infinite;}
@keyframes pulseLive{0%,100%{box-shadow:0 0 0 0 rgba(230,57,70,.5);}50%{box-shadow:0 0 0 8px rgba(230,57,70,0);}}
.live-card{border-radius:var(--card-radius);background:linear-gradient(135deg,#1a1a2e,#16213e);color:#fff;border:none;}
.video-thumb{border-radius:12px;overflow:hidden;position:relative;aspect-ratio:16/9;background:#1a1a2e;}
.video-thumb img{width:100%;height:100%;object-fit:cover;transition:transform .3s;}
.video-thumb:hover img{transform:scale(1.05);}
.play-overlay{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.35);transition:.2s;}
.play-overlay .play-btn{width:50px;height:50px;background:rgba(255,255,255,.9);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#1a365d;font-size:1.2rem;}
.video-thumb:hover .play-overlay{background:rgba(0,0,0,.5);}
.section-card{border-radius:var(--card-radius);border:none;box-shadow:0 2px 16px rgba(0,0,0,.07);}
.tt-row{border-left:4px solid transparent;transition:.15s;}
.tt-row:hover{background:#f8fafc;border-left-color:#fb8500;}
.notice-item{border-left:3px solid #fb8500;padding-left:12px;margin-bottom:14px;}
.quick-btn{border-radius:12px;padding:.85rem 1rem;border:2px solid #e2e8f0;color:#1a365d;font-weight:600;transition:.2s;text-align:left;}
.quick-btn:hover{border-color:#fb8500;color:#fb8500;background:#fff8f0;}
.time-chip{background:#eff6ff;color:#1d4ed8;font-size:.75rem;font-weight:700;padding:3px 10px;border-radius:20px;}
.greeting-time{font-size:.85rem;opacity:.7;font-weight:500;}
</style>

<!-- HERO BANNER -->
<div class="dash-hero mb-4 shadow">
  <div class="row align-items-center position-relative" style="z-index:2">
    <div class="col-md-8">
      <?php
        $hour = (int)date('G');
        $greet = $hour < 12 ? '☀️ Good Morning' : ($hour < 17 ? '🌤️ Good Afternoon' : '🌙 Good Evening');
      ?>
      <div class="greeting-time mb-1"><?= $greet ?> · <?= date('l, d M Y') ?></div>
      <h2 class="fw-bold mb-1" style="font-size:1.8rem"><?= htmlspecialchars($student['name'] ?? 'Student') ?>!</h2>
      <p class="mb-3 opacity-75">You're enrolled in <strong style="color:#ffb703"><?= htmlspecialchars($student['course_name'] ?? 'General') ?></strong> · Batch: <strong><?= htmlspecialchars($batch) ?></strong></p>
      <a href="lectures.php" class="btn fw-bold px-4 py-2 me-2" style="background:#fb8500;color:#fff;border-radius:10px"><i class="fa-solid fa-play me-1"></i> Go to Lectures</a>
      <a href="doubts.php" class="btn btn-outline-light px-4 py-2" style="border-radius:10px"><i class="fa-solid fa-circle-question me-1"></i> Ask Doubt</a>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
      <div style="font-size:3.5rem">🎓</div>
      <div class="small opacity-60 mt-1">Last login: <?= $student['last_login'] ? date('d M, h:i A', strtotime($student['last_login'])) : 'First visit' ?></div>
    </div>
  </div>
</div>

<!-- STAT CARDS -->
<div class="row g-3 mb-4">
  <?php
  $stats = [
    ['label'=>'Course Materials','val'=>$total_materials,'icon'=>'fa-book-open','color'=>'#3b82f6','bg'=>'#eff6ff'],
    ['label'=>'Pending Doubts','val'=>$pending_doubts,'icon'=>'fa-comments','color'=>'#f59e0b','bg'=>'#fffbeb'],
    ['label'=>'Available Tests','val'=>$total_tests,'icon'=>'fa-file-pen','color'=>'#10b981','bg'=>'#ecfdf5'],
    ['label'=>'Recorded Videos','val'=>$recorded_count,'icon'=>'fa-video','color'=>'#8b5cf6','bg'=>'#f5f3ff'],
  ];
  foreach($stats as $s): ?>
  <div class="col-6 col-md-3">
    <div class="stat-card card p-3 shadow-sm">
      <div class="d-flex align-items-center gap-3">
        <div class="stat-icon" style="background:<?= $s['bg'] ?>;color:<?= $s['color'] ?>"><i class="fa-solid <?= $s['icon'] ?>"></i></div>
        <div>
          <div class="fw-bold fs-4 mb-0 lh-1" style="color:<?= $s['color'] ?>"><?= $s['val'] ?></div>
          <div class="text-muted small"><?= $s['label'] ?></div>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- LIVE CLASS BANNER -->
<?php if($nextLive): ?>
<div class="live-card p-4 mb-4 shadow">
  <div class="row align-items-center">
    <div class="col-md-8">
      <div class="d-flex align-items-center gap-3 mb-2">
        <span class="live-badge badge text-white px-3 py-2 rounded-pill" style="font-size:.8rem"><i class="fa-solid fa-circle me-1" style="font-size:.5rem;vertical-align:middle"></i> LIVE SESSION</span>
        <span class="badge" style="background:#2d3748;color:#90cdf4"><?= strtoupper($nextLive['platform']) ?></span>
      </div>
      <h5 class="fw-bold mb-1 text-white"><?= htmlspecialchars($nextLive['title']) ?></h5>
      <p class="opacity-75 mb-2 small"><?= htmlspecialchars($nextLive['description'] ?? '') ?></p>
      <div class="d-flex gap-3 flex-wrap small opacity-75">
        <span><i class="fa-regular fa-calendar me-1"></i><?= date('d M, h:i A', strtotime($nextLive['scheduled_at'])) ?></span>
        <span><i class="fa-regular fa-clock me-1"></i><?= $nextLive['duration_mins'] ?> mins</span>
        <?php if($nextLive['meeting_id']): ?><span><i class="fa-solid fa-hashtag me-1"></i>ID: <?= htmlspecialchars($nextLive['meeting_id']) ?></span><?php endif; ?>
        <?php if($nextLive['passcode']): ?><span><i class="fa-solid fa-key me-1"></i>Pass: <?= htmlspecialchars($nextLive['passcode']) ?></span><?php endif; ?>
      </div>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
      <a href="<?= htmlspecialchars($nextLive['join_url']) ?>" target="_blank" class="btn btn-lg fw-bold px-4" style="background:#fb8500;color:#fff;border-radius:12px"><i class="fa-solid fa-video me-2"></i>Join Class Now</a>
    </div>
  </div>
</div>
<?php else: ?>
<div class="alert d-flex align-items-center gap-3 mb-4" style="background:#f8fafc;border-radius:12px;border:1.5px dashed #cbd5e1">
  <i class="fa-regular fa-calendar-xmark fs-4 text-muted"></i>
  <div class="text-muted">No upcoming live classes scheduled. Check <a href="lectures.php">Lectures</a> for recorded content.</div>
</div>
<?php endif; ?>

<div class="row g-4">
  <!-- TODAY'S SCHEDULE -->
  <div class="col-lg-7">
    <div class="section-card card mb-4">
      <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="fa-solid fa-calendar-day text-primary me-2"></i>Today's Schedule <span class="badge bg-primary ms-2"><?= $today ?></span></h6>
        <a href="timetable.php" class="small text-primary text-decoration-none">Full timetable →</a>
      </div>
      <div class="card-body p-0">
        <?php if(empty($classes)): ?>
          <div class="text-center py-5 text-muted"><i class="fa-regular fa-calendar fa-2x mb-2 d-block opacity-50"></i>No classes today. Take a break! 🌟</div>
        <?php else: foreach($classes as $cl): $now = strtotime('now'); $start = strtotime($cl['time_start']); $end = strtotime($cl['time_end']); $isNow = $now>=$start && $now<=$end; ?>
          <div class="tt-row px-4 py-3 border-bottom d-flex align-items-center gap-3">
            <div class="time-chip"><?= date('h:i A', $start) ?></div>
            <div class="flex-grow-1">
              <div class="fw-bold small"><?= htmlspecialchars($cl['subject']) ?></div>
              <div class="text-muted" style="font-size:.78rem"><i class="fa-solid fa-user-tie me-1"></i><?= htmlspecialchars($cl['faculty_name'] ?? 'TBA') ?></div>
            </div>
            <?php if($isNow): ?>
              <span class="badge bg-danger">LIVE NOW</span>
            <?php elseif($now < $start): ?>
              <span class="badge bg-success">Upcoming</span>
            <?php else: ?>
              <span class="badge bg-secondary">Done</span>
            <?php endif; ?>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <!-- RECENT RECORDED VIDEOS -->
    <div class="section-card card">
      <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="fa-solid fa-film text-purple me-2" style="color:#8b5cf6"></i>Recent Recorded Lectures</h6>
        <a href="lectures.php#recorded" class="small text-primary text-decoration-none">View all →</a>
      </div>
      <div class="card-body">
        <?php if(empty($recentVideos)): ?>
          <div class="text-center py-3 text-muted small">No recorded lectures yet. Check back soon!</div>
        <?php else: foreach($recentVideos as $rv): ?>
          <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
            <div style="width:90px;flex-shrink:0">
              <div class="video-thumb" style="border-radius:8px;aspect-ratio:16/9;background:#1a1a2e;position:relative;overflow:hidden">
                <?php
                  $thumb = $rv['thumbnail'];
                  if(!$thumb && $rv['video_type']=='youtube') {
                    preg_match('/(?:v=|youtu\.be\/|embed\/)([^&\?\/]+)/', $rv['video_url'], $m);
                    if(isset($m[1])) $thumb = "https://img.youtube.com/vi/{$m[1]}/mqdefault.jpg";
                  }
                ?>
                <?php if($thumb): ?><img src="<?= htmlspecialchars($thumb) ?>" alt="thumb" style="width:100%;height:100%;object-fit:cover"><?php endif; ?>
                <div style="position:absolute;inset:0;background:rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center">
                  <i class="fa-solid fa-play text-white" style="font-size:.8rem"></i>
                </div>
              </div>
            </div>
            <div class="flex-grow-1 min-w-0">
              <div class="fw-bold small text-truncate"><?= htmlspecialchars($rv['title']) ?></div>
              <div class="text-muted" style="font-size:.75rem"><?= htmlspecialchars($rv['subject'] ?? '') ?> <?= $rv['duration'] ? "· ".$rv['duration'] : '' ?></div>
              <a href="lectures.php?watch=<?= $rv['id'] ?>" class="btn btn-sm mt-1 px-2 py-0" style="font-size:.72rem;background:#eff6ff;color:#1d4ed8;border-radius:6px">Watch</a>
            </div>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>

  <!-- RIGHT COLUMN -->
  <div class="col-lg-5">
    <!-- QUICK ACTIONS -->
    <div class="section-card card mb-4 p-4">
      <h6 class="fw-bold mb-3"><i class="fa-solid fa-bolt me-2" style="color:#fb8500"></i>Quick Actions</h6>
      <div class="d-grid gap-2">
        <a href="lectures.php" class="quick-btn btn"><i class="fa-solid fa-play-circle me-2" style="color:#8b5cf6"></i>Lectures & Videos</a>
        <a href="tests.php" class="quick-btn btn"><i class="fa-solid fa-file-pen me-2" style="color:#10b981"></i>Take a Test</a>
        <a href="doubts.php" class="quick-btn btn"><i class="fa-solid fa-circle-question me-2" style="color:#f59e0b"></i>Ask a Doubt</a>
        <a href="materials.php" class="quick-btn btn"><i class="fa-solid fa-book-open me-2" style="color:#3b82f6"></i>Study Materials</a>
        <a href="submit-review.php" class="quick-btn btn"><i class="fa-solid fa-star me-2" style="color:#ef4444"></i>Write a Review</a>
      </div>
    </div>
    <!-- NOTICES -->
    <div class="section-card card p-4">
      <h6 class="fw-bold mb-3"><i class="fa-solid fa-bell me-2 text-warning"></i>Latest Notices</h6>
      <?php if(empty($notices)): ?>
        <div class="text-muted small text-center py-2">No active notices.</div>
      <?php else: foreach($notices as $n): ?>
        <div class="notice-item">
          <div class="fw-semibold small"><?= htmlspecialchars($n['title']) ?></div>
          <div class="text-muted" style="font-size:.75rem"><?= date('d M Y', strtotime($n['created_at'])) ?></div>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/student_footer.php'; ?>
