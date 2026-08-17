-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Aug 17, 2026 at 10:24 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `brgy_rbim`
--

-- --------------------------------------------------------

--
-- Table structure for table `action`
--

DROP TABLE IF EXISTS `action`;
CREATE TABLE IF NOT EXISTS `action` (
  `action_id` int NOT NULL AUTO_INCREMENT,
  `action` varchar(45) NOT NULL,
  PRIMARY KEY (`action_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `action`
--

INSERT INTO `action` (`action_id`, `action`) VALUES
(1, 'Create'),
(2, 'Update'),
(3, 'View'),
(4, 'Print'),
(5, 'Export'),
(6, 'Backup'),
(7, 'Restore');

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

DROP TABLE IF EXISTS `audit_log`;
CREATE TABLE IF NOT EXISTS `audit_log` (
  `audit_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `action_id` int NOT NULL,
  `record_id` int NOT NULL,
  `description` varchar(255) NOT NULL,
  `old_value` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `new_value` varchar(45) NOT NULL,
  `performed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `target` varchar(45) NOT NULL,
  `entity` varchar(45) NOT NULL,
  PRIMARY KEY (`audit_id`),
  KEY `action` (`action_id`),
  KEY `audit_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `audit_log`
--

INSERT INTO `audit_log` (`audit_id`, `user_id`, `action_id`, `record_id`, `description`, `old_value`, `new_value`, `performed_at`, `target`, `entity`) VALUES
(1, 1, 1, 1, 'Create user account', NULL, 'ellen', '2026-08-06 23:56:57', 'account', 'user'),
(2, 1, 1, 2, 'Create user account', NULL, 'laila', '2026-08-06 23:56:57', 'account', 'user'),
(3, 1, 1, 1, 'Create barangay personnel record', NULL, 'ellen', '2026-08-06 23:59:23', 'record', 'barangay personnel'),
(4, 1, 1, 2, 'Create barangay personnel record', NULL, 'laila', '2026-08-06 23:59:23', 'record', 'barangay personnel'),
(5, 1, 2, 4, 'Updated user role status', '1', '0', '2026-08-07 00:14:57', 'status', 'user role'),
(6, 3, 2, 1, 'Reset user password', NULL, 'ellen', '2026-08-14 09:21:35', 'password', 'user'),
(7, 1, 2, 2, 'Reset user password', NULL, 'laila', '2026-08-14 09:28:31', 'password', 'user'),
(8, 1, 1, 9, 'Assign role to user', NULL, '4', '2026-08-17 10:10:22', 'assignment', 'user role'),
(9, 1, 1, 4, 'Create user account', NULL, 'paulene', '2026-08-17 10:10:22', 'account', 'user'),
(10, 1, 2, 6, 'Updated user role status', '0', '1', '2026-08-17 10:11:55', 'status', 'user role'),
(11, 1, 2, 2, 'Update barangay personnel', 'Adawi, Laila', 'Adawi, Laila', '2026-08-17 10:13:11', 'record', 'barangay_personnel'),
(12, 1, 2, 2, 'Update barangay personnel', 'Adawi, Laila', 'Adawi, Laila', '2026-08-17 10:13:24', 'record', 'barangay_personnel'),
(13, 1, 1, 10, 'Assign role to user', NULL, '2', '2026-08-17 10:17:17', 'assignment', 'user role'),
(14, 1, 1, 4, 'Create personnel position', NULL, 'Barangay Treasurer', '2026-08-17 10:22:21', 'position_name', 'personnel_position');

-- --------------------------------------------------------

--
-- Table structure for table `barangay_personnel`
--

DROP TABLE IF EXISTS `barangay_personnel`;
CREATE TABLE IF NOT EXISTS `barangay_personnel` (
  `personnel_id` int NOT NULL AUTO_INCREMENT,
  `position_id` int NOT NULL,
  `personnel_last_name` varchar(45) NOT NULL,
  `personnel_first_name` varchar(45) NOT NULL,
  `personnel_middle_name` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `personnel_suffix` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `personnel_status_id` int NOT NULL DEFAULT '1',
  `personnel_date_of_birth` date NOT NULL,
  PRIMARY KEY (`personnel_id`),
  KEY `personnel_position` (`position_id`),
  KEY `personnel_status` (`personnel_status_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `barangay_personnel`
--

INSERT INTO `barangay_personnel` (`personnel_id`, `position_id`, `personnel_last_name`, `personnel_first_name`, `personnel_middle_name`, `personnel_suffix`, `personnel_status_id`, `personnel_date_of_birth`) VALUES
(1, 1, 'Salibad', 'Ellen', NULL, NULL, 1, '1986-08-21'),
(2, 2, 'Adawi', 'Laila', NULL, NULL, 1, '2000-08-04');

--
-- Triggers `barangay_personnel`
--
DROP TRIGGER IF EXISTS `trg_personnel_dob_insert`;
DELIMITER $$
CREATE TRIGGER `trg_personnel_dob_insert` BEFORE INSERT ON `barangay_personnel` FOR EACH ROW BEGIN
    IF NEW.personnel_date_of_birth > CURDATE() THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Date of birth cannot be in the future.';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `login_status`
--

DROP TABLE IF EXISTS `login_status`;
CREATE TABLE IF NOT EXISTS `login_status` (
  `login_status_id` int NOT NULL AUTO_INCREMENT,
  `login_status` varchar(45) NOT NULL,
  PRIMARY KEY (`login_status_id`),
  UNIQUE KEY `uq_login_status` (`login_status`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `login_status`
--

INSERT INTO `login_status` (`login_status_id`, `login_status`) VALUES
(5, 'Account Locked'),
(4, 'Invalid Credentials'),
(2, 'Invalid Password'),
(3, 'Invalid Username'),
(1, 'Success');

-- --------------------------------------------------------

--
-- Table structure for table `permission`
--

DROP TABLE IF EXISTS `permission`;
CREATE TABLE IF NOT EXISTS `permission` (
  `permission_id` int NOT NULL AUTO_INCREMENT,
  `permission` varchar(45) NOT NULL,
  PRIMARY KEY (`permission_id`),
  UNIQUE KEY `uq_permission` (`permission_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `permission`
--

INSERT INTO `permission` (`permission_id`, `permission`) VALUES
(1, 'user.create'),
(2, 'user.view'),
(3, 'user.resetpassword'),
(4, 'user.updatestatus'),
(5, 'userlog.view'),
(6, 'userrole.create'),
(7, 'userrole.view'),
(8, 'userrole.updatestatus'),
(9, 'personnel.create'),
(10, 'personnel.view'),
(11, 'personnel.update'),
(12, 'pposition.create'),
(13, 'pposition.view'),
(14, 'pposition.delete'),
(15, 'setting.view'),
(16, 'setting.update'),
(17, 'user.changepassword');

-- --------------------------------------------------------

--
-- Table structure for table `personnel_position`
--

DROP TABLE IF EXISTS `personnel_position`;
CREATE TABLE IF NOT EXISTS `personnel_position` (
  `position_id` int NOT NULL AUTO_INCREMENT,
  `position_name` varchar(45) NOT NULL,
  PRIMARY KEY (`position_id`),
  UNIQUE KEY `uq_position` (`position_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `personnel_position`
--

INSERT INTO `personnel_position` (`position_id`, `position_name`) VALUES
(1, 'Barangay Secretary'),
(4, 'Barangay Treasurer'),
(2, 'SK Chairperson');

-- --------------------------------------------------------

--
-- Table structure for table `personnel_status`
--

DROP TABLE IF EXISTS `personnel_status`;
CREATE TABLE IF NOT EXISTS `personnel_status` (
  `personnel_status_id` int NOT NULL AUTO_INCREMENT,
  `personnel_status` varchar(45) NOT NULL,
  PRIMARY KEY (`personnel_status_id`),
  UNIQUE KEY `uq_personnel_status` (`personnel_status`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `personnel_status`
--

INSERT INTO `personnel_status` (`personnel_status_id`, `personnel_status`) VALUES
(1, 'Active'),
(2, 'Inactive'),
(5, 'On Leave'),
(3, 'Resigned'),
(4, 'Retired');

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

DROP TABLE IF EXISTS `role`;
CREATE TABLE IF NOT EXISTS `role` (
  `role_id` int NOT NULL AUTO_INCREMENT,
  `role_name` varchar(45) NOT NULL,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `uq_role` (`role_name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`role_id`, `role_name`) VALUES
(2, 'Admin'),
(1, 'Encoder'),
(4, 'Guest'),
(3, 'Super Admin');

-- --------------------------------------------------------

--
-- Table structure for table `role_permission`
--

DROP TABLE IF EXISTS `role_permission`;
CREATE TABLE IF NOT EXISTS `role_permission` (
  `role_permission_id` int NOT NULL AUTO_INCREMENT,
  `role_id` int NOT NULL,
  `permission_id` int NOT NULL,
  PRIMARY KEY (`role_permission_id`),
  UNIQUE KEY `uq_permission_assignment` (`role_id`,`permission_id`),
  KEY `permission_role` (`permission_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `role_permission`
--

INSERT INTO `role_permission` (`role_permission_id`, `role_id`, `permission_id`) VALUES
(19, 1, 17),
(16, 2, 15),
(17, 2, 16),
(18, 2, 17),
(7, 3, 1),
(10, 3, 2),
(8, 3, 3),
(9, 3, 4),
(11, 3, 5),
(12, 3, 6),
(14, 3, 7),
(13, 3, 8),
(1, 3, 9),
(3, 3, 10),
(2, 3, 11),
(4, 3, 12),
(6, 3, 13),
(5, 3, 14),
(20, 3, 17),
(21, 4, 17);

-- --------------------------------------------------------

--
-- Table structure for table `system_setting`
--

DROP TABLE IF EXISTS `system_setting`;
CREATE TABLE IF NOT EXISTS `system_setting` (
  `setting_id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(45) NOT NULL,
  `setting_value` varchar(45) NOT NULL,
  `data_type` varchar(45) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`setting_id`),
  UNIQUE KEY `uq_setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `system_setting`
--

INSERT INTO `system_setting` (`setting_id`, `setting_key`, `setting_value`, `data_type`, `description`) VALUES
(1, 'city_name', 'Baguio City', 'string', 'Official City Name'),
(2, 'barangay_name', 'Barangay Happy Hallow', 'string', 'Official Barangay Name'),
(3, 'barangay_code', '1430300006', 'string', 'Philippine Standard Geographic Code'),
(4, 'barangay_address', 'Barangay Happy Hallow, Baguio City, Benguet, ', 'string', 'Complete Address'),
(5, 'barangay_contact_no', '09123456789', 'string', 'Contact Number'),
(6, 'barangay_email', 'brgyhappyhallow@gmail.com', 'string', 'Official Email Address'),
(7, 'default_password', 'Temp12345', 'string', 'Default Password'),
(8, 'password_min_length', '8', 'int', 'Minimum length of password'),
(9, 'max_login_attempts', '5', 'int', 'Maximum attempts of login'),
(10, 'account_lockout_minutes', '10', 'int', 'Account lockout minutes duration'),
(11, 'session_timeout_minutes', '30', 'int', 'Session timeout minutes when idle'),
(12, 'audit_log_retention_days', '365', 'int', 'Audit log retention days');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(45) NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_status_id` int NOT NULL DEFAULT '1',
  `personnel_id` int DEFAULT NULL,
  `must_change_password` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `uq_username` (`username`),
  UNIQUE KEY `uq_personnel` (`personnel_id`),
  KEY `user_status` (`user_status_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `username`, `password_hash`, `created_at`, `user_status_id`, `personnel_id`, `must_change_password`) VALUES
(1, 'ellen', '$2y$12$u3zD2jX/B4be7SrQI6q2C.22iS5c6zvqlBiGccL3e0e9ylN9nuumW', '2026-08-06 22:47:52', 1, 1, 0),
(2, 'laila', '$2y$12$GsvVr.zfB06NZ3qvsOTNOOFhuIL7tIhneSyD39ol2NoZMw7gbS71O', '2026-08-06 22:48:05', 1, 2, 0),
(3, 'daniel', '$2y$12$QsRrTUyqymdeuZgl8sRJ5.lQFPK///dKIGYFf.dIzbEooCG8a0uyC', '2026-08-14 17:16:54', 1, NULL, 0),
(4, 'paulene', '$2y$12$4FMlC1iB456Wag0UdexEVuAokhOQsSD7JVU4KVtqJX3UHeqLs9UUK', '2026-08-17 10:10:22', 1, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_log`
--

DROP TABLE IF EXISTS `user_log`;
CREATE TABLE IF NOT EXISTS `user_log` (
  `user_log_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `login_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `logout_time` datetime DEFAULT NULL,
  `login_status_id` int NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `device` varchar(45) NOT NULL,
  PRIMARY KEY (`user_log_id`),
  KEY `user_log` (`user_id`),
  KEY `login_status` (`login_status_id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_log`
--

INSERT INTO `user_log` (`user_log_id`, `user_id`, `login_time`, `logout_time`, `login_status_id`, `ip_address`, `device`) VALUES
(1, 3, '2026-08-14 09:17:43', '2026-08-14 09:17:43', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(2, 3, '2026-08-14 09:18:53', '2026-08-14 09:18:53', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(3, 3, '2026-08-14 09:20:24', '2026-08-14 09:21:45', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(4, 1, '2026-08-14 09:21:51', '2026-08-14 09:23:25', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(5, 1, '2026-08-14 09:23:29', '2026-08-14 09:23:29', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(6, 3, '2026-08-14 09:23:39', '2026-08-14 09:23:39', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(7, 1, '2026-08-14 09:23:48', '2026-08-14 09:24:02', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(8, 3, '2026-08-14 09:24:06', '2026-08-14 09:24:20', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(9, 1, '2026-08-14 09:24:24', '2026-08-14 09:26:04', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(10, 3, '2026-08-14 09:26:10', '2026-08-14 09:27:44', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(11, 3, '2026-08-14 09:27:53', '2026-08-14 09:28:08', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(12, 1, '2026-08-14 09:28:12', '2026-08-14 09:30:05', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(13, 2, '2026-08-14 09:30:13', '2026-08-14 19:36:36', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(14, 1, '2026-08-14 09:38:54', '2026-08-14 09:38:54', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(15, 1, '2026-08-14 09:38:56', '2026-08-14 09:38:56', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(16, 1, '2026-08-14 09:39:04', '2026-08-14 09:39:04', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(17, 1, '2026-08-14 09:39:34', '2026-08-14 09:39:34', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(18, 3, '2026-08-14 19:30:13', '2026-08-14 19:30:30', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(19, 1, '2026-08-14 19:30:34', '2026-08-14 19:30:47', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(20, 2, '2026-08-14 19:30:54', '2026-08-14 19:31:02', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(21, 3, '2026-08-14 19:31:07', '2026-08-14 19:31:11', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(22, 1, '2026-08-14 19:31:16', '2026-08-14 19:31:16', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(23, 3, '2026-08-14 19:36:49', '2026-08-14 19:37:04', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(24, 3, '2026-08-14 19:37:20', '2026-08-14 19:38:39', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(25, 1, '2026-08-14 19:49:32', '2026-08-14 19:50:15', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(26, 3, '2026-08-16 17:23:48', '2026-08-16 17:31:10', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(27, 3, '2026-08-16 17:31:24', '2026-08-16 17:32:20', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(28, 1, '2026-08-16 17:32:31', '2026-08-16 17:58:30', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(29, 3, '2026-08-16 17:45:15', '2026-08-16 17:50:16', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(30, 3, '2026-08-16 17:56:51', '2026-08-16 17:57:01', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(31, 3, '2026-08-16 17:58:24', '2026-08-16 18:24:06', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(32, 1, '2026-08-16 17:58:35', '2026-08-16 18:03:43', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(33, 1, '2026-08-16 18:06:03', '2026-08-16 18:06:03', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(34, 1, '2026-08-16 18:06:12', '2026-08-16 18:28:44', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(35, 1, '2026-08-16 18:24:12', '2026-08-16 18:30:34', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(36, 1, '2026-08-16 18:29:04', '2026-08-16 18:37:08', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(37, 3, '2026-08-16 18:30:39', '2026-08-16 18:43:22', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(38, 1, '2026-08-17 10:07:55', '2026-08-17 10:15:58', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(39, 2, '2026-08-17 10:16:04', '2026-08-17 10:16:04', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(40, 2, '2026-08-17 10:16:14', '2026-08-17 10:16:16', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(41, 1, '2026-08-17 10:16:21', '2026-08-17 10:16:21', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(42, 1, '2026-08-17 10:16:26', '2026-08-17 10:17:21', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(43, 2, '2026-08-17 10:17:29', '2026-08-17 10:17:34', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(44, 1, '2026-08-17 10:17:40', '2026-08-17 10:17:40', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(45, 1, '2026-08-17 10:17:44', '2026-08-17 10:24:14', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App');

-- --------------------------------------------------------

--
-- Table structure for table `user_role`
--

DROP TABLE IF EXISTS `user_role`;
CREATE TABLE IF NOT EXISTS `user_role` (
  `user_role_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `role_id` int NOT NULL,
  `assigned_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `assigned_by` int NOT NULL,
  `enable` tinyint(1) NOT NULL,
  PRIMARY KEY (`user_role_id`),
  UNIQUE KEY `uq_role_assignment` (`user_id`,`role_id`),
  KEY `user_role` (`role_id`),
  KEY `role_assignor` (`assigned_by`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_role`
--

INSERT INTO `user_role` (`user_role_id`, `user_id`, `role_id`, `assigned_at`, `assigned_by`, `enable`) VALUES
(1, 1, 2, '2026-08-06 22:49:17', 1, 1),
(2, 2, 1, '2026-08-06 22:49:17', 1, 1),
(3, 1, 3, '2026-08-06 22:57:33', 1, 1),
(4, 1, 1, '2026-08-06 23:13:42', 1, 0),
(6, 3, 4, '2026-08-14 17:16:54', 3, 1),
(9, 4, 4, '2026-08-17 10:10:22', 1, 1),
(10, 2, 2, '2026-08-17 10:17:17', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_status`
--

DROP TABLE IF EXISTS `user_status`;
CREATE TABLE IF NOT EXISTS `user_status` (
  `user_status_id` int NOT NULL AUTO_INCREMENT,
  `user_status` varchar(45) NOT NULL,
  `can_login` tinyint(1) NOT NULL,
  PRIMARY KEY (`user_status_id`),
  UNIQUE KEY `uq_user_status` (`user_status`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_status`
--

INSERT INTO `user_status` (`user_status_id`, `user_status`, `can_login`) VALUES
(1, 'Active', 1),
(2, 'Disabled', 0),
(3, 'Locked', 0),
(4, 'Suspended', 0);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `action` FOREIGN KEY (`action_id`) REFERENCES `action` (`action_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `audit_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `barangay_personnel`
--
ALTER TABLE `barangay_personnel`
  ADD CONSTRAINT `personnel_position` FOREIGN KEY (`position_id`) REFERENCES `personnel_position` (`position_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `personnel_status` FOREIGN KEY (`personnel_status_id`) REFERENCES `personnel_status` (`personnel_status_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `role_permission`
--
ALTER TABLE `role_permission`
  ADD CONSTRAINT `permission_role` FOREIGN KEY (`permission_id`) REFERENCES `permission` (`permission_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `role_permission` FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `brgy_user` FOREIGN KEY (`personnel_id`) REFERENCES `barangay_personnel` (`personnel_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `user_status` FOREIGN KEY (`user_status_id`) REFERENCES `user_status` (`user_status_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `user_log`
--
ALTER TABLE `user_log`
  ADD CONSTRAINT `login_status` FOREIGN KEY (`login_status_id`) REFERENCES `login_status` (`login_status_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `user_log` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `user_role`
--
ALTER TABLE `user_role`
  ADD CONSTRAINT `role_assignor` FOREIGN KEY (`assigned_by`) REFERENCES `user` (`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `user_assigned` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `user_role` FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
