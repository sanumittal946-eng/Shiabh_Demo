<?php
// admin/lectures.php
require_once __DIR__ . '/includes/header.php';
$db = getDB();
$msg = $err = "";

// --- DELETE ---
if (isset($_GET['del_live'])) {
    $db->prepare("DELETE FROM live_lectures WHERE id=:id")->execute([':id'=>(int)$_GET['del_live']]);
    $msg = "Live session deleted.";
}
if (isset($_GET['del_rec'])) {
    $db->prepare("DELETE FROM recorded_lectures WHERE id=:id")->execute([':id'=>(int)$_GET['del_rec']]);
    $msg = "Recorded lecture deleted.";
}

// --- SAVE LIVE ---
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save_live'])) {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) { $err = "Invalid token."; }
    else {
        $id = (int)($_POST['live_id'] ?? 0);
        $d = [
            ':cid'  => $_POST['course_id'] ?: null,
            ':title'=> trim($_POST['title']),
            ':desc' => trim($_POST['description']),
            ':plat' => $_POST['platform'],
            ':url'  => trim($_POST['join_url']),
            ':mid'  => trim($_POST['meeting_id']),
            ':pass' => trim($_POST['passcode']),
            ':sched'=> $_POST['scheduled_at'],
            ':dur'  => (int)$_POST['duration_mins'],
            ':ia'   => isset($_POST['is_active']) ? 1 : 0,
        ];
        if ($id > 0) {
            $d[':id'] = $id;
            $db->prepare("UPDATE live_lectures SET course_id=:cid,title=:title,description=:desc,platform=:plat,join_url=:url,meeting_id=:mid,passcode=:pass,scheduled_at=:sched,duration_mins=:dur,is_active=:ia WHERE id=:id")->execute($d);
        } else {
            $db->prepare("INSERT INTO live_lectures (course_id,title,description,platform,join_url,meeting_id,passcode,scheduled_at,duration_mins,is_active) VALUES(:cid,:title,:desc,:plat,:url,:mid,:pass,:sched,:dur,:ia)")->execute($d);
        }
        $msg = "Live session saved!";
    }
}

// --- SAVE RECORDED ---
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save_recorded'])) {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) { $err = "Invalid token."; }
    else {
        $id     = (int)($_POST['rec_id'] ?? 0);
        $vtype  = $_POST['video_type'];
        $vurl   = trim($_POST['video_url'] ?? '');

        // Handle file upload if video_type is 'upload'
        if ($vtype === 'upload' && isset($_FILES['video_file']) && $_FILES['video_file']['error'] === 0) {
            $allowed = ['video/mp4','video/webm','video/ogg','video/x-msvideo','video/quicktime'];
            $fsize   = $_FILES['video_file']['size'];
            $ftype   = mime_content_type($_FILES['video_file']['tmp_name']);

            if (!in_array($ftype, $allowed)) {
                $err = "Invalid file type. Only MP4, WebM, OGG, AVI, MOV allowed.";
            } elseif ($fsize > 500 * 1024 * 1024) {
                $err = "File too large. Max allowed size is 500 MB.";
            } else {
                $uploadDir = __DIR__ . '/../uploads/lectures/';
                $safeName  = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['video_file']['name']));
                if (move_uploaded_file($_FILES['video_file']['tmp_name'], $uploadDir . $safeName)) {
                    $vurl = 'uploads/lectures/' . $safeName;
                } else {
                    $err = "Upload failed. Check that uploads/lectures/ is writable.";
                }
            }
        }

        if (!$err) {
            if (empty($vurl)) { $err = "Please provide a video URL or upload a file."; }
            else {
                $d = [
                    ':cid'  => $_POST['course_id'] ?: null,
                    ':title'=> trim($_POST['title']),
                    ':desc' => trim($_POST['description']),
                    ':subj' => trim($_POST['subject']),
                    ':vtype'=> $vtype,
                    ':vurl' => $vurl,
                    ':thumb'=> trim($_POST['thumbnail']),
                    ':dur'  => trim($_POST['duration']),
                    ':ia'   => isset($_POST['is_active']) ? 1 : 0,
                ];
                if ($id > 0) {
                    $d[':id'] = $id;
                    $db->prepare("UPDATE recorded_lectures SET course_id=:cid,title=:title,description=:desc,subject=:subj,video_type=:vtype,video_url=:vurl,thumbnail=:thumb,duration=:dur,is_active=:ia WHERE id=:id")->execute($d);
                } else {
                    $db->prepare("INSERT INTO recorded_lectures (course_id,title,description,subject,video_type,video_url,thumbnail,duration,is_active) VALUES(:cid,:title,:desc,:subj,:vtype,:vurl,:thumb,:dur,:ia)")->execute($d);
                }
                $msg = "Recorded lecture saved successfully!";
            }
        }
    }
}

// Fetch data
$courses   = $db->query("SELECT id,name FROM courses ORDER BY name")->fetchAll();
$lives     = $db->query("SELECT l.*,c.name as cname FROM live_lectures l LEFT JOIN courses c ON l.course_id=c.id ORDER BY l.scheduled_at DESC LIMIT 30")->fetchAll();
$recorded  = $db->query("SELECT r.*,c.name as cname FROM recorded_lectures r LEFT JOIN courses c ON r.course_id=c.id ORDER BY r.created_at DESC LIMIT 50")->fetchAll();
?>

<style>
.lec-tabs .nav-link{border:none;font-weight:700;color:#475569;border-radius:10px;padding:.55rem 1.4rem;}
.lec-tabs .nav-link.active{background:linear-gradient(135deg,#1a365d,#2563eb);color:#fff;}
.plat-badge{padding:3px 12px;border-radius:20px;font-size:.72rem;font-weight:700;color:#fff;}
.plat-zoom{background:#2D8CFF;}.plat-meet{background:#1a73e8;}.plat-youtube{background:#ff0000;}.plat-teams{background:#464775;}.plat-other{background:#64748b;}
.video-mini{width:90px;height:55px;object-fit:cover;border-radius:8px;background:#1a1a2e;}
</style>

<div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 shadow-sm rounded">
    <h4 class="mb-0 fw-bold"><i class="fa-solid fa-video text-primary me-2"></i>Lectures Manager</h4>
    <ul class="nav lec-tabs mb-0">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabLive"><i class="fa-solid fa-circle text-danger me-1" style="font-size:.5rem;vertical-align:middle"></i> Live Sessions</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabRec"><i class="fa-solid fa-film me-1" style="color:#8b5cf6"></i> Recorded Lectures</a></li>
    </ul>
</div>

<?php if($msg): ?><div class="alert alert-success py-2"><i class="fa-solid fa-check-circle me-2"></i><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if($err): ?><div class="alert alert-danger py-2"><i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($err) ?></div><?php endif; ?>

<div class="tab-content">

<!-- ================== LIVE SESSIONS ================== -->
<div class="tab-pane fade show active" id="tabLive">
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-primary" onclick="openLiveModal()"><i class="fa-solid fa-plus me-1"></i> Schedule Live Session</button>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr><th>Title</th><th>Course</th><th>Platform</th><th>Scheduled</th><th>Duration</th><th>Status</th><th>Action</th></tr>
                </thead>
                <tbody>
                <?php if(empty($lives)): ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">No live sessions yet. Schedule one!</td></tr>
                <?php else: foreach($lives as $l): $past = strtotime($l['scheduled_at']) < time(); ?>
                    <tr>
                        <td>
                            <div class="fw-bold small"><?= htmlspecialchars($l['title']) ?></div>
                            <?php if($l['meeting_id']): ?><small class="text-muted">ID: <?= htmlspecialchars($l['meeting_id']) ?></small><?php endif; ?>
                        </td>
                        <td><small><?= htmlspecialchars($l['cname'] ?? 'All Courses') ?></small></td>
                        <td><span class="plat-badge plat-<?= $l['platform'] ?>"><?= strtoupper($l['platform']) ?></span></td>
                        <td><small><?= date('d M Y, h:i A', strtotime($l['scheduled_at'])) ?></small></td>
                        <td><?= $l['duration_mins'] ?> min</td>
                        <td>
                            <?php if(!$l['is_active']): ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php elseif($past): ?>
                                <span class="badge bg-secondary">Past</span>
                            <?php else: ?>
                                <span class="badge bg-success">Active</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick='editLive(<?= json_encode($l) ?>)'><i class="fa-solid fa-pen"></i></button>
                            <a href="?del_live=<?= $l['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete?')"><i class="fa-solid fa-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ================== RECORDED ================== -->
<div class="tab-pane fade" id="tabRec">
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-primary" onclick="openRecModal()"><i class="fa-solid fa-plus me-1"></i> Add Recorded Lecture</button>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr><th>Video</th><th>Title</th><th>Course</th><th>Subject</th><th>Type</th><th>Status</th><th>Action</th></tr>
                </thead>
                <tbody>
                <?php if(empty($recorded)): ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">No recorded lectures yet.</td></tr>
                <?php else: foreach($recorded as $r):
                    $thumb = $r['thumbnail'];
                    if(!$thumb && $r['video_type']==='youtube'){ preg_match('/(?:v=|youtu\.be\/|embed\/)([^&\?\/]+)/',$r['video_url'],$m); if(isset($m[1])) $thumb="https://img.youtube.com/vi/{$m[1]}/mqdefault.jpg"; }
                ?>
                    <tr>
                        <td><img src="<?= htmlspecialchars($thumb ?: 'https://placehold.co/90x55/1a1a2e/fff?text=Vid') ?>" class="video-mini" alt="thumb"></td>
                        <td>
                            <div class="fw-bold small"><?= htmlspecialchars($r['title']) ?></div>
                            <?php if($r['duration']): ?><small class="text-muted"><?= htmlspecialchars($r['duration']) ?></small><?php endif; ?>
                        </td>
                        <td><small><?= htmlspecialchars($r['cname'] ?? 'All') ?></small></td>
                        <td><small><?= htmlspecialchars($r['subject'] ?? '—') ?></small></td>
                        <td><span class="badge bg-secondary"><?= strtoupper($r['video_type']) ?></span></td>
                        <td><?= $r['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Hidden</span>' ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick='editRec(<?= json_encode($r) ?>)'><i class="fa-solid fa-pen"></i></button>
                            <a href="?del_rec=<?= $r['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete?')"><i class="fa-solid fa-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

<!-- =========== LIVE MODAL =========== -->
<div class="modal fade" id="liveModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST">
        <?= csrfField() ?>
        <input type="hidden" name="live_id" id="live_id" value="0">
        <div class="modal-header"><h5 class="modal-title" id="liveModalTitle">Schedule Live Session</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label fw-bold">Session Title *</label>
              <input type="text" name="title" id="l_title" class="form-control" required placeholder="e.g. Physics Chapter 5 – Live Revision">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-bold">Platform</label>
              <select name="platform" id="l_platform" class="form-select">
                <option value="zoom">Zoom</option>
                <option value="meet">Google Meet</option>
                <option value="youtube">YouTube Live</option>
                <option value="teams">MS Teams</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-bold">Join / Meeting URL *</label>
              <input type="url" name="join_url" id="l_url" class="form-control" required placeholder="https://zoom.us/j/...">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Meeting ID</label>
              <input type="text" name="meeting_id" id="l_mid" class="form-control" placeholder="123 456 7890">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Passcode</label>
              <input type="text" name="passcode" id="l_pass" class="form-control" placeholder="abc123">
            </div>
            <div class="col-md-5">
              <label class="form-label fw-bold">Scheduled Date & Time *</label>
              <input type="datetime-local" name="scheduled_at" id="l_sched" class="form-control" required>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-bold">Duration (mins)</label>
              <input type="number" name="duration_mins" id="l_dur" class="form-control" value="60" min="10">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-bold">Course (optional)</label>
              <select name="course_id" id="l_cid" class="form-select">
                <option value="">All Courses</option>
                <?php foreach($courses as $c): ?><option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-bold">Description</label>
              <textarea name="description" id="l_desc" class="form-control" rows="2" placeholder="Topics to be covered…"></textarea>
            </div>
            <div class="col-12">
              <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" name="is_active" id="l_active" checked>
                <label class="form-check-label fw-bold">Visible to Students</label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="save_live" class="btn btn-primary px-4">Save Session</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- =========== RECORDED MODAL =========== -->
<div class="modal fade" id="recModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" id="recForm" enctype="multipart/form-data">
        <?= csrfField() ?>
        <input type="hidden" name="rec_id" id="rec_id" value="0">
        <div class="modal-header"><h5 class="modal-title" id="recModalTitle">Add Recorded Lecture</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label fw-bold">Lecture Title *</label>
              <input type="text" name="title" id="r_title" class="form-control" required placeholder="e.g. Kinematics – Part 1">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-bold">Video Type</label>
              <select name="video_type" id="r_vtype" class="form-select" onchange="toggleVideoInput(this.value)">
                <option value="youtube">🔗 YouTube Link</option>
                <option value="vimeo">🔗 Vimeo Link</option>
                <option value="upload">📁 Upload Video File</option>
              </select>
            </div>

            <!-- URL Section (YouTube / Vimeo) -->
            <div class="col-12" id="urlSection">
              <label class="form-label fw-bold">Video URL *</label>
              <input type="url" name="video_url" id="r_vurl" class="form-control" placeholder="https://youtu.be/... or https://vimeo.com/...">
              <div class="form-text"><i class="fa-brands fa-youtube text-danger me-1"></i>Paste the full YouTube or Vimeo video URL. Thumbnail will be auto-detected.</div>
            </div>

            <!-- Upload Section -->
            <div class="col-12" id="uploadSection" style="display:none">
              <label class="form-label fw-bold">Upload Video File *</label>
              <div class="upload-drop-zone" id="dropZone" onclick="document.getElementById('videoFileInput').click()">
                <i class="fa-solid fa-cloud-arrow-up fa-2x mb-2" style="color:#1a365d"></i>
                <div class="fw-bold">Click or drag & drop to upload</div>
                <div class="text-muted small mt-1">MP4, WebM, OGG, AVI, MOV · Max 500 MB</div>
                <div id="fileNameDisplay" class="mt-2 text-primary fw-semibold small"></div>
              </div>
              <input type="file" name="video_file" id="videoFileInput" accept="video/mp4,video/webm,video/ogg,video/x-msvideo,video/quicktime" style="display:none" onchange="handleFileSelect(this)">
              <!-- Progress bar (shown during upload) -->
              <div id="uploadProgressWrap" class="mt-2" style="display:none">
                <div class="d-flex justify-content-between small mb-1"><span class="fw-bold text-primary">Uploading...</span><span id="uploadPct">0%</span></div>
                <div class="progress" style="height:8px"><div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" id="uploadBar" style="width:0%"></div></div>
              </div>
              <input type="hidden" name="video_url" id="r_vurl_hidden" value="">
            </div>

            <div class="col-md-6">
              <label class="form-label fw-bold">Subject</label>
              <input type="text" name="subject" id="r_subj" class="form-control" placeholder="e.g. Physics, Maths">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-bold">Duration</label>
              <input type="text" name="duration" id="r_dur" class="form-control" placeholder="e.g. 45:30">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-bold">Course (optional)</label>
              <select name="course_id" id="r_cid" class="form-select">
                <option value="">All Courses</option>
                <?php foreach($courses as $c): ?><option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-12" id="thumbSection">
              <label class="form-label fw-bold">Custom Thumbnail URL <span class="text-muted fw-normal small">(optional)</span></label>
              <input type="url" name="thumbnail" id="r_thumb" class="form-control" placeholder="Leave blank to auto-detect from YouTube">
            </div>
            <div class="col-12">
              <label class="form-label fw-bold">Description</label>
              <textarea name="description" id="r_desc" class="form-control" rows="2" placeholder="Brief description of this lecture…"></textarea>
            </div>
            <div class="col-12">
              <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" name="is_active" id="r_active" checked>
                <label class="form-check-label fw-bold">Visible to Students</label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="save_recorded" class="btn btn-primary px-4" id="saveRecBtn"><i class="fa-solid fa-save me-1"></i>Save Lecture</button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
.upload-drop-zone{border:2px dashed #cbd5e1;border-radius:14px;padding:2rem;text-align:center;cursor:pointer;transition:.2s;background:#f8fafc;}
.upload-drop-zone:hover,.upload-drop-zone.dragover{border-color:#1a365d;background:#eff6ff;}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const liveModal = new bootstrap.Modal(document.getElementById('liveModal'));
    const recModal  = new bootstrap.Modal(document.getElementById('recModal'));

    window.openLiveModal = function() {
        document.getElementById('live_id').value = 0;
        document.getElementById('liveModalTitle').innerText = 'Schedule Live Session';
        ['l_title','l_url','l_mid','l_pass','l_sched','l_desc'].forEach(id => document.getElementById(id).value = '');
        document.getElementById('l_platform').value = 'zoom';
        document.getElementById('l_dur').value = 60;
        document.getElementById('l_cid').value = '';
        document.getElementById('l_active').checked = true;
        liveModal.show();
    };
    window.editLive = function(d) {
        document.getElementById('live_id').value = d.id;
        document.getElementById('liveModalTitle').innerText = 'Edit Live Session';
        document.getElementById('l_title').value = d.title;
        document.getElementById('l_platform').value = d.platform;
        document.getElementById('l_url').value = d.join_url;
        document.getElementById('l_mid').value = d.meeting_id || '';
        document.getElementById('l_pass').value = d.passcode || '';
        document.getElementById('l_sched').value = d.scheduled_at.replace(' ','T');
        document.getElementById('l_dur').value = d.duration_mins;
        document.getElementById('l_cid').value = d.course_id || '';
        document.getElementById('l_desc').value = d.description || '';
        document.getElementById('l_active').checked = d.is_active == 1;
        liveModal.show();
    };

    window.toggleVideoInput = function(val) {
        const isUpload = val === 'upload';
        document.getElementById('urlSection').style.display    = isUpload ? 'none' : 'block';
        document.getElementById('uploadSection').style.display = isUpload ? 'block' : 'none';
        document.getElementById('thumbSection').style.display  = isUpload ? 'none' : 'block';
        document.getElementById('r_vurl').required = !isUpload;
    };

    window.handleFileSelect = function(input) {
        if (input.files && input.files[0]) {
            const f = input.files[0];
            const sizeMB = (f.size / (1024*1024)).toFixed(1);
            document.getElementById('fileNameDisplay').innerHTML =
                '<i class="fa-solid fa-file-video me-1"></i>' + f.name + ' (' + sizeMB + ' MB)';
        }
    };

    // Drag & drop
    const dropZone = document.getElementById('dropZone');
    if (dropZone) {
        dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('dragover'); });
        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
        dropZone.addEventListener('drop', e => {
            e.preventDefault(); dropZone.classList.remove('dragover');
            const dt = e.dataTransfer;
            if (dt.files.length) {
                document.getElementById('videoFileInput').files = dt.files;
                handleFileSelect(document.getElementById('videoFileInput'));
            }
        });
    }

    // Show upload progress via XHR
    document.getElementById('recForm').addEventListener('submit', function(e) {
        const vtype = document.getElementById('r_vtype').value;
        if (vtype !== 'upload') return; // Normal submit for URL types
        const fileInput = document.getElementById('videoFileInput');
        if (!fileInput.files.length) return; // No file chosen, let PHP validate

        e.preventDefault();
        const formData = new FormData(this);
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'lectures.php', true);

        document.getElementById('uploadProgressWrap').style.display = 'block';
        document.getElementById('saveRecBtn').disabled = true;

        xhr.upload.onprogress = function(ev) {
            if (ev.lengthComputable) {
                const pct = Math.round((ev.loaded / ev.total) * 100);
                document.getElementById('uploadBar').style.width = pct + '%';
                document.getElementById('uploadPct').innerText = pct + '%';
            }
        };
        xhr.onload = function() {
            // Reload page to show result
            window.location.href = 'lectures.php?saved=1#tabRec';
        };
        xhr.onerror = function() {
            alert('Upload failed. Please try again.');
            document.getElementById('saveRecBtn').disabled = false;
        };
        xhr.send(formData);
    });

    window.openRecModal = function() {
        document.getElementById('rec_id').value = 0;
        document.getElementById('recModalTitle').innerText = 'Add Recorded Lecture';
        ['r_title','r_vurl','r_subj','r_dur','r_thumb','r_desc'].forEach(id => document.getElementById(id).value = '');
        document.getElementById('r_vtype').value = 'youtube';
        document.getElementById('r_cid').value = '';
        document.getElementById('r_active').checked = true;
        document.getElementById('fileNameDisplay').innerText = '';
        document.getElementById('uploadProgressWrap').style.display = 'none';
        toggleVideoInput('youtube');
        recModal.show();
    };
    window.editRec = function(d) {
        document.getElementById('rec_id').value = d.id;
        document.getElementById('recModalTitle').innerText = 'Edit Recorded Lecture';
        document.getElementById('r_title').value = d.title;
        document.getElementById('r_vtype').value = d.video_type;
        document.getElementById('r_vurl').value = d.video_url;
        document.getElementById('r_subj').value = d.subject || '';
        document.getElementById('r_dur').value = d.duration || '';
        document.getElementById('r_cid').value = d.course_id || '';
        document.getElementById('r_thumb').value = d.thumbnail || '';
        document.getElementById('r_desc').value = d.description || '';
        document.getElementById('r_active').checked = d.is_active == 1;
        toggleVideoInput(d.video_type);
        recModal.show();
    };

    // Auto-switch to recorded tab if ?saved=1#tabRec
    if (location.search.includes('saved=1')) {
        const t = new bootstrap.Tab(document.querySelector('[href="#tabRec"]'));
        if (t) t.show();
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
