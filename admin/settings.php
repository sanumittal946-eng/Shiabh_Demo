<?php
// admin/settings.php
require_once __DIR__ . '/includes/header.php';

$success = "";
$error = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_settings'])) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        $error = "Invalid security token.";
    } else {
        try {
            $db = getDB();
            $settings_to_update = [
                'site_name', 'tagline', 'phone_1', 'phone_2', 'email_1', 'email_2', 
                'address', 'office_hours', 'facebook_url', 'instagram_url', 
                'youtube_url', 'whatsapp_num', 'telegram_url', 'map_iframe'
            ];

            $db->beginTransaction();
            $stmt = $db->prepare("UPDATE site_settings SET setting_value = :val WHERE setting_key = :key");
            
            foreach ($settings_to_update as $key) {
                if (isset($_POST[$key])) {
                    $stmt->execute([
                        ':val' => sanitizeInput($_POST[$key]),
                        ':key' => $key
                    ]);
                }
            }
            $db->commit();
            $success = "Settings updated successfully!";
            
            // Refresh settings in global variable if using any caching
            // (The getSiteSettings function fetches from DB if empty, so it will refresh next time it's called)
            unset($GLOBALS['siteSettings']); 

        } catch (Exception $e) {
            $db->rollBack();
            $error = "Error updating settings: " . $e->getMessage();
        }
    }
}

// Fetch current settings
$settings = getSiteSettings();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-gray-800 fw-bold"><i class="fa-solid fa-gear me-2 text-primary"></i>Site Settings</h2>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i><?= $success ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i><?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form method="POST" action="settings.php" class="row g-4">
        <?= csrfField() ?>
        
        <!-- General Settings -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-primary fw-bold"><i class="fa-solid fa-info-circle me-2"></i>General Information</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Website Name</label>
                        <input type="text" name="site_name" class="form-control" value="<?= htmlspecialchars($settings['site_name'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Site Tagline</label>
                        <input type="text" name="tagline" class="form-control" value="<?= htmlspecialchars($settings['tagline'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Office Address</label>
                        <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($settings['address'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold small text-muted">Office Hours</label>
                        <input type="text" name="office_hours" class="form-control" value="<?= htmlspecialchars($settings['office_hours'] ?? '') ?>" placeholder="e.g. Mon - Sat: 9 AM - 6 PM">
                    </div>
                    <div class="mt-3">
                        <label class="form-label fw-bold small text-muted">Google Maps Embed URL</label>
                        <input type="text" name="map_iframe" class="form-control" value="<?= htmlspecialchars($settings['map_iframe'] ?? '') ?>" placeholder="Paste the 'src' attribute of your Google Maps iframe">
                        <div class="form-text small">Only the URL (e.g., https://www.google.com/maps/embed?pb=...)</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Settings -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-primary fw-bold"><i class="fa-solid fa-phone me-2"></i>Contact Details</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">Phone Number 1</label>
                            <input type="text" name="phone_1" class="form-control" value="<?= htmlspecialchars($settings['phone_1'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">Phone Number 2</label>
                            <input type="text" name="phone_2" class="form-control" value="<?= htmlspecialchars($settings['phone_2'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">Email Address 1</label>
                            <input type="email" name="email_1" class="form-control" value="<?= htmlspecialchars($settings['email_1'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">Email Address 2</label>
                            <input type="email" name="email_2" class="form-control" value="<?= htmlspecialchars($settings['email_2'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted">WhatsApp Number (For floating button)</label>
                            <input type="text" name="whatsapp_num" class="form-control" value="<?= htmlspecialchars($settings['whatsapp_num'] ?? '') ?>" placeholder="e.g. 919876543210 (without +)">
                            <div class="form-text small">Include country code without '+' or '00'.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Social Media Settings -->
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-primary fw-bold"><i class="fa-solid fa-share-nodes me-2"></i>Social Media Links</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6 col-lg-3">
                            <label class="form-label fw-bold small text-muted"><i class="fa-brands fa-facebook text-primary me-1"></i> Facebook URL</label>
                            <input type="url" name="facebook_url" class="form-control" value="<?= htmlspecialchars($settings['facebook_url'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label class="form-label fw-bold small text-muted"><i class="fa-brands fa-instagram text-danger me-1"></i> Instagram URL</label>
                            <input type="url" name="instagram_url" class="form-control" value="<?= htmlspecialchars($settings['instagram_url'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label class="form-label fw-bold small text-muted"><i class="fa-brands fa-youtube text-danger me-1"></i> YouTube URL</label>
                            <input type="url" name="youtube_url" class="form-control" value="<?= htmlspecialchars($settings['youtube_url'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label class="form-label fw-bold small text-muted"><i class="fa-brands fa-telegram text-info me-1"></i> Telegram URL</label>
                            <input type="url" name="telegram_url" class="form-control" value="<?= htmlspecialchars($settings['telegram_url'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 text-end mb-5">
            <hr>
            <button type="submit" name="save_settings" class="btn btn-primary px-5 fw-bold shadow-sm rounded-pill">
                <i class="fa-solid fa-save me-2"></i>Save All Settings
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
