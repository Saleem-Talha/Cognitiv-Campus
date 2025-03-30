-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 22, 2024 at 12:49 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `talha_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `course_images`
--

CREATE TABLE `course_images` (
  `id` int(11) NOT NULL,
  `course_id` varchar(255) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_images`
--

INSERT INTO `course_images` (`id`, `course_id`, `user_email`, `image_path`, `created_at`) VALUES
(1, '676016607913', '210973@students.au.edu.pk', 'img/676016607913_1721252504.jpg', '2024-07-17 21:41:44'),
(2, '666174933500', '210973@students.au.edu.pk', 'img/666174933500_1721254329.jpg', '2024-07-17 22:12:09'),
(3, '662663097520', '210973@students.au.edu.pk', 'img/662663097520_1721254348.gif', '2024-07-17 22:12:28'),
(4, '666174933500', '210973@students.au.edu.pk', 'img/666174933500_1721254355.gif', '2024-07-17 22:12:35'),
(5, '676016607913', '210973@students.au.edu.pk', 'img/676016607913_1721254361.gif', '2024-07-17 22:12:41'),
(6, '676016607913', '210973@students.au.edu.pk', 'img/676016607913_1721254395.gif', '2024-07-17 22:13:15'),
(7, '676016607913', '210973@students.au.edu.pk', 'img/676016607913_1721254403.gif', '2024-07-17 22:13:23'),
(8, '676016607913', '210973@students.au.edu.pk', 'img/676016607913_1721254416.gif', '2024-07-17 22:13:36'),
(9, '666174933500', '210973@students.au.edu.pk', 'img/666174933500_1721254435.jpeg', '2024-07-17 22:13:55'),
(10, '666174933500', '210973@students.au.edu.pk', 'img/666174933500_1721254458.gif', '2024-07-17 22:14:18'),
(11, '662663097520', '210973@students.au.edu.pk', 'img/662663097520_1721254467.jpg', '2024-07-17 22:14:27'),
(12, '663160811255', '210973@students.au.edu.pk', 'img/663160811255_1721254474.jpg', '2024-07-17 22:14:34'),
(13, '663908690561', '210946@students.au.edu.pk', 'img/663908690561_1721417587.gif', '2024-07-19 19:33:07');

-- --------------------------------------------------------

--
-- Table structure for table `course_status`
--

CREATE TABLE `course_status` (
  `id` int(11) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `course_id` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `course_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_status`
--

INSERT INTO `course_status` (`id`, `user_id`, `course_id`, `status`, `created_at`, `updated_at`, `course_name`) VALUES
(28, '210973@students.au.edu.pk', '676016607913', 1, '2024-07-18 21:06:23', '2024-07-18 21:06:23', 'Advisory Class'),
(29, '210973@students.au.edu.pk', '666174933500', 1, '2024-07-18 21:06:28', '2024-07-18 21:06:28', 'DIP (C)'),
(30, '210973@students.au.edu.pk', '663160811255', 1, '2024-07-18 21:06:30', '2024-07-18 21:06:30', 'Full Stack Web Development Lab Spring 2024'),
(31, '210973@students.au.edu.pk', '662663097520', 1, '2024-07-18 21:06:32', '2024-07-18 21:06:32', 'FYP-01-Sp2024(Fall\'21Batch)'),
(32, '210973@students.au.edu.pk', '651323442318', 1, '2024-07-18 21:06:37', '2024-07-18 21:06:37', 'Artificial Intelligence (Theory + Lab)'),
(34, '210973@students.au.edu.pk', '658387132436', 1, '2024-07-18 21:06:42', '2024-07-18 21:06:42', 'Theory of Automata (CS-333)'),
(35, '210973@students.au.edu.pk', '658427158896', 1, '2024-07-18 21:06:44', '2024-07-18 21:06:44', 'AIR-BS-CS'),
(36, '210973@students.au.edu.pk', '661257652200', 1, '2024-07-18 21:06:47', '2024-07-18 21:06:47', 'Full Stack Web'),
(37, '210946@students.au.edu.pk', '663908690561', 1, '2024-07-19 19:32:57', '2024-07-19 19:32:57', 'CS-364L-BSCS-F21-BlockChain'),
(38, '210946@students.au.edu.pk', '663908752901', 1, '2024-07-19 19:32:59', '2024-07-19 19:32:59', 'CS-364-BSCS-F21-Blockchain'),
(39, '210946@students.au.edu.pk', '663160811255', 1, '2024-07-19 19:33:02', '2024-07-19 19:33:02', 'Full Stack Web Development Lab Spring 2024'),
(40, '210946@students.au.edu.pk', '662663097520', 1, '2024-07-19 19:33:04', '2024-07-19 19:33:04', 'FYP-01-Sp2024(Fall\'21Batch)'),
(42, 'working7816@gmail.com', '701184263629', 1, '2024-07-21 19:37:20', '2024-07-21 19:37:20', 'Fyp Testing');

-- --------------------------------------------------------

--
-- Table structure for table `notes_course`
--

CREATE TABLE `notes_course` (
  `id` int(11) NOT NULL,
  `page_title` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `datetime` varchar(255) NOT NULL,
  `userEmail` varchar(255) NOT NULL,
  `courseId` varchar(255) NOT NULL,
  `content` text NOT NULL DEFAULT '<h1>Note Taking...</h1> <p>&nbsp;</p> <p>Starter Template</p>',
  `courseType` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notes_course`
--

INSERT INTO `notes_course` (`id`, `page_title`, `type`, `datetime`, `userEmail`, `courseId`, `content`, `courseType`) VALUES
(8, 'PHP Fundaments', 'Notes', '2024-07-22 03:02:44', '210973@students.au.edu.pk', '10', '<h1>Note Taking...</h1> <p>&nbsp;</p> <p>Starter Template</p>', 'extraCourse'),
(9, 'Advisory Notes', 'Quiz', '2024-07-22 03:04:19', '210973@students.au.edu.pk', '676016607913', '<h1>Note Taking...</h1> <p>&nbsp;</p> <p>Starter Template</p>', 'uniCourse');

-- --------------------------------------------------------

--
-- Table structure for table `notes_project`
--

CREATE TABLE `notes_project` (
  `id` int(11) NOT NULL,
  `page_title` varchar(255) NOT NULL,
  `datetime` varchar(255) NOT NULL,
  `userEmail` varchar(255) NOT NULL,
  `project_id` varchar(255) NOT NULL,
  `content` text NOT NULL DEFAULT '<h1>Note Taking...</h1> <p>&nbsp;</p> <p>Starter Template</p>'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notes_project`
--

INSERT INTO `notes_project` (`id`, `page_title`, `datetime`, `userEmail`, `project_id`, `content`) VALUES
(1, 'Hehehe', '2024-07-22 01:06:15', '210973@students.au.edu.pk', '2', '<h1>Note Taking...</h1> <p>&nbsp;</p> <p>Starter Template</p>'),
(2, 'FYP notes', '2024-07-22 01:08:30', '210973@students.au.edu.pk', '2', '<p><span style=\"font-size: 18pt;\">Note 2 Details</span></p>\n<p>&nbsp;</p>\n<p><span style=\"font-size: 12pt;\">asdasd</span></p>');

-- --------------------------------------------------------

--
-- Table structure for table `notice`
--

CREATE TABLE `notice` (
  `id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `message` varchar(255) NOT NULL,
  `userEmail` varchar(255) NOT NULL,
  `datetime` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notice`
--

INSERT INTO `notice` (`id`, `type`, `message`, `userEmail`, `datetime`) VALUES
(5, 'Project', 'Muhammad Saad Accepted Your Project Request : PHP Basics', '210973@students.au.edu.pk', '2024-07-19 20:27:15'),
(6, 'Project', 'Saleem Talha Accepted Your Project Request : PHP Basics', '210973@students.au.edu.pk', '2024-07-19 21:00:12');

-- --------------------------------------------------------

--
-- Table structure for table `own_course`
--

CREATE TABLE `own_course` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `image` varchar(100) NOT NULL,
  `userEmail` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `own_course`
--

INSERT INTO `own_course` (`id`, `name`, `image`, `userEmail`) VALUES
(10, 'PHP Basics', 'course_669979d8744f82.76547806.jpg', '210973@students.au.edu.pk'),
(11, 'Testing Course', 'course_669ac02eb3e311.06922008.jpg', '210946@students.au.edu.pk'),
(13, 'FYP', 'course_669ad3331472c9.07307395.jpg', 'saleemtalha967@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `start_date` varchar(255) NOT NULL,
  `end_date` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `course_id` varchar(255) NOT NULL,
  `ownerEmail` varchar(255) NOT NULL,
  `courseType` varchar(255) NOT NULL,
  `project_file` varchar(255) NOT NULL,
  `readme` text NOT NULL DEFAULT '<h1>Read Me.</h1> <p>This is readme file where you can set the files</p>'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `name`, `start_date`, `end_date`, `status`, `course_id`, `ownerEmail`, `courseType`, `project_file`, `readme`) VALUES
(1, 'FYP', '2024-07-14', '2024-07-17', 'Completed', '662663097520', '210973@students.au.edu.pk', 'uniCourse', 'My Final Year Project.rar', '<h1>Read Me.</h1>\n<p>This is readme file where you can set the files</p>'),
(2, 'PHP BASICS', '2024-07-18', '', 'Active', '10', '210973@students.au.edu.pk', 'extraCourse', 'My Final Year Project.rar', '<h1>Read Me.</h1>\n<p>This is readme file where you can set the files</p>\n<p>&nbsp;</p>\n<p>HI THERE ITS ME TALHA</p>');

-- --------------------------------------------------------

--
-- Table structure for table `project_branch`
--

CREATE TABLE `project_branch` (
  `id` int(11) NOT NULL,
  `project_id` varchar(255) NOT NULL,
  `branch_file` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `branch_image` varchar(255) NOT NULL,
  `datetime` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_branch`
--

INSERT INTO `project_branch` (`id`, `project_id`, `branch_file`, `description`, `branch_image`, `datetime`) VALUES
(3, '2', '2_My Final Year Project.rar', '', 'https://lh3.googleusercontent.com/a/ACg8ocKULi_EdgFbjDtQ6Nv8qxGjq-gsrumsK5Hh7keTSQWnvVcmBWA=s96-c', '2024-07-20 02:49:56'),
(7, '2', '2_My Final Year Project.rar', 'Hehehehe', 'https://lh3.googleusercontent.com/a/ACg8ocJDwrwqaZFqeFqGIz3X53YuR54iI8vGNbVjONBtZUVd9qc4TFXg=s96-c', '2024-07-21 01:04:25');

-- --------------------------------------------------------

--
-- Table structure for table `project_notice`
--

CREATE TABLE `project_notice` (
  `id` int(11) NOT NULL,
  `project_id` varchar(255) NOT NULL,
  `notice` varchar(255) NOT NULL,
  `datetime` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_notice`
--

INSERT INTO `project_notice` (`id`, `project_id`, `notice`, `datetime`) VALUES
(6, '2', 'No Meeting Today', '2024-07-21 03:10:05'),
(7, '2', 'Check Branch 2 Again it is not completed ', '2024-07-21 03:10:31');

-- --------------------------------------------------------

--
-- Table structure for table `project_requests`
--

CREATE TABLE `project_requests` (
  `id` int(11) NOT NULL,
  `project_id` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `ownerEmail` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_requests`
--

INSERT INTO `project_requests` (`id`, `project_id`, `email`, `status`, `ownerEmail`) VALUES
(2, '2', '210946@students.au.edu.pk', 'Pending', '210973@students.au.edu.pk'),
(3, '2', 'saleemtalha967@gmail.com', 'Accepted', '210973@students.au.edu.pk');

-- --------------------------------------------------------

--
-- Table structure for table `project_tasks`
--

CREATE TABLE `project_tasks` (
  `id` int(11) NOT NULL,
  `project_id` varchar(255) NOT NULL,
  `userEmail` varchar(255) NOT NULL,
  `task` varchar(255) NOT NULL,
  `task_description` varchar(255) NOT NULL,
  `task_steps` varchar(255) NOT NULL,
  `deadline` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `completed_tasks` varchar(255) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_tasks`
--

INSERT INTO `project_tasks` (`id`, `project_id`, `userEmail`, `task`, `task_description`, `task_steps`, `deadline`, `status`, `completed_tasks`) VALUES
(1, '2', 'saleemtalha967@gmail.com', 'Enhance Login Page', 'User boxicons instead of font-awesome and also make sure you use google auth', '3', '2024-07-31', 'Not Completed', '3'),
(2, '2', 'saleemtalha967@gmail.com', 'Enhance the Register Page', 'Use boxicons in this page as well and must use google auth ', '10', '2024-07-31', 'Active', '3');

-- --------------------------------------------------------

--
-- Table structure for table `task_complete`
--

CREATE TABLE `task_complete` (
  `id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `task_id` int(11) DEFAULT NULL,
  `step_no` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task_complete`
--

INSERT INTO `task_complete` (`id`, `project_id`, `task_id`, `step_no`, `description`) VALUES
(1, 2, 2, 1, 'asdasd'),
(2, 2, 2, 2, 'I implemented the boxicons in the branch no 2'),
(3, 2, 1, 1, 'Done i added the box icons'),
(4, 2, 1, 2, 'Step 2 done '),
(5, 2, 1, 3, 'i added the google auth you can check in branch no 2'),
(6, 2, 2, 3, 'ASDASDASD');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `name`, `picture`, `created_at`) VALUES
(1, '210973@students.au.edu.pk', 'Muhammad Saleem Talha', 'https://lh3.googleusercontent.com/a/ACg8ocKULi_EdgFbjDtQ6Nv8qxGjq-gsrumsK5Hh7keTSQWnvVcmBWA=s96-c', '2024-07-16 16:35:21'),
(24, '210946@students.au.edu.pk', 'Muhammad Saad', 'https://lh3.googleusercontent.com/a/ACg8ocLe8J9fL7MCbqvSoHtkBInl1HklQzT5ObClRF65ef57DZNLO2c=s96-c', '2024-07-19 19:32:34'),
(28, 'saleemtalha967@gmail.com', 'Saleem Talha', 'https://lh3.googleusercontent.com/a/ACg8ocJDwrwqaZFqeFqGIz3X53YuR54iI8vGNbVjONBtZUVd9qc4TFXg=s96-c', '2024-07-19 20:56:22'),
(44, 'working7816@gmail.com', 'Mr Exception', 'https://lh3.googleusercontent.com/a/ACg8ocJhTliTbDDTmrHm6nWgIEIPBwCWLXdU1jZ4ZY-0AnvufqfrB-2n=s96-c', '2024-07-21 19:36:40');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `course_images`
--
ALTER TABLE `course_images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `course_status`
--
ALTER TABLE `course_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_course` (`user_id`,`course_id`);

--
-- Indexes for table `notes_course`
--
ALTER TABLE `notes_course`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notes_project`
--
ALTER TABLE `notes_project`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notice`
--
ALTER TABLE `notice`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `own_course`
--
ALTER TABLE `own_course`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `project_branch`
--
ALTER TABLE `project_branch`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `project_notice`
--
ALTER TABLE `project_notice`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `project_requests`
--
ALTER TABLE `project_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `project_tasks`
--
ALTER TABLE `project_tasks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `task_complete`
--
ALTER TABLE `task_complete`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `course_images`
--
ALTER TABLE `course_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `course_status`
--
ALTER TABLE `course_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `notes_course`
--
ALTER TABLE `notes_course`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `notes_project`
--
ALTER TABLE `notes_project`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `notice`
--
ALTER TABLE `notice`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `own_course`
--
ALTER TABLE `own_course`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `project_branch`
--
ALTER TABLE `project_branch`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `project_notice`
--
ALTER TABLE `project_notice`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `project_requests`
--
ALTER TABLE `project_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `project_tasks`
--
ALTER TABLE `project_tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `task_complete`
--
ALTER TABLE `task_complete`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
