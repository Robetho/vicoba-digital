-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 08, 2025 at 10:33 PM
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
-- Database: `vicoba_management_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `borrowers`
--

CREATE TABLE `borrowers` (
  `id` int(30) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `middlename` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `username` text DEFAULT NULL,
  `password` text DEFAULT NULL,
  `contact_no` varchar(30) NOT NULL,
  `address` text NOT NULL,
  `email` varchar(50) NOT NULL,
  `tax_id` varchar(50) NOT NULL,
  `date_created` int(11) NOT NULL,
  `type` enum('2') NOT NULL DEFAULT '2'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Table structure for table `bulk_messages`
--

CREATE TABLE `bulk_messages` (
  `id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message_content` text NOT NULL,
  `date_sent` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Table structure for table `loan_list`
--

CREATE TABLE `loan_list` (
  `id` int(30) NOT NULL,
  `ref_no` varchar(50) NOT NULL,
  `loan_type_id` int(30) NOT NULL,
  `borrower_id` int(30) NOT NULL,
  `purpose` text NOT NULL,
  `amount` double NOT NULL,
  `plan_id` int(30) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0= request, 1= confrimed,2=released,3=complteted,4=denied\r\n',
  `date_released` datetime NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------

--
-- Table structure for table `loan_plan`
--

CREATE TABLE `loan_plan` (
  `id` int(30) NOT NULL,
  `months` int(11) NOT NULL,
  `interest_percentage` float NOT NULL,
  `penalty_rate` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_plan`
--

INSERT INTO `loan_plan` (`id`, `months`, `interest_percentage`, `penalty_rate`) VALUES
(1, 36, 8, 3),
(2, 24, 5, 2),
(3, 27, 6, 2);

-- --------------------------------------------------------

--
-- Table structure for table `loan_schedules`
--

CREATE TABLE `loan_schedules` (
  `id` int(30) NOT NULL,
  `loan_id` int(30) NOT NULL,
  `date_due` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_schedules`
--

INSERT INTO `loan_schedules` (`id`, `loan_id`, `date_due`) VALUES
(38, 5, '2025-06-17'),
(39, 5, '2025-07-17'),
(40, 5, '2025-08-17'),
(41, 5, '2025-09-17'),
(42, 5, '2025-10-17'),
(43, 5, '2025-11-17'),
(44, 5, '2025-12-17'),
(45, 5, '2026-01-17'),
(46, 5, '2026-02-17'),
(47, 5, '2026-03-17'),
(48, 5, '2026-04-17'),
(49, 5, '2026-05-17'),
(50, 5, '2026-06-17'),
(51, 5, '2026-07-17'),
(52, 5, '2026-08-17'),
(53, 5, '2026-09-17'),
(54, 5, '2026-10-17'),
(55, 5, '2026-11-17'),
(56, 5, '2026-12-17'),
(57, 5, '2027-01-17'),
(58, 5, '2027-02-17'),
(59, 5, '2027-03-17'),
(60, 5, '2027-04-17'),
(61, 5, '2027-05-17'),
(62, 5, '2027-06-17'),
(63, 5, '2027-07-17'),
(64, 5, '2027-08-17'),
(65, 4, '2025-06-17'),
(66, 4, '2025-07-17'),
(67, 4, '2025-08-17'),
(68, 4, '2025-09-17'),
(69, 4, '2025-10-17'),
(70, 4, '2025-11-17'),
(71, 4, '2025-12-17'),
(72, 4, '2026-01-17'),
(73, 4, '2026-02-17'),
(74, 4, '2026-03-17'),
(75, 4, '2026-04-17'),
(76, 4, '2026-05-17'),
(77, 4, '2026-06-17'),
(78, 4, '2026-07-17'),
(79, 4, '2026-08-17'),
(80, 4, '2026-09-17'),
(81, 4, '2026-10-17'),
(82, 4, '2026-11-17'),
(83, 4, '2026-12-17'),
(84, 4, '2027-01-17'),
(85, 4, '2027-02-17'),
(86, 4, '2027-03-17'),
(87, 4, '2027-04-17'),
(88, 4, '2027-05-17'),
(101, 1, '2025-06-17'),
(102, 1, '2025-07-17'),
(103, 1, '2025-08-17'),
(104, 1, '2025-09-17'),
(105, 1, '2025-10-17'),
(106, 1, '2025-11-17'),
(107, 1, '2025-12-17'),
(108, 1, '2026-01-17'),
(109, 1, '2026-02-17'),
(110, 1, '2026-03-17'),
(111, 1, '2026-04-17'),
(112, 1, '2026-05-17'),
(113, 1, '2026-06-17'),
(114, 1, '2026-07-17'),
(115, 1, '2026-08-17'),
(116, 1, '2026-09-17'),
(117, 1, '2026-10-17'),
(118, 1, '2026-11-17'),
(119, 1, '2026-12-17'),
(120, 1, '2027-01-17'),
(121, 1, '2027-02-17'),
(122, 1, '2027-03-17'),
(123, 1, '2027-04-17'),
(124, 1, '2027-05-17'),
(125, 1, '2027-06-17'),
(126, 1, '2027-07-17'),
(127, 1, '2027-08-17'),
(128, 1, '2027-09-17'),
(129, 1, '2027-10-17'),
(130, 1, '2027-11-17'),
(131, 1, '2027-12-17'),
(132, 1, '2028-01-17'),
(133, 1, '2028-02-17'),
(134, 1, '2028-03-17'),
(135, 1, '2028-04-17'),
(136, 1, '2028-05-17'),
(137, 3, '2025-06-17'),
(138, 3, '2025-07-17'),
(139, 3, '2025-08-17'),
(140, 3, '2025-09-17'),
(141, 3, '2025-10-17'),
(142, 3, '2025-11-17'),
(143, 3, '2025-12-17'),
(144, 3, '2026-01-17'),
(145, 3, '2026-02-17'),
(146, 3, '2026-03-17'),
(147, 3, '2026-04-17'),
(148, 3, '2026-05-17'),
(149, 3, '2026-06-17'),
(150, 3, '2026-07-17'),
(151, 3, '2026-08-17'),
(152, 3, '2026-09-17'),
(153, 3, '2026-10-17'),
(154, 3, '2026-11-17'),
(155, 3, '2026-12-17'),
(156, 3, '2027-01-17'),
(157, 3, '2027-02-17'),
(158, 3, '2027-03-17'),
(159, 3, '2027-04-17'),
(160, 3, '2027-05-17'),
(161, 6, '2025-06-17'),
(162, 6, '2025-07-17'),
(163, 6, '2025-08-17'),
(164, 6, '2025-09-17'),
(165, 6, '2025-10-17'),
(166, 6, '2025-11-17'),
(167, 6, '2025-12-17'),
(168, 6, '2026-01-17'),
(169, 6, '2026-02-17'),
(170, 6, '2026-03-17'),
(171, 6, '2026-04-17'),
(172, 6, '2026-05-17'),
(173, 6, '2026-06-17'),
(174, 6, '2026-07-17'),
(175, 6, '2026-08-17'),
(176, 6, '2026-09-17'),
(177, 6, '2026-10-17'),
(178, 6, '2026-11-17'),
(179, 6, '2026-12-17'),
(180, 6, '2027-01-17'),
(181, 6, '2027-02-17'),
(182, 6, '2027-03-17'),
(183, 6, '2027-04-17'),
(184, 6, '2027-05-17'),
(185, 7, '2025-06-17'),
(186, 7, '2025-07-17'),
(187, 7, '2025-08-17'),
(188, 7, '2025-09-17'),
(189, 7, '2025-10-17'),
(190, 7, '2025-11-17'),
(191, 7, '2025-12-17'),
(192, 7, '2026-01-17'),
(193, 7, '2026-02-17'),
(194, 7, '2026-03-17'),
(195, 7, '2026-04-17'),
(196, 7, '2026-05-17'),
(197, 7, '2026-06-17'),
(198, 7, '2026-07-17'),
(199, 7, '2026-08-17'),
(200, 7, '2026-09-17'),
(201, 7, '2026-10-17'),
(202, 7, '2026-11-17'),
(203, 7, '2026-12-17'),
(204, 7, '2027-01-17'),
(205, 7, '2027-02-17'),
(206, 7, '2027-03-17'),
(207, 7, '2027-04-17'),
(208, 7, '2027-05-17'),
(233, 11, '2025-08-07'),
(234, 11, '2025-09-07'),
(235, 11, '2025-10-07'),
(236, 11, '2025-11-07'),
(237, 11, '2025-12-07'),
(238, 11, '2026-01-07'),
(239, 11, '2026-02-07'),
(240, 11, '2026-03-07'),
(241, 11, '2026-04-07'),
(242, 11, '2026-05-07'),
(243, 11, '2026-06-07'),
(244, 11, '2026-07-07'),
(245, 11, '2026-08-07'),
(246, 11, '2026-09-07'),
(247, 11, '2026-10-07'),
(248, 11, '2026-11-07'),
(249, 11, '2026-12-07'),
(250, 11, '2027-01-07'),
(251, 11, '2027-02-07'),
(252, 11, '2027-03-07'),
(253, 11, '2027-04-07'),
(254, 11, '2027-05-07'),
(255, 11, '2027-06-07'),
(256, 11, '2027-07-07'),
(257, 11, '2027-08-07'),
(258, 11, '2027-09-07'),
(259, 11, '2027-10-07'),
(260, 11, '2027-11-07'),
(261, 11, '2027-12-07'),
(262, 11, '2028-01-07'),
(263, 11, '2028-02-07'),
(264, 11, '2028-03-07'),
(265, 11, '2028-04-07'),
(266, 11, '2028-05-07'),
(267, 11, '2028-06-07'),
(268, 11, '2028-07-07'),
(269, 10, '2025-08-08'),
(270, 10, '2025-09-08'),
(271, 10, '2025-10-08'),
(272, 10, '2025-11-08'),
(273, 10, '2025-12-08'),
(274, 10, '2026-01-08'),
(275, 10, '2026-02-08'),
(276, 10, '2026-03-08'),
(277, 10, '2026-04-08'),
(278, 10, '2026-05-08'),
(279, 10, '2026-06-08'),
(280, 10, '2026-07-08'),
(281, 10, '2026-08-08'),
(282, 10, '2026-09-08'),
(283, 10, '2026-10-08'),
(284, 10, '2026-11-08'),
(285, 10, '2026-12-08'),
(286, 10, '2027-01-08'),
(287, 10, '2027-02-08'),
(288, 10, '2027-03-08'),
(289, 10, '2027-04-08'),
(290, 10, '2027-05-08'),
(291, 10, '2027-06-08'),
(292, 10, '2027-07-08'),
(293, 10, '2027-08-08'),
(294, 10, '2027-09-08'),
(295, 10, '2027-10-08');

-- --------------------------------------------------------

--
-- Table structure for table `loan_types`
--

CREATE TABLE `loan_types` (
  `id` int(30) NOT NULL,
  `type_name` text NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_types`
--

INSERT INTO `loan_types` (`id`, `type_name`, `description`) VALUES
(1, 'Small Business', 'Small Business Loans'),
(2, 'Mortgages', 'Mortgages'),
(3, 'Personal Loans', 'Personal Loans');

-- --------------------------------------------------------

--
-- Table structure for table `message_views`
--

CREATE TABLE `message_views` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `borrower_id` int(11) NOT NULL,
  `viewed_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(30) NOT NULL,
  `loan_id` int(30) NOT NULL,
  `payee` text NOT NULL,
  `amount` float NOT NULL DEFAULT 0,
  `penalty_amount` float NOT NULL DEFAULT 0,
  `overdue` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=no , 1 = yes',
  `date_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `savings`
--

CREATE TABLE `savings` (
  `id` int(11) NOT NULL,
  `borrower_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `savings_date` date DEFAULT curdate(),
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(30) NOT NULL,
  `doctor_id` int(30) NOT NULL,
  `name` varchar(200) NOT NULL,
  `address` text NOT NULL,
  `contact` text NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(200) NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT 2 COMMENT '1=admin , 2 = staff'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `doctor_id`, `name`, `address`, `contact`, `username`, `password`, `type`) VALUES
(1, 0, 'Administrator', '', '', 'admin', 'admin123', 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_messages`
--

CREATE TABLE `user_messages` (
  `id` int(11) NOT NULL,
  `borrower_id` int(30) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message_content` text NOT NULL,
  `date_sent` datetime NOT NULL DEFAULT current_timestamp(),
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=unread, 1=read'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Indexes for dumped tables
--

--
-- Indexes for table `borrowers`
--
ALTER TABLE `borrowers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bulk_messages`
--
ALTER TABLE `bulk_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `loan_list`
--
ALTER TABLE `loan_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `loan_plan`
--
ALTER TABLE `loan_plan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `loan_schedules`
--
ALTER TABLE `loan_schedules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `loan_types`
--
ALTER TABLE `loan_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `message_views`
--
ALTER TABLE `message_views`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `message_id` (`message_id`,`borrower_id`),
  ADD KEY `borrower_id` (`borrower_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `savings`
--
ALTER TABLE `savings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `borrower_id` (`borrower_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_messages`
--
ALTER TABLE `user_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `borrower_id` (`borrower_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `borrowers`
--
ALTER TABLE `borrowers`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `bulk_messages`
--
ALTER TABLE `bulk_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loan_list`
--
ALTER TABLE `loan_list`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loan_plan`
--
ALTER TABLE `loan_plan`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `loan_schedules`
--
ALTER TABLE `loan_schedules`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=296;

--
-- AUTO_INCREMENT for table `loan_types`
--
ALTER TABLE `loan_types`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `message_views`
--
ALTER TABLE `message_views`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `savings`
--
ALTER TABLE `savings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_messages`
--
ALTER TABLE `user_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `message_views`
--
ALTER TABLE `message_views`
  ADD CONSTRAINT `message_views_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `bulk_messages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `message_views_ibfk_2` FOREIGN KEY (`borrower_id`) REFERENCES `borrowers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `savings`
--
ALTER TABLE `savings`
  ADD CONSTRAINT `savings_ibfk_1` FOREIGN KEY (`borrower_id`) REFERENCES `borrowers` (`id`);

--
-- Constraints for table `user_messages`
--
ALTER TABLE `user_messages`
  ADD CONSTRAINT `fk_borrower_id_messages` FOREIGN KEY (`borrower_id`) REFERENCES `borrowers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
