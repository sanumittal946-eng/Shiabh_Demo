<?php
// student/lectures.php
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

// Watch a specific video?
$watchId = isset($_GET['watch']) ? (int)$_GET['watch'] : 0;
$watchVideo = null;
if ($watchId) {
    $wStmt = $db->prepare("SELECT * FROM recorded_lectures WHERE id = :id AND is_active = 1");
    $wStmt->execute([':id' => $watchId]);
    $watchVideo = $wStmt->fetch();
}

// Live lectures — filtered by student's course
$liveStmt = $db->prepare("SELECT l.*, c.name as course_name FROM live_lectures l LEFT JOIN courses c ON l.course_id = c.id WHERE (l.course_id = :cid OR l.course_id IS NULL) AND l.is_active = 1 AND l.scheduled_at >= NOW() ORDER BY l.scheduled_at ASC");
$liveStmt->execute([':cid' => $cid]);
$liveLectures = $liveStmt->fetchAll();

// Past live lectures (for archive) — filtered by course
$pastStmt = $db->prepare("SELECT l.*, c.name as course_name FROM live_lectures l LEFT JOIN courses c ON l.course_id = c.id WHERE (l.course_id = :cid OR l.course_id IS NULL) AND l.is_active = 1 AND l.scheduled_at < NOW() ORDER BY l.scheduled_at DESC LIMIT 5");
$pastStmt->execute([':cid' => $cid]);
$pastLectures = $pastStmt->fetchAll();

// Recorded lectures — filtered by course
$filter = $_GET['subject'] ?? 'all';
$recQuery = "SELECT r.*, c.name as course_name FROM recorded_lectures r LEFT JOIN courses c ON r.course_id = c.id WHERE (r.course_id = :cid OR r.course_id IS NULL) AND r.is_active = 1";
if ($filter !== 'all') $recQuery .= " AND r.subject = :subj";
$recQuery .= " ORDER BY r.created_at DESC";
$recStmt = $db->prepare($recQuery);
$params = [':cid' => $cid];
if ($filter !== 'all') $params[':subj'] = $filter;
$recStmt->execute($params);
$recorded = $recStmt->fetchAll();

// Subjects for filter — course-specific
$subjects = $db->prepare("SELECT DISTINCT subject FROM recorded_lectures WHERE (course_id = :cid OR course_id IS NULL) AND is_active=1 AND subject IS NOT NULL AND subject != ''");
$subjects->execute([':cid' => $cid]);
$subjects = $subjects->fetchAll(PDO::FETCH_COLUMN);

// Embed URL builder
function buildEmbedUrl($url, $type) {
    if ($type === 'youtube') {
        preg_match('/(?:v=|youtu\.be\/|embed\/)([^&\?\/]+)/', $url, $m);
        return isset($m[1]) ? "https://www.youtube.com/embed/{$m[1]}?autoplay=1" : $url;
    }
    if ($type === 'vimeo') {
        preg_match('/vimeo\.com\/(\d+)/', $url, $m);
        return isset($m[1]) ? "https://player.vimeo.com/video/{$m[1]}?autoplay=1" : $url;
    }
    return $url;
}

function getThumb($rv) {
    if ($rv['thumbnail']) return $rv['thumbnail'];
    if ($rv['video_type'] === 'youtube') {
        preg_match('/(?:v=|youtu\.be\/|embed\/)([^&\?\/]+)/', $rv['video_url'], $m);
        if (isset($m[1])) return "https://img.youtube.com/vi/{$m[1]}/hqdefault.jpg";
    }
    return "https://placehold.co/640x360/1a1a2e/ffffff?text=Video";
}
?>
<style>
:root{--grad1:linear-gradient(135deg,#1a365d 0%,#2d6a4f 100%);}
.page-tabs .nav-link{border:none;color:#64748b;font-weight:600;padding:.6rem 1.3rem;border-radius:10px;transition:.2s;}
.page-tabs .nav-link.active{background:linear-gradient(135deg,#1a365d,#2563eb);color:#fff;}
.live-upcoming-card{background:#0f172a;color:#fff;border-radius:16px;border:none;transition:transform .2s;}
.live-upcoming-card:hover{transform:translateY(-3px);}
.platform-badge.zoom{background:#2D8CFF;}
.platform-badge.meet{background:#1a73e8;}
.platform-badge.youtube{background:#ff0000;}
.platform-badge.teams{background:#464775;}
.platform-badge.other{background:#64748b;}
.video-card{border-radius:14px;border:none;transition:transform .2s,box-shadow .2s;overflow:hidden;cursor:pointer;}
.video-card:hover{transform:translateY(-5px);box-shadow:0 16px 40px rgba(0,0,0,.13)!important;}
.v-thumb{position:relative;aspect-ratio:16/9;overflow:hidden;background:#1a1a2e;}
.v-thumb img{width:100%;height:100%;object-fit:cover;transition:transform .3s;}
.video-card:hover .v-thumb img{transform:scale(1.07);}
.v-overlay{position:absolute;inset:0;background:rgba(0,0,0,.35);display:flex;align-items:center;justify-content:center;transition:.25s;}
.video-card:hover .v-overlay{background:rgba(0,0,0,.5);}
.v-play{width:54px;height:54px;background:rgba(255,255,255,.92);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.3rem;color:#1a365d;transform:scale(1);transition:.2s;}
.video-card:hover .v-play{transform:scale(1.12);}
.filter-chip{border-radius:20px;padding:5px 16px;border:1.5px solid #e2e8f0;font-size:.82rem;font-weight:600;color:#475569;background:#fff;transition:.18s;cursor:pointer;}
.filter-chip:hover,.filter-chip.active{background:#1a365d;color:#fff;border-color:#1a365d;}
.watch-modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:1100;display:flex;align-items:center;justify-content:center;padding:1rem;}
.watch-modal-box{background:#0f172a;border-radius:18px;width:100%;max-width:900px;overflow:hidden;}
.watch-modal-box iframe{width:100%;height:500px;border:0;}
.past-badge{background:#334155;color:#94a3b8;font-size:.7rem;}
.countdown-chip{font-size:.78rem;font-weight:700;color:#fb8500;}
.section-head{font-size:1.1rem;font-weight:800;color:#1a365d;}
</style>

<!-- PAGE HEADER -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <h4 class="fw-bold mb-0">📺 Lectures & Videos</h4>
    <div class="text-muted small">Access your live classes and recorded lecture library.</div>
  </div>
  <ul class="nav page-tabs" id="lectureTab">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabLive"><i class="fa-solid fa-circle text-danger me-1" style="font-size:.55rem;vertical-align:middle"></i> Live Classes</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabRecorded"><i class="fa-solid fa-video me-1" style="color:#8b5cf6"></i> Recorded</a></li>
  </ul>
</div>

<?php if ($watchVideo): ?>
<!-- WATCH MODAL -->
<div class="watch-modal-overlay" id="watchOverlay">
  <div class="watch-modal-box shadow-lg">
    <div class="p-3 d-flex justify-content-between align-items-center" style="border-bottom:1px solid #1e293b">
      <div class="text-white fw-bold"><?= htmlspecialchars($watchVideo['title']) ?></div>
      <a href="lectures.php#recorded" class="btn btn-sm btn-outline-light rounded-pill"><i class="fa-solid fa-xmark"></i> Close</a>
    </div>
    <iframe src="<?= buildEmbedUrl($watchVideo['video_url'], $watchVideo['video_type']) ?>" allowfullscreen allow="autoplay; encrypted-media"></iframe>
    <div class="p-3 text-muted small">
      <?= htmlspecialchars($watchVideo['subject'] ?? '') ?>
      <?= $watchVideo['duration'] ? ' · ' . $watchVideo['duration'] : '' ?>
      <?= $watchVideo['description'] ? ' — ' . htmlspecialchars($watchVideo['description']) : '' ?>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="tab-content">
<!-- ============================== LIVE TAB ============================== -->
<div class="tab-pane fade show active" id="tabLive">
  <div class="section-head mb-3"><i class="fa-solid fa-broadcast-tower me-2" style="color:#ef4444"></i>Upcoming Live Sessions</div>
  <?php if(empty($liveLectures)): ?>
    <div class="text-center py-5" style="background:#f8fafc;border-radius:16px;border:1.5px dashed #cbd5e1">
      <i class="fa-regular fa-calendar-xmark fa-3x text-muted mb-3 d-block"></i>
      <div class="fw-bold text-muted">No upcoming live classes scheduled right now.</div>
      <div class="small text-muted mt-1">Your teacher will post a live class link here. Check back later!</div>
    </div>
  <?php else: ?>
  <div class="row g-3 mb-4">
    <?php foreach($liveLectures as $lec): $dt = strtotime($lec['scheduled_at']); $diff = $dt - time(); ?>
    <div class="col-md-6">
      <div class="live-upcoming-card card p-4">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <span class="badge platform-badge <?= $lec['platform'] ?> text-white px-3 py-2 rounded-pill"><?= strtoupper($lec['platform']) ?></span>
          <?php if($diff <= 0): ?>
            <span class="badge bg-danger" style="animation:pulseLive 1.2s infinite"><i class="fa-solid fa-circle me-1" style="font-size:.5rem;vertical-align:middle"></i>LIVE NOW</span>
          <?php else: ?>
            <span class="countdown-chip"><i class="fa-regular fa-clock me-1"></i><?= $diff < 3600 ? round($diff/60).'m away' : gmdate('H\h i\m', $diff).' away' ?></span>
          <?php endif; ?>
        </div>
        <h5 class="fw-bold text-white mb-1"><?= htmlspecialchars($lec['title']) ?></h5>
        <p class="opacity-60 small mb-3"><?= htmlspecialchars($lec['description'] ?? '') ?></p>
        <div class="d-flex gap-3 mb-4 flex-wrap" style="font-size:.8rem;color:#94a3b8">
          <span><i class="fa-regular fa-calendar me-1"></i><?= date('d M Y', $dt) ?></span>
          <span><i class="fa-regular fa-clock me-1"></i><?= date('h:i A', $dt) ?></span>
          <span><i class="fa-solid fa-hourglass me-1"></i><?= $lec['duration_mins'] ?> mins</span>
          <?php if(!empty($lec['course_name'])): ?><span><i class="fa-solid fa-users me-1"></i><?= htmlspecialchars($lec['course_name']) ?></span><?php else: ?><span><i class="fa-solid fa-users me-1"></i>All Students</span><?php endif; ?>
        </div>
        <?php if($lec['meeting_id'] || $lec['passcode']): ?>
        <div class="d-flex gap-3 mb-3 flex-wrap" style="font-size:.8rem">
          <?php if($lec['meeting_id']): ?><span class="badge" style="background:#1e293b;color:#7dd3fc">ID: <?= htmlspecialchars($lec['meeting_id']) ?></span><?php endif; ?>
          <?php if($lec['passcode']): ?><span class="badge" style="background:#1e293b;color:#86efac">Pass: <?= htmlspecialchars($lec['passcode']) ?></span><?php endif; ?>
        </div>
        <?php endif; ?>
        <a href="<?= htmlspecialchars($lec['join_url']) ?>" target="_blank" class="btn fw-bold w-100" style="background:#fb8500;color:#fff;border-radius:10px"><i class="fa-solid fa-video me-2"></i>Join Class</a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if(!empty($pastLectures)): ?>
  <div class="section-head mb-3 mt-4"><i class="fa-solid fa-clock-rotate-left me-2 text-muted"></i>Past Sessions</div>
  <div class="card border-0 shadow-sm" style="border-radius:14px">
    <?php foreach($pastLectures as $lec): ?>
    <div class="d-flex align-items-center gap-3 px-4 py-3 border-bottom">
      <div style="width:40px;height:40px;background:#1e293b;border-radius:10px;display:flex;align-items:center;justify-content:center">
        <i class="fa-solid fa-video text-muted"></i>
      </div>
      <div class="flex-grow-1">
        <div class="fw-semibold small"><?= htmlspecialchars($lec['title']) ?></div>
        <div class="text-muted" style="font-size:.75rem"><?= date('d M Y, h:i A', strtotime($lec['scheduled_at'])) ?> · <?= $lec['duration_mins'] ?> mins</div>
      </div>
      <span class="badge past-badge">Completed</span>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<!-- ============================== RECORDED TAB ============================== -->
<div class="tab-pane fade" id="tabRecorded">
  <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div class="section-head"><i class="fa-solid fa-film me-2" style="color:#8b5cf6"></i>Recorded Lectures</div>
    <div class="d-flex flex-wrap gap-2">
      <a href="lectures.php#recorded" class="filter-chip <?= $filter==='all'?'active':'' ?>">All</a>
      <?php foreach($subjects as $subj): ?>
        <a href="lectures.php?subject=<?= urlencode($subj) ?>#recorded" class="filter-chip <?= $filter===$subj?'active':'' ?>"><?= htmlspecialchars($subj) ?></a>
      <?php endforeach; ?>
    </div>
  </div>

  <?php if(empty($recorded)): ?>
    <div class="text-center py-5" style="background:#f8fafc;border-radius:16px;border:1.5px dashed #cbd5e1">
      <i class="fa-solid fa-video-slash fa-3x text-muted mb-3 d-block"></i>
      <div class="fw-bold text-muted">No recorded lectures available yet.</div>
      <div class="small text-muted mt-1">Your instructor will upload recorded sessions here soon.</div>
    </div>
  <?php else: ?>
  <div class="row g-4" id="recorded">
    <?php foreach($recorded as $rv): ?>
    <div class="col-md-6 col-lg-4">
      <div class="video-card card shadow-sm" onclick="watchVideo('<?= htmlspecialchars(buildEmbedUrl($rv['video_url'], $rv['video_type'])) ?>','<?= htmlspecialchars(addslashes($rv['title'])) ?>')">
        <div class="v-thumb">
          <img src="<?= getThumb($rv) ?>" alt="<?= htmlspecialchars($rv['title']) ?>" loading="lazy">
          <div class="v-overlay"><div class="v-play"><i class="fa-solid fa-play ms-1"></i></div></div>
          <?php if($rv['duration']): ?>
            <span class="badge" style="position:absolute;bottom:8px;right:8px;background:rgba(0,0,0,.75)"><?= htmlspecialchars($rv['duration']) ?></span>
          <?php endif; ?>
        </div>
        <div class="p-3">
          <div class="fw-bold mb-1" style="font-size:.9rem;line-height:1.3"><?= htmlspecialchars($rv['title']) ?></div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <?php if($rv['subject']): ?><span class="badge" style="background:#eff6ff;color:#1d4ed8;font-size:.7rem"><?= htmlspecialchars($rv['subject']) ?></span><?php endif; ?>
            <span class="badge" style="background:#f5f3ff;color:#7c3aed;font-size:.7rem"><?= strtoupper($rv['video_type']) ?></span>
          </div>
          <div class="text-muted" style="font-size:.75rem"><?= date('d M Y', strtotime($rv['created_at'])) ?></div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
</div>

<!-- VIDEO PLAYER MODAL -->
<div class="modal fade" id="videoModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="background:#0f172a;border-radius:18px;overflow:hidden">
      <div class="modal-header border-0 px-4 py-3">
        <h6 class="modal-title text-white fw-bold" id="videoModalTitle"></h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" onclick="stopVideo()"></button>
      </div>
      <div style="position:relative;padding-top:56.25%">
        <iframe id="videoFrame" src="" style="position:absolute;inset:0;width:100%;height:100%;border:0" allowfullscreen allow="autoplay; encrypted-media"></iframe>
      </div>
      <div class="px-4 pb-4 pt-2"><p id="videoDesc" class="text-muted small mb-0"></p></div>
    </div>
  </div>
</div>

<?php
// activate recorded tab if hash is #recorded or watch param set
?>
<script>
function watchVideo(url, title) {
  document.getElementById('videoFrame').src = url;
  document.getElementById('videoModalTitle').innerText = title;
  new bootstrap.Modal(document.getElementById('videoModal')).show();
}
function stopVideo() { document.getElementById('videoFrame').src = ''; }
document.getElementById('videoModal').addEventListener('hidden.bs.modal', stopVideo);

// auto-switch to recorded tab if URL has #recorded
document.addEventListener('DOMContentLoaded', function(){
  if(location.hash === '#recorded'){
    const t = new bootstrap.Tab(document.querySelector('[href="#tabRecorded"]'));
    t.show();
  }
  <?php if($watchId && $watchVideo): ?>
  watchVideo('<?= buildEmbedUrl($watchVideo['video_url'], $watchVideo['video_type']) ?>', '<?= htmlspecialchars(addslashes($watchVideo['title'])) ?>');
  <?php endif; ?>
});
</script>

<style>@keyframes pulseLive{0%,100%{box-shadow:0 0 0 0 rgba(239,68,68,.5);}50%{box-shadow:0 0 0 8px rgba(239,68,68,0);}}</style>

<?php include __DIR__ . '/../includes/student_footer.php'; ?>
