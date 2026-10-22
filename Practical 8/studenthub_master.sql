-- StudentHub Master Database Schema & Seed Data
-- CHARUSAT - FTE - ITUE203 Web Development Frameworks
-- Database: studenthub

CREATE DATABASE IF NOT EXISTS `studenthub` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `studenthub`;

-- --------------------------------------------------------
-- 1. Table structure for table `users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `registrations`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `students`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','student') NOT NULL DEFAULT 'student',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `remember_token` varchar(64) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 2. Table structure for table `students`
-- --------------------------------------------------------
CREATE TABLE `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL UNIQUE,
  `course` varchar(100) NOT NULL,
  `year` int(11) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_students_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 3. Table structure for table `events`
-- --------------------------------------------------------
CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `category` varchar(50) NOT NULL DEFAULT 'Technical',
  `description` text DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `poster` varchar(255) DEFAULT NULL,
  `status` enum('open','closed') NOT NULL DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 4. Table structure for table `registrations`
-- --------------------------------------------------------
CREATE TABLE `registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_event_unique` (`student_id`,`event_id`),
  KEY `event_id` (`event_id`),
  CONSTRAINT `fk_registrations_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_registrations_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 5. Table structure for table `audit_logs`
-- --------------------------------------------------------
CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Seed Data for `users`
-- Default Passwords:
-- Admin@123 -> $2y$10$5s/IHC/K9lxSzgo/n3eQqOTEAq80QrFjKzQj0cFJBjZZk9H/V0JQe
-- Student@123 -> $2y$10$.ji3MTwlSGmsRMcUhk7wA.ChumN1JuDwkVB2fENTaHbFkZ7Ne3ydW
-- --------------------------------------------------------
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `status`, `created_at`) VALUES
(1, 'Admin System', 'admin@studenthub.com', '$2y$10$5s/IHC/K9lxSzgo/n3eQqOTEAq80QrFjKzQj0cFJBjZZk9H/V0JQe', 'admin', 'active', '2026-10-01 09:00:00'),
(2, 'Harsh Joshi', 'harsh@gmail.com', '$2y$10$.ji3MTwlSGmsRMcUhk7wA.ChumN1JuDwkVB2fENTaHbFkZ7Ne3ydW', 'student', 'active', '2026-10-01 09:15:00'),
(3, 'Aarav Patel', 'aarav.patel@studenthub.com', '$2y$10$.ji3MTwlSGmsRMcUhk7wA.ChumN1JuDwkVB2fENTaHbFkZ7Ne3ydW', 'student', 'active', '2026-10-02 10:00:00'),
(4, 'Diya Shah', 'diya.shah@studenthub.com', '$2y$10$.ji3MTwlSGmsRMcUhk7wA.ChumN1JuDwkVB2fENTaHbFkZ7Ne3ydW', 'student', 'active', '2026-10-03 11:20:00'),
(5, 'Rohan Mehta', 'rohan.mehta@studenthub.com', '$2y$10$.ji3MTwlSGmsRMcUhk7wA.ChumN1JuDwkVB2fENTaHbFkZ7Ne3ydW', 'student', 'active', '2026-10-04 14:10:00');

-- --------------------------------------------------------
-- Seed Data for `students` (25 Seeded Records as required by Practical 11)
-- --------------------------------------------------------
INSERT INTO `students` (`id`, `user_id`, `name`, `email`, `course`, `year`, `phone`, `status`) VALUES
(1, 2, 'Harsh Joshi', 'harsh@gmail.com', 'B.Tech IT', 2, '9876543210', 'active'),
(2, 3, 'Aarav Patel', 'aarav.patel@studenthub.com', 'B.Tech CSE', 3, '9825123456', 'active'),
(3, 4, 'Diya Shah', 'diya.shah@studenthub.com', 'B.Tech IT', 2, '9724123890', 'active'),
(4, 5, 'Rohan Mehta', 'rohan.mehta@studenthub.com', 'BCA', 1, '9909234512', 'active'),
(5, NULL, 'Ananya Sharma', 'ananya.sharma@studenthub.com', 'B.Tech CSE', 4, '9426012345', 'active'),
(6, NULL, 'Kavya Desai', 'kavya.desai@studenthub.com', 'MCA', 1, '9879512345', 'active'),
(7, NULL, 'Siddharth Trivedi', 'siddharth.t@studenthub.com', 'B.Tech IT', 3, '9898123456', 'active'),
(8, NULL, 'Pooja Varma', 'pooja.varma@studenthub.com', 'BCA', 2, '9712345678', 'active'),
(9, NULL, 'Yash Parmar', 'yash.parmar@studenthub.com', 'B.Tech CSE', 2, '9601234567', 'active'),
(10, NULL, 'Tanvi Dave', 'tanvi.dave@studenthub.com', 'MCA', 2, '9978123456', 'active'),
(11, NULL, 'Devang Solanki', 'devang.s@studenthub.com', 'B.Tech IT', 4, '9824098765', 'active'),
(12, NULL, 'Isha Panchal', 'isha.panchal@studenthub.com', 'BCA', 3, '9428765432', 'active'),
(13, NULL, 'Manan Bhatt', 'manan.bhatt@studenthub.com', 'B.Tech CSE', 1, '9879123890', 'active'),
(14, NULL, 'Khushi Prajapati', 'khushi.p@studenthub.com', 'B.Tech IT', 2, '9904567890', 'active'),
(15, NULL, 'Dhruv Chaudhari', 'dhruv.c@studenthub.com', 'MCA', 1, '9725345678', 'active'),
(16, NULL, 'Riddhi Raval', 'riddhi.raval@studenthub.com', 'BCA', 1, '9898765432', 'active'),
(17, NULL, 'Parth Shukla', 'parth.shukla@studenthub.com', 'B.Tech CSE', 3, '9825678901', 'active'),
(18, NULL, 'Nidhi Kothari', 'nidhi.kothari@studenthub.com', 'B.Tech IT', 3, '9714567890', 'active'),
(19, NULL, 'Jayesh Barot', 'jayesh.barot@studenthub.com', 'MCA', 2, '9638123456', 'inactive'),
(20, NULL, 'Bhavin Rana', 'bhavin.rana@studenthub.com', 'B.Tech CSE', 2, '9979012345', 'active'),
(21, NULL, 'Shreya Jani', 'shreya.jani@studenthub.com', 'BCA', 2, '9429123456', 'active'),
(22, NULL, 'Meet Soni', 'meet.soni@studenthub.com', 'B.Tech IT', 1, '9876123450', 'active'),
(23, NULL, 'Prachi Chauhan', 'prachi.c@studenthub.com', 'B.Tech CSE', 4, '9824567123', 'active'),
(24, NULL, 'Akash Zala', 'akash.zala@studenthub.com', 'MCA', 1, '9909876543', 'active'),
(25, NULL, 'Kinjal Thakkar', 'kinjal.t@studenthub.com', 'BCA', 3, '9723456789', 'inactive');

-- --------------------------------------------------------
-- Seed Data for `events`
-- --------------------------------------------------------
INSERT INTO `events` (`id`, `title`, `category`, `description`, `event_date`, `poster`, `status`) VALUES
(1, 'Hackathon 2026: Code the Future', 'Technical', '36-hour inter-college coding marathon focused on Web3, AI, and Sustainable Tech.', '2026-11-15', 'poster_hackathon.jpg', 'open'),
(2, 'AI & Machine Learning Workshop', 'Workshop', 'Hands-on session exploring deep learning architectures and LLMs using PyTorch.', '2026-11-22', 'poster_ai_workshop.jpg', 'open'),
(3, 'Web Development Sprint', 'Technical', 'Fast-paced frontend and backend development challenge using modern stacks.', '2026-12-05', 'poster_web_sprint.jpg', 'open'),
(4, 'Annual Cultural Fest - Spoural', 'Cultural', 'Mega college festival featuring music, dance, theatre, art, and food stalls.', '2026-12-18', 'poster_cultural.jpg', 'open'),
(5, 'Cyber Security & Ethical Hacking', 'Workshop', 'Practical workshop on network security, penetration testing, and digital forensics.', '2026-10-10', 'poster_cyber.jpg', 'closed'),
(6, 'Inter-Department Cricket Tournament', 'Sports', 'Annual knockout cricket tournament between engineering and computer science branches.', '2026-12-28', 'poster_sports.jpg', 'open');

-- --------------------------------------------------------
-- Seed Data for `registrations`
-- --------------------------------------------------------
INSERT INTO `registrations` (`id`, `student_id`, `event_id`, `registration_date`) VALUES
(1, 1, 1, '2026-10-05 10:14:00'),
(2, 1, 2, '2026-10-06 11:30:00'),
(3, 2, 1, '2026-10-05 14:22:00'),
(4, 2, 3, '2026-10-07 09:12:00'),
(5, 3, 2, '2026-10-08 16:45:00'),
(6, 4, 4, '2026-10-09 12:00:00'),
(7, 5, 1, '2026-10-10 15:30:00'),
(8, 7, 3, '2026-10-11 18:20:00');

-- --------------------------------------------------------
-- Seed Data for `audit_logs`
-- --------------------------------------------------------
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
(1, 1, 'DATABASE_INIT', 'StudentHub master database initialized with seed records.', '127.0.0.1', '2026-10-01 08:30:00'),
(2, 1, 'USER_LOGIN', 'Admin logged into portal from local console.', '127.0.0.1', '2026-10-01 09:00:00'),
(3, 2, 'USER_REGISTER', 'Student Harsh Joshi registered with email harsh@gmail.com.', '127.0.0.1', '2026-10-01 09:15:00'),
(4, 1, 'EVENT_CREATE', 'Created event: Hackathon 2026: Code the Future.', '127.0.0.1', '2026-10-02 11:00:00'),
(5, 2, 'EVENT_REGISTER', 'Student enrolled in Hackathon 2026.', '127.0.0.1', '2026-10-05 10:14:00');
