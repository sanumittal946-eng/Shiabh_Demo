<?php
// timetable.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/student_header.php';

$db = getDB();
$student_id = $_SESSION['student_id'];

// Get Student Batch
$sStmt = $db->prepare("SELECT batch FROM students WHERE id = :id");
$sStmt->execute([':id' => $student_id]);
$batch = $sStmt->fetchColumn() ?: 'General';

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 text-dark fw-bold">My Learning Schedule</h4>
    <span class="badge bg-primary px-3 py-2">Batch: <?= htmlspecialchars($batch) ?></span>
</div>

<div class="row g-4">
    <?php foreach($days as $day): ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fw-bold <?= date('l') == $day ? 'text-accent' : 'text-primary' ?>">
                        <i class="fa-regular fa-calendar-check me-2"></i><?= $day ?>
                        <?php if(date('l') == $day) echo '<span class="badge bg-accent ms-2 text-white small">Today</span>'; ?>
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-uppercase">
                                <tr>
                                    <th style="width: 200px;">Time Slot</th>
                                    <th>Subject & Topic</th>
                                    <th>Faculty</th>
                                    <th>Room / Link</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $tStmt = $db->prepare("SELECT t.*, f.name as fac_name FROM timetable t LEFT JOIN faculty f ON t.faculty_id = f.id WHERE t.batch = :batch AND t.day = :day ORDER BY t.time_start ASC");
                                $tStmt->execute([':batch' => $batch, ':day' => $day]);
                                $slots = $tStmt->fetchAll();
                                
                                if(empty($slots)):
                                ?>
                                    <tr><td colspan="4" class="text-center py-3 text-muted small">No sessions scheduled for <?= $day ?>.</td></tr>
                                <?php else: foreach($slots as $slot): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-primary"><?= date('h:i A', strtotime($slot['time_start'])) ?></div>
                                            <div class="smaller text-muted"><?= date('h:i A', strtotime($slot['time_end'])) ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($slot['subject']) ?></div>
                                        </td>
                                        <td><small><?= htmlspecialchars($slot['fac_name'] ?? 'TBA') ?></small></td>
                                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($slot['room'] ?? 'Hall A') ?></span></td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
