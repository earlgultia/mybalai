-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 29, 2026 at 04:05 AM
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
-- Database: `mybalai_db`
--

-- --------------------------------------------------------

--
-- Stand-in structure for view `active_users_with_roles`
-- (See below for the actual view)
--
CREATE TABLE `active_users_with_roles` (
`user_id` int(11)
,`username` varchar(50)
,`email` varchar(255)
,`first_name` varchar(100)
,`last_name` varchar(100)
,`phone_number` varchar(20)
,`is_active` tinyint(1)
,`last_login` timestamp
,`primary_role` varchar(50)
,`all_roles` mediumtext
);

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `entity_type` varchar(100) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`log_id`, `user_id`, `action`, `entity_type`, `entity_id`, `ip_address`, `user_agent`, `details`, `created_at`) VALUES
(1, NULL, 'User logged in', 'auth', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 04:44:26'),
(2, NULL, 'User logged out', 'auth', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 04:44:30'),
(3, 1, 'User logged in', 'auth', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 04:44:48'),
(4, 1, 'User logged out', 'auth', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 04:45:20'),
(5, 1, 'User logged in', 'auth', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 04:45:26'),
(6, 1, 'User logged out', 'auth', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 04:53:10'),
(7, NULL, 'User logged in', 'auth', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 04:53:23'),
(8, NULL, 'User logged out', 'auth', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 04:59:57'),
(9, 1, 'User logged in', 'auth', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 05:00:06'),
(10, 1, 'Deleted Barangay Captain account', 'users', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Juan Dela Cruz', '2026-05-26 05:14:03'),
(11, 1, 'User logged out', 'auth', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 05:14:16'),
(12, 1, 'User logged in', 'auth', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 05:14:30'),
(13, 1, 'User logged out', 'auth', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 05:15:16'),
(14, NULL, 'User logged in', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 05:15:26'),
(15, NULL, 'User logged out', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 05:15:38'),
(16, 1, 'User logged in', 'auth', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 05:15:45'),
(17, 1, 'Created staff account', 'users', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'barangay_captain', '2026-05-26 05:17:00'),
(18, 1, 'User logged out', 'auth', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 05:17:07'),
(19, NULL, 'User logged in', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 05:17:11'),
(20, NULL, 'User logged out', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 05:18:52'),
(21, NULL, 'User logged in', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 05:19:12'),
(22, NULL, 'User logged out', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 05:29:12'),
(23, NULL, 'User logged in', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 05:29:29'),
(24, NULL, 'User logged out', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 05:32:08'),
(25, NULL, 'User logged in', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 05:33:29'),
(26, NULL, 'User logged out', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 05:33:33'),
(27, NULL, 'Resident account registered', 'users', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 07:26:31'),
(28, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 07:26:45'),
(29, NULL, 'Updated profile', 'users', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 07:27:57'),
(30, NULL, 'Generated resident QR ID', 'resident_profiles', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 07:43:21'),
(31, NULL, 'Submitted document request', 'document_requests', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 07:44:35'),
(32, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 07:44:50'),
(33, NULL, 'User logged in', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 07:44:55'),
(34, NULL, 'User logged out', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 07:45:15'),
(35, NULL, 'User logged in', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 07:45:19'),
(36, NULL, 'Created staff account', 'users', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'barangay_secretary', '2026-05-26 07:46:18'),
(37, NULL, 'User logged out', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 07:46:26'),
(38, NULL, 'User logged in', 'auth', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 07:46:29'),
(39, NULL, 'User logged out', 'auth', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 07:49:41'),
(40, NULL, 'User logged in', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 07:50:01'),
(41, NULL, 'User logged out', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 07:50:20'),
(42, NULL, 'User logged in', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 07:50:40'),
(43, NULL, 'User logged out', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 08:52:37'),
(44, NULL, 'User logged in', 'auth', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 08:52:42'),
(45, NULL, 'Updated document request', 'document_requests', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 08:54:00'),
(46, NULL, 'User logged out', 'auth', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 08:54:06'),
(47, NULL, 'User logged in', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 08:54:14'),
(48, NULL, 'Recorded document payment', 'document_requests', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Earl Gultia - DOC-20260526-9417', '2026-05-26 08:54:38'),
(49, NULL, 'User logged out', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 08:54:59'),
(50, NULL, 'User logged in', 'auth', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 08:55:06'),
(51, NULL, 'Updated document request', 'document_requests', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 08:55:13'),
(52, NULL, 'Updated document request', 'document_requests', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 08:55:25'),
(53, NULL, 'User logged out', 'auth', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 08:55:33'),
(54, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 08:56:53'),
(55, NULL, 'Filed complaint', 'complaints', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 08:58:40'),
(56, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 08:59:34'),
(57, NULL, 'User logged in', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 08:59:40'),
(58, NULL, 'User logged out', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 09:00:23'),
(59, NULL, 'User logged in', 'auth', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 09:00:54'),
(60, NULL, 'User logged out', 'auth', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 09:01:00'),
(61, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 09:01:05'),
(62, NULL, 'Submitted document request', 'document_requests', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 09:01:17'),
(63, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 09:01:21'),
(64, NULL, 'User logged in', 'auth', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 09:01:25'),
(65, NULL, 'Updated document request', 'document_requests', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 09:01:30'),
(66, NULL, 'User logged out', 'auth', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 09:01:34'),
(67, NULL, 'User logged in', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 09:01:42'),
(68, NULL, 'Recorded document payment', 'document_requests', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Earl Gultia - DOC-20260526-1788', '2026-05-26 09:02:04'),
(69, NULL, 'User logged out', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 09:02:15'),
(70, NULL, 'User logged in', 'auth', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 09:02:18'),
(71, NULL, 'Updated document request', 'document_requests', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 09:02:29'),
(72, NULL, 'Updated document request', 'document_requests', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 09:02:32'),
(73, NULL, 'User logged out', 'auth', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 09:02:37'),
(74, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 09:02:42'),
(75, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 09:05:08'),
(76, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 09:05:48'),
(77, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 09:06:08'),
(78, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 09:12:56'),
(79, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 09:13:29'),
(80, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 09:16:28'),
(81, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 09:16:33'),
(82, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 09:16:51'),
(83, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 09:25:27'),
(84, NULL, 'User logged in', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 09:25:32'),
(85, NULL, 'User logged out', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 09:26:10'),
(86, NULL, 'User logged in', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 09:26:19'),
(87, NULL, 'User logged out', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 09:30:55'),
(88, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 22:15:11'),
(89, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 22:15:27'),
(90, NULL, 'User logged in', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 22:15:31'),
(91, NULL, 'User logged out', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 22:17:57'),
(92, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 22:18:01'),
(93, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 22:21:23'),
(94, NULL, 'User logged in', 'auth', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 22:21:33'),
(95, NULL, 'User logged out', 'auth', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 22:21:48'),
(96, NULL, 'User logged in', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 22:43:14'),
(97, NULL, 'User logged out', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 22:49:50'),
(98, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-26 23:04:13'),
(99, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-27 00:49:47'),
(100, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-27 01:53:56'),
(101, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-27 01:54:19'),
(102, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-27 01:55:50'),
(103, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-27 02:12:00'),
(104, NULL, 'User logged in', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-27 02:12:03'),
(105, NULL, 'User logged out', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-27 02:32:35'),
(106, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-27 02:32:38'),
(107, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-27 02:56:07'),
(108, NULL, 'User logged in', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-27 02:56:12'),
(109, NULL, 'User logged out', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-27 03:02:41'),
(110, NULL, 'User logged in', 'auth', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-27 03:02:45'),
(111, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-27 10:58:32'),
(112, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-27 11:02:43'),
(113, NULL, 'User logged in', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-27 11:02:50'),
(114, NULL, 'User logged out', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-27 12:10:14'),
(115, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-27 12:10:20'),
(116, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-27 12:19:52'),
(117, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-27 22:33:00'),
(118, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-27 22:33:10'),
(119, NULL, 'User logged in', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-27 22:33:16'),
(120, NULL, 'User logged out', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-27 23:32:54'),
(121, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-27 23:33:00'),
(122, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-27 23:36:17'),
(123, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-27 23:36:31'),
(124, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-27 23:36:37'),
(125, NULL, 'User logged in', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-27 23:36:44'),
(126, NULL, 'User logged out', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 00:38:01'),
(127, NULL, 'User logged in', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 06:43:18'),
(128, NULL, 'User logged out', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 07:16:34'),
(129, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 07:17:24'),
(130, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 07:41:53'),
(131, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:29:44'),
(132, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:30:43'),
(133, NULL, 'User logged in', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:31:16'),
(134, NULL, 'User logged out', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:32:00'),
(135, 1, 'User logged in', 'auth', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:32:13'),
(136, 1, 'User logged out', 'auth', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:32:43'),
(137, NULL, 'User logged in', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:32:49'),
(138, NULL, 'User logged out', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:35:05'),
(139, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:35:09'),
(140, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:35:42'),
(141, NULL, 'User logged in', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:35:48'),
(142, NULL, 'User logged out', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:36:19'),
(143, NULL, 'User logged in', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:36:24'),
(144, NULL, 'User logged out', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:36:29'),
(145, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:36:34'),
(146, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:42:15'),
(147, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:42:19'),
(148, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:44:20'),
(149, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:44:22'),
(150, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:44:59'),
(151, NULL, 'User logged in', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:45:02'),
(152, NULL, 'User logged out', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:45:33'),
(153, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:45:36'),
(154, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:46:05'),
(155, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:47:00'),
(156, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:47:49'),
(157, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:48:39'),
(158, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:51:07'),
(159, NULL, 'User logged in', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:51:13'),
(160, NULL, 'User logged out', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:51:50'),
(161, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:51:53'),
(162, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:53:52'),
(163, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:53:54'),
(164, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:55:43'),
(165, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:55:45'),
(166, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:56:12'),
(167, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:57:46'),
(168, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 22:58:46'),
(169, NULL, 'User logged in', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 23:01:41'),
(170, NULL, 'User logged out', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 23:01:54'),
(171, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 23:03:09'),
(172, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 23:04:57'),
(173, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 23:07:54'),
(174, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 23:09:47'),
(175, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 23:12:12'),
(176, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 23:12:20'),
(177, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 23:15:12'),
(178, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 23:16:52'),
(179, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 23:17:25'),
(180, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 23:19:27'),
(181, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 23:19:29'),
(182, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 23:56:12'),
(183, NULL, 'User logged in', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 23:56:16'),
(184, NULL, 'Announcement deleted', 'announcement', 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'DISCO', '2026-05-28 23:57:48'),
(185, NULL, 'Announcement deleted', 'announcement', 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'DISCO', '2026-05-28 23:57:55'),
(186, NULL, 'Announcement deleted', 'announcement', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'PARADA', '2026-05-28 23:59:03'),
(187, NULL, 'Announcement deleted', 'announcement', 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'DISCO', '2026-05-28 23:59:06'),
(188, NULL, 'User logged out', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 23:59:26'),
(189, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 23:59:32'),
(190, NULL, 'Submitted document request', 'document_requests', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 23:59:45'),
(191, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-28 23:59:49'),
(192, NULL, 'User logged in', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:00:01'),
(193, NULL, 'User logged out', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:00:11'),
(194, NULL, 'User logged in', 'auth', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:00:16'),
(195, NULL, 'Updated document request', 'document_requests', 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:00:35'),
(196, NULL, 'User logged out', 'auth', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:00:40'),
(197, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:00:43'),
(198, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:01:15'),
(199, NULL, 'User logged in', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:01:25'),
(200, NULL, 'User logged out', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:06:06'),
(201, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:06:11'),
(202, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:18:53'),
(203, NULL, 'User logged in', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:19:02'),
(204, NULL, 'User logged out', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:19:21'),
(205, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:19:26'),
(206, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:19:56'),
(207, NULL, 'User logged in', 'auth', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:20:05'),
(208, NULL, 'Updated document request', 'document_requests', 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:20:16'),
(209, NULL, 'User logged out', 'auth', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:20:21'),
(210, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:20:25'),
(211, NULL, 'Submitted document request', 'document_requests', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:20:48'),
(212, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:20:57'),
(213, NULL, 'User logged in', 'auth', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:21:00'),
(214, NULL, 'Updated document request', 'document_requests', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:21:07'),
(215, NULL, 'User logged out', 'auth', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:21:11'),
(216, NULL, 'User logged in', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:21:17'),
(217, NULL, 'User logged out', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:25:14'),
(218, NULL, 'User logged in', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:25:17'),
(219, NULL, 'User logged out', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:28:01'),
(220, NULL, 'User logged in', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:28:15'),
(221, NULL, 'User logged out', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:28:31'),
(222, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:28:36'),
(223, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:29:00'),
(224, NULL, 'User logged in', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:29:06'),
(225, NULL, 'User logged out', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:29:27'),
(226, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:29:30'),
(227, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:29:38'),
(228, NULL, 'User logged in', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:29:44'),
(229, NULL, 'Recorded document payment', 'document_requests', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Earl Gultia - DOC-20260529-6599', '2026-05-29 00:30:02'),
(230, NULL, 'User logged out', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:30:07'),
(231, NULL, 'User logged in', 'auth', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:30:11'),
(232, NULL, 'Updated document request', 'document_requests', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:30:24'),
(233, NULL, 'Updated document request', 'document_requests', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:30:26'),
(234, NULL, 'User logged out', 'auth', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:30:30'),
(235, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:30:33'),
(236, NULL, 'Submitted document request', 'document_requests', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:31:03'),
(237, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:31:10'),
(238, NULL, 'User logged in', 'auth', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:31:14'),
(239, NULL, 'Updated document request', 'document_requests', 5, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:31:19'),
(240, NULL, 'User logged out', 'auth', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:31:23'),
(241, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:31:29'),
(242, NULL, 'Submitted payment proof', 'document_requests', 5, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'uploads/payment_proofs/proof_5_1780014700_35c53ca9.png', '2026-05-29 00:31:40'),
(243, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:31:46'),
(244, NULL, 'User logged in', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:31:52'),
(245, NULL, 'Verified payment proof', 'document_requests', 5, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'DOC-20260529-7813', '2026-05-29 00:32:01'),
(246, NULL, 'User logged out', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:32:43'),
(247, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:32:56'),
(248, NULL, 'Submitted document request', 'document_requests', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:33:09'),
(249, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:33:12'),
(250, NULL, 'User logged in', 'auth', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:33:16'),
(251, NULL, 'Updated document request', 'document_requests', 6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:33:20'),
(252, NULL, 'User logged out', 'auth', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:33:24'),
(253, NULL, 'User logged in', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:33:30'),
(254, NULL, 'User logged out', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:33:45'),
(255, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:33:48');
INSERT INTO `activity_logs` (`log_id`, `user_id`, `action`, `entity_type`, `entity_id`, `ip_address`, `user_agent`, `details`, `created_at`) VALUES
(256, NULL, 'Submitted payment proof', 'document_requests', 6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'uploads/payment_proofs/proof_6_1780014836_1f7ea589.png', '2026-05-29 00:33:56'),
(257, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:34:00'),
(258, NULL, 'User logged in', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:34:08'),
(259, NULL, 'Verified payment proof', 'document_requests', 6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'DOC-20260529-5048', '2026-05-29 00:37:25'),
(260, NULL, 'User logged out', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:41:44'),
(261, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:41:47'),
(262, NULL, 'Submitted document request', 'document_requests', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:42:00'),
(263, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:42:05'),
(264, NULL, 'User logged in', 'auth', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:42:09'),
(265, NULL, 'Updated document request', 'document_requests', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:42:20'),
(266, NULL, 'User logged out', 'auth', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:42:24'),
(267, NULL, 'User logged in', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:42:29'),
(268, NULL, 'User logged out', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:42:42'),
(269, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:42:45'),
(270, NULL, 'Submitted payment proof', 'document_requests', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'uploads/payment_proofs/proof_7_1780015374_30c68338.png', '2026-05-29 00:42:54'),
(271, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:42:59'),
(272, NULL, 'User logged in', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:43:08'),
(273, NULL, 'Verified payment proof', 'document_requests', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'DOC-20260529-7492', '2026-05-29 00:43:18'),
(274, NULL, 'User logged out', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:44:28'),
(275, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:44:32'),
(276, NULL, 'Submitted document request', 'document_requests', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:50:58'),
(277, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:51:02'),
(278, NULL, 'User logged in', 'auth', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:51:06'),
(279, NULL, 'Updated document request', 'document_requests', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:51:10'),
(280, NULL, 'User logged out', 'auth', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:51:14'),
(281, NULL, 'User logged in', 'auth', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:51:41'),
(282, NULL, 'User logged out', 'auth', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:51:45'),
(283, NULL, 'User logged in', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:51:50'),
(284, NULL, 'User logged out', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:52:11'),
(285, NULL, 'User logged in', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:52:14'),
(286, NULL, 'Submitted payment proof', 'document_requests', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'uploads/payment_proofs/proof_8_1780015951_22d341b8.png', '2026-05-29 00:52:31'),
(287, NULL, 'User logged out', 'auth', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:52:34'),
(288, NULL, 'User logged in', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:52:41'),
(289, NULL, 'Verified payment proof', 'document_requests', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'DOC-20260529-3292', '2026-05-29 00:53:29'),
(290, NULL, 'User logged out', 'auth', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:53:52'),
(291, 1, 'User logged in', 'auth', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:54:25'),
(292, 1, 'User logged out', 'auth', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:55:10'),
(293, NULL, 'User logged in', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:55:14'),
(294, NULL, 'Deleted Barangay Secretary account', 'users', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Celine Dion', '2026-05-29 00:55:31'),
(295, NULL, 'User logged out', 'auth', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:56:04'),
(296, 1, 'User logged in', 'auth', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:56:25'),
(297, 1, 'Deleted Barangay Captain account', 'users', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Jigy Jamago', '2026-05-29 00:56:35'),
(298, 1, 'User logged out', 'auth', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:57:23'),
(299, 1, 'User logged in', 'auth', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:57:26'),
(300, 1, 'User logged out', 'auth', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:57:32'),
(301, 1, 'User logged in', 'auth', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:58:08'),
(302, 1, 'Created staff account', 'users', 10, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'barangay_captain', '2026-05-29 00:59:43'),
(303, 1, 'User logged out', 'auth', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:59:49'),
(304, 10, 'User logged in', 'auth', 10, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 00:59:54'),
(305, 10, 'Deleted resident', 'users', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:00:03'),
(306, 10, 'Created staff account', 'users', 11, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'barangay_secretary', '2026-05-29 01:06:48'),
(307, 10, 'Created staff account', 'users', 12, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'barangay_treasurer', '2026-05-29 01:08:00'),
(308, 10, 'Announcement deleted', 'announcement', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'ASSEMBLY MEETING', '2026-05-29 01:08:11'),
(309, 10, 'User logged out', 'auth', 10, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:08:16'),
(310, 13, 'Resident account registered', 'users', 13, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:13:28'),
(311, 13, 'User logged in', 'auth', 13, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:13:35'),
(312, 13, 'User logged out', 'auth', 13, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:13:49'),
(313, 12, 'User logged in', 'auth', 12, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:13:53'),
(314, 12, 'User logged out', 'auth', 12, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:14:03'),
(315, 13, 'User logged in', 'auth', 13, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:14:08'),
(316, 13, 'Submitted document request', 'document_requests', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:14:40'),
(317, 13, 'User logged out', 'auth', 13, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:14:48'),
(318, 11, 'User logged in', 'auth', 11, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:14:54'),
(319, 11, 'Updated document request', 'document_requests', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:15:00'),
(320, 11, 'User logged out', 'auth', 11, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:15:04'),
(321, 12, 'User logged in', 'auth', 12, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:15:06'),
(322, 12, 'Recorded document payment', 'document_requests', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Earl Gultia - DOC-20260529-5038', '2026-05-29 01:15:27'),
(323, 12, 'User logged out', 'auth', 12, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:15:43'),
(324, 13, 'User logged in', 'auth', 13, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:15:50'),
(325, 13, 'User logged out', 'auth', 13, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:16:23'),
(326, 12, 'User logged in', 'auth', 12, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:16:27'),
(327, 12, 'User logged out', 'auth', 12, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:32:49'),
(328, 13, 'User logged in', 'auth', 13, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:32:53'),
(329, 13, 'Submitted document request', 'document_requests', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:33:04'),
(330, 13, 'Submitted document request', 'document_requests', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:33:12'),
(331, 13, 'Submitted document request', 'document_requests', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:33:28'),
(332, 13, 'Submitted document request', 'document_requests', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:33:37'),
(333, 13, 'User logged out', 'auth', 13, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:33:40'),
(334, 13, 'User logged in', 'auth', 13, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:33:44'),
(335, 13, 'User logged out', 'auth', 13, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:33:47'),
(336, 11, 'User logged in', 'auth', 11, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:33:53'),
(337, 11, 'Updated document request', 'document_requests', 13, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:34:05'),
(338, 11, 'User logged out', 'auth', 11, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:34:09'),
(339, 12, 'User logged in', 'auth', 12, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:34:13'),
(340, 12, 'Recorded document payment', 'document_requests', 13, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Earl Gultia - DOC-20260529-3399', '2026-05-29 01:34:34'),
(341, 12, 'User logged out', 'auth', 12, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:34:45'),
(342, 11, 'User logged in', 'auth', 11, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:34:52'),
(343, 11, 'User logged out', 'auth', 11, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:35:26'),
(344, 13, 'User logged in', 'auth', 13, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:35:30'),
(345, 13, 'Submitted document request', 'document_requests', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:35:42'),
(346, 13, 'User logged out', 'auth', 13, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:35:46'),
(347, 11, 'User logged in', 'auth', 11, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:35:50'),
(348, 11, 'Updated document request', 'document_requests', 14, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:36:30'),
(349, 11, 'User logged out', 'auth', 11, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:36:34'),
(350, 12, 'User logged in', 'auth', 12, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:36:38'),
(351, 12, 'Recorded document payment', 'document_requests', 14, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Earl Gultia - DOC-20260529-1974', '2026-05-29 01:36:55'),
(352, 12, 'User logged out', 'auth', 12, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:37:14'),
(353, 13, 'User logged in', 'auth', 13, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:37:18'),
(354, 13, 'Submitted document request', 'document_requests', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:37:30'),
(355, 13, 'User logged out', 'auth', 13, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:37:33'),
(356, 12, 'User logged in', 'auth', 12, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:37:38'),
(357, 12, 'User logged out', 'auth', 12, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:37:47'),
(358, 12, 'User logged in', 'auth', 12, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:37:58'),
(359, 12, 'User logged out', 'auth', 12, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:38:08'),
(360, 11, 'User logged in', 'auth', 11, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:38:11'),
(361, 11, 'Updated document request', 'document_requests', 15, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:38:15'),
(362, 11, 'User logged out', 'auth', 11, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:38:25'),
(363, 13, 'User logged in', 'auth', 13, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:38:33'),
(364, 13, 'Submitted document request', 'document_requests', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:42:37'),
(365, 13, 'User logged out', 'auth', 13, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:42:49'),
(366, 11, 'User logged in', 'auth', 11, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:42:52'),
(367, 11, 'User logged out', 'auth', 11, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:46:00'),
(368, 13, 'User logged in', 'auth', 13, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:46:04'),
(369, 13, 'Submitted document request', 'document_requests', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:46:15'),
(370, 13, 'User logged out', 'auth', 13, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:46:19'),
(371, 11, 'User logged in', 'auth', 11, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:46:23'),
(372, 11, 'User logged out', 'auth', 11, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:48:37'),
(373, 11, 'User logged in', 'auth', 11, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:48:43'),
(374, 11, 'User logged out', 'auth', 11, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:48:48'),
(375, 13, 'User logged in', 'auth', 13, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:48:51'),
(376, 13, 'Submitted document request', 'document_requests', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:49:02'),
(377, 13, 'Submitted document request', 'document_requests', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:55:10'),
(378, 13, 'Submitted document request', 'document_requests', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:55:28'),
(379, 13, 'Submitted document request', 'document_requests', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:55:47'),
(380, 13, 'Submitted document request', 'document_requests', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:55:52'),
(381, 13, 'Submitted document request', 'document_requests', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:57:46'),
(382, 13, 'Submitted document request', 'document_requests', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:58:06'),
(383, 13, 'User logged out', 'auth', 13, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:58:12'),
(384, 11, 'User logged in', 'auth', 11, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:58:16'),
(385, 11, 'Updated document request', 'document_requests', 24, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:58:20'),
(386, 11, 'User logged out', 'auth', 11, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:58:24'),
(387, 12, 'User logged in', 'auth', 12, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 01:58:27'),
(388, 12, 'Recorded document payment', 'document_requests', 24, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Earl Gultia - DOC-20260529-1235', '2026-05-29 01:58:40'),
(389, 12, 'User logged out', 'auth', 12, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 02:02:23'),
(390, 13, 'User logged in', 'auth', 13, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 02:02:26'),
(391, 13, 'Submitted document request', 'document_requests', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 02:02:34'),
(392, 13, 'User logged out', 'auth', 13, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 02:02:41'),
(393, 11, 'User logged in', 'auth', 11, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 02:02:47'),
(394, 11, 'User logged out', 'auth', 11, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 02:03:18'),
(395, 13, 'User logged in', 'auth', 13, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 02:04:30'),
(396, 13, 'User logged out', 'auth', 13, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, '2026-05-29 02:04:37');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `announcement_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `announcement_type` enum('general','emergency','event','advisory','reminder') DEFAULT 'general',
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `target_audience` enum('all','residents_only','staff_only') DEFAULT 'all',
  `created_by` int(11) NOT NULL,
  `published_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `expiry_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `attachment_url` varchar(500) DEFAULT NULL,
  `views_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `announcement_views`
--

CREATE TABLE `announcement_views` (
  `view_id` int(11) NOT NULL,
  `announcement_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcement_views`
--

INSERT INTO `announcement_views` (`view_id`, `announcement_id`, `user_id`, `viewed_at`) VALUES
(1, 1, 8, '2026-05-28 22:42:19'),
(2, 1, 7, '2026-05-28 22:45:02'),
(3, 2, 8, '2026-05-28 22:45:36'),
(4, 2, 7, '2026-05-28 22:51:13'),
(5, 3, 8, '2026-05-28 22:51:53'),
(6, 3, 7, '2026-05-28 23:01:41');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `appointment_type` enum('document_request','complaint_filing','barangay_captain','secretary','health_checkup','other') NOT NULL,
  `preferred_date` date NOT NULL,
  `preferred_time` time NOT NULL,
  `purpose` text DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled','completed','rescheduled') DEFAULT 'pending',
  `confirmed_by` int(11) DEFAULT NULL,
  `confirmation_date` timestamp NULL DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `reschedule_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `barangay_officials`
--

CREATE TABLE `barangay_officials` (
  `official_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `position` varchar(100) NOT NULL,
  `term_start` date NOT NULL,
  `term_end` date DEFAULT NULL,
  `is_current` tinyint(1) DEFAULT 1,
  `responsibilities` text DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `complaint_id` int(11) NOT NULL,
  `complainant_id` int(11) NOT NULL,
  `respondent_name` varchar(255) DEFAULT NULL,
  `respondent_address` text DEFAULT NULL,
  `complaint_type` enum('noise','neighbor_dispute','property_damage','theft','assault','public_nuisance','other') NOT NULL,
  `incident_date` date DEFAULT NULL,
  `incident_time` time DEFAULT NULL,
  `incident_location` text DEFAULT NULL,
  `description` text NOT NULL,
  `supporting_documents` varchar(500) DEFAULT NULL,
  `status` enum('submitted','reviewing','mediation_scheduled','resolved','dismissed','for_blotter') DEFAULT 'submitted',
  `blotter_entry_number` varchar(50) DEFAULT NULL,
  `assigned_staff_id` int(11) DEFAULT NULL,
  `resolution` text DEFAULT NULL,
  `mediation_date` date DEFAULT NULL,
  `mediation_time` time DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_requests`
--

CREATE TABLE `document_requests` (
  `request_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `document_type` enum('barangay_clearance','certificate_of_residency','certificate_of_indigency','business_permit','cedula','other') NOT NULL,
  `purpose` text DEFAULT NULL,
  `other_details` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','ready_for_pickup','claimed') DEFAULT 'pending',
  `reference_number` varchar(50) DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `processed_by` int(11) DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `pickup_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT 0.00,
  `payment_method` enum('cash','gcash') NOT NULL DEFAULT 'cash',
  `payment_proof` varchar(500) DEFAULT NULL,
  `payment_proof_status` enum('none','submitted','verified','rejected') NOT NULL DEFAULT 'none',
  `payment_proof_submitted_at` timestamp NULL DEFAULT NULL,
  `payment_proof_reviewed_at` timestamp NULL DEFAULT NULL,
  `payment_proof_reviewed_by` int(11) DEFAULT NULL,
  `payment_status` enum('unpaid','paid','waived') DEFAULT 'unpaid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `household_members`
--

CREATE TABLE `household_members` (
  `member_id` int(11) NOT NULL,
  `resident_profile_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `relationship` enum('spouse','child','parent','sibling','grandparent','other') NOT NULL,
  `birth_date` date DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `is_dependent` tinyint(1) DEFAULT 1,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `permission_id` int(11) NOT NULL,
  `permission_name` varchar(100) NOT NULL,
  `permission_key` varchar(100) NOT NULL,
  `module` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`permission_id`, `permission_name`, `permission_key`, `module`, `description`, `created_at`) VALUES
(1, 'View Dashboard', 'view_dashboard', 'dashboard', 'Access to main dashboard', '2026-05-25 14:48:13'),
(2, 'View Reports', 'view_reports', 'reports', 'Access to reports and analytics', '2026-05-25 14:48:13'),
(3, 'View Users', 'view_users', 'users', 'View user list and details', '2026-05-25 14:48:13'),
(4, 'Create Users', 'create_users', 'users', 'Add new users to the system', '2026-05-25 14:48:13'),
(5, 'Edit Users', 'edit_users', 'users', 'Modify user information', '2026-05-25 14:48:13'),
(6, 'Delete Users', 'delete_users', 'users', 'Remove users from the system', '2026-05-25 14:48:13'),
(7, 'Assign Roles', 'assign_roles', 'users', 'Assign or change user roles', '2026-05-25 14:48:13'),
(8, 'Manage User Permissions', 'manage_permissions', 'users', 'Configure role permissions', '2026-05-25 14:48:13'),
(9, 'View Residents', 'view_residents', 'residents', 'View resident profiles', '2026-05-25 14:48:13'),
(10, 'Add Residents', 'add_residents', 'residents', 'Add new resident profiles', '2026-05-25 14:48:13'),
(11, 'Edit Residents', 'edit_residents', 'residents', 'Modify resident information', '2026-05-25 14:48:13'),
(12, 'Delete Residents', 'delete_residents', 'residents', 'Remove resident profiles', '2026-05-25 14:48:13'),
(13, 'View Documents', 'view_documents', 'documents', 'View document requests', '2026-05-25 14:48:13'),
(14, 'Process Documents', 'process_documents', 'documents', 'Process and approve document requests', '2026-05-25 14:48:13'),
(15, 'Release Documents', 'release_documents', 'documents', 'Release ready documents', '2026-05-25 14:48:13'),
(16, 'Generate QR Codes', 'generate_qr', 'documents', 'Generate QR codes for documents', '2026-05-25 14:48:13'),
(17, 'View Complaints', 'view_complaints', 'complaints', 'View complaint/blotter records', '2026-05-25 14:48:13'),
(18, 'Create Complaints', 'create_complaints', 'complaints', 'File new complaints', '2026-05-25 14:48:13'),
(19, 'Assign Complaints', 'assign_complaints', 'complaints', 'Assign complaints to staff', '2026-05-25 14:48:13'),
(20, 'Resolve Complaints', 'resolve_complaints', 'complaints', 'Mark complaints as resolved', '2026-05-25 14:48:13'),
(21, 'View Appointments', 'view_appointments', 'appointments', 'View all appointments', '2026-05-25 14:48:13'),
(22, 'Manage Appointments', 'manage_appointments', 'appointments', 'Create and modify appointments', '2026-05-25 14:48:13'),
(23, 'Confirm Appointments', 'confirm_appointments', 'appointments', 'Confirm or cancel appointments', '2026-05-25 14:48:13'),
(24, 'View Finances', 'view_finances', 'finance', 'View financial records', '2026-05-25 14:48:13'),
(25, 'Process Payments', 'process_payments', 'finance', 'Process and record payments', '2026-05-25 14:48:13'),
(26, 'Generate Receipts', 'generate_receipts', 'finance', 'Generate official receipts', '2026-05-25 14:48:13'),
(27, 'View Transactions', 'view_transactions', 'finance', 'View transaction history', '2026-05-25 14:48:13'),
(28, 'View Announcements', 'view_announcements', 'announcements', 'View announcements', '2026-05-25 14:48:13'),
(29, 'Create Announcements', 'create_announcements', 'announcements', 'Post new announcements', '2026-05-25 14:48:13'),
(30, 'Edit Announcements', 'edit_announcements', 'announcements', 'Modify announcements', '2026-05-25 14:48:13'),
(31, 'View Settings', 'view_settings', 'system', 'View system settings', '2026-05-25 14:48:13'),
(32, 'Edit Settings', 'edit_settings', 'system', 'Modify system settings', '2026-05-25 14:48:13');

-- --------------------------------------------------------

--
-- Table structure for table `qr_logs`
--

CREATE TABLE `qr_logs` (
  `qr_log_id` int(11) NOT NULL,
  `qr_code` varchar(255) NOT NULL,
  `entity_type` enum('resident','document','appointment') NOT NULL,
  `entity_id` int(11) NOT NULL,
  `scanned_by` int(11) DEFAULT NULL,
  `scan_location` varchar(255) DEFAULT NULL,
  `scan_timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resident_profiles`
--

CREATE TABLE `resident_profiles` (
  `profile_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `house_number` varchar(50) DEFAULT NULL,
  `street_address` varchar(255) DEFAULT NULL,
  `barangay` varchar(100) DEFAULT 'LATROBE',
  `city_municipality` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `zip_code` varchar(10) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `birth_place` varchar(255) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `civil_status` enum('single','married','widowed','divorced','separated') DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `monthly_income` decimal(12,2) DEFAULT NULL,
  `voter_status` tinyint(1) DEFAULT 0,
  `pwd_status` tinyint(1) DEFAULT 0,
  `senior_citizen` tinyint(1) DEFAULT 0,
  `four_ps_beneficiary` tinyint(1) DEFAULT 0,
  `profile_photo` varchar(255) DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `emergency_contact_name` varchar(255) DEFAULT NULL,
  `emergency_contact_number` varchar(20) DEFAULT NULL,
  `emergency_contact_relationship` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resident_profiles`
--

INSERT INTO `resident_profiles` (`profile_id`, `user_id`, `house_number`, `street_address`, `barangay`, `city_municipality`, `province`, `zip_code`, `birth_date`, `birth_place`, `gender`, `civil_status`, `occupation`, `monthly_income`, `voter_status`, `pwd_status`, `senior_citizen`, `four_ps_beneficiary`, `profile_photo`, `qr_code`, `emergency_contact_name`, `emergency_contact_number`, `emergency_contact_relationship`) VALUES
(2, 13, NULL, 'Purok 3, Alejawan Lutao, Duero, Bohol', 'LATROBE', NULL, NULL, '6309', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `role_description` text DEFAULT NULL,
  `role_level` int(11) DEFAULT 1,
  `is_system_role` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`, `role_description`, `role_level`, `is_system_role`, `created_at`, `updated_at`) VALUES
(1, 'super_admin', 'Full system access with all permissions', 100, 1, '2026-05-25 14:48:13', '2026-05-25 14:48:13'),
(2, 'barangay_captain', 'Head of barangay with approval authority', 90, 1, '2026-05-25 14:48:13', '2026-05-25 14:48:13'),
(3, 'barangay_secretary', 'Manages documents and appointments', 80, 1, '2026-05-25 14:48:13', '2026-05-25 14:48:13'),
(4, 'barangay_treasurer', 'Manages financial transactions and collections', 80, 1, '2026-05-25 14:48:13', '2026-05-25 14:48:13'),
(5, 'barangay_kagawad', 'Barangay council member with limited admin access', 70, 1, '2026-05-25 14:48:13', '2026-05-25 14:48:13'),
(6, 'health_worker', 'Manages health-related services and appointments', 70, 1, '2026-05-25 14:48:13', '2026-05-25 14:48:13'),
(7, 'tanod', 'Security and peacekeeping role', 60, 1, '2026-05-25 14:48:13', '2026-05-25 14:48:13'),
(8, 'admin_staff', 'General administrative staff', 65, 1, '2026-05-25 14:48:13', '2026-05-25 14:48:13'),
(9, 'resident', 'Regular barangay resident', 10, 1, '2026-05-25 14:48:13', '2026-05-25 14:48:13'),
(10, 'senior_citizen', 'Senior resident with special privileges', 15, 0, '2026-05-25 14:48:13', '2026-05-25 14:48:13'),
(11, 'pwd', 'Person with disability', 15, 0, '2026-05-25 14:48:13', '2026-05-25 14:48:13'),
(12, 'business_owner', 'Business permit holder in the barangay', 12, 0, '2026-05-25 14:48:13', '2026-05-25 14:48:13');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_permission_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `granted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `granted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`role_permission_id`, `role_id`, `permission_id`, `granted_at`, `granted_by`) VALUES
(1, 1, 28, '2026-05-25 14:48:13', NULL),
(2, 1, 29, '2026-05-25 14:48:13', NULL),
(3, 1, 30, '2026-05-25 14:48:13', NULL),
(4, 1, 21, '2026-05-25 14:48:13', NULL),
(5, 1, 22, '2026-05-25 14:48:13', NULL),
(6, 1, 23, '2026-05-25 14:48:13', NULL),
(7, 1, 17, '2026-05-25 14:48:13', NULL),
(8, 1, 18, '2026-05-25 14:48:13', NULL),
(9, 1, 19, '2026-05-25 14:48:13', NULL),
(10, 1, 20, '2026-05-25 14:48:13', NULL),
(11, 1, 1, '2026-05-25 14:48:13', NULL),
(12, 1, 13, '2026-05-25 14:48:13', NULL),
(13, 1, 14, '2026-05-25 14:48:13', NULL),
(14, 1, 15, '2026-05-25 14:48:13', NULL),
(15, 1, 16, '2026-05-25 14:48:13', NULL),
(16, 1, 24, '2026-05-25 14:48:13', NULL),
(17, 1, 25, '2026-05-25 14:48:13', NULL),
(18, 1, 26, '2026-05-25 14:48:13', NULL),
(19, 1, 27, '2026-05-25 14:48:13', NULL),
(20, 1, 2, '2026-05-25 14:48:13', NULL),
(21, 1, 9, '2026-05-25 14:48:13', NULL),
(22, 1, 10, '2026-05-25 14:48:13', NULL),
(23, 1, 11, '2026-05-25 14:48:13', NULL),
(24, 1, 12, '2026-05-25 14:48:13', NULL),
(25, 1, 31, '2026-05-25 14:48:13', NULL),
(26, 1, 32, '2026-05-25 14:48:13', NULL),
(27, 1, 3, '2026-05-25 14:48:13', NULL),
(28, 1, 4, '2026-05-25 14:48:13', NULL),
(29, 1, 5, '2026-05-25 14:48:13', NULL),
(30, 1, 6, '2026-05-25 14:48:13', NULL),
(31, 1, 7, '2026-05-25 14:48:13', NULL),
(32, 1, 8, '2026-05-25 14:48:13', NULL),
(64, 2, 10, '2026-05-25 14:48:13', NULL),
(65, 2, 19, '2026-05-25 14:48:13', NULL),
(66, 2, 23, '2026-05-25 14:48:13', NULL),
(67, 2, 29, '2026-05-25 14:48:13', NULL),
(68, 2, 30, '2026-05-25 14:48:13', NULL),
(69, 2, 11, '2026-05-25 14:48:13', NULL),
(70, 2, 22, '2026-05-25 14:48:13', NULL),
(71, 2, 14, '2026-05-25 14:48:13', NULL),
(72, 2, 15, '2026-05-25 14:48:13', NULL),
(73, 2, 20, '2026-05-25 14:48:13', NULL),
(74, 2, 28, '2026-05-25 14:48:13', NULL),
(75, 2, 21, '2026-05-25 14:48:13', NULL),
(76, 2, 17, '2026-05-25 14:48:13', NULL),
(77, 2, 1, '2026-05-25 14:48:13', NULL),
(78, 2, 13, '2026-05-25 14:48:13', NULL),
(79, 2, 24, '2026-05-25 14:48:13', NULL),
(80, 2, 2, '2026-05-25 14:48:13', NULL),
(81, 2, 9, '2026-05-25 14:48:13', NULL),
(82, 2, 27, '2026-05-25 14:48:13', NULL),
(83, 2, 3, '2026-05-25 14:48:13', NULL),
(95, 3, 10, '2026-05-25 14:48:13', NULL),
(96, 3, 29, '2026-05-25 14:48:13', NULL),
(97, 3, 18, '2026-05-25 14:48:13', NULL),
(98, 3, 11, '2026-05-25 14:48:13', NULL),
(99, 3, 16, '2026-05-25 14:48:13', NULL),
(100, 3, 22, '2026-05-25 14:48:13', NULL),
(101, 3, 14, '2026-05-25 14:48:13', NULL),
(102, 3, 15, '2026-05-25 14:48:13', NULL),
(103, 3, 28, '2026-05-25 14:48:13', NULL),
(104, 3, 21, '2026-05-25 14:48:13', NULL),
(105, 3, 17, '2026-05-25 14:48:13', NULL),
(106, 3, 1, '2026-05-25 14:48:13', NULL),
(107, 3, 13, '2026-05-25 14:48:13', NULL),
(108, 3, 9, '2026-05-25 14:48:13', NULL),
(110, 4, 26, '2026-05-25 14:48:13', NULL),
(111, 4, 25, '2026-05-25 14:48:13', NULL),
(112, 4, 1, '2026-05-25 14:48:13', NULL),
(113, 4, 24, '2026-05-25 14:48:13', NULL),
(114, 4, 2, '2026-05-25 14:48:13', NULL),
(115, 4, 9, '2026-05-25 14:48:13', NULL),
(116, 4, 27, '2026-05-25 14:48:13', NULL),
(117, 9, 18, '2026-05-25 14:48:13', NULL),
(118, 9, 28, '2026-05-25 14:48:13', NULL),
(119, 9, 21, '2026-05-25 14:48:13', NULL),
(120, 9, 1, '2026-05-25 14:48:13', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `subscription_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subscription_type` enum('monthly','quarterly','annual') DEFAULT 'monthly',
  `amount` decimal(12,2) NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('pending','paid','overdue','cancelled') DEFAULT 'pending',
  `payment_method` enum('cash','gcash','bank_transfer') DEFAULT NULL,
  `payment_reference` varchar(100) DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `invoice_number` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_id`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
(1, 'barangay_name', 'MyBalai', '2026-05-26 16:49:33', '2026-05-28 18:52:07'),
(2, 'barangay_address', 'Alejawan Lutao, Duero, Bohol', '2026-05-26 16:49:33', '2026-05-28 18:52:07'),
(3, 'contact_email', '', '2026-05-26 16:49:33', '2026-05-28 18:52:07'),
(4, 'contact_phone', '', '2026-05-26 16:49:33', '2026-05-28 18:52:07'),
(5, 'monthly_fee', '', '2026-05-26 16:49:33', '2026-05-28 18:52:07'),
(6, 'treasurer_availability', 'in_office', '2026-05-28 18:28:27', '2026-05-28 18:52:07'),
(7, 'treasurer_gcash_phone', '09944462851', '2026-05-28 18:52:07', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `transaction_type` enum('subscription','document_fee','clearance_fee','other') NOT NULL,
  `document_type` varchar(100) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_method` enum('cash','gcash','bank_transfer') NOT NULL,
  `payment_reference` varchar(100) DEFAULT NULL,
  `or_number` varchar(50) DEFAULT NULL,
  `status` enum('pending','completed','failed','refunded') DEFAULT 'completed',
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `collected_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `primary_role_id` int(11) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `suffix` varchar(20) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_locked` tinyint(1) DEFAULT 0,
  `is_verified` tinyint(1) DEFAULT 0,
  `email_verified` tinyint(1) DEFAULT 0,
  `phone_verified` tinyint(1) DEFAULT 0,
  `two_factor_enabled` tinyint(1) DEFAULT 0,
  `two_factor_secret` varchar(255) DEFAULT NULL,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `password_reset_expires` timestamp NULL DEFAULT NULL,
  `email_verification_token` varchar(255) DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `last_ip` varchar(45) DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `primary_role_id`, `username`, `email`, `password_hash`, `first_name`, `last_name`, `middle_name`, `suffix`, `phone_number`, `mobile_number`, `profile_picture`, `is_active`, `is_locked`, `is_verified`, `email_verified`, `phone_verified`, `two_factor_enabled`, `two_factor_secret`, `password_reset_token`, `password_reset_expires`, `email_verification_token`, `last_login`, `last_ip`, `login_attempts`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES
(1, 1, 'superadmin', 'superadmin@mybalai.com', '$2y$10$rsUcxCkZu/EFEWKxtksnLuY/Jx.3JfLVL4tLHU8SfoCjBweWIBc2O', 'System', 'Administrator', NULL, NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0, NULL, NULL, NULL, NULL, '2026-05-29 00:58:08', NULL, 0, '2026-05-25 14:48:13', '2026-05-29 00:58:08', NULL, NULL),
(10, 2, 'Barangay Captian', 'captain@mybalai.com', '$2y$10$07euAEr/yQbCgf5/IBMOve5xXKGK7l.Ce.Uxq8JLKeHFUQnuuGp4S', 'Erenio', 'Jamago', NULL, NULL, '09123456987', NULL, NULL, 1, 0, 1, 1, 0, 0, NULL, NULL, NULL, NULL, '2026-05-29 00:59:54', NULL, 0, '2026-05-29 00:59:43', '2026-05-29 00:59:54', 1, NULL),
(11, 3, 'Barangay Secretary', 'celine.secretary@gmail.com', '$2y$10$l1fmIE3txTn9IsX7JJRVZOdTWa49afevTsQ9jhgnKFUlNmL9ytWfi', 'Celine', 'Paredes', NULL, NULL, '09123456789', NULL, NULL, 1, 0, 1, 1, 0, 0, NULL, NULL, NULL, NULL, '2026-05-29 02:02:47', NULL, 0, '2026-05-29 01:06:48', '2026-05-29 02:02:47', 10, NULL),
(12, 4, 'Barangay Treasurer', 'arlene.treasurer@gmail.com', '$2y$10$UXiz7P0AmxXbvhHMjG.ufu4pr0EUsNVJ1rFmqBUp8FVpEhMAqCBaC', 'Arlene', 'Opena', NULL, NULL, '09944462851', NULL, NULL, 1, 0, 1, 1, 0, 0, NULL, NULL, NULL, NULL, '2026-05-29 01:58:27', NULL, 0, '2026-05-29 01:08:00', '2026-05-29 01:58:27', 10, NULL),
(13, 9, 'Earl Gultia', 'gultia98@gmail.com', '$2y$10$ZF75NSVVYA1cMowNe10c4uL/ZqNU096wDvg5hDbGyAFpTKfaNXgU6', 'Earl', 'Gultia', NULL, NULL, '09944462851', NULL, NULL, 1, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, '2026-05-29 02:04:30', NULL, 0, '2026-05-29 01:13:28', '2026-05-29 02:04:30', NULL, NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `user_permissions_view`
-- (See below for the actual view)
--
CREATE TABLE `user_permissions_view` (
`user_id` int(11)
,`username` varchar(50)
,`email` varchar(255)
,`role_name` varchar(50)
,`permission_key` varchar(100)
,`permission_name` varchar(100)
,`module` varchar(50)
);

-- --------------------------------------------------------

--
-- Table structure for table `user_role_assignments`
--

CREATE TABLE `user_role_assignments` (
  `assignment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_role_assignments`
--

INSERT INTO `user_role_assignments` (`assignment_id`, `user_id`, `role_id`, `assigned_by`, `assigned_at`, `is_active`, `expires_at`) VALUES
(1, 1, 1, 1, '2026-05-25 14:48:13', 1, NULL),
(5, 5, 9, 1, '2026-05-25 14:48:13', 1, NULL),
(6, 6, 9, 1, '2026-05-25 14:48:13', 1, NULL),
(7, 6, 10, 1, '2026-05-25 14:48:13', 1, NULL),
(11, 10, 2, 1, '2026-05-29 00:59:43', 1, NULL),
(12, 11, 3, 10, '2026-05-29 01:06:48', 1, NULL),
(13, 12, 4, 10, '2026-05-29 01:08:00', 1, NULL),
(14, 13, 9, NULL, '2026-05-29 01:13:28', 1, NULL);

-- --------------------------------------------------------

--
-- Structure for view `active_users_with_roles`
--
DROP TABLE IF EXISTS `active_users_with_roles`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `active_users_with_roles`  AS SELECT `u`.`user_id` AS `user_id`, `u`.`username` AS `username`, `u`.`email` AS `email`, `u`.`first_name` AS `first_name`, `u`.`last_name` AS `last_name`, `u`.`phone_number` AS `phone_number`, `u`.`is_active` AS `is_active`, `u`.`last_login` AS `last_login`, `r`.`role_name` AS `primary_role`, group_concat(distinct `r2`.`role_name` separator ', ') AS `all_roles` FROM (((`users` `u` left join `roles` `r` on(`u`.`primary_role_id` = `r`.`role_id`)) left join `user_role_assignments` `ura` on(`u`.`user_id` = `ura`.`user_id` and `ura`.`is_active` = 1)) left join `roles` `r2` on(`ura`.`role_id` = `r2`.`role_id`)) WHERE `u`.`deleted_at` is null GROUP BY `u`.`user_id` ;

-- --------------------------------------------------------

--
-- Structure for view `user_permissions_view`
--
DROP TABLE IF EXISTS `user_permissions_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `user_permissions_view`  AS SELECT DISTINCT `u`.`user_id` AS `user_id`, `u`.`username` AS `username`, `u`.`email` AS `email`, `r`.`role_name` AS `role_name`, `p`.`permission_key` AS `permission_key`, `p`.`permission_name` AS `permission_name`, `p`.`module` AS `module` FROM ((((`users` `u` join `user_role_assignments` `ura` on(`u`.`user_id` = `ura`.`user_id` and `ura`.`is_active` = 1)) join `roles` `r` on(`ura`.`role_id` = `r`.`role_id`)) join `role_permissions` `rp` on(`r`.`role_id` = `rp`.`role_id`)) join `permissions` `p` on(`rp`.`permission_id` = `p`.`permission_id`)) WHERE `u`.`is_active` = 1 AND `u`.`deleted_at` is null ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`announcement_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_published_date` (`published_date`);

--
-- Indexes for table `announcement_views`
--
ALTER TABLE `announcement_views`
  ADD PRIMARY KEY (`view_id`),
  ADD UNIQUE KEY `unique_view` (`announcement_id`,`user_id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `confirmed_by` (`confirmed_by`),
  ADD KEY `idx_date` (`preferred_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_appointments_date_status` (`preferred_date`,`status`);

--
-- Indexes for table `barangay_officials`
--
ALTER TABLE `barangay_officials`
  ADD PRIMARY KEY (`official_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_position` (`position`),
  ADD KEY `idx_is_current` (`is_current`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`complaint_id`),
  ADD UNIQUE KEY `blotter_entry_number` (`blotter_entry_number`),
  ADD KEY `assigned_staff_id` (`assigned_staff_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_complaint_type` (`complaint_type`),
  ADD KEY `idx_complaints_complainant` (`complainant_id`);

--
-- Indexes for table `document_requests`
--
ALTER TABLE `document_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD UNIQUE KEY `reference_number` (`reference_number`),
  ADD KEY `processed_by` (`processed_by`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_document_type` (`document_type`),
  ADD KEY `idx_reference` (`reference_number`),
  ADD KEY `idx_document_requests_user_status` (`user_id`,`status`);

--
-- Indexes for table `household_members`
--
ALTER TABLE `household_members`
  ADD PRIMARY KEY (`member_id`),
  ADD KEY `resident_profile_id` (`resident_profile_id`),
  ADD KEY `idx_relationship` (`relationship`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`permission_id`),
  ADD UNIQUE KEY `permission_name` (`permission_name`),
  ADD UNIQUE KEY `permission_key` (`permission_key`),
  ADD KEY `idx_module` (`module`),
  ADD KEY `idx_permission_key` (`permission_key`),
  ADD KEY `idx_permissions_module` (`module`);

--
-- Indexes for table `qr_logs`
--
ALTER TABLE `qr_logs`
  ADD PRIMARY KEY (`qr_log_id`),
  ADD KEY `scanned_by` (`scanned_by`),
  ADD KEY `idx_qr_code` (`qr_code`),
  ADD KEY `idx_scan_timestamp` (`scan_timestamp`);

--
-- Indexes for table `resident_profiles`
--
ALTER TABLE `resident_profiles`
  ADD PRIMARY KEY (`profile_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_house_number` (`house_number`),
  ADD KEY `idx_status_flags` (`senior_citizen`,`pwd_status`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`),
  ADD KEY `idx_role_level` (`role_level`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_permission_id`),
  ADD UNIQUE KEY `unique_role_permission` (`role_id`,`permission_id`),
  ADD KEY `idx_role` (`role_id`),
  ADD KEY `idx_permission` (`permission_id`),
  ADD KEY `idx_role_permissions_role` (`role_id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`subscription_id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `idx_due_date` (`due_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_subscriptions_user_status` (`user_id`,`status`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `unique_setting_key` (`setting_key`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD UNIQUE KEY `or_number` (`or_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `collected_by` (`collected_by`),
  ADD KEY `idx_transaction_date` (`transaction_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_payment_method` (`payment_method`),
  ADD KEY `idx_transactions_date_status` (`transaction_date`,`status`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `primary_role_id` (`primary_role_id`);

--
-- Indexes for table `user_role_assignments`
--
ALTER TABLE `user_role_assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD UNIQUE KEY `unique_user_role_active` (`user_id`,`role_id`,`is_active`),
  ADD KEY `assigned_by` (`assigned_by`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_role` (`role_id`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_user_role_assignments_active` (`user_id`,`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=397;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `announcement_views`
--
ALTER TABLE `announcement_views`
  MODIFY `view_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `barangay_officials`
--
ALTER TABLE `barangay_officials`
  MODIFY `official_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `complaint_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `document_requests`
--
ALTER TABLE `document_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `household_members`
--
ALTER TABLE `household_members`
  MODIFY `member_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `permission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `qr_logs`
--
ALTER TABLE `qr_logs`
  MODIFY `qr_log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resident_profiles`
--
ALTER TABLE `resident_profiles`
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `role_permission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `subscription_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `user_role_assignments`
--
ALTER TABLE `user_role_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`confirmed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `barangay_officials`
--
ALTER TABLE `barangay_officials`
  ADD CONSTRAINT `barangay_officials_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`complainant_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `complaints_ibfk_2` FOREIGN KEY (`assigned_staff_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `document_requests`
--
ALTER TABLE `document_requests`
  ADD CONSTRAINT `document_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `document_requests_ibfk_2` FOREIGN KEY (`processed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `document_requests_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `household_members`
--
ALTER TABLE `household_members`
  ADD CONSTRAINT `household_members_ibfk_1` FOREIGN KEY (`resident_profile_id`) REFERENCES `resident_profiles` (`profile_id`) ON DELETE CASCADE;

--
-- Constraints for table `qr_logs`
--
ALTER TABLE `qr_logs`
  ADD CONSTRAINT `qr_logs_ibfk_1` FOREIGN KEY (`scanned_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `resident_profiles`
--
ALTER TABLE `resident_profiles`
  ADD CONSTRAINT `resident_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`permission_id`) ON DELETE CASCADE;

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`collected_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`primary_role_id`) REFERENCES `roles` (`role_id`) ON DELETE SET NULL;

--
-- Constraints for table `user_role_assignments`
--
ALTER TABLE `user_role_assignments`
  ADD CONSTRAINT `user_role_assignments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_role_assignments_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_role_assignments_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
