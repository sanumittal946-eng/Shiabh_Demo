-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: edu_focus
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'admin','$2y$10$4J/xV3ZvZQcXffBUtwIsGO7FZRgAiYFgHnFJkphlPlq40GPjNUwfy','admin@sahibclasses.com',NULL,NULL);
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admission_dates`
--

DROP TABLE IF EXISTS `admission_dates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admission_dates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_name` varchar(255) NOT NULL,
  `event_date` date NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admission_dates`
--

LOCK TABLES `admission_dates` WRITE;
/*!40000 ALTER TABLE `admission_dates` DISABLE KEYS */;
/*!40000 ALTER TABLE `admission_dates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `batches`
--

DROP TABLE IF EXISTS `batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `batches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `timing` varchar(100) NOT NULL,
  `seats_total` int(11) NOT NULL,
  `seats_filled` int(11) DEFAULT 0,
  `mode` varchar(50) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `batches`
--

LOCK TABLES `batches` WRITE;
/*!40000 ALTER TABLE `batches` DISABLE KEYS */;
INSERT INTO `batches` VALUES (1,1,'2024-06-01','4:00 PM - 7:00 PM',50,0,'Offline',1),(2,2,'2024-06-15','10:00 AM - 1:00 PM',40,0,'Offline',1),(3,3,'2024-05-10','5:00 PM - 8:00 PM',60,0,'Online',1);
/*!40000 ALTER TABLE `batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `duration` varchar(100) NOT NULL,
  `fee` decimal(10,2) NOT NULL,
  `mode` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `syllabus_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`syllabus_json`)),
  `batch_timing` varchar(255) DEFAULT NULL,
  `brochure_pdf` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courses`
--

LOCK TABLES `courses` WRITE;
/*!40000 ALTER TABLE `courses` DISABLE KEYS */;
INSERT INTO `courses` VALUES (2,'NEET UG Target Course','Competitive','1 Year',20000.00,'Offline','Intensive medical entrance coaching.',NULL,NULL,NULL,1,2,'2026-04-29 13:28:53'),(3,'Class 12 CBSE Board','Board (11-12)','1 Year',15000.00,'Offline','Complete syllabus coverage for board exams.',NULL,NULL,NULL,1,3,'2026-04-29 13:28:53'),(4,'Foundation Course Class 9-10','School (6-10)','2 Years',18000.00,'Online','Building strong basics for early starters.',NULL,NULL,NULL,1,4,'2026-04-29 13:28:53');
/*!40000 ALTER TABLE `courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doubt_replies`
--

DROP TABLE IF EXISTS `doubt_replies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `doubt_replies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `doubt_id` int(11) NOT NULL,
  `reply` text NOT NULL,
  `replied_by` varchar(100) NOT NULL,
  `replied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doubt_replies`
--

LOCK TABLES `doubt_replies` WRITE;
/*!40000 ALTER TABLE `doubt_replies` DISABLE KEYS */;
/*!40000 ALTER TABLE `doubt_replies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doubts`
--

DROP TABLE IF EXISTS `doubts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `doubts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `question` text NOT NULL,
  `status` enum('open','resolved') DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doubts`
--

LOCK TABLES `doubts` WRITE;
/*!40000 ALTER TABLE `doubts` DISABLE KEYS */;
/*!40000 ALTER TABLE `doubts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enquiries`
--

DROP TABLE IF EXISTS `enquiries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enquiries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `source` enum('contact','admission','course') NOT NULL,
  `status` enum('pending','contacted','resolved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enquiries`
--

LOCK TABLES `enquiries` WRITE;
/*!40000 ALTER TABLE `enquiries` DISABLE KEYS */;
/*!40000 ALTER TABLE `enquiries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `faculty`
--

DROP TABLE IF EXISTS `faculty`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faculty` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `designation` varchar(100) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `qualification` varchar(255) NOT NULL,
  `experience_years` int(11) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `faculty`
--

LOCK TABLES `faculty` WRITE;
/*!40000 ALTER TABLE `faculty` DISABLE KEYS */;
/*!40000 ALTER TABLE `faculty` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `live_lectures`
--

DROP TABLE IF EXISTS `live_lectures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `live_lectures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `platform` varchar(50) DEFAULT NULL,
  `join_url` varchar(255) NOT NULL,
  `meeting_id` varchar(100) DEFAULT NULL,
  `passcode` varchar(100) DEFAULT NULL,
  `scheduled_at` datetime NOT NULL,
  `duration_mins` int(11) DEFAULT 60,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `live_lectures`
--

LOCK TABLES `live_lectures` WRITE;
/*!40000 ALTER TABLE `live_lectures` DISABLE KEYS */;
INSERT INTO `live_lectures` VALUES (1,3,'maths','','zoom','https://us05web.zoom.us/j/2633910331?pwd=RGZGcDZGQmc2MGJBUElpbjU4YklrZz09&omn=89152096037','263 391 0331','r59Am0','2026-04-30 09:30:00',60,1);
/*!40000 ALTER TABLE `live_lectures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `materials`
--

DROP TABLE IF EXISTS `materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `materials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `type` enum('Notes','Videos','Practice Papers','Previous Years') NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` varchar(50) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `is_free` tinyint(1) DEFAULT 0,
  `download_count` int(11) DEFAULT 0,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `materials`
--

LOCK TABLES `materials` WRITE;
/*!40000 ALTER TABLE `materials` DISABLE KEYS */;
INSERT INTO `materials` VALUES (1,'Physics Kinematics Notes','Physics','Notes','uploads/materials/physics_notes.pdf',NULL,1,1,0,'2026-04-29 13:28:53');
/*!40000 ALTER TABLE `materials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `milestones`
--

DROP TABLE IF EXISTS `milestones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `milestones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `year` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `milestones`
--

LOCK TABLES `milestones` WRITE;
/*!40000 ALTER TABLE `milestones` DISABLE KEYS */;
INSERT INTO `milestones` VALUES (1,2010,'Foundation','Sahib Classes was established with a batch of 20 students.','fa-star'),(2,2015,'Expansion','Opened our second and third branches.','fa-building'),(3,2018,'Digital Classes','Started offering comprehensive online live classes.','fa-laptop'),(4,2023,'10,000+ Students','Crossed the milestone of teaching 10,000 students successfully.','fa-users');
/*!40000 ALTER TABLE `milestones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `content` longtext DEFAULT NULL,
  `excerpt` text DEFAULT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `author` varchar(100) DEFAULT NULL,
  `status` enum('published','draft') DEFAULT 'published',
  `published_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news`
--

LOCK TABLES `news` WRITE;
/*!40000 ALTER TABLE `news` DISABLE KEYS */;
/*!40000 ALTER TABLE `news` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notices`
--

DROP TABLE IF EXISTS `notices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notices`
--

LOCK TABLES `notices` WRITE;
/*!40000 ALTER TABLE `notices` DISABLE KEYS */;
INSERT INTO `notices` VALUES (1,'Admissions open for 2024-2025 batch!',NULL,NULL,'Enrolments are now accepted for all major courses.',1,0,'2026-04-29 13:28:53'),(2,'New weekend doubt clearing classes introduced',NULL,NULL,'Join our special weekend sessions.',1,0,'2026-04-29 13:28:53'),(3,'Scholarship test scheduled for next Sunday',NULL,NULL,'Register online to participate.',1,0,'2026-04-29 13:28:53');
/*!40000 ALTER TABLE `notices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recorded_lectures`
--

DROP TABLE IF EXISTS `recorded_lectures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recorded_lectures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `video_type` varchar(50) DEFAULT 'youtube',
  `video_url` varchar(255) NOT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recorded_lectures`
--

LOCK TABLES `recorded_lectures` WRITE;
/*!40000 ALTER TABLE `recorded_lectures` DISABLE KEYS */;
/*!40000 ALTER TABLE `recorded_lectures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `results`
--

DROP TABLE IF EXISTS `results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_name` varchar(255) NOT NULL,
  `course` varchar(100) NOT NULL,
  `exam_type` varchar(100) NOT NULL,
  `score` varchar(50) NOT NULL,
  `rank` varchar(50) DEFAULT NULL,
  `year` int(11) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `quote` text DEFAULT NULL,
  `is_topper` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `results`
--

LOCK TABLES `results` WRITE;
/*!40000 ALTER TABLE `results` DISABLE KEYS */;
INSERT INTO `results` VALUES (1,'Priya M','NEET Target','NEET UG','710/720','AIR 45',2023,NULL,'Hard work and correct guidance paid off.',1),(2,'Aman R','JEE Prep','JEE Advanced','99.9 PR','AIR 120',2023,NULL,'Thankful to my teachers.',1);
/*!40000 ALTER TABLE `results` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site_settings`
--

DROP TABLE IF EXISTS `site_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `site_settings`
--

LOCK TABLES `site_settings` WRITE;
/*!40000 ALTER TABLE `site_settings` DISABLE KEYS */;
INSERT INTO `site_settings` VALUES (1,'site_name','Sahib Classes','2026-04-29 13:28:53'),(2,'tagline','Empowering Your Future Through Education','2026-04-29 13:28:53'),(3,'phone_1','+91-9928764349','2026-04-29 13:36:47'),(4,'phone_2','','2026-04-29 13:45:11'),(5,'email_1','info@edufocus.com','2026-04-29 13:28:53'),(6,'email_2','admissions@edufocus.com','2026-04-29 13:28:53'),(7,'address','Sahib Classes 95 B Jawahar Nagar, 95 B, Old Jawahar Nagar, New Jawahar Nagar, Talwandi, Kota, Rajasthan 324005','2026-04-29 13:36:47'),(8,'office_hours','Mon - Sat: 8:00 AM - 7:00 PM','2026-04-29 13:28:53'),(9,'facebook_url','https://facebook.com/','2026-04-29 13:28:53'),(10,'instagram_url','https://instagram.com/','2026-04-29 13:28:53'),(11,'youtube_url','https://youtube.com/','2026-04-29 13:28:53'),(12,'whatsapp_num','919928764349','2026-04-29 13:36:47'),(13,'telegram_url','https://t.me/','2026-04-29 13:28:53'),(14,'map_iframe','https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3611.3621466285604!2d75.83416789770126!3d25.157246902422457!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x396f85ab9d839a6f%3A0x85a0fedc6d3550b6!2sSahib%20Classes%2095%20B%20Jawahar%20Nagar!5e0!3m2!1sen!2sin!4v1777469970488!5m2!1sen!2sin','2026-04-29 13:43:28');
/*!40000 ALTER TABLE `site_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site_stats`
--

DROP TABLE IF EXISTS `site_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `site_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stat_key` varchar(100) NOT NULL,
  `stat_value` varchar(255) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `stat_key` (`stat_key`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `site_stats`
--

LOCK TABLES `site_stats` WRITE;
/*!40000 ALTER TABLE `site_stats` DISABLE KEYS */;
INSERT INTO `site_stats` VALUES (1,'students_enrolled','5000+','2026-04-29 13:28:53'),(2,'courses_offered','45+','2026-04-29 13:28:53'),(3,'years_experience','15+','2026-04-29 13:28:53'),(4,'pass_rate','98%','2026-04-29 13:28:53');
/*!40000 ALTER TABLE `site_stats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `batch` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `course_id` (`course_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `students`
--

LOCK TABLES `students` WRITE;
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
INSERT INTO `students` VALUES (1,'Student One','student@sahibclasses.com',NULL,'','$2y$10$DXd9hKALgn3qo.ThNiAs7ejbYpWa8hxDzgo/muee.hkzJo.se5RCi',3,'Batch A',1,'2026-04-29 13:28:53','2026-04-29 14:31:25');
/*!40000 ALTER TABLE `students` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscribers`
--

DROP TABLE IF EXISTS `subscribers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscribers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscribers`
--

LOCK TABLES `subscribers` WRITE;
/*!40000 ALTER TABLE `subscribers` DISABLE KEYS */;
/*!40000 ALTER TABLE `subscribers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_submissions`
--

DROP TABLE IF EXISTS `test_submissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `test_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `score` int(11) DEFAULT NULL,
  `status` enum('submitted','checked') DEFAULT 'submitted',
  `teacher_remarks` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_submissions`
--

LOCK TABLES `test_submissions` WRITE;
/*!40000 ALTER TABLE `test_submissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `test_submissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testimonials`
--

DROP TABLE IF EXISTS `testimonials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `course` varchar(100) DEFAULT NULL,
  `review` text NOT NULL,
  `rating` int(11) NOT NULL DEFAULT 5,
  `photo` varchar(255) DEFAULT NULL,
  `type` enum('student','parent','video') NOT NULL DEFAULT 'student',
  `video_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testimonials`
--

LOCK TABLES `testimonials` WRITE;
/*!40000 ALTER TABLE `testimonials` DISABLE KEYS */;
INSERT INTO `testimonials` VALUES (1,'Rahul Sharma','JEE Prep','The faculty here is amazing. Cleared my concepts completely.',5,NULL,'student',NULL,1),(2,'Anita Patel','Parent of Class 10 student','My son has shown significant improvement since joining.',5,NULL,'parent',NULL,1),(3,'Vikas Singh','NEET Target','Mock tests and frequent doubt clearing sessions were incredibly helpful.',4,NULL,'student',NULL,1);
/*!40000 ALTER TABLE `testimonials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tests`
--

DROP TABLE IF EXISTS `tests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `total_marks` int(11) NOT NULL,
  `passing_marks` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tests`
--

LOCK TABLES `tests` WRITE;
/*!40000 ALTER TABLE `tests` DISABLE KEYS */;
/*!40000 ALTER TABLE `tests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timetable`
--

DROP TABLE IF EXISTS `timetable`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `timetable` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `batch` varchar(100) NOT NULL,
  `day` varchar(20) NOT NULL,
  `period` int(11) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `faculty_id` int(11) DEFAULT NULL,
  `room` varchar(50) DEFAULT NULL,
  `time_start` time NOT NULL,
  `time_end` time NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timetable`
--

LOCK TABLES `timetable` WRITE;
/*!40000 ALTER TABLE `timetable` DISABLE KEYS */;
INSERT INTO `timetable` VALUES (1,'A','Monday',1,'Mathamatics',NULL,'','10:00:00','23:00:00');
/*!40000 ALTER TABLE `timetable` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-03 22:49:38
