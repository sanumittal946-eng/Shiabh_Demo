<?php
// admin/notices.php — Manage the scrolling news ticker on the public website
require_once __DIR__ . '/includes/header.php';
$db  = getDB();
$msg = $err = '';

// DELETE
if (isset($_GET['del']) && is_numeric($_GET['del'])) {
    $db->prepare("DELETE FROM notices WHERE id=:id")->execute([':id' => (int)$_GET['del']]);
    $msg = "Notice deleted.";
}

// TOGGLE active
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $db->prepare("UPDATE notices SET is_active = 1 - is_active WHERE id=:id")->execute([':id' => (int)$_GET['toggle']]);
    header("Location: notices.php"); exit;
}

// SAVE (add / edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_notice'])) {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) { $err = "Invalid token."; }
    else {
        $id    = (int)($_POST['notice_id'] ?? 0);
        $title = trim($_POST['title']);
        $link  = trim($_POST['link']);
        $icon  = trim($_POST['icon']) ?: 'fa-bell';
        $order = (int)($_POST['sort_order'] ?? 0);
        $active= isset($_POST['is_active']) ? 1 : 0;

        if (empty($title)) { $err = "Title is required."; }
        else {
            if ($id > 0) {
                $db->prepare("UPDATE notices SET title=:t,link=:l,icon=:i,sort_order=:o,is_active=:a WHERE id=:id")
                   ->execute([':t'=>$title,':l'=>$link?:null,':i'=>$icon,':o'=>$order,':a'=>$active,':id'=>$id]);
            } else {
                $db->prepare("INSERT INTO notices (title,link,icon,sort_order,is_active) VALUES(:t,:l,:i,:o,:a)")
                   ->execute([':t'=>$title,':l'=>$link?:null,':i'=>$icon,':o'=>$order,':a'=>$active]);
            }
            $msg = "Notice saved successfully!";
        }
    }
}

$notices = $db->query("SELECT * FROM notices ORDER BY sort_order ASC, id DESC")->fetchAll();
?>

<style>
.notice-row{transition:.15s;}
.notice-row:hover{background:#f8faff;}
.icon-opt{display:inline-block;padding:4px 10px;border-radius:8px;cursor:pointer;border:1.5px solid #e2e8f0;font-size:.82rem;transition:.15s;}
.icon-opt:hover,.icon-opt.selected{background:#1a365d;color:#fff;border-color:#1a365d;}
.ticker-preview{background:#fb8500;color:#fff;padding:.55rem 1rem;border-radius:10px;overflow:hidden;white-space:nowrap;font-size:.85rem;font-weight:600;position:relative;}
.ticker-inner{display:inline-block;animation:tickerMove 18s linear infinite;}
@keyframes tickerMove{0%{transform:translateX(100%);}100%{transform:translateX(-100%);}}
</style>

<!-- Page header -->
<div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 shadow-sm rounded">
  <h4 class="mb-0 fw-bold"><i class="fa-solid fa-bullhorn text-warning me-2"></i>News Ticker / Notices Manager</h4>
  <button class="btn btn-primary" onclick="openModal()"><i class="fa-solid fa-plus me-1"></i> Add Notice</button>
</div>

<?php if($msg): ?><div class="alert alert-success py-2"><i class="fa-solid fa-check-circle me-2"></i><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if($err): ?><div class="alert alert-danger py-2"><i class="fa-solid fa-exclamation-circle me-2"></i><?= htmlspecialchars($err) ?></div><?php endif; ?>

<!-- Live preview -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-2">
    <div class="small text-muted mb-2 fw-bold"><i class="fa-solid fa-eye me-1"></i> Live Preview (as it appears on the website)</div>
    <div class="ticker-preview">
      <span class="badge bg-dark me-3 fw-bold" style="font-size:.72rem">LATEST UPDATES</span>
      <span class="ticker-inner">
        <?php foreach(array_filter($notices, fn($n)=>$n['is_active']) as $n): ?>
          <i class="fa-solid <?= htmlspecialchars($n['icon'] ?? '') ?> me-1"></i>
          <?= htmlspecialchars($n['title']) ?> &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
        <?php endforeach; ?>
      </span>
    </div>
  </div>
</div>

<!-- Notices table -->
<div class="card border-0 shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light small text-uppercase">
        <tr>
          <th style="width:50px">#</th>
          <th>Notice Text</th>
          <th>Link</th>
          <th>Icon</th>
          <th>Order</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php if(empty($notices)): ?>
        <tr><td colspan="7" class="text-center py-4 text-muted">No notices yet. Add one!</td></tr>
      <?php else: foreach($notices as $n): ?>
        <tr class="notice-row">
          <td class="text-muted small"><?= $n['id'] ?></td>
          <td>
            <div class="fw-semibold" style="font-size:.9rem"><?= htmlspecialchars($n['title']) ?></div>
          </td>
          <td>
            <?php if($n['link']): ?>
              <span class="badge bg-light text-primary border" style="font-size:.72rem"><?= htmlspecialchars($n['link'] ?? '') ?></span>
            <?php else: ?>
              <span class="text-muted small">—</span>
            <?php endif; ?>
          </td>
          <td><i class="fa-solid <?= htmlspecialchars($n['icon'] ?? '') ?> text-warning"></i> <small class="text-muted"><?= htmlspecialchars($n['icon'] ?? '') ?></small></td>
          <td><span class="badge bg-secondary"><?= $n['sort_order'] ?></span></td>
          <td>
            <a href="?toggle=<?= $n['id'] ?>" title="Click to toggle">
              <?= $n['is_active']
                ? '<span class="badge bg-success">Active</span>'
                : '<span class="badge bg-danger">Hidden</span>' ?>
            </a>
          </td>
          <td>
            <button class="btn btn-sm btn-outline-primary" onclick='editNotice(<?= json_encode($n) ?>)'><i class="fa-solid fa-pen"></i></button>
            <a href="?del=<?= $n['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this notice?')"><i class="fa-solid fa-trash"></i></a>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ====== MODAL ====== -->
<div class="modal fade" id="noticeModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <?= csrfField() ?>
        <input type="hidden" name="notice_id" id="notice_id" value="0">
        <div class="modal-header"><h5 class="modal-title" id="noticeModalTitle">Add Notice</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-bold">Notice Text *</label>
            <input type="text" name="title" id="n_title" class="form-control" required placeholder="e.g. Admissions open for 2025-26 batch!">
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Link URL <span class="text-muted fw-normal small">(optional — where to go when clicked)</span></label>
            <input type="text" name="link" id="n_link" class="form-control" placeholder="e.g. admission.php or https://...">
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Icon</label>
            <div class="d-flex flex-wrap gap-2 mt-1" id="iconPicker">
              <?php
              $icons = ['fa-bell'=>'🔔 Bell','fa-star'=>'⭐ Star','fa-circle-exclamation'=>'⚠️ Alert','fa-trophy'=>'🏆 Trophy','fa-calendar-days'=>'📅 Date','fa-graduation-cap'=>'🎓 Grad','fa-book'=>'📚 Book','fa-bullhorn'=>'📢 Megaphone','fa-circle-check'=>'✅ Check','fa-fire'=>'🔥 Fire'];
              foreach($icons as $cls=>$label): ?>
                <span class="icon-opt" data-icon="<?= $cls ?>" onclick="selectIcon(this)">
                  <i class="fa-solid <?= $cls ?> me-1"></i><?= $label ?>
                </span>
              <?php endforeach; ?>
            </div>
            <input type="hidden" name="icon" id="n_icon" value="fa-bell">
          </div>
          <div class="row g-3">
            <div class="col-6">
              <label class="form-label fw-bold">Sort Order</label>
              <input type="number" name="sort_order" id="n_order" class="form-control" value="0" min="0">
              <div class="form-text">Lower = shown first in ticker</div>
            </div>
            <div class="col-6 d-flex align-items-center pt-3">
              <div class="form-check form-switch mt-3">
                <input class="form-check-input" type="checkbox" name="is_active" id="n_active" checked>
                <label class="form-check-label fw-bold">Visible on website</label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="save_notice" class="btn btn-primary px-4"><i class="fa-solid fa-save me-1"></i>Save Notice</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const noticeModal = new bootstrap.Modal(document.getElementById('noticeModal'));

    window.selectIcon = function(el) {
        document.querySelectorAll('.icon-opt').forEach(e => e.classList.remove('selected'));
        el.classList.add('selected');
        document.getElementById('n_icon').value = el.dataset.icon;
    }

    window.openModal = function() {
        document.getElementById('notice_id').value = 0;
        document.getElementById('noticeModalTitle').innerText = 'Add Notice';
        document.getElementById('n_title').value = '';
        document.getElementById('n_link').value = '';
        document.getElementById('n_order').value = 0;
        document.getElementById('n_active').checked = true;
        selectIconByValue('fa-bell');
        noticeModal.show();
    }

    window.editNotice = function(n) {
        document.getElementById('notice_id').value = n.id;
        document.getElementById('noticeModalTitle').innerText = 'Edit Notice';
        document.getElementById('n_title').value = n.title;
        document.getElementById('n_link').value = n.link || '';
        document.getElementById('n_order').value = n.sort_order;
        document.getElementById('n_active').checked = n.is_active == 1;
        selectIconByValue(n.icon);
        noticeModal.show();
    }

    window.selectIconByValue = function(val) {
        document.querySelectorAll('.icon-opt').forEach(e => {
            e.classList.toggle('selected', e.dataset.icon === val);
        });
        document.getElementById('n_icon').value = val;
    }

    // Pre-select default icon on load
    selectIconByValue('fa-bell');
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
