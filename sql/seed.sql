-- Seed Data for Educational and Coaching Institute

-- Admin Users (password: sahib123)
INSERT INTO `admins` (`username`, `password_hash`, `email`) VALUES
('admin', '$2y$10$d7b1TSU.7VTQbsdFeohRNOOQptTwp7U8FgGWNczcuDC4ByYn5T8O.', 'admin@sahibclasses.com');

-- Site Settings
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'Sahib Classes'),
('tagline', 'Empowering Your Future Through Education'),
('phone_1', '+91-9928764349'),
('phone_2', ''),
('email_1', 'info@edufocus.com'),
('email_2', 'admissions@edufocus.com'),
('address', 'Sahib Classes 95 B Jawahar Nagar, 95 B, Old Jawahar Nagar, New Jawahar Nagar, Talwandi, Kota, Rajasthan 324005'),
('office_hours', 'Mon - Sat: 8:00 AM - 7:00 PM'),
('facebook_url', 'https://facebook.com/'),
('instagram_url', 'https://instagram.com/'),
('youtube_url', 'https://youtube.com/'),
('whatsapp_num', '919928764349'),
('telegram_url', 'https://t.me/'),
('map_iframe', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3611.3621466285604!2d75.83416789770126!3d25.157246902422457!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x396f85ab9d839a6f%3A0x85a0fedc6d3550b6!2sSahib%20Classes%2095%20B%20Jawahar%20Nagar!5e0!3m2!1sen!2sin!4v1777469970488!5m2!1sen!2sin');

-- Site Stats
INSERT INTO `site_stats` (`stat_key`, `stat_value`) VALUES
('students_enrolled', '5000+'),
('courses_offered', '45+'),
('years_experience', '15+'),
('pass_rate', '98%');

-- Notices
INSERT INTO `notices` (`title`, `content`, `is_active`) VALUES
('Admissions open for 2024-2025 batch!', 'Enrolments are now accepted for all major courses.', 1),
('New weekend doubt clearing classes introduced', 'Join our special weekend sessions.', 1),
('Scholarship test scheduled for next Sunday', 'Register online to participate.', 1);

-- Faculty
INSERT INTO `faculty` (`name`, `designation`, `subject`, `qualification`, `experience_years`, `bio`) VALUES
('Dr. Robert Clarke', 'Head of Physics', 'Physics', 'Ph.D. in Applied Physics', 12, 'Expert in advanced physics concepts.'),
('Sarah Mitchell', 'Senior Lecturer', 'Mathematics', 'M.Sc. Mathematics', 10, 'Specialized in Calculus.'),
('David Harrison', 'HOD Chemistry', 'Chemistry', 'Ph.D. in Organic Chemistry', 15, 'Engaging chemistry lessons for competitive exams.');

-- Courses
INSERT INTO `courses` (`name`, `category`, `duration`, `fee`, `mode`, `description`, `is_featured`, `sort_order`) VALUES
('JEE Main & Advanced Prep', 'Competitive', '2 Years', 25000.00, 'Hybrid', 'Comprehensive prep program for engineering aspirants.', 1, 1),
('NEET UG Target Course', 'Competitive', '1 Year', 20000.00, 'Offline', 'Intensive medical entrance coaching.', 1, 2),
('Class 12 CBSE Board', 'Board (11-12)', '1 Year', 15000.00, 'Offline', 'Complete syllabus coverage for board exams.', 1, 3),
('Foundation Course Class 9-10', 'School (6-10)', '2 Years', 18000.00, 'Online', 'Building strong basics for early starters.', 1, 4),
('Spoken English Masterclass', 'Spoken English', '3 Months', 5000.00, 'Online', 'Fluency training and personality development.', 1, 5),
('UPSC Mains Preparation', 'Competitive', '1.5 Years', 40000.00, 'Hybrid', 'Premium Civil Services preparation.', 1, 6);

-- Testimonials
INSERT INTO `testimonials` (`name`, `course`, `review`, `rating`, `type`) VALUES
('Rahul Sharma', 'JEE Prep', 'The faculty here is amazing. Cleared my concepts completely.', 5, 'student'),
('Anita Patel', 'Parent of Class 10 student', 'My son has shown significant improvement since joining.', 5, 'parent'),
('Vikas Singh', 'NEET Target', 'Mock tests and frequent doubt clearing sessions were incredibly helpful.', 4, 'student');

-- Milestones
INSERT INTO `milestones` (`year`, `title`, `description`, `icon`) VALUES
(2010, 'Foundation', 'Sahib Classes was established with a batch of 20 students.', 'fa-star'),
(2015, 'Expansion', 'Opened our second and third branches.', 'fa-building'),
(2018, 'Digital Classes', 'Started offering comprehensive online live classes.', 'fa-laptop'),
(2023, '10,000+ Students', 'Crossed the milestone of teaching 10,000 students successfully.', 'fa-users');

-- Results
INSERT INTO `results` (`student_name`, `course`, `exam_type`, `score`, `rank`, `year`, `quote`, `is_topper`) VALUES
('Priya M', 'NEET Target', 'NEET UG', '710/720', 'AIR 45', 2023, 'Hard work and correct guidance paid off.', 1),
('Aman R', 'JEE Prep', 'JEE Advanced', '99.9 PR', 'AIR 120', 2023, 'Thankful to my teachers.', 1);

-- Batches
INSERT INTO `batches` (`course_id`, `start_date`, `timing`, `seats_total`, `mode`) VALUES
(1, '2024-06-01', '4:00 PM - 7:00 PM', 50, 'Offline'),
(2, '2024-06-15', '10:00 AM - 1:00 PM', 40, 'Offline'),
(3, '2024-05-10', '5:00 PM - 8:00 PM', 60, 'Online');

-- Students (password: student123)
INSERT INTO `students` (`name`, `email`, `password_hash`, `course_id`, `batch`, `is_active`) VALUES
('Student One', 'student@sahibclasses.com', '$2y$10$jGRzqgujd.1.Un45nzyVIu2vh86mS67NE6U8SRL9pTCnTUbS4W/XS', 1, 'Batch A', 1);

-- Materials
INSERT INTO `materials` (`title`, `subject`, `type`, `file_path`, `course_id`, `is_free`) VALUES
('Physics Kinematics Notes', 'Physics', 'Notes', 'uploads/materials/physics_notes.pdf', 1, 1),
('Organic Chemistry Intro', 'Chemistry', 'Videos', 'https://youtube.com/embed/dQw4w9WgXcQ', 2, 1);
