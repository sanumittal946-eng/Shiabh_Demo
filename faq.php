<?php
// faq.php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';

// Mock FAQ Data (Since FAQ table wasn't in DB schema, but required in spec)
$faqs = [
    [
        'category' => 'Admission',
        'question' => 'What is the admission procedure?',
        'answer' => 'You can apply online through our Admission page. After screening your application, our counselors will call you to guide you through the process, document verification, and fee payment.'
    ],
    [
        'category' => 'Admission',
        'question' => 'Is there any entrance exam?',
        'answer' => 'Yes, for competitive courses (JEE/NEET target batches), we conduct a mandatory scholarship/screening test to understand the student baseline.'
    ],
    [
        'category' => 'Fees',
        'question' => 'Are there any hidden charges in the course fee?',
        'answer' => 'No. Our fee structure is completely transparent covering tuition, regular assignments, and study materials. However, a small registration fee applies initially.'
    ],
    [
        'category' => 'Fees',
        'question' => 'Do you provide refund of fees?',
        'answer' => 'Fees are generally non-refundable. However, refer to our comprehensive refund policy during admission for exceptional 7-day withdrawal conditions.'
    ],
    [
        'category' => 'Courses',
        'question' => 'Do you offer hybrid (online + offline) classes?',
        'answer' => 'Yes, select courses offer hybrid modes. Students can attend physical classes and get access to recorded online lectures on their portal.'
    ],
    [
        'category' => 'Technical',
        'question' => 'How can I access study materials online?',
        'answer' => 'Upon successful admission, you will be provided a Student ID and Password. You can log in using the Student Portal and access all materials.'
    ],
    [
        'category' => 'General',
        'question' => 'What is the student to teacher ratio?',
        'answer' => 'We maintain a strict 30:1 student-to-teacher ratio to ensure personalized attention and focused doubt clearing.'
    ]
];

$categories = ['Admission', 'Fees', 'Courses', 'Technical', 'General'];
?>

<!-- Page Hero -->
<section class="banner-section bg-primary text-white py-5 position-relative" style="background-image: url('https://www.transparenttextures.com/patterns/black-linen-2.png'); border-bottom: 5px solid var(--accent-color);">
    <div class="container text-center py-5 position-relative" style="z-index: 2;">
        <h1 class="display-4 fw-bold text-white mb-3" data-aos="fade-down">Frequently Asked Questions</h1>
        <p class="lead text-light opacity-75 mx-auto" style="max-width: 600px;">Find answers to the most common questions about our courses, admission process, and more.</p>
        <nav aria-label="breadcrumb" class="mt-3">
            <ol class="breadcrumb justify-content-center mb-0">
                <li class="breadcrumb-item"><a href="index.php" class="text-white text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active text-accent" aria-current="page">FAQ</li>
            </ol>
        </nav>
    </div>
</section>

<section class="section-padding bg-light-grey min-vh-100">
    <div class="container">
        <!-- Search and Filter -->
        <div class="row justify-content-center mb-5" data-aos="fade-up">
            <div class="col-lg-8">
                <!-- Search Box -->
                <div class="input-group input-group-lg shadow-sm border-0 rounded-pill overflow-hidden mb-4">
                    <span class="input-group-text bg-white border-0 ps-4"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" id="faqSearch" class="form-control border-0 px-3 bg-white" placeholder="Search your question here...">
                </div>
                
                <!-- Category Tabs -->
                <ul class="nav nav-pills justify-content-center gap-2" id="faqTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill filter-btn" data-filter="all">All FAQs</button>
                    </li>
                    <?php foreach($categories as $cat): ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill filter-btn" data-filter="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></button>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- FAQ Accordion -->
        <div class="row justify-content-center">
            <div class="col-lg-8" data-aos="fade-up" data-aos-delay="100">
                <style>
                    /* Make active nav pill use primary color */
                    #faqTabs .nav-link.active {
                        background-color: var(--primary-color);
                        color: #ffffff;
                    }
                    #faqTabs .nav-link {
                        color: var(--text-dark);
                        border: 1px solid rgba(0,0,0,0.1);
                        background-color: #fff;
                    }
                </style>
                
                <div class="accordion shadow-sm rounded-4 overflow-hidden" id="accordionFAQ">
                    <?php $i=0; foreach($faqs as $faq): $i++; ?>
                    <div class="accordion-item border-0 border-bottom faq-item" data-category="<?= htmlspecialchars($faq['category']) ?>">
                        <h2 class="accordion-header" id="heading<?= $i ?>">
                            <button class="accordion-button collapsed fw-bold text-primary bg-white py-4 faq-question" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $i ?>" aria-expanded="false" aria-controls="collapse<?= $i ?>">
                                <?= htmlspecialchars($faq['question']) ?>
                                <span class="badge bg-light text-dark border ms-3 fw-normal" style="font-size: 0.75rem;"><?= htmlspecialchars($faq['category']) ?></span>
                            </button>
                        </h2>
                        <div id="collapse<?= $i ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $i ?>" data-bs-parent="#accordionFAQ">
                            <div class="accordion-body bg-light text-muted py-4">
                                <?= htmlspecialchars($faq['answer']) ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- No Results State -->
                <div id="noResults" class="text-center py-5 d-none">
                    <i class="fa-regular fa-face-frown text-muted opacity-50 display-1 mb-3"></i>
                    <h5 class="text-muted">No questions found matching your search.</h5>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Still Have Questions CTA -->
<section class="py-5 bg-accent text-white border-top border-5 border-primary">
    <div class="container text-center py-3">
        <h3 class="fw-bold mb-3">Still have questions?</h3>
        <p class="mb-4 text-white opacity-75">If you cannot find answer to your question in our FAQ, you can always contact us. We will answer to you shortly!</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="contact.php" class="btn btn-primary rounded-pill px-4 fw-bold shadow"><i class="fa-regular fa-envelope me-2"></i> Mail Us</a>
            <a href="https://wa.me/<?= getSetting('whatsapp_num') ?>" target="_blank" class="btn btn-success rounded-pill px-4 fw-bold shadow"><i class="fa-brands fa-whatsapp me-2"></i> WhatsApp Support</a>
        </div>
    </div>
</section>

<!-- JS Live Filter & Search Logic -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('faqSearch');
    const filterBtns = document.querySelectorAll('.filter-btn');
    const items = document.querySelectorAll('.faq-item');
    const noResults = document.getElementById('noResults');
    let currentFilter = 'all';

    function filterFaqs() {
        let searchTerm = searchInput.value.toLowerCase();
        let visibleCount = 0;

        items.forEach(item => {
            const question = item.querySelector('.faq-question').innerText.toLowerCase();
            const category = item.getAttribute('data-category');
            
            const matchSearch = question.includes(searchTerm);
            const matchCategory = (currentFilter === 'all') || (category === currentFilter);
            
            if (matchSearch && matchCategory) {
                item.style.display = 'block';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        if (visibleCount === 0) {
            noResults.classList.remove('d-none');
        } else {
            noResults.classList.add('d-none');
        }
    }

    // Search Box Listener
    searchInput.addEventListener('input', filterFaqs);

    // Tab Listeners
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            currentFilter = this.getAttribute('data-filter');
            filterFaqs();
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
