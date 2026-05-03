<?php
// contact.php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

$db = getDB();
$msg = $err = "";

// Pre-fill course interest if coming from a course-detail page
$course_interest = sanitizeInput($_GET['course_interest'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contact'])) {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        $err = "Invalid security token. Please try again.";
    } elseif (!empty($_POST['contact_hp'])) {
        die("Spam blocked.");
    } else {
        $name = sanitizeInput($_POST['name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $subjectText = sanitizeInput($_POST['subject'] ?? '');
        $message = sanitizeInput($_POST['message'] ?? '');

        if (empty($name) || empty($email) || empty($message)) {
            $err = "Please fill in all required fields.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $err = "Invalid email format.";
        } else {
            try {
                $fullMessage = "Subject: $subjectText\n\n$message";
                $stmt = $db->prepare("INSERT INTO enquiries (name, phone, email, message, source, status) VALUES (?, ?, ?, ?, 'contact', 'pending')");
                $stmt->execute([$name, $phone, $email, $fullMessage]);

                // Email admin
                $to = getSetting('email_1', 'info@example.com');
                @mail($to, "New Contact Entry: $subjectText", $fullMessage, "From: $email\r\n");

                $msg = "Thanks for reaching out! We've received your message and will get back to you shortly.";
            } catch (Exception $e) {
                $err = "Server error while submitting form. Please try again.";
            }
        }
    }
}

// Contact Info Data
$contact_info = [
    ['icon' => 'fa-map-location-dot', 'title' => 'Address', 'text' => getSetting('address'), 'theme' => 'primary'],
    ['icon' => 'fa-square-phone', 'title' => 'Call Us', 'text' => getSetting('phone_1') . '<br>' . getSetting('phone_2'), 'theme' => 'accent'],
    ['icon' => 'fa-envelope-open-text', 'title' => 'Email', 'text' => getSetting('email_1') . '<br>' . getSetting('email_2'), 'theme' => 'primary'],
    ['icon' => 'fa-business-time', 'title' => 'Working Hours', 'text' => getSetting('office_hours') . '<br><span class="text-accent">Sunday Closed</span>', 'theme' => 'accent']
];

// Social Links Data
$socials = [
    ['icon' => 'fa-facebook-f', 'url' => getSetting('facebook_url')],
    ['icon' => 'fa-instagram', 'url' => getSetting('instagram_url')],
    ['icon' => 'fa-youtube', 'url' => getSetting('youtube_url')],
    ['icon' => 'fa-telegram', 'url' => getSetting('telegram_url')]
];

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<section class="banner-section bg-primary text-white py-5 position-relative" style="background-image: url('https://www.transparenttextures.com/patterns/black-linen.png'); border-bottom: 5px solid var(--accent-color);">
    <div class="container text-center py-5 position-relative" style="z-index: 2;">
        <h1 class="display-4 fw-bold text-white mb-3" data-aos="fade-down">Contact Us</h1>
        <p class="lead text-light opacity-75 mx-auto" style="max-width: 600px;">We're here to help and answer any questions you might have. We look forward to hearing from you.</p>
        <nav aria-label="breadcrumb" class="mt-3">
            <ol class="breadcrumb justify-content-center mb-0">
                <li class="breadcrumb-item"><a href="index.php" class="text-white text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active text-accent" aria-current="page">Contact</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Section 1: Info Cards -->
<section class="section-padding bg-light-grey pb-0">
    <div class="container">
        <div class="row g-4 text-center mt-n5 position-relative" style="z-index: 3;">
            <?php foreach ($contact_info as $index => $item): ?>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="<?= ($index + 1) * 100 ?>">
                <div class="card border-0 shadow h-100 p-4 rounded-4 rounded-top-0 border-top border-4 border-<?= $item['theme'] ?> bg-white">
                    <div class="mx-auto bg-<?= ($item['theme'] == 'accent' ? 'warning' : 'primary') ?>-subtle text-<?= $item['theme'] ?> rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                        <i class="fa-solid <?= $item['icon'] ?> fs-4"></i>
                    </div>
                    <h5 class="fw-bold text-dark"><?= $item['title'] ?></h5>
                    <p class="text-muted small mb-0"><?= $item['text'] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Section 2 & 3: FORM & MAPS -->
<section class="section-padding bg-light-grey pt-5">
    <div class="container">
        <?php if ($msg): ?><div class="alert alert-success fw-bold text-center mb-5"><i class="fa-solid fa-circle-check me-2"></i><?= htmlspecialchars($msg) ?></div><?php endif; ?>
        <?php if ($err): ?><div class="alert alert-danger fw-bold text-center mb-5"><i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($err) ?></div><?php endif; ?>

        <div class="row g-0 rounded-4 overflow-hidden shadow-lg">
            <!-- MAPS -->
            <div class="col-lg-5 d-none d-lg-block bg-light" style="min-height: 400px;">
                <?php $map = getSetting('map_iframe'); ?>
                <?php if ($map): ?>
                    <iframe src="<?= htmlspecialchars($map) ?>" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                <?php else: ?>
                    <img src="https://images.unsplash.com/photo-1526778548025-fa2f459cd5c1?auto=format&fit=crop&w=800&q=80" class="w-100 h-100 object-fit-cover" alt="Map Location">
                <?php endif; ?>
            </div>

            <!-- FORM -->
            <div class="col-lg-7 bg-white p-4 p-md-5">
                <h3 class="text-primary fw-bold mb-1">Send a Message</h3>
                <p class="text-muted small mb-4 border-bottom pb-3">Drop us a line and we will get back to you as soon as possible.</p>

                <form action="contact.php" method="POST">
                    <?= csrfField() ?>
                    <input type="text" name="contact_hp" style="display:none;" tabindex="-1">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6"><input type="text" name="name" class="form-control bg-light px-3 py-2 border-0" placeholder="Your Name *" required></div>
                        <div class="col-md-6"><input type="email" name="email" class="form-control bg-light px-3 py-2 border-0" placeholder="Your Email *" required></div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6"><input type="tel" name="phone" class="form-control bg-light px-3 py-2 border-0" placeholder="Your Phone Number"></div>
                        <div class="col-md-6">
                            <select name="subject" class="form-select bg-light px-3 py-2 border-0" required>
                                <option value="" disabled selected>Select Subject *</option>
                                <option value="Admissions Inquiry">Admissions Inquiry</option>
                                <option value="Fee Details">Fee Details</option>
                                <option value="Course Catalog">Course Catalog</option>
                                <option value="Technical Support">Technical Support</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-4">
                        <textarea name="message" class="form-control bg-light px-3 py-2 border-0" rows="5" placeholder="Write your message here... *" required><?= $course_interest ? "I am interested in learning more about: $course_interest" : "" ?></textarea>
                    </div>
                    <button type="submit" name="submit_contact" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold text-uppercase w-100 shadow-sm">Send Message <i class="fa-solid fa-paper-plane ms-2"></i></button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Section 4: Social Links -->
<section class="py-5 bg-primary text-center">
    <div class="container">
        <h4 class="text-white mb-4">Connect with us on Social Media</h4>
        <div class="d-flex justify-content-center gap-3 mb-5">
            <?php foreach ($socials as $s): ?>
            <a href="<?= $s['url'] ?>" target="_blank" class="btn btn-outline-light rounded-circle d-flex align-items-center justify-content-center" style="width:50px; height:50px; font-size:18px;"><i class="fa-brands <?= $s['icon'] ?>"></i></a>
            <?php endforeach; ?>
        </div>
        <div class="d-flex justify-content-center gap-2 flex-wrap">
            <a href="admission.php" class="btn btn-accent rounded-pill px-4 fw-bold">Admission Application</a>
            <a href="https://wa.me/<?= getSetting('whatsapp_num') ?>" target="_blank" class="btn btn-success rounded-pill px-4 fw-bold"><i class="fa-brands fa-whatsapp me-2"></i> WhatsApp Support</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>