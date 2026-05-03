<?php
// admin/enquiries.php
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$msg = "";
$err = "";

// Handle status update
if (isset($_GET['status']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $status = $_GET['status'];
    if (in_array($status, ['pending', 'contacted', 'resolved'])) {
        $stmt = $db->prepare("UPDATE enquiries SET status = :status WHERE id = :id");
        if ($stmt->execute([':status' => $status, ':id' => $id])) {
            $msg = "Enquiry status updated to " . ucfirst($status) . ".";
        } else {
            $err = "Database error.";
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 shadow-sm rounded">
    <h4 class="mb-0 text-dark fw-bold">Manage Admin Enquiries</h4>
</div>

<?php if (!empty($msg)): ?>
    <div class="alert alert-success mt-3"><i class="fa-solid fa-check-circle me-2"></i><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<?php if (!empty($err)): ?>
    <div class="alert alert-danger mt-3"><i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($err) ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Contact User</th>
                        <th>Details</th>
                        <th>Source</th>
                        <th>Status</th>
                        <th>Quick Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $enquiries = $db->query("SELECT * FROM enquiries ORDER BY id DESC")->fetchAll();
                    if (empty($enquiries)):
                    ?>
                        <tr><td colspan="6" class="text-center text-muted">No enquiries found.</td></tr>
                    <?php else: foreach($enquiries as $enq): ?>
                        <tr>
                            <td>
                                <div class="small fw-bold text-dark"><?= date('M d, Y', strtotime($enq['created_at'])) ?></div>
                                <div class="small text-muted"><?= date('h:i A', strtotime($enq['created_at'])) ?></div>
                            </td>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($enq['name']) ?></div>
                                <div><a href="mailto:<?= htmlspecialchars($enq['email']) ?>" class="small text-decoration-none"><?= htmlspecialchars($enq['email']) ?></a></div>
                                <div class="small text-muted"><i class="fa-solid fa-phone me-1"></i><?= htmlspecialchars($enq['phone']) ?></div>
                            </td>
                            <td>
                                <div class="small text-truncate" style="max-width: 250px;" title="<?= htmlspecialchars($enq['message']) ?>">
                                    <?= htmlspecialchars($enq['message'] ?: 'No message provided.') ?>
                                </div>
                            </td>
                            <td><span class="badge bg-secondary"><?= ucfirst($enq['source']) ?></span></td>
                            <td>
                                <?php if($enq['status'] == 'pending'): ?>
                                    <span class="badge bg-warning text-dark"><i class="fa-regular fa-clock me-1"></i>Pending</span>
                                <?php elseif($enq['status'] == 'contacted'): ?>
                                    <span class="badge bg-primary"><i class="fa-solid fa-phone-volume me-1"></i>Contacted</span>
                                <?php else: ?>
                                    <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i>Resolved</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($enq['status'] == 'pending'): ?>
                                    <a href="enquiries.php?status=contacted&id=<?= $enq['id'] ?>" class="btn btn-sm btn-primary">Mark Contacted</a>
                                <?php elseif($enq['status'] == 'contacted'): ?>
                                    <a href="enquiries.php?status=resolved&id=<?= $enq['id'] ?>" class="btn btn-sm btn-success">Mark Resolved</a>
                                <?php else: ?>
                                    <a href="enquiries.php?status=pending&id=<?= $enq['id'] ?>" class="btn btn-sm btn-outline-warning text-dark">Reopen</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
