<?php
// admin/batches.php
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$msg = "";
$err = "";

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $db->prepare("DELETE FROM batches WHERE id = :id");
    if ($stmt->execute([':id' => $id])) {
        $msg = "Batch deleted successfully.";
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_batch'])) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        $err = "Invalid security token.";
    } else {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $course_id = (int)$_POST['course_id'];
        $start_date = $_POST['start_date'];
        $timing = trim($_POST['timing']);
        $mode = $_POST['mode'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (empty($course_id) || empty($start_date) || empty($timing)) {
            $err = "All fields are required.";
        } else {
            if ($id > 0) {
                $stmt = $db->prepare("UPDATE batches SET course_id = :cid, start_date = :sd, timing = :timing, mode = :mode, is_active = :ia WHERE id = :id");
                $params = [':cid' => $course_id, ':sd' => $start_date, ':timing' => $timing, ':mode' => $mode, ':ia' => $is_active, ':id' => $id];
            } else {
                $stmt = $db->prepare("INSERT INTO batches (course_id, start_date, timing, mode, is_active) VALUES (:cid, :sd, :timing, :mode, :ia)");
                $params = [':cid' => $course_id, ':sd' => $start_date, ':timing' => $timing, ':mode' => $mode, ':ia' => $is_active];
            }

            if ($stmt->execute($params)) {
                $msg = $id > 0 ? "Batch updated successfully." : "Batch added successfully.";
            } else {
                $err = "Database error occurred.";
            }
        }
    }
}

// Fetch Courses for Dropdown
$courses = $db->query("SELECT id, name FROM courses ORDER BY name ASC")->fetchAll();

// Fetch Batches
$stmt = $db->query("SELECT b.*, c.name as course_name FROM batches b JOIN courses c ON b.course_id = c.id ORDER BY b.start_date ASC");
$allBatches = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 shadow-sm rounded">
    <h4 class="mb-0 text-dark fw-bold">Manage Upcoming Batches</h4>
    <button class="btn btn-primary" onclick="openModal()"><i class="fa-solid fa-plus me-1"></i> Add Batch</button>
</div>

<?php if (!empty($msg)): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-check-circle me-2"></i><?= htmlspecialchars($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (!empty($err)): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($err) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Course</th>
                        <th>Start Date</th>
                        <th>Timing</th>
                        <th>Mode</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($allBatches)): ?>
                        <tr><td colspan="6" class="text-center text-muted">No upcoming batches defined.</td></tr>
                    <?php else: foreach($allBatches as $b): ?>
                        <tr>
                            <td class="fw-bold"><?= htmlspecialchars($b['course_name']) ?></td>
                            <td><?= date('d M, Y', strtotime($b['start_date'])) ?></td>
                            <td><?= htmlspecialchars($b['timing']) ?></td>
                            <td><span class="badge <?= $b['mode'] == 'Online' ? 'bg-info' : ($b['mode'] == 'Offline' ? 'bg-success' : 'bg-warning') ?>"><?= $b['mode'] ?></span></td>
                            <td>
                                <?php if($b['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="editBatch(<?= htmlspecialchars(json_encode($b)) ?>)"><i class="fa-solid fa-pen"></i></button>
                                <a href="batches.php?delete=<?= $b['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this batch?');"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="batchModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="id" id="batch_id" value="0">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New Batch</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Course *</label>
                        <select name="course_id" id="course_id" class="form-select" required>
                            <option value="">-- Choose Course --</option>
                            <?php foreach($courses as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Start Date *</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Timing *</label>
                        <input type="text" name="timing" id="timing" class="form-control" placeholder="e.g. 10:00 AM - 12:00 PM" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mode</label>
                        <select name="mode" id="mode" class="form-select">
                            <option value="Offline">Offline</option>
                            <option value="Online">Online</option>
                            <option value="Hybrid">Hybrid</option>
                        </select>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                        <label class="form-check-label fw-bold">Active (Show in Frontend)</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_batch" class="btn btn-primary px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalElement = document.getElementById('batchModal');
    const modal = new bootstrap.Modal(modalElement);

    window.openModal = function() {
        document.getElementById('batch_id').value = 0;
        document.getElementById('course_id').value = "";
        document.getElementById('start_date').value = "";
        document.getElementById('timing').value = "";
        document.getElementById('mode').value = "Offline";
        document.getElementById('is_active').checked = true;
        document.getElementById('modalTitle').innerText = "Add New Batch";
        modal.show();
    }

    window.editBatch = function(data) {
        document.getElementById('batch_id').value = data.id;
        document.getElementById('course_id').value = data.course_id;
        document.getElementById('start_date').value = data.start_date;
        document.getElementById('timing').value = data.timing;
        document.getElementById('mode').value = data.mode;
        document.getElementById('is_active').checked = data.is_active == 1;
        document.getElementById('modalTitle').innerText = "Edit Batch Details";
        modal.show();
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
