// assets/js/main.js

$(document).ready(function() {
    // Initialize AOS Animation
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 100
        });
    }

    // Back to Top Button Logic
    var backToTopBtn = $('#backToTopBtn');
    $(window).scroll(function() {
        if ($(window).scrollTop() > 300) {
            backToTopBtn.addClass('show');
        } else {
            backToTopBtn.removeClass('show');
        }
    });

    backToTopBtn.on('click', function(e) {
        e.preventDefault();
        $('html, body').animate({scrollTop:0}, '300');
    });

    // Newsletter Ajax Submit
    $('#newsletter-form').on('submit', function(e) {
        e.preventDefault();
        var email = $(this).find('input[type="email"]').val();
        var csrf = $(this).find('input[name="csrf_token"]').val();
        var msgDiv = $('#newsletter-msg');
        
        // Mock success for now since we don't have endpoints yet
        msgDiv.text('Thank you for subscribing!').addClass('text-success').removeClass('text-danger').show();
        $(this).find('input[type="email"]').val('');
        setTimeout(function(){ msgDiv.fadeOut(); }, 4000);
    });

    // Filtering Logic for Courses / Faculty (Data attributes)
    $('.filter-btn').on('click', function() {
        var filterValue = $(this).attr('data-filter');
        
        // active state handling
        $(this).siblings().removeClass('active btn-primary').addClass('btn-outline-primary');
        $(this).removeClass('btn-outline-primary').addClass('active btn-primary');

        if (filterValue === 'all') {
            $('.filter-item').show(300);
        } else {
            $('.filter-item').hide();
            $('.filter-item[data-category="' + filterValue + '"]').show(300);
        }
    });
});
