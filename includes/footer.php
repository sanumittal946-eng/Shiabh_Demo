<?php
// includes/footer.php
require_once __DIR__ . '/functions.php';

// Fetch courses for footer
$footerCourses = [];
try {
    $db = getDB();
    $stmt = $db->query("SELECT id, name FROM courses ORDER BY sort_order ASC LIMIT 6");
    $footerCourses = $stmt->fetchAll();
} catch (Exception $e) {
    // silently fail for footer
}
?>

<footer class="site-footer">
    <!-- Footer Top Strip -->
    <div class="footer-top-strip">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center py-3">
            <div class="footer-tagline text-white mb-2 mb-md-0">
                <?= getSetting('tagline', 'Empowering Your Future Through Education') ?>
            </div>
            <div class="footer-social">
                <a href="<?= getSetting('facebook_url') ?>" target="_blank" aria-label="Facebook"><i
                        class="fa-brands fa-facebook-f"></i></a>
                <a href="<?= getSetting('instagram_url') ?>" target="_blank" aria-label="Instagram"><i
                        class="fa-brands fa-instagram"></i></a>
                <a href="<?= getSetting('youtube_url') ?>" target="_blank" aria-label="YouTube"><i
                        class="fa-brands fa-youtube"></i></a>
                <a href="https://wa.me/<?= getSetting('whatsapp_num') ?>" target="_blank" aria-label="WhatsApp"><i
                        class="fa-brands fa-whatsapp"></i></a>
                <a href="<?= getSetting('telegram_url') ?>" target="_blank" aria-label="Telegram"><i
                        class="fa-brands fa-telegram"></i></a>
            </div>
        </div>
    </div>

    <!-- Main Footer -->
    <div class="footer-main py-5">
        <div class="container">
            <div class="row g-4">
                <!-- Column 1 -->
                <div class="col-lg-3 col-md-6 col-12">
                    <h4 class="text-white mb-3"><i
                            class="fa-solid fa-graduation-cap me-2"></i><?= getSetting('site_name', 'Sahib Classes') ?></h4>
                    <p class="footer-desc text-light opacity-75">
                        We are a premier coaching institute dedicated to helping students achieve their academic and
                        career goals with expert faculty and proven methodologies.
                    </p>
                    <a href="assets/uploads/brochure.pdf" target="_blank"
                        class="btn btn-outline-light btn-sm rounded-pill"><i
                            class="fa-solid fa-download me-2"></i>Download Brochure</a>
                </div>

                <!-- Column 2 -->
                <div class="col-lg-3 col-md-6 col-12">
                    <h5 class="text-white mb-3">Quick Links</h5>
                    <ul class="list-unstyled footer-links">
                        <li><a href="./"><i class="fa-solid fa-angle-right me-2"></i>Home</a></li>
                        <li><a href="about"><i class="fa-solid fa-angle-right me-2"></i>About Us</a></li>
                        <li><a href="courses"><i class="fa-solid fa-angle-right me-2"></i>Courses</a></li>
                        <li><a href="admission"><i class="fa-solid fa-angle-right me-2"></i>Admission</a></li>
                        <li><a href="faculty"><i class="fa-solid fa-angle-right me-2"></i>Faculty</a></li>
                        <li><a href="results"><i class="fa-solid fa-angle-right me-2"></i>Results</a></li>
                        <li><a href="testimonials"><i class="fa-solid fa-angle-right me-2"></i>Testimonials</a></li>
                        <li><a href="contact"><i class="fa-solid fa-angle-right me-2"></i>Contact Us</a></li>
                    </ul>
                </div>

                <!-- Column 3 -->
                <div class="col-lg-3 col-md-6 col-12">
                    <h5 class="text-white mb-3">Our Courses</h5>
                    <ul class="list-unstyled footer-links">
                        <?php foreach ($footerCourses as $course): ?>
                            <li><a href="course-detail.php?id=<?= $course['id'] ?>"><i
                                        class="fa-solid fa-angle-right me-2"></i><?= htmlspecialchars($course['name']) ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="courses" class="text-accent text-decoration-none mt-2 d-inline-block">View All Courses
                        <i class="fa-solid fa-arrow-right ms-1"></i></a>
                </div>

                <!-- Column 4 -->
                <div class="col-lg-3 col-md-6 col-12">
                    <h5 class="text-white mb-3">Get In Touch</h5>
                    <ul class="list-unstyled footer-contact">
                        <li><i class="fa-solid fa-map-pin me-2 text-accent"></i>
                            <?= htmlspecialchars(getSetting('address')) ?></li>
                        <li><i class="fa-solid fa-phone me-2 text-accent"></i>
                            <?= htmlspecialchars(getSetting('phone_1')) ?></li>
                        <li><i class="fa-solid fa-envelope me-2 text-accent"></i>
                            <?= htmlspecialchars(getSetting('email_1')) ?></li>
                        <li><i class="fa-solid fa-clock me-2 text-accent"></i>
                            <?= htmlspecialchars(getSetting('office_hours')) ?></li>
                    </ul>
                    <a href="https://wa.me/<?= getSetting('whatsapp_num') ?>"
                        class="btn btn-success btn-sm rounded-pill mt-2 mb-4"><i
                            class="fa-brands fa-whatsapp me-2"></i>Chat with Us</a>

                    <hr class="border-secondary mb-3">

                    <h6 class="text-white">Newsletter</h6>
                    <form id="newsletter-form" class="mt-2">
                        <?= csrfField() ?>
                        <div class="input-group">
                            <input type="email" class="form-control form-control-sm bg-dark text-white border-secondary"
                                placeholder="Email Address" required>
                            <button class="btn btn-accent btn-sm text-white" type="submit">Subscribe</button>
                        </div>
                        <div id="newsletter-msg" class="form-text text-white mt-1" style="display:none;"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Bar -->
    <div class="footer-bottom py-3">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center">
            <div class="text-light opacity-50 small mb-2 mb-md-0">
                &copy; <?= date('Y') ?> <?= getSetting('site_name', 'Sahib Classes') ?>. All Rights Reserved.
            </div>
            <div class="footer-bottom-links small">
                <a href="#">Privacy Policy</a> |
                <a href="#">Terms of Use</a> |
                <a href="sitemap.xml">Sitemap</a>
            </div>
            <div class="text-light opacity-50 small mt-2 mt-md-0">
                Designed & Developed by Somil Mittal
            </div>
        </div>
    </div>
</footer>

<!-- Floating WhatsApp Button -->
<a href="https://wa.me/<?= getSetting('whatsapp_num') ?>" class="floating-whatsapp" target="_blank"
    aria-label="Chat on WhatsApp">
    <i class="fa-brands fa-whatsapp"></i>
</a>

<!-- Back to Top Button -->
<button class="back-to-top" id="backToTopBtn" aria-label="Back to Top">
    <i class="fa-solid fa-arrow-up"></i>
</button>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
    crossorigin="anonymous"></script>
<!-- AOS Animation JS -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<!-- Custom JS -->
<script src="assets/js/main.js"></script>
</body>

</html>