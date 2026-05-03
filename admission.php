<?php
// admission.php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$db = getDB();

// Form processing
$msg = $err = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_application'])) {

    // CSRF Check
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        $err = "Invalid security token. Please try submitting again.";
    }
    // Honeypot Check
    elseif (!empty($_POST['website_hp'])) {
        die("Spam blocked.");
    } else {
        // Sanitize and Validate
        $name = sanitizeInput($_POST['student_name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $course_id = (int) ($_POST['course_id'] ?? 0);
        $message = sanitizeInput($_POST['message'] ?? '');
        // Additional info compiled to message field for simplicity in enquiries table
        $dob = sanitizeInput($_POST['dob'] ?? '');
        $parent_name = sanitizeInput($_POST['parent_name'] ?? '');
        $parent_phone = sanitizeInput($_POST['parent_phone'] ?? '');
        $address = sanitizeInput($_POST['address'] ?? '');
        $prev_school = sanitizeInput($_POST['prev_school'] ?? '');
        $marks = sanitizeInput($_POST['marks'] ?? '');

        if (empty($name) || empty($email) || empty($phone) || empty($course_id)) {
            $err = "Please fill all required fields.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $err = "Invalid email format.";
        } else {
            $full_message = "DOB: $dob\nParent: $parent_name ($parent_phone)\nAddress: $address\nPrev School: $prev_school\nMarks: $marks\n\nNotes:\n$message";

            try {
                $stmt = $db->prepare("INSERT INTO enquiries (name, phone, email, course_id, message, source, status) VALUES (:n, :p, :e, :cid, :m, 'admission', 'pending')");
                $stmt->execute([
                    ':n' => $name,
                    ':p' => $phone,
                    ':e' => $email,
                    ':cid' => $course_id,
                    ':m' => $full_message
                ]);

                // Email simulation
                $to = getSetting('email_1', 'admin@example.com');
                $subject = "New Admission Application - " . $name;
                $headers = "From: " . $email . "\r\n";
                @mail($to, $subject, $full_message, $headers); // Supress warning if mail server not configured

                $msg = "Your application has been submitted successfully. Our admission team will contact you soon.";
            } catch (Exception $e) {
                $err = "An error occurred while submitting. Please try again later.";
            }
        }
    }
}

// Fetch Courses for Dropdown & Fee structure
$coursesStmt = $db->query("SELECT id, name, category, fee FROM courses ORDER BY category ASC, sort_order ASC");
$allCourses = $coursesStmt->fetchAll();

$coursesByCategory = [];
foreach ($allCourses as $c) {
    $coursesByCategory[$c['category']][] = $c;
}

// Fetch admission dates
$datesStmt = $db->query("SELECT event_name, event_date FROM admission_dates ORDER BY event_date ASC LIMIT 5");
$dates = $datesStmt->fetchAll();
// If empty mock data
if (empty($dates)) {
    $dates = [
        ['event_name' => 'Online Registration Starts', 'event_date' => date('Y-m-d', strtotime('+10 days'))],
        ['event_name' => 'Scholarship Test', 'event_date' => date('Y-m-d', strtotime('+20 days'))],
        ['event_name' => 'First Merit List', 'event_date' => date('Y-m-d', strtotime('+25 days'))],
    ];
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Section 1: Page Hero -->
<section class="banner-section bg-primary text-white py-5 position-relative"
    style="background-image: url('https://www.transparenttextures.com/patterns/clean-gray-paper.png'); border-bottom: 5px solid var(--accent-color);">
    <div class="container text-center py-5 position-relative" style="z-index: 2;">
        <h1 class="display-4 fw-bold text-white mb-3" data-aos="fade-down">Admissions & Enrolment</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-center mb-0">
                <li class="breadcrumb-item"><a href="./" class="text-white text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active text-accent" aria-current="page">Admission</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Section 1: Admission Steps -->
<section class="section-padding bg-white">
    <div class="container">
        <div class="section-title text-center mb-5">
            <h2 data-aos="fade-up">Admission Process</h2>
            <p class="text-muted" data-aos="fade-up" data-aos-delay="100">Simple and transparent 4-step process</p>
        </div>

        <div class="row position-relative text-center g-4">
            <!-- connecting line for desktop -->
            <div class="d-none d-lg-block position-absolute"
                style="top: 25%; left: 15%; right: 15%; border-top: 2px dashed var(--primary-color); z-index: 0;"></div>

            <div class="col-lg-3 col-md-6 position-relative" style="z-index: 1;" data-aos="fade-up"
                data-aos-delay="100">
                <div class="bg-white border border-primary border-2 rounded-circle mx-auto d-flex align-items-center justify-content-center shadow-sm mb-3"
                    style="width:70px; height:70px;">
                    <i class="fa-solid fa-file-pen fs-3 text-primary"></i>
                </div>
                <h5 class="fw-bold text-primary">Step 1: Apply</h5>
                <p class="small text-muted">Submit the online application form below with accurate details.</p>
            </div>

            <div class="col-lg-3 col-md-6 position-relative" style="z-index: 1;" data-aos="fade-up"
                data-aos-delay="200">
                <div class="bg-white border border-primary border-2 rounded-circle mx-auto d-flex align-items-center justify-content-center shadow-sm mb-3"
                    style="width:70px; height:70px;">
                    <i class="fa-solid fa-phone-volume fs-3 text-primary"></i>
                </div>
                <h5 class="fw-bold text-primary">Step 2: Counseling</h5>
                <p class="small text-muted">Receive a callback from our academic counselors.</p>
            </div>

            <div class="col-lg-3 col-md-6 position-relative" style="z-index: 1;" data-aos="fade-up"
                data-aos-delay="300">
                <div class="bg-white border border-primary border-2 rounded-circle mx-auto d-flex align-items-center justify-content-center shadow-sm mb-3"
                    style="width:70px; height:70px;">
                    <i class="fa-solid fa-file-invoice-dollar fs-3 text-primary"></i>
                </div>
                <h5 class="fw-bold text-primary">Step 3: Document & Fee</h5>
                <p class="small text-muted">Submit required documents and pay the admission fee.</p>
            </div>

            <div class="col-lg-3 col-md-6 position-relative" style="z-index: 1;" data-aos="fade-up"
                data-aos-delay="400">
                <div class="bg-primary rounded-circle mx-auto d-flex align-items-center justify-content-center shadow mb-3"
                    style="width:70px; height:70px;">
                    <i class="fa-solid fa-check-double fs-3 text-accent"></i>
                </div>
                <h5 class="fw-bold text-primary">Step 4: Confirm</h5>
                <p class="small text-muted">Get your student ID and access to the portal.</p>
            </div>
        </div>
    </div>
</section>

<!-- Section 2: Important Dates & Eligibility -->
<section class="section-padding bg-light-grey">
    <div class="container">
        <div class="row g-5">
            <!-- Important Dates -->
            <div class="col-lg-6" data-aos="fade-right">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-primary text-white p-3">
                        <h4 class="mb-0"><i class="fa-regular fa-calendar-check me-2"></i> Important Dates</h4>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($dates as $date): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                                    <div>
                                        <h6 class="mb-0 text-primary fw-bold"><?= htmlspecialchars($date['event_name']) ?>
                                        </h6>
                                    </div>
                                    <span class="badge bg-light text-dark border p-2"><i
                                            class="fa-regular fa-clock text-accent me-1"></i>
                                        <?= date('d M, Y', strtotime($date['event_date'])) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Eligibility -->
            <div class="col-lg-6" data-aos="fade-left">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-primary text-white p-3">
                        <h4 class="mb-0"><i class="fa-solid fa-user-graduate me-2"></i> Eligibility Criteria</h4>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Program Category</th>
                                        <th>Requirement</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="fw-semibold text-primary">Foundation (Class 6-10)</td>
                                        <td>Must have passed the previous academic year.</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-primary">Board (Class 11-12)</td>
                                        <td>Class 10th pass from recognized board. </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-primary">Competitive (JEE/NEET)</td>
                                        <td>Class 10th or 12th appearing/passed. Admission through screening test.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section 3: Fee Structure Accordion -->
<section class="section-padding bg-white">
    <div class="container text-center">
        <h2 class="text-primary fw-bold mb-5" data-aos="fade-up">Fee <span class="text-accent">Structure</span></h2>

        <div class="row justify-content-center text-start">
            <div class="col-lg-8" data-aos="fade-up">
                <div class="accordion shadow-sm rounded" id="feeAccordion">
                    <?php $i = 0;
                    foreach ($coursesByCategory as $category => $catCourses): ?>
                        <div class="accordion-item border-0 border-bottom">
                            <h2 class="accordion-header" id="feeHeading<?= $i ?>">
                                <button
                                    class="accordion-button <?= $i != 0 ? 'collapsed' : '' ?> fw-bold text-primary bg-light"
                                    type="button" data-bs-toggle="collapse" data-bs-target="#feeCollapse<?= $i ?>"
                                    aria-expanded="<?= $i == 0 ? 'true' : 'false' ?>">
                                    <?= htmlspecialchars($category) ?> Category
                                </button>
                            </h2>
                            <div id="feeCollapse<?= $i ?>" class="accordion-collapse collapse <?= $i == 0 ? 'show' : '' ?>"
                                data-bs-parent="#feeAccordion">
                                <div class="accordion-body p-0">
                                    <table class="table table-striped mb-0">
                                        <tbody>
                                            <?php foreach ($catCourses as $c): ?>
                                                <tr>
                                                    <td class="p-3"><?= htmlspecialchars($c['name']) ?></td>
                                                    <td class="p-3 text-end fw-bold text-primary">
                                                        ₹<?= number_format($c['fee']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php $i++; endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section 4 & 5: Scholarships & Form -->
<section class="section-padding bg-light-grey" id="apply">
    <div class="container">

        <?php if (!empty($msg)): ?>
            <div class="alert alert-success fw-bold text-center mb-5"><i
                    class="fa-solid fa-circle-check me-2"></i><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
        <?php if (!empty($err)): ?>
            <div class="alert alert-danger fw-bold text-center mb-5"><i
                    class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($err) ?></div>
        <?php endif; ?>

        <!-- Right: Application Form -->
        <div class="col-lg-7" data-aos="fade-left">
            <div class="card border-0 shadow-lg border-top border-4 border-primary">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h3 class="text-primary fw-bold">Online Application</h3>
                        <p class="text-muted small">Fill out the details carefully. Fields marked with * are mandatory.
                        </p>
                    </div>

                    <form action="admission#apply" method="POST">
                        <?= csrfField() ?>
                        <!-- Honeypot -->
                        <input type="text" name="website_hp" style="display:none;" tabindex="-1" autocomplete="off">

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small text-muted fw-bold">Student Name *</label>
                                <input type="text" name="student_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted fw-bold">Date of Birth *</label>
                                <input type="date" name="dob" class="form-control" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small text-muted fw-bold">Email Address *</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted fw-bold">Phone Number *</label>
                                <input type="tel" name="phone" class="form-control" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small text-muted fw-bold">Parent Name *</label>
                                <input type="text" name="parent_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted fw-bold">Parent Phone</label>
                                <input type="tel" name="parent_phone" class="form-control">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold">Full Address *</label>
                            <textarea name="address" class="form-control" rows="2" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold">Select Course *</label>
                            <select name="course_id" class="form-select" required>
                                <option value="" selected disabled>-- Select a course to apply for --</option>
                                <?php
                                // preselect if passed via GET
                                $selected_course = isset($_GET['course_id']) ? (int) $_GET['course_id'] : 0;
                                foreach ($allCourses as $c) {
                                    $sel = ($selected_course == $c['id']) ? 'selected' : '';
                                    echo "<option value='{$c['id']}' $sel>{$c['name']} (₹" . number_format($c['fee']) . ")</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small text-muted fw-bold">Previous School/College</label>
                                <input type="text" name="prev_school" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted fw-bold">Prev Marks/Percentage</label>
                                <input type="text" name="marks" class="form-control">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small text-muted fw-bold">Any Message / Query</label>
                            <textarea name="message" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" name="submit_application"
                                class="btn btn-primary btn-lg fw-bold rounded-pill text-uppercase">Submit Application <i
                                    class="fa-solid fa-paper-plane ms-2"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
</section>

<!-- Section 6: Refund Policy -->
<section class="py-5 bg-white">
    <div class="container text-center">
        <h5 class="text-primary mb-3">Want to know about our Refund Policy?</h5>
        <div class="accordion w-75 mx-auto text-start bg-light rounded" id="refundAcc">
            <div class="accordion-item border-0">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed bg-transparent fw-bold" type="button"
                        data-bs-toggle="collapse" data-bs-target="#refundCol">
                        View Refund Policy details
                    </button>
                </h2>
                <div id="refundCol" class="accordion-collapse collapse" data-bs-parent="#refundAcc">
                    <div class="accordion-body small text-muted">
                        Fees once paid are strictly non-refundable and non-transferable under any circumstances.
                        However, if a student decides to withdraw within 7 days of batch commencement, only the tuition
                        fee minus registration charges will be reviewed for partial refund at the management's
                        discretion.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>