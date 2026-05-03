<?php
// admin/timetable_admin.php
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$msg = $err = "";

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($db->prepare("DELETE FROM timetable WHERE id = :id")->execute([':id' => $id])) {
        $msg = "Schedule entry deleted.";
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_schedule'])) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        $err = "Invalid security token.";
    } else {
        $batch = trim($_POST['batch']);
        $day = trim($_POST['day']);
        $period = (int)$_POST['period'];
        $subject = trim($_POST['subject']);
        $faculty_id = (int)$_POST['faculty_id'] ?: null;
        $room = trim($_POST['room']);
        $time_start = $_POST['time_start'];
        $time_end = $_POST['time_end'];

        if (empty($batch) || empty($day) || empty($time_start) || empty($time_end)) {
            $err = "Batch, Day, and Time are required.";
        } else {
            $stmt = $db->prepare("INSERT INTO timetable (batch, day, period, subject, faculty_id, room, time_start, time_end) VALUES (:batch, :day, :period, :sub, :fac, :room, :ts, :te)");
            if ($stmt->execute([
                ':batch' => $batch,
                ':day' => $day,
                ':period' => $period,
                ':sub' => $subject,
                ':fac' => $faculty_id,
                ':room' => $room,
                ':ts' => $time_start,
                ':te' => $time_end
            ])) {
                $msg = "Schedule entry added.";
            } else {
                $err = "Database error.";
            }
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 shadow-sm rounded">
    <h4 class="mb-0 text-dark fw-bold">Live Timetable</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addScheduleModal"><i class="fa-solid fa-plus me-1"></i> Add Schedule</button>
</div>

<?php if (!empty($msg)): ?>
    <div class="alert alert-success"><i class="fa-solid fa-check-circle me-2"></i><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<?php if (!empty($err)): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($err) ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Batch & Day</th>
                        <th>Timing</th>
                        <th>Subject</th>
                        <th>Faculty & Room</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $schedules = $db->query("SELECT t.*, f.name as faculty_name FROM timetable t LEFT JOIN faculty f ON t.faculty_id = f.id ORDER BY FIELD(day,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), time_start ASC")->fetchAll();
                    if (empty($schedules)):
                    ?>
                        <tr><td colspan="5" class="text-center text-muted">No schedule found.</td></tr>
                    <?php else: foreach($schedules as $s): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-primary"><?= htmlspecialchars($s['batch']) ?></div>
                                <span class="badge bg-secondary"><?= htmlspecialchars($s['day']) ?></span>
                            </td>
                            <td>
                                <div><?= date('h:i A', strtotime($s['time_start'])) ?> - <?= date('h:i A', strtotime($s['time_end'])) ?></div>
                            </td>
                            <td class="fw-bold"><?= htmlspecialchars($s['subject']) ?></td>
                            <td>
                                <div><i class="fa-solid fa-chalkboard-user me-1 text-muted"></i><?= htmlspecialchars($s['faculty_name'] ?? 'TBD') ?></div>
                                <div><i class="fa-solid fa-door-open me-1 text-muted"></i><?= htmlspecialchars($s['room'] ?? 'N/A') ?></div>
                            </td>
                            <td>
                                <a href="timetable_admin.php?delete=<?= $s['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this slot?');"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addScheduleModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="timetable_admin.php">
          <?= csrfField() ?>
          <div class="modal-header border-0 pb-0">
            <h5 class="modal-title fw-bold text-primary">Add Schedule Entry</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4">
              <div class="row g-3">
                  <div class="col-md-6">
                      <label class="form-label fw-bold">Batch Name *</label>
                      <input type="text" name="batch" class="form-control" required placeholder="e.g. JEE Toppers">
                  </div>
                  <div class="col-md-6">
                      <label class="form-label fw-bold">Day of Week *</label>
                      <select name="day" class="form-select" required>
                          <option value="Monday">Monday</option><option value="Tuesday">Tuesday</option><option value="Wednesday">Wednesday</option>
                          <option value="Thursday">Thursday</option><option value="Friday">Friday</option><option value="Saturday">Saturday</option><option value="Sunday">Sunday</option>
                      </select>
                  </div>
                  <div class="col-md-6">
                      <label class="form-label fw-bold">Time Start *</label>
                      <input type="time" name="time_start" class="form-control" required>
                  </div>
                  <div class="col-md-6">
                      <label class="form-label fw-bold">Time End *</label>
                      <input type="time" name="time_end" class="form-control" required>
                  </div>
                  <div class="col-md-6">
                      <label class="form-label fw-bold">Subject *</label>
                      <input type="text" name="subject" class="form-control" required placeholder="e.g. Mathematics">
                  </div>
                  <div class="col-md-6">
                      <label class="form-label fw-bold">Faculty</label>
                      <select name="faculty_id" class="form-select">
                          <option value="">-- Auto-assign --</option>
                          <?php
                          $facs = $db->query("SELECT id, name FROM faculty")->fetchAll();
                          foreach($facs as $f) { echo "<option value='{$f['id']}'>".htmlspecialchars($f['name'])."</option>"; }
                          ?>
                      </select>
                  </div>
                  <div class="col-md-6">
                      <label class="form-label fw-bold">Room / Hall</label>
                      <input type="text" name="room" class="form-control" placeholder="e.g. Hall A">
                  </div>
                  <div class="col-md-6">
                      <label class="form-label fw-bold">Period / Slot Number</label>
                      <input type="number" name="period" class="form-control" value="1">
                  </div>
              </div>
          </div>
          <div class="modal-footer border-0">
            <button type="submit" name="save_schedule" class="btn btn-success px-4"><i class="fa-solid fa-save me-1"></i> Save to Timetable</button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
