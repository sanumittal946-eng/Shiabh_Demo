<?php
// admin/news.php
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$msg = "";
$err = "";

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $db->prepare("DELETE FROM news WHERE id = :id");
    if ($stmt->execute([':id' => $id])) {
        $msg = "Article deleted successfully.";
    }
}

// Handle Add / Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_news'])) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        $err = "Invalid security token.";
    } else {
        $id       = (int)($_POST['news_id'] ?? 0);
        $title    = trim($_POST['title']);
        $category = trim($_POST['category']);
        $content  = trim($_POST['content']);
        $status   = $_POST['status'];

        if (empty($title) || empty($content)) {
            $err = "Title and Content are required.";
        } else {
            if ($id > 0) {
                // UPDATE
                $stmt = $db->prepare("UPDATE news SET title=:title, category=:cat, content=:content, status=:status WHERE id=:id");
                $ok = $stmt->execute([':title'=>$title,':cat'=>$category,':content'=>$content,':status'=>$status,':id'=>$id]);
                $msg = $ok ? "Article updated successfully!" : "Database error.";
            } else {
                // INSERT
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
                // ensure unique slug
                $checkSlug = $db->prepare("SELECT id FROM news WHERE slug=:slug");
                $checkSlug->execute([':slug' => $slug]);
                if ($checkSlug->fetch()) {
                    $slug = $slug . '-' . time();
                }
                $stmt = $db->prepare("INSERT INTO news (title, slug, category, content, status, author) VALUES (:title, :slug, :cat, :content, :status, :author)");
                if ($stmt->execute([':title'=>$title,':slug'=>$slug,':cat'=>$category,':content'=>$content,':status'=>$status,':author'=>$_SESSION['admin_username'] ?? 'Admin'])) {
                    $msg = "News article published successfully!";
                } else {
                    $err = "Database error occurred.";
                }
            }
        }
    }
}

$news = $db->query("SELECT * FROM news ORDER BY id DESC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 shadow-sm rounded">
    <h4 class="mb-0 text-dark fw-bold">News &amp; CMS</h4>
    <button class="btn btn-primary" onclick="openNewsModal()"><i class="fa-solid fa-plus me-1"></i> Post Article</button>
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
                        <th>Date</th>
                        <th>Title &amp; Category</th>
                        <th>Author</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($news)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No news articles found.</td></tr>
                    <?php else: foreach($news as $n): ?>
                        <tr>
                            <td>
                                <div class="small fw-bold"><?= date('M d, Y', strtotime($n['published_at'])) ?></div>
                            </td>
                            <td>
                                <div class="fw-bold text-primary"><?= htmlspecialchars($n['title']) ?></div>
                                <span class="badge bg-secondary"><?= htmlspecialchars($n['category']) ?></span>
                            </td>
                            <td><i class="fa-regular fa-user me-1 text-muted"></i><?= htmlspecialchars($n['author']) ?></td>
                            <td>
                                <?php if($n['status'] == 'published'): ?>
                                    <span class="badge bg-success">Published</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Draft</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1" onclick='editNews(<?= json_encode($n) ?>)'><i class="fa-solid fa-pen"></i></button>
                                <a href="news.php?delete=<?= $n['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this post?');"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add / Edit Modal -->
<div class="modal fade" id="newsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="news_id" id="news_id" value="0">
                <div class="modal-header">
                    <h5 class="modal-title" id="newsModalTitle">Post Article</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Article Title *</label>
                            <input type="text" name="title" id="n_title" class="form-control" required placeholder="e.g. New Batch Starting...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Category</label>
                            <select name="category" id="n_category" class="form-select">
                                <option value="Announcement">Announcement</option>
                                <option value="Results">Results</option>
                                <option value="Events">Events</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status" id="n_status" class="form-select">
                                <option value="published">Published</option>
                                <option value="draft">Draft</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">HTML Content *</label>
                        <textarea name="content" id="n_content" class="form-control" rows="8" required placeholder="Write your news post here..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_news" class="btn btn-success px-4"><i class="fa-solid fa-save me-1"></i> Save Post</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const newsModal = new bootstrap.Modal(document.getElementById('newsModal'));

    window.openNewsModal = function() {
        document.getElementById('news_id').value = 0;
        document.getElementById('newsModalTitle').innerText = 'Post Article';
        document.getElementById('n_title').value = '';
        document.getElementById('n_category').value = 'Announcement';
        document.getElementById('n_status').value = 'published';
        document.getElementById('n_content').value = '';
        newsModal.show();
    }

    window.editNews = function(n) {
        document.getElementById('news_id').value = n.id;
        document.getElementById('newsModalTitle').innerText = 'Edit Article';
        document.getElementById('n_title').value = n.title || '';
        document.getElementById('n_category').value = n.category || 'Announcement';
        document.getElementById('n_status').value = n.status || 'published';
        document.getElementById('n_content').value = n.content || '';
        newsModal.show();
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
