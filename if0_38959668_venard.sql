-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql302.infinityfree.com
-- Generation Time: Feb 24, 2026 at 09:21 AM
-- Server version: 11.4.10-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_38959668_venard`
--

-- --------------------------------------------------------

--
-- Table structure for table `home`
--

CREATE TABLE `home` (
  `id` int(11) NOT NULL,
  `greetings` varchar(255) NOT NULL,
  `facebook_link` varchar(255) DEFAULT NULL,
  `instagram_link` varchar(255) DEFAULT NULL,
  `youtube_link` varchar(255) DEFAULT NULL,
  `typing_text` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `home`
--

INSERT INTO `home` (`id`, `greetings`, `facebook_link`, `instagram_link`, `youtube_link`, `typing_text`) VALUES
(1, 'Greetings!<br/> Venard here', 'https://www.facebook.com/venard.jhon.c.salido', 'https://www.instagram.com/venplaystrings/', 'https://www.linkedin.com/in/venard-jhon-cabahug-salido-08041434b/', 'Gaming, Music, Adventure, Fitness, Web Developing, App Developing');

-- --------------------------------------------------------

--
-- Table structure for table `home_images`
--

CREATE TABLE `home_images` (
  `id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `home_images`
--

INSERT INTO `home_images` (`id`, `image_path`) VALUES
(13, '../assets/images/vv.jpg'),
(14, '../assets/images/vv0.jpg'),
(15, '../assets/images/vv1.jpg'),
(16, '../assets/images/vv2.jpg'),
(17, '../assets/images/vv3.jpg'),
(18, '../assets/images/vv5.jpg'),
(19, '../assets/images/vv6.jpg'),
(21, '../assets/images/vv9.jpg'),
(22, '../assets/images/vv11.jpg'),
(23, '../assets/images/vv4.jpg'),
(24, '../assets/images/vv7.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `thumbnail_path` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `title`, `description`, `thumbnail_path`, `url`, `created_at`, `updated_at`) VALUES
(13, 'Photojournalism', 'Shots during WMSU palaro', '../assets/images/projects/thumb_1765802995__694003f31e140.png', '', '2025-12-15 12:49:55', '2025-12-20 02:01:58'),
(14, 'Photoshop', 'Gained hands-on experience with Adobe Photoshop during the Electives 4 course.', '../assets/images/projects/thumb_1765803699_14_694006b313d9c.png', '', '2025-12-15 12:59:12', '2025-12-16 00:47:42'),
(18, 'Photography', 'Some of my amateur shots using phone camera.', '../assets/images/projects/thumb_1765847452__6940b19cb4d45.jpg', '', '2025-12-16 01:10:52', '2025-12-16 01:14:19'),
(19, 'Yapidz Library ', 'Designed and developed a library management website for a client, providing an online book reservation system.', '../assets/images/projects/thumb_1765847503__6940b1cf7fa6b.jpg', 'https://yapidzlibrary.free.nf', '2025-12-16 01:11:43', '2025-12-16 01:11:43'),
(20, 'NMG Insurance Agency ', 'Designed and developed a website for my client, an insurance agency, that allows customers to apply for insurance online.', '../assets/images/projects/thumb_1765847580__6940b21c47061.jpg', 'https://nmginsurance.free.nf', '2025-12-16 01:13:00', '2025-12-16 01:13:00'),
(21, 'Zecure', 'A cross-platform crime mapping and public safety system for Zamboanga City. Developed for our Capstone project using Flutter.', '../assets/images/projects/thumb_1765847612__6940b23c530ec.jpg', 'https://zecure.netlify.app/', '2025-12-16 01:13:32', '2025-12-16 01:37:20'),
(22, 'Task Manager', 'I made a todo-list app using React.js and deployed it on Vercel', '../assets/images/projects/thumb_1771922312__699d638898c24.png', 'https://venardtask.vercel.app/', '2026-02-24 08:38:32', '2026-02-24 08:38:32');

-- --------------------------------------------------------

--
-- Table structure for table `project_gallery`
--

CREATE TABLE `project_gallery` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_gallery`
--

INSERT INTO `project_gallery` (`id`, `project_id`, `image_path`, `created_at`) VALUES
(69, 13, '../assets/images/projects/gallery/gallery_1765802995_13_694003f3907f8.png', '2025-12-15 12:49:55'),
(79, 13, '../assets/images/projects/gallery/gallery_1765844829_13_6940a75d7acce.png', '2025-12-16 00:27:09'),
(80, 13, '../assets/images/projects/gallery/gallery_1765844844_13_6940a76c1685b.png', '2025-12-16 00:27:24'),
(81, 13, '../assets/images/projects/gallery/gallery_1765844853_13_6940a77564114.png', '2025-12-16 00:27:33'),
(82, 13, '../assets/images/projects/gallery/gallery_1765844872_13_6940a7887a74f.png', '2025-12-16 00:27:53'),
(83, 13, '../assets/images/projects/gallery/gallery_1765844885_13_6940a79501f78.png', '2025-12-16 00:28:05'),
(84, 13, '../assets/images/projects/gallery/gallery_1765844897_13_6940a7a14ebfa.png', '2025-12-16 00:28:17'),
(85, 13, '../assets/images/projects/gallery/gallery_1765844906_13_6940a7aa23839.png', '2025-12-16 00:28:26'),
(86, 14, '../assets/images/projects/gallery/gallery_1765844923_14_6940a7bb58436.png', '2025-12-16 00:28:43'),
(87, 18, '../assets/images/projects/gallery/gallery_1765847452_18_6940b19cb5463.jpg', '2025-12-16 01:10:52'),
(88, 18, '../assets/images/projects/gallery/gallery_1765847452_18_6940b19cb5b96.jpg', '2025-12-16 01:10:52'),
(89, 18, '../assets/images/projects/gallery/gallery_1765847452_18_6940b19cb60b6.jpg', '2025-12-16 01:10:52'),
(90, 18, '../assets/images/projects/gallery/gallery_1765847452_18_6940b19cb6644.jpg', '2025-12-16 01:10:52'),
(91, 18, '../assets/images/projects/gallery/gallery_1765847452_18_6940b19cb6ef8.jpg', '2025-12-16 01:10:52'),
(92, 18, '../assets/images/projects/gallery/gallery_1765847452_18_6940b19cb7478.jpg', '2025-12-16 01:10:52'),
(93, 18, '../assets/images/projects/gallery/gallery_1765847452_18_6940b19cb79ad.jpg', '2025-12-16 01:10:52'),
(94, 18, '../assets/images/projects/gallery/gallery_1765847452_18_6940b19cb7ff6.jpg', '2025-12-16 01:10:52'),
(95, 18, '../assets/images/projects/gallery/gallery_1765847452_18_6940b19cb84d1.jpg', '2025-12-16 01:10:52'),
(96, 18, '../assets/images/projects/gallery/gallery_1765847452_18_6940b19cb89e3.jpg', '2025-12-16 01:10:52'),
(97, 18, '../assets/images/projects/gallery/gallery_1765847452_18_6940b19cb8fbe.jpg', '2025-12-16 01:10:52'),
(98, 18, '../assets/images/projects/gallery/gallery_1765847452_18_6940b19cb9499.jpg', '2025-12-16 01:10:52'),
(103, 14, '../assets/images/projects/gallery/gallery_1766303755_14_6947a80b016c9.png', '2025-12-21 07:55:55'),
(104, 14, '../assets/images/projects/gallery/gallery_1766303792_14_6947a830e845e.png', '2025-12-21 07:56:32'),
(105, 14, '../assets/images/projects/gallery/gallery_1766303836_14_6947a85cbbdbd.png', '2025-12-21 07:57:16');

-- --------------------------------------------------------

--
-- Table structure for table `timeline_items`
--

CREATE TABLE `timeline_items` (
  `id` int(11) NOT NULL,
  `position` enum('left','right') NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `status` varchar(100) DEFAULT 'on progress',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `timeline_items`
--

INSERT INTO `timeline_items` (`id`, `position`, `title`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'left', 'Cute child was born', 'Behold! Venard, the legend was born.', '05/12/1994', '2025-05-09 02:35:06', '2025-05-12 11:14:50'),
(4, 'right', 'Kinder Years', 'Just a little boy.', '1998-2000', '2025-05-09 02:53:48', '2026-01-16 02:52:23'),
(5, 'left', 'Elementary Days', 'Still learning new things.', '2000-2007', '2025-05-09 02:56:32', '2026-01-16 02:52:07'),
(6, 'right', 'Highschool Days', 'High school days were the best!', '2007-2011', '2025-05-09 02:57:47', '2026-01-16 02:51:38'),
(7, 'left', 'College Days', 'Inconsistent, with too many stops—but now at the finish line.', '2011-2026', '2025-05-12 04:54:50', '2026-01-16 02:51:28');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `username` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `gender` enum('Male','Female','Non-Binary','LGBTQ+','Other') DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `birth_place` varchar(100) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `civil_status` enum('Single','Married','Divorced','Widowed') DEFAULT NULL,
  `street_address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `bio` longtext DEFAULT NULL,
  `cover_photo` varchar(255) DEFAULT NULL,
  `religion` varchar(100) DEFAULT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `caption` text DEFAULT NULL,
  `resume_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `role`, `created_at`, `username`, `first_name`, `middle_name`, `last_name`, `gender`, `birth_date`, `birth_place`, `age`, `phone_number`, `civil_status`, `street_address`, `city`, `profile_photo`, `bio`, `cover_photo`, `religion`, `nationality`, `caption`, `resume_path`) VALUES
(1, 'venardjhoncsalido@gmail.com', '$2y$10$XkfR3XlppDFBZgU0XBZai.L.bofV9Ah.86G78MXEiiSmCRUkvCWE6', 'admin', '2025-04-21 14:58:30', 'venard', 'Venard Jhon ', 'C.', 'Salido', NULL, '1994-05-12', 'Zamboanga City', 31, '+63 935-136-3586', NULL, 'Kaputatan Putik', 'Zamboanga City Zamboanga Del Sur', '../assets/images/profiles/481201762_9918028778226973_2180622624945434484_n.jpg', '                I am passionate about improving my coding skills and developing applications and websites.\r\n                I have built several web apps, mobile apps and websites using Flutter and Php.\r\n                Currently, I am working on projects to further enhance my skills. I enjoy creating full-stack clones.\r\n                In addition to coding, I have a deep love for music and play in a band.\r\n                I am also an adventurer who enjoys capturing stunning scenic shots and am on a fitness journey.', '../assets/images/covers/430988045_7819159424780596_2606990541394713286_n.jpg', 'Christian', 'Filipino', 'Just a little boy :)', '../resume/CV.html'),
(9, 'borat@gmail.com', '$2y$10$p18eY0qf7lzAFFc8qoE1R.XZgKNeFSAlqPccmV8T5sRXXbFZ5mjf6', 'user', '2025-04-22 07:09:13', 'borat', 'Borat', 'C', 'Sagadiyev', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_education`
--

CREATE TABLE `user_education` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `level` enum('Primary','Secondary','Tertiary') NOT NULL,
  `school_name` varchar(255) NOT NULL,
  `course` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `start_year` year(4) DEFAULT NULL,
  `end_year` year(4) DEFAULT NULL,
  `image_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_education`
--

INSERT INTO `user_education` (`id`, `user_id`, `level`, `school_name`, `course`, `address`, `start_year`, `end_year`, `image_path`) VALUES
(1, 1, 'Tertiary', 'Western Mindanao State University', 'BS in Information Technology', 'Normal Rd, Zamboanga, 7000 Zamboanga del Sur', 2018, 2026, '../assets/images/edu_cms/wmsu.jpg'),
(2, 1, 'Secondary', 'Zamboanga City Highschool (Main)', '', ' Don Toribio St, Zamboanga, Zamboanga Peninsula', 2007, 2011, '../assets/images/edu_cms/zchs.jpg'),
(3, 1, 'Primary', 'Zamboanga West Centra School', '', 'Carmen St, Zamboanga, Zamboanga del Sur', 2000, 2007, '../assets/images/edu_cms/zwcs.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `user_gallery`
--

CREATE TABLE `user_gallery` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_gallery`
--

INSERT INTO `user_gallery` (`id`, `user_id`, `image_path`, `uploaded_at`) VALUES
(1, 1, '../assets/images/gallery/macho.jpg', '2025-04-23 18:24:57'),
(2, 1, '../assets/images/gallery/macho1.jpg', '2025-04-23 18:24:57'),
(3, 1, '../assets/images/gallery/macho2.jpg', '2025-04-23 20:51:51'),
(4, 1, '../assets/images/gallery/macho3.jpg', '2025-04-23 20:51:51'),
(5, 1, '../assets/images/gallery/v1.jpg', '2025-04-23 20:51:51'),
(6, 1, '../assets/images/gallery/v2.jpg', '2025-04-23 20:51:51'),
(7, 1, '../assets/images/gallery/v3.jpg', '2025-04-23 20:51:51'),
(8, 1, '../assets/images/gallery/v4.jpg', '2025-04-23 20:51:51'),
(10, 1, '../assets/images/gallery/v6.jpg', '2025-04-23 20:51:51'),
(11, 1, '../assets/images/gallery/v7.jpg', '2025-04-23 20:51:51'),
(12, 1, '../assets/images/gallery/v8.jpg', '2025-04-23 20:51:51'),
(13, 1, '../assets/images/gallery/v9.jpg', '2025-04-23 20:51:51'),
(15, 1, '../assets/images/gallery/v10.jpg', '2025-05-10 04:07:43'),
(16, 1, '../assets/images/gallery/Picsart_25-11-24_14-54-03-479.jpg', '2025-12-16 04:35:19'),
(17, 1, '../assets/images/gallery/Picsart_25-08-24_17-44-20-515.jpg', '2025-12-16 04:35:20');

-- --------------------------------------------------------

--
-- Table structure for table `user_skills`
--

CREATE TABLE `user_skills` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `skill_name` varchar(100) NOT NULL,
  `icon_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_skills`
--

INSERT INTO `user_skills` (`id`, `user_id`, `skill_name`, `icon_path`) VALUES
(3, 1, 'HTML', '../assets/images/skill_cms/icons8-html-96.png'),
(4, 1, 'CSS', '../assets/images/skill_cms/css.png'),
(5, 1, 'PHP', '../assets/images/skill_cms/PHP.png'),
(15, 1, 'Django', '../assets/images/skill_cms/python.png'),
(16, 1, 'Javscript', '../assets/images/skill_cms/javascript.png'),
(24, 1, 'Flutter', '../assets/images/skill_cms/FLUTTER.png'),
(28, 1, 'Bootstraps', '../assets/images/skill_cms/bootstrap.png'),
(29, 1, 'Tailwind', '../assets/images/skill_cms/TailwindCSS.png'),
(30, 1, 'SQL', '../assets/images/skill_cms/sql.png'),
(31, 1, 'Photoshop', '../assets/images/skill_cms/Photoshop.png'),
(32, 1, 'Music', '../assets/images/skill_cms/bass.png'),
(33, 1, 'Adventure', '../assets/images/skill_cms/adventure.png'),
(34, 1, 'Fitness', '../assets/images/skill_cms/barbell.png'),
(35, 1, 'Gaming', '../assets/images/skill_cms/gaming.png'),
(36, 1, 'Photography', '../assets/images/skill_cms/camera.png'),
(37, 1, 'Videography', '../assets/images/skill_cms/vid.png'),
(38, 1, 'ReactJS', '../assets/images/skill_cms/icons8-react-100.png'),
(39, 1, 'SQL', '../assets/images/skill_cms/sql.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `home`
--
ALTER TABLE `home`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `home_images`
--
ALTER TABLE `home_images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `project_gallery`
--
ALTER TABLE `project_gallery`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `timeline_items`
--
ALTER TABLE `timeline_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_education`
--
ALTER TABLE `user_education`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_gallery`
--
ALTER TABLE `user_gallery`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_skills`
--
ALTER TABLE `user_skills`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `home`
--
ALTER TABLE `home`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `home_images`
--
ALTER TABLE `home_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `project_gallery`
--
ALTER TABLE `project_gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT for table `timeline_items`
--
ALTER TABLE `timeline_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `user_education`
--
ALTER TABLE `user_education`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_gallery`
--
ALTER TABLE `user_gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `user_skills`
--
ALTER TABLE `user_skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `project_gallery`
--
ALTER TABLE `project_gallery`
  ADD CONSTRAINT `project_gallery_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_gallery`
--
ALTER TABLE `user_gallery`
  ADD CONSTRAINT `user_gallery_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
