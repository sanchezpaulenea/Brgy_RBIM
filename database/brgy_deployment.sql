-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 29, 2026 at 05:36 AM
-- Server version: 9.1.0
-- PHP Version: 8.4.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `brgy_deployment`
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
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `audit_log`
--

INSERT INTO `audit_log` (`audit_id`, `user_id`, `action_id`, `record_id`, `description`, `old_value`, `new_value`, `performed_at`, `target`, `entity`) VALUES
(1, 1, 1, 1, 'Create user account', NULL, 'ellen', '2026-08-06 23:56:57', 'account', 'user'),
(2, 1, 1, 2, 'Create user account', NULL, 'laila', '2026-08-06 23:56:57', 'account', 'user'),
(3, 1, 1, 1, 'Create barangay personnel record', NULL, 'ellen', '2026-08-06 23:59:23', 'record', 'barangay personnel'),
(4, 1, 1, 2, 'Create barangay personnel record', NULL, 'laila', '2026-08-06 23:59:23', 'record', 'barangay personnel'),
(5, 1, 2, 4, 'Updated user role status', '1', '0', '2026-08-07 00:14:57', 'status', 'user role'),
(6, 1, 2, 1, 'Updated user role status', '1', '0', '2026-08-31 19:27:36', 'status', 'user role'),
(7, 1, 1, 6, 'Assign role to user', NULL, '2', '2026-08-31 19:27:42', 'assignment', 'user role'),
(8, 1, 2, 2, 'Updated user role status', '1', '0', '2026-08-31 19:27:46', 'status', 'user role'),
(9, 1, 1, 4, 'Create personnel position', NULL, 'Kagawad On Health And Nutrition', '2026-08-31 19:29:32', 'position_name', 'personnel_position'),
(10, 1, 1, 3, 'Create barangay personnel', NULL, 'Rullan, Deborah', '2026-08-31 19:29:32', 'record', 'barangay_personnel'),
(11, 1, 1, 7, 'Assign role to user', NULL, '1', '2026-08-31 19:29:46', 'assignment', 'user role'),
(12, 1, 1, 3, 'Create user account', NULL, 'deborah', '2026-08-31 19:29:46', 'account', 'user'),
(13, 3, 2, 3, 'User changed password', '[REDACTED]', '[REDACTED]', '2026-08-31 19:30:16', 'password', 'user'),
(17, 2, 2, 6, 'Session ended because the account was signed in on another device.', NULL, 'concurrent_login', '2026-10-05 10:44:53', 'session', 'user_log'),
(18, 2, 1, 7, 'Create household', NULL, 'Penduko, Pedro', '2026-10-05 11:52:27', 'household', 'household'),
(19, 2, 1, 8, 'Create resident', NULL, 'Penduko, Pedro', '2026-10-05 11:52:27', 'record', 'resident'),
(20, 2, 1, 1, 'Create education', NULL, 'College graduate', '2026-10-05 11:52:32', 'record', 'education'),
(21, 2, 1, 1, 'Create economic', NULL, '500.00', '2026-10-05 11:52:49', 'record', 'economic'),
(22, 2, 1, 1, 'Create health', NULL, 'GSIS', '2026-10-05 11:53:01', 'record', 'health'),
(23, 2, 1, 1, 'Create sociocivic', NULL, 'Non-Solo Parent', '2026-10-05 11:53:17', 'record', 'sociocivic'),
(24, 2, 1, 1, 'Create migration', NULL, 'Non-Migrant', '2026-10-05 11:53:28', 'record', 'migration'),
(25, 2, 1, 1, 'Create community tax cert', NULL, 'Yes', '2026-10-05 11:53:34', 'record', 'community_tax_cert'),
(26, 2, 1, 1, 'Create skills development', NULL, 'Tesda', '2026-10-05 11:53:42', 'record', 'skills_development'),
(27, 2, 1, 1, 'Create household questions', NULL, 'Household 7', '2026-10-05 11:55:05', 'record', 'household_questions'),
(28, 2, 1, 8, 'Create household', NULL, 'Piattos, Mary Grace', '2026-10-05 11:57:36', 'household', 'household'),
(29, 2, 1, 9, 'Create resident', NULL, 'Piattos, Mary Grace', '2026-10-05 11:57:36', 'record', 'resident'),
(30, 2, 1, 2, 'Create education', NULL, 'College graduate', '2026-10-05 11:57:41', 'record', 'education'),
(31, 2, 1, 2, 'Create economic', NULL, '5000.00', '2026-10-05 11:57:55', 'record', 'economic'),
(32, 2, 1, 2, 'Create health', NULL, 'GSIS', '2026-10-05 11:58:12', 'record', 'health'),
(33, 2, 1, 2, 'Create sociocivic', NULL, 'Non-Solo Parent', '2026-10-05 11:58:21', 'record', 'sociocivic'),
(34, 2, 1, 2, 'Create migration', NULL, 'Non-Migrant', '2026-10-05 11:58:32', 'record', 'migration'),
(35, 2, 1, 2, 'Create community tax cert', NULL, 'Yes', '2026-10-05 11:58:37', 'record', 'community_tax_cert'),
(36, 2, 1, 2, 'Create skills development', NULL, 'Tesda', '2026-10-05 11:58:46', 'record', 'skills_development'),
(37, 2, 1, 2, 'Create household questions', NULL, 'Household 8', '2026-10-05 11:59:24', 'record', 'household_questions'),
(38, 2, 1, 1, 'Create pet census', NULL, 'Household 8', '2026-10-05 12:00:43', 'record', 'pet_census'),
(39, 2, 1, 2, 'Create pet census', NULL, 'Household 8', '2026-10-05 12:00:43', 'record', 'pet_census'),
(40, 1, 2, 1, 'Session ended because the account was signed in on another device.', NULL, 'concurrent_login', '2026-10-05 14:52:08', 'session', 'user_log'),
(41, 2, 2, 13, 'Session ended because the account was signed in on another device.', NULL, 'concurrent_login', '2026-10-05 14:53:10', 'session', 'user_log'),
(49, 2, 2, 17, 'Session ended because the account was signed in on another device.', NULL, 'concurrent_login', '2026-10-07 16:32:50', 'session', 'user_log'),
(50, 2, 1, 9, 'Create household', NULL, 'Manaligod, Juli', '2026-10-09 19:45:24', 'household', 'household'),
(51, 2, 1, 10, 'Create resident', NULL, 'Manaligod, Juli', '2026-10-09 19:45:24', 'record', 'resident'),
(52, 2, 1, 3, 'Create education', NULL, 'College graduate', '2026-10-09 19:48:27', 'record', 'education'),
(53, 2, 2, 23, 'Session ended because the account was signed in on another device.', NULL, 'concurrent_login', '2026-10-09 20:55:24', 'session', 'user_log'),
(54, 2, 1, 4, 'Create education', NULL, 'College level', '2026-10-09 21:01:45', 'record', 'education'),
(55, 2, 1, 3, 'Create health', NULL, 'GSIS', '2026-10-09 21:10:56', 'record', 'health'),
(56, 1, 2, 6, 'Update system setting', 'brgyhappyhallow@gmail.com', 'bchallowhappy@gmail.com', '2026-10-09 23:51:25', 'barangay_email', 'system_setting'),
(57, 2, 2, 31, 'Session ended because the account was signed in on another device.', NULL, 'concurrent_login', '2026-10-28 03:13:43', 'session', 'user_log'),
(58, 2, 2, 228, 'Updated household head', 'Abuan, Frederick Segundo', 'Abuan, Liberty Castillones', '2026-10-28 03:18:54', 'head', 'household'),
(59, 2, 2, 814, 'Updated resident relationship', 'Head', 'Spouse', '2026-10-28 03:18:54', 'relationship', 'resident'),
(60, 2, 2, 814, 'Updated resident status', 'Active', 'Deceased', '2026-10-28 03:18:54', 'status', 'resident'),
(61, 2, 2, 66, 'Updated household status', 'Active', 'Inactive', '2026-10-28 03:20:55', 'status', 'household'),
(62, 2, 2, 166, 'Updated resident marital status', '', 'Married', '2026-10-28 03:20:55', 'marital status', 'resident'),
(63, 2, 2, 166, 'Updated resident status', 'Active', 'Deceased', '2026-10-28 03:20:55', 'status', 'resident'),
(64, 2, 2, 387, 'Updated resident name', 'Layog, Zhaider jhed Pelingan', 'Layog, Zhaiden Jhed Pelingan', '2026-10-28 03:22:54', 'name', 'resident'),
(65, 2, 1, 1292, 'Create resident', NULL, 'Layog, Zhaira Gae Kimmayong', '2026-10-28 03:24:57', 'record', 'resident'),
(66, 1, 2, 1, 'Updated user role status', '0', '1', '2026-10-28 03:41:37', 'status', 'user role'),
(67, 1, 1, 5, 'Create personnel position', NULL, 'Punong Barangay', '2026-10-28 04:02:28', 'position_name', 'personnel_position'),
(68, 1, 1, 4, 'Create barangay personnel', NULL, 'Palunan, Soriano Abante', '2026-10-28 04:02:30', 'record', 'barangay_personnel'),
(69, 1, 1, 6, 'Create personnel position', NULL, 'Committee On Peace And Order', '2026-10-28 04:05:27', 'position_name', 'personnel_position'),
(70, 1, 1, 5, 'Create barangay personnel', NULL, 'Aniban, Ester', '2026-10-28 04:05:29', 'record', 'barangay_personnel'),
(71, 1, 1, 7, 'Create personnel position', NULL, 'Kagawad On Finance And Education', '2026-10-28 04:07:27', 'position_name', 'personnel_position'),
(72, 1, 1, 6, 'Create barangay personnel', NULL, 'Nayoda, Nelda Estilong', '2026-10-28 04:07:29', 'record', 'barangay_personnel'),
(73, 1, 1, 8, 'Create personnel position', NULL, 'Kagawad On Social Services', '2026-10-28 04:14:55', 'position_name', 'personnel_position'),
(74, 1, 1, 7, 'Create barangay personnel', NULL, 'Dangpa, Julia Cobebe', '2026-10-28 04:15:00', 'record', 'barangay_personnel'),
(75, 1, 2, 2, 'Updated barangay personnel birth date', '2000-08-04', '2002-07-04', '2026-10-28 04:17:02', 'birth date', 'barangay_personnel'),
(76, 1, 2, 3, 'Updated barangay personnel birth date', '1987-01-02', '1992-08-16', '2026-10-28 04:18:38', 'birth date', 'barangay_personnel'),
(77, 1, 1, 9, 'Create personnel position', NULL, 'President Of Solo Parent', '2026-10-28 21:24:53', 'position_name', 'personnel_position'),
(78, 1, 1, 8, 'Create barangay personnel', NULL, 'Compas, Marlyn C.', '2026-10-28 21:24:55', 'record', 'barangay_personnel'),
(79, 1, 2, 1, 'Updated user role status', '1', '0', '2026-10-28 21:41:18', 'status', 'user role'),
(80, 1, 2, 4, 'Updated user role status', '0', '1', '2026-10-28 21:41:24', 'status', 'user role');

-- --------------------------------------------------------

--
-- Table structure for table `audit_log_archive`
--

DROP TABLE IF EXISTS `audit_log_archive`;
CREATE TABLE IF NOT EXISTS `audit_log_archive` (
  `audit_id` int NOT NULL,
  `user_id` int NOT NULL,
  `action_id` int NOT NULL,
  `record_id` int NOT NULL,
  `description` varchar(255) NOT NULL,
  `old_value` varchar(45) DEFAULT NULL,
  `new_value` varchar(45) NOT NULL,
  `performed_at` datetime NOT NULL,
  `target` varchar(45) NOT NULL,
  `entity` varchar(45) NOT NULL,
  `archived_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `archived_by` int NOT NULL,
  PRIMARY KEY (`audit_id`),
  KEY `performed_at` (`performed_at`),
  KEY `audit_archive_action` (`action_id`),
  KEY `audit_archive_user` (`user_id`),
  KEY `audit_archive_archived_by` (`archived_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
  `sex_id` int NOT NULL,
  PRIMARY KEY (`personnel_id`),
  KEY `personnel_position` (`position_id`),
  KEY `personnel_status` (`personnel_status_id`),
  KEY `personnel_sex` (`sex_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `barangay_personnel`
--

INSERT INTO `barangay_personnel` (`personnel_id`, `position_id`, `personnel_last_name`, `personnel_first_name`, `personnel_middle_name`, `personnel_suffix`, `personnel_status_id`, `personnel_date_of_birth`, `sex_id`) VALUES
(1, 1, 'Salibad', 'Ellen', NULL, NULL, 1, '1986-08-21', 2),
(2, 2, 'Adawi', 'Laila', NULL, NULL, 1, '2002-07-04', 2),
(3, 4, 'Rullan', 'Deborah', NULL, NULL, 1, '1992-08-16', 2),
(4, 5, 'Palunan', 'Soriano', 'Abante', NULL, 1, '1958-03-19', 1),
(5, 6, 'Aniban', 'Ester', NULL, NULL, 1, '1983-10-15', 2),
(6, 7, 'Nayoda', 'Nelda', 'Estilong', NULL, 1, '1980-11-09', 2),
(7, 8, 'Dangpa', 'Julia', 'Cobebe', NULL, 1, '1973-05-31', 2),
(8, 9, 'Compas', 'Marlyn', 'C.', NULL, 1, '1970-10-28', 2);

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
-- Table structure for table `birth_attendant`
--

DROP TABLE IF EXISTS `birth_attendant`;
CREATE TABLE IF NOT EXISTS `birth_attendant` (
  `birth_attendant_id` int NOT NULL AUTO_INCREMENT,
  `birth_attendant` varchar(45) NOT NULL,
  PRIMARY KEY (`birth_attendant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `birth_attendant`
--

INSERT INTO `birth_attendant` (`birth_attendant_id`, `birth_attendant`) VALUES
(1, 'Doctor'),
(2, 'Nurse'),
(3, 'Midwife'),
(4, 'Hilot');

-- --------------------------------------------------------

--
-- Table structure for table `breed`
--

DROP TABLE IF EXISTS `breed`;
CREATE TABLE IF NOT EXISTS `breed` (
  `breed_id` int NOT NULL AUTO_INCREMENT,
  `breed` varchar(45) NOT NULL,
  `specie_id` int DEFAULT NULL,
  PRIMARY KEY (`breed_id`),
  KEY `breed_specie` (`specie_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `breed`
--

INSERT INTO `breed` (`breed_id`, `breed`, `specie_id`) VALUES
(1, 'Chihuahua', 1),
(2, 'Ragdoll', 2),
(6, 'Shih Tzu', 1),
(7, 'Golden Retriever', 1),
(8, 'Not Applicable', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `building_house_type`
--

DROP TABLE IF EXISTS `building_house_type`;
CREATE TABLE IF NOT EXISTS `building_house_type` (
  `building_house_type_id` int NOT NULL AUTO_INCREMENT,
  `building_house_type` varchar(45) NOT NULL,
  PRIMARY KEY (`building_house_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `building_house_type`
--

INSERT INTO `building_house_type` (`building_house_type_id`, `building_house_type`) VALUES
(1, 'Single house'),
(2, 'Duplex'),
(3, 'Multi-unit residential (three units or more)'),
(4, 'Commercial/industrial/agricultural'),
(5, 'Institutional living quarter (hotel, hospital'),
(6, 'Other housing units (boat, cave, others)');

-- --------------------------------------------------------

--
-- Table structure for table `certificate`
--

DROP TABLE IF EXISTS `certificate`;
CREATE TABLE IF NOT EXISTS `certificate` (
  `certificate_id` int NOT NULL AUTO_INCREMENT,
  `resident_id` int NOT NULL,
  `date_of_issue` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `purpose_of_request` varchar(255) NOT NULL,
  `type_of_certificate_id` int NOT NULL,
  `issuer_id` int NOT NULL,
  `representative_name` varchar(100) DEFAULT NULL,
  `representative_relationship` varchar(45) DEFAULT NULL,
  `date_of_death` date DEFAULT NULL,
  `include_income_amount` tinyint(1) NOT NULL DEFAULT '0',
  `stated_income_amount` decimal(10,2) DEFAULT NULL,
  `place_of_death` varchar(100) DEFAULT NULL,
  `cause_of_death` varchar(100) DEFAULT NULL,
  `ethnic_group` varchar(60) DEFAULT NULL,
  `resident_since_year` smallint DEFAULT NULL,
  `stated_civil_status` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`certificate_id`),
  KEY `requester` (`resident_id`),
  KEY `issuer` (`issuer_id`),
  KEY `certificate_type` (`type_of_certificate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `certificate_solo_parent`
--

DROP TABLE IF EXISTS `certificate_solo_parent`;
CREATE TABLE IF NOT EXISTS `certificate_solo_parent` (
  `certificate_id` int NOT NULL,
  `circumstance` varchar(120) NOT NULL,
  `president_name` varchar(100) NOT NULL,
  `president_address` varchar(255) NOT NULL,
  `neighbor_name` varchar(100) NOT NULL,
  `neighbor_address` varchar(255) NOT NULL,
  `kagawad_name` varchar(100) NOT NULL,
  PRIMARY KEY (`certificate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `certificate_solo_parent_child`
--

DROP TABLE IF EXISTS `certificate_solo_parent_child`;
CREATE TABLE IF NOT EXISTS `certificate_solo_parent_child` (
  `certificate_solo_parent_child_id` int NOT NULL AUTO_INCREMENT,
  `certificate_id` int NOT NULL,
  `child_name` varchar(100) NOT NULL,
  `date_of_birth` date NOT NULL,
  PRIMARY KEY (`certificate_solo_parent_child_id`),
  KEY `certificate_solo_parent_child_certificate` (`certificate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `child_hhm_died`
--

DROP TABLE IF EXISTS `child_hhm_died`;
CREATE TABLE IF NOT EXISTS `child_hhm_died` (
  `child_hhm_died_id` int NOT NULL AUTO_INCREMENT,
  `age` int NOT NULL,
  `cause_of_death` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `sex_id` int NOT NULL,
  `household_question_id` int NOT NULL,
  PRIMARY KEY (`child_hhm_died_id`),
  KEY `household_question3` (`household_question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clan`
--

DROP TABLE IF EXISTS `clan`;
CREATE TABLE IF NOT EXISTS `clan` (
  `clan_id` int NOT NULL AUTO_INCREMENT,
  `clan_name` varchar(45) NOT NULL,
  PRIMARY KEY (`clan_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `clan`
--

INSERT INTO `clan` (`clan_id`, `clan_name`) VALUES
(1, 'Siso'),
(2, 'Otinguey'),
(3, 'Paytocan'),
(4, 'Pitlongay'),
(5, 'Canuto'),
(6, 'Liwan');

-- --------------------------------------------------------

--
-- Table structure for table `common_disease`
--

DROP TABLE IF EXISTS `common_disease`;
CREATE TABLE IF NOT EXISTS `common_disease` (
  `common_disease_id` int NOT NULL,
  `household_question_id` int NOT NULL,
  KEY `common_diseases` (`common_disease_id`),
  KEY `household_question` (`household_question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `common_disease_cause_death_in_brgy`
--

DROP TABLE IF EXISTS `common_disease_cause_death_in_brgy`;
CREATE TABLE IF NOT EXISTS `common_disease_cause_death_in_brgy` (
  `common_disease_id` int NOT NULL AUTO_INCREMENT,
  `common_disease` varchar(45) NOT NULL,
  PRIMARY KEY (`common_disease_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `common_disease_cause_death_in_brgy`
--

INSERT INTO `common_disease_cause_death_in_brgy` (`common_disease_id`, `common_disease`) VALUES
(1, 'Dengue'),
(2, 'Tb'),
(3, 'Pneumonia');

-- --------------------------------------------------------

--
-- Table structure for table `community_tax_cert`
--

DROP TABLE IF EXISTS `community_tax_cert`;
CREATE TABLE IF NOT EXISTS `community_tax_cert` (
  `community_tax_cert` int NOT NULL AUTO_INCREMENT,
  `has_valid_ctc` tinyint(1) NOT NULL,
  `ctc_issued_here` tinyint(1) NOT NULL,
  `resident_id` int NOT NULL,
  PRIMARY KEY (`community_tax_cert`),
  KEY `residentctc` (`resident_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `construction_material_outer_wall`
--

DROP TABLE IF EXISTS `construction_material_outer_wall`;
CREATE TABLE IF NOT EXISTS `construction_material_outer_wall` (
  `construction_material_outer_wall_id` int NOT NULL AUTO_INCREMENT,
  `construction_material_outer_wall` varchar(45) NOT NULL,
  PRIMARY KEY (`construction_material_outer_wall_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `construction_material_outer_wall`
--

INSERT INTO `construction_material_outer_wall` (`construction_material_outer_wall_id`, `construction_material_outer_wall`) VALUES
(1, 'No walls'),
(2, 'Makeshift/salvaged/improvised material'),
(3, 'Glass'),
(4, 'Asbestos'),
(5, 'Bamboo/Sawali/Cogon/Nipa'),
(6, 'Galvanized iron/aluminum'),
(7, 'Half concrete/brick/stone and half wood'),
(8, 'Wood'),
(9, 'Concrete/brick/stone'),
(10, 'Others');

-- --------------------------------------------------------

--
-- Table structure for table `current_enrollment_status`
--

DROP TABLE IF EXISTS `current_enrollment_status`;
CREATE TABLE IF NOT EXISTS `current_enrollment_status` (
  `current_enrollment_status_id` int NOT NULL AUTO_INCREMENT,
  `current_enrollement_status` varchar(45) NOT NULL,
  PRIMARY KEY (`current_enrollment_status_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `current_enrollment_status`
--

INSERT INTO `current_enrollment_status` (`current_enrollment_status_id`, `current_enrollement_status`) VALUES
(1, 'Yes, public'),
(2, 'Yes, private'),
(3, 'No');

-- --------------------------------------------------------

--
-- Table structure for table `disability`
--

DROP TABLE IF EXISTS `disability`;
CREATE TABLE IF NOT EXISTS `disability` (
  `disability_id` int NOT NULL AUTO_INCREMENT,
  `disability` varchar(45) NOT NULL,
  PRIMARY KEY (`disability_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `disability`
--

INSERT INTO `disability` (`disability_id`, `disability`) VALUES
(1, 'Visual'),
(2, 'Physical'),
(3, 'Deaf'),
(4, 'Speech/Language Impairment'),
(5, 'Psychosocial'),
(6, 'None');

-- --------------------------------------------------------

--
-- Table structure for table `economic`
--

DROP TABLE IF EXISTS `economic`;
CREATE TABLE IF NOT EXISTS `economic` (
  `economic_id` int NOT NULL AUTO_INCREMENT,
  `monthly_income` decimal(10,2) DEFAULT NULL,
  `source_of_income_id` int DEFAULT NULL,
  `status_of_work_business_id` int DEFAULT NULL,
  `place_of_work_business` varchar(45) DEFAULT NULL,
  `resident_id` int NOT NULL,
  PRIMARY KEY (`economic_id`),
  KEY `resident_economic` (`resident_id`),
  KEY `source_of_income` (`source_of_income_id`),
  KEY `status_of_work_business` (`status_of_work_business_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `education`
--

DROP TABLE IF EXISTS `education`;
CREATE TABLE IF NOT EXISTS `education` (
  `education_id` int NOT NULL AUTO_INCREMENT,
  `resident_id` int NOT NULL,
  `highest_lvl_of_educ_id` int NOT NULL,
  `current_enrollement_status_id` int NOT NULL,
  `school_lvl_id` int NOT NULL,
  `place_of_school_brgy` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `place_of_school_city_municipality` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`education_id`),
  KEY `current_enrollment_status` (`current_enrollement_status_id`),
  KEY `highest_lvl_of_educ` (`highest_lvl_of_educ_id`),
  KEY `resident_education` (`resident_id`),
  KEY `school_lvl` (`school_lvl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ethnicity`
--

DROP TABLE IF EXISTS `ethnicity`;
CREATE TABLE IF NOT EXISTS `ethnicity` (
  `ethnicity_id` int NOT NULL AUTO_INCREMENT,
  `ethnicity` varchar(45) NOT NULL,
  PRIMARY KEY (`ethnicity_id`),
  UNIQUE KEY `uq_ethnicity` (`ethnicity`)
) ENGINE=InnoDB AUTO_INCREMENT=77 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `ethnicity`
--

INSERT INTO `ethnicity` (`ethnicity_id`, `ethnicity`) VALUES
(1, 'Applai'),
(2, 'Applai/Ibaloi'),
(3, 'Applai/Ibaloi/Ifugao'),
(4, 'Applai/Ilocano'),
(5, 'Ayangan'),
(6, 'Bago'),
(7, 'Bago/Kankanaey'),
(8, 'Balangao'),
(10, 'Bicolano'),
(11, 'Bicolano/Tagalog'),
(12, 'Bisaya'),
(13, 'Bisaya/Kankanaey'),
(14, 'Chamorro'),
(15, 'English'),
(16, 'Ibaloi'),
(41, 'Ibaloi/Applai'),
(42, 'Ibaloi/Applai/Ifugao/Kankanaey'),
(17, 'Ibaloi/Ayangan'),
(48, 'Ibaloi/Bisaya'),
(43, 'Ibaloi/Ifugao'),
(19, 'Ibaloi/Ifugao/Kankanaey'),
(44, 'Ibaloi/Ilocano'),
(20, 'Ibaloi/Kalanguya'),
(18, 'Ibaloi/Kankanaey'),
(46, 'Ibaloi/Pangasinan'),
(47, 'Ibaloi/Tagalog'),
(49, 'Ifugao'),
(50, 'Igorot'),
(51, 'Iliganon'),
(52, 'Ilocano'),
(54, 'Ilocano/Kalanguya'),
(55, 'Ilocano/Kankanaey'),
(53, 'Ilocano/Tagalog'),
(56, 'Ilonggo'),
(57, 'Isneg'),
(59, 'Itneg/Ibaloi'),
(60, 'Kalanguya'),
(9, 'Kankanaey'),
(61, 'Kankanaey/Kalanguya'),
(62, 'Kankanaey/Kalanguya/Isneg'),
(63, 'Kankanaey/Pangasinan'),
(65, 'Kankanaey/Sambal'),
(64, 'Kankanaey/Tagalog'),
(66, 'Kankanaey/Tuwali'),
(69, 'Kapampangan'),
(67, 'Lubo'),
(68, 'Maranao'),
(71, 'Pangasinan'),
(70, 'Pangasinan/Ilocano'),
(76, 'Pangasinan/Tagalog'),
(72, 'Tagalog'),
(73, 'Tingguian'),
(58, 'Tinguian'),
(74, 'Tuwali'),
(75, 'Waray');

-- --------------------------------------------------------

--
-- Table structure for table `facility_visited_past_12mos`
--

DROP TABLE IF EXISTS `facility_visited_past_12mos`;
CREATE TABLE IF NOT EXISTS `facility_visited_past_12mos` (
  `facility_visited_past_12mos_id` int NOT NULL AUTO_INCREMENT,
  `facility_visited_past_12mos` varchar(45) NOT NULL,
  PRIMARY KEY (`facility_visited_past_12mos_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `facility_visited_past_12mos`
--

INSERT INTO `facility_visited_past_12mos` (`facility_visited_past_12mos_id`, `facility_visited_past_12mos`) VALUES
(1, 'Government hospital'),
(2, 'RHU/Health center'),
(3, 'Brgy. Health Station'),
(4, 'Private hospital'),
(5, 'Private clinic'),
(6, 'Pharmacy'),
(7, 'Hilot/Herbalist'),
(8, 'None');

-- --------------------------------------------------------

--
-- Table structure for table `facility_visit_reason`
--

DROP TABLE IF EXISTS `facility_visit_reason`;
CREATE TABLE IF NOT EXISTS `facility_visit_reason` (
  `facility_visit_reason_id` int NOT NULL AUTO_INCREMENT,
  `facility_visit_reason` varchar(45) NOT NULL,
  PRIMARY KEY (`facility_visit_reason_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `facility_visit_reason`
--

INSERT INTO `facility_visit_reason` (`facility_visit_reason_id`, `facility_visit_reason`) VALUES
(1, 'Sick/Injured'),
(2, 'Prenatal/Postnatal'),
(3, 'Gave birth'),
(4, 'Dental'),
(5, 'Medical check-up'),
(6, 'Medical requirement'),
(7, 'NHTS/CCT/4PS requirement'),
(8, 'Not Applicable');

-- --------------------------------------------------------

--
-- Table structure for table `family_planning_method`
--

DROP TABLE IF EXISTS `family_planning_method`;
CREATE TABLE IF NOT EXISTS `family_planning_method` (
  `family_planning_method_id` int NOT NULL AUTO_INCREMENT,
  `family_planning_method` varchar(45) NOT NULL,
  PRIMARY KEY (`family_planning_method_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `family_planning_method`
--

INSERT INTO `family_planning_method` (`family_planning_method_id`, `family_planning_method`) VALUES
(1, 'Female sterilization/Ligation'),
(2, 'Male sterilization/vasectomy'),
(3, 'IUD'),
(4, 'Injectables'),
(5, 'Implants'),
(6, 'Pill'),
(7, 'Condom'),
(8, 'Modern natural FP'),
(9, 'Lactational Amenorrhea Method (LAM)'),
(10, 'Traditional'),
(11, 'None');

-- --------------------------------------------------------

--
-- Table structure for table `female_hhm_died`
--

DROP TABLE IF EXISTS `female_hhm_died`;
CREATE TABLE IF NOT EXISTS `female_hhm_died` (
  `female_hhm_died_id` int NOT NULL AUTO_INCREMENT,
  `age` int NOT NULL,
  `cause_of_death` varchar(45) NOT NULL,
  `household_question_id` int NOT NULL,
  PRIMARY KEY (`female_hhm_died_id`),
  KEY `household_question2` (`household_question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fuel_type`
--

DROP TABLE IF EXISTS `fuel_type`;
CREATE TABLE IF NOT EXISTS `fuel_type` (
  `fuel_type_id` int NOT NULL AUTO_INCREMENT,
  `fuel_type` varchar(45) NOT NULL,
  PRIMARY KEY (`fuel_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `fuel_type`
--

INSERT INTO `fuel_type` (`fuel_type_id`, `fuel_type`) VALUES
(1, 'Oil (vegetables, animal, others)'),
(2, 'Liquefied petroleum gas (LGP)'),
(3, 'Kerosene (gaas)'),
(4, 'Electricity'),
(5, 'Others');

-- --------------------------------------------------------

--
-- Table structure for table `health`
--

DROP TABLE IF EXISTS `health`;
CREATE TABLE IF NOT EXISTS `health` (
  `health_id` int NOT NULL AUTO_INCREMENT,
  `health_insurance_id` int NOT NULL,
  `facility_visited_past_12mos_id` int NOT NULL,
  `facility_visit_reason_id` int NOT NULL,
  `disability_id` int NOT NULL,
  `pwd_id_number` int DEFAULT NULL,
  `resident_id` int NOT NULL,
  PRIMARY KEY (`health_id`),
  KEY `disability` (`disability_id`),
  KEY `facility_visited` (`facility_visited_past_12mos_id`),
  KEY `facility_visit_reason` (`facility_visit_reason_id`),
  KEY `health_insurance` (`health_insurance_id`),
  KEY `resident_health` (`resident_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `health_insurance`
--

DROP TABLE IF EXISTS `health_insurance`;
CREATE TABLE IF NOT EXISTS `health_insurance` (
  `health_insurance_id` int NOT NULL AUTO_INCREMENT,
  `health_insurance` varchar(45) NOT NULL,
  PRIMARY KEY (`health_insurance_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `health_insurance`
--

INSERT INTO `health_insurance` (`health_insurance_id`, `health_insurance`) VALUES
(1, 'PhilHealth paying member'),
(2, 'PhilHealth dependent of paying member'),
(3, 'PhilHealth indigent member'),
(4, 'PhilHealth dependent of indigent member'),
(5, 'GSIS'),
(6, 'SSS'),
(7, 'Private/HMO'),
(8, 'None');

-- --------------------------------------------------------

--
-- Table structure for table `highest_lvl_of_educ`
--

DROP TABLE IF EXISTS `highest_lvl_of_educ`;
CREATE TABLE IF NOT EXISTS `highest_lvl_of_educ` (
  `highest_lvl_of_educ_id` int NOT NULL AUTO_INCREMENT,
  `lvl_of_educ` varchar(45) NOT NULL,
  PRIMARY KEY (`highest_lvl_of_educ_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `highest_lvl_of_educ`
--

INSERT INTO `highest_lvl_of_educ` (`highest_lvl_of_educ_id`, `lvl_of_educ`) VALUES
(1, 'No education'),
(2, 'Pre-school'),
(3, 'Elementary level'),
(4, 'Elementary graduate'),
(5, 'High school level'),
(6, 'High school graduate'),
(7, 'Junior HS'),
(8, 'Junior HS graduate'),
(9, 'Senior HS level'),
(10, 'Senior HS graduate'),
(11, 'Vocational/Tech'),
(12, 'College level'),
(13, 'College graduate'),
(14, 'Post-graduate');

-- --------------------------------------------------------

--
-- Table structure for table `household`
--

DROP TABLE IF EXISTS `household`;
CREATE TABLE IF NOT EXISTS `household` (
  `household_id` int NOT NULL AUTO_INCREMENT,
  `clan_id` int DEFAULT NULL,
  `head_resident_id` int DEFAULT NULL,
  `street_id` int NOT NULL,
  `number_of_house_story` int DEFAULT NULL,
  `number_of_basement_level` int DEFAULT NULL,
  `house_number` varchar(45) DEFAULT NULL,
  `registration_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `household_status_id` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`household_id`),
  UNIQUE KEY `uq_address` (`street_id`,`house_number`),
  KEY `house_street` (`street_id`),
  KEY `house_clan` (`clan_id`),
  KEY `house_status` (`household_status_id`),
  KEY `house_head` (`head_resident_id`)
) ENGINE=InnoDB AUTO_INCREMENT=325 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `household`
--

INSERT INTO `household` (`household_id`, `clan_id`, `head_resident_id`, `street_id`, `number_of_house_story`, `number_of_basement_level`, `house_number`, `registration_date`, `household_status_id`) VALUES
(1, NULL, 1, 1, NULL, NULL, '204', '2026-10-28 02:24:53', 1),
(2, NULL, 2, 1, NULL, NULL, '190', '2026-10-28 02:24:53', 1),
(3, NULL, 8, 1, NULL, NULL, '162', '2026-10-28 02:24:53', 1),
(4, NULL, 9, 1, NULL, NULL, '139', '2026-10-28 02:24:53', 1),
(5, NULL, 10, 1, NULL, NULL, '90', '2026-10-28 02:24:53', 1),
(6, NULL, 11, 1, NULL, NULL, '130', '2026-10-28 02:24:53', 1),
(7, NULL, 16, 1, NULL, NULL, '148', '2026-10-28 02:24:53', 1),
(8, NULL, 17, 1, NULL, NULL, '280', '2026-10-28 02:24:53', 1),
(9, NULL, 18, 1, NULL, NULL, '179', '2026-10-28 02:24:53', 1),
(10, NULL, 22, 1, NULL, NULL, '253', '2026-10-28 02:24:53', 1),
(11, NULL, 25, 1, NULL, NULL, '33', '2026-10-28 02:24:53', 1),
(12, NULL, 26, 1, NULL, NULL, '46', '2026-10-28 02:24:53', 1),
(13, NULL, 30, 1, NULL, NULL, '223', '2026-10-28 02:24:53', 1),
(14, NULL, 31, 1, NULL, NULL, '230', '2026-10-28 02:24:53', 1),
(15, NULL, 32, 1, NULL, NULL, '13', '2026-10-28 02:24:53', 1),
(16, NULL, 33, 1, NULL, NULL, '183', '2026-10-28 02:24:53', 1),
(17, NULL, 37, 1, NULL, NULL, '284', '2026-10-28 02:24:53', 1),
(18, NULL, 38, 1, NULL, NULL, '143', '2026-10-28 02:24:53', 1),
(19, NULL, 40, 1, NULL, NULL, '87', '2026-10-28 02:24:53', 1),
(20, NULL, 44, 1, NULL, NULL, '103', '2026-10-28 02:24:53', 1),
(21, NULL, 45, 1, NULL, NULL, '104', '2026-10-28 02:24:53', 1),
(22, NULL, 46, 1, NULL, NULL, '156', '2026-10-28 02:24:53', 1),
(23, NULL, 50, 1, NULL, NULL, '74', '2026-10-28 02:24:53', 1),
(24, NULL, 53, 1, NULL, NULL, '305', '2026-10-28 02:24:53', 1),
(25, NULL, 54, 1, NULL, NULL, '292', '2026-10-28 02:24:53', 1),
(26, NULL, 57, 1, NULL, NULL, '243', '2026-10-28 02:24:53', 1),
(27, NULL, 58, 1, NULL, NULL, '250', '2026-10-28 02:24:53', 1),
(28, NULL, 59, 1, NULL, NULL, '246', '2026-10-28 02:24:53', 1),
(29, NULL, 64, 1, NULL, NULL, '345', '2026-10-28 02:24:53', 1),
(30, NULL, 67, 1, NULL, NULL, '274', '2026-10-28 02:24:53', 1),
(31, NULL, 68, 1, NULL, NULL, '282', '2026-10-28 02:24:53', 1),
(32, NULL, 69, 1, NULL, NULL, '256', '2026-10-28 02:24:53', 1),
(33, NULL, 70, 1, NULL, NULL, '30', '2026-10-28 02:24:53', 1),
(34, NULL, 71, 1, NULL, NULL, '185', '2026-10-28 02:24:53', 1),
(35, NULL, 76, 1, NULL, NULL, '146', '2026-10-28 02:24:53', 1),
(36, NULL, 77, 1, NULL, NULL, '264', '2026-10-28 02:24:53', 1),
(37, NULL, 78, 1, NULL, NULL, '142', '2026-10-28 02:24:53', 1),
(38, NULL, 79, 1, NULL, NULL, '23', '2026-10-28 02:24:53', 1),
(39, NULL, 81, 1, NULL, NULL, '145', '2026-10-28 02:24:53', 1),
(40, NULL, 82, 1, NULL, NULL, '281', '2026-10-28 02:24:53', 1),
(41, NULL, 83, 1, NULL, NULL, '15', '2026-10-28 02:24:53', 1),
(42, NULL, 90, 1, NULL, NULL, '285', '2026-10-28 02:24:53', 1),
(43, NULL, 95, 1, NULL, NULL, '25', '2026-10-28 02:24:53', 1),
(44, NULL, 101, 1, NULL, NULL, '35', '2026-10-28 02:24:53', 1),
(45, NULL, 102, 1, NULL, NULL, '200', '2026-10-28 02:24:53', 1),
(46, NULL, 111, 1, NULL, NULL, '20,21,283', '2026-10-28 02:24:53', 1),
(47, NULL, 112, 1, NULL, NULL, '249', '2026-10-28 02:24:53', 1),
(48, NULL, 118, 1, NULL, NULL, '338', '2026-10-28 02:24:53', 1),
(49, NULL, 119, 1, NULL, NULL, '214', '2026-10-28 02:24:53', 1),
(50, NULL, 122, 1, NULL, NULL, '216', '2026-10-28 02:24:53', 1),
(51, NULL, 123, 1, NULL, NULL, '215', '2026-10-28 02:24:53', 1),
(52, NULL, 124, 1, NULL, NULL, '213', '2026-10-28 02:24:53', 1),
(53, NULL, 127, 1, NULL, NULL, '45', '2026-10-28 02:24:53', 1),
(54, NULL, 128, 1, NULL, NULL, '18', '2026-10-28 02:24:53', 1),
(55, NULL, 129, 1, NULL, NULL, '38', '2026-10-28 02:24:53', 1),
(56, NULL, 133, 1, NULL, NULL, '40', '2026-10-28 02:24:53', 1),
(57, NULL, 137, 1, NULL, NULL, '295', '2026-10-28 02:24:53', 1),
(58, NULL, 141, 1, NULL, NULL, '96', '2026-10-28 02:24:53', 1),
(59, 3, 147, 1, NULL, NULL, '66', '2026-10-28 02:24:53', 1),
(60, NULL, 150, 1, NULL, NULL, '65', '2026-10-28 02:24:53', 1),
(61, NULL, 154, 1, NULL, NULL, '329', '2026-10-28 02:24:53', 1),
(62, NULL, 156, 1, NULL, NULL, '275', '2026-10-28 02:24:53', 1),
(63, NULL, 157, 1, NULL, NULL, '218', '2026-10-28 02:24:53', 1),
(64, NULL, 158, 1, NULL, NULL, '106-B', '2026-10-28 02:24:53', 1),
(65, NULL, 161, 1, NULL, NULL, '161', '2026-10-28 02:24:53', 1),
(66, NULL, 166, 1, NULL, NULL, '181', '2026-10-28 02:24:53', 2),
(67, NULL, 167, 1, NULL, NULL, '175', '2026-10-28 02:24:53', 1),
(68, NULL, 173, 1, NULL, NULL, '135', '2026-10-28 02:24:53', 1),
(69, NULL, 174, 1, NULL, NULL, '49', '2026-10-28 02:24:53', 1),
(70, NULL, 177, 1, NULL, NULL, '289', '2026-10-28 02:24:53', 1),
(71, NULL, 178, 1, NULL, NULL, '22/23', '2026-10-28 02:24:53', 1),
(72, NULL, 179, 1, NULL, NULL, '22', '2026-10-28 02:24:53', 1),
(73, NULL, 183, 1, NULL, NULL, '205', '2026-10-28 02:24:53', 1),
(74, NULL, 189, 1, NULL, NULL, '178', '2026-10-28 02:24:53', 1),
(75, NULL, 193, 1, NULL, NULL, '206', '2026-10-28 02:24:53', 1),
(76, NULL, 197, 1, NULL, NULL, '85', '2026-10-28 02:24:53', 1),
(77, NULL, 198, 1, NULL, NULL, '99', '2026-10-28 02:24:53', 1),
(78, NULL, 205, 1, NULL, NULL, '245', '2026-10-28 02:24:53', 1),
(79, NULL, 210, 1, NULL, NULL, '268', '2026-10-28 02:24:53', 1),
(80, NULL, 215, 1, NULL, NULL, '231', '2026-10-28 02:24:53', 1),
(81, NULL, 218, 1, NULL, NULL, '294', '2026-10-28 02:24:53', 1),
(82, NULL, 219, 1, NULL, NULL, '173', '2026-10-28 02:24:53', 1),
(83, NULL, 224, 1, NULL, NULL, '69', '2026-10-28 02:24:53', 1),
(84, NULL, 229, 1, NULL, NULL, '70', '2026-10-28 02:24:53', 1),
(85, NULL, 232, 1, NULL, NULL, '273', '2026-10-28 02:24:53', 1),
(86, NULL, 236, 1, NULL, NULL, '191', '2026-10-28 02:24:53', 1),
(87, NULL, 237, 1, NULL, NULL, '120', '2026-10-28 02:24:53', 1),
(88, NULL, 238, 1, NULL, NULL, '116', '2026-10-28 02:24:53', 1),
(89, NULL, 239, 1, NULL, NULL, '137', '2026-10-28 02:24:53', 1),
(90, NULL, 240, 1, NULL, NULL, '170', '2026-10-28 02:24:53', 1),
(91, NULL, 252, 1, NULL, NULL, '154', '2026-10-28 02:24:53', 1),
(92, NULL, 269, 1, NULL, NULL, '64', '2026-10-28 02:24:53', 1),
(93, NULL, 259, 1, NULL, NULL, '157', '2026-10-28 02:24:53', 1),
(94, NULL, 260, 1, NULL, NULL, '48&90', '2026-10-28 02:24:53', 1),
(95, NULL, 261, 1, NULL, NULL, '167', '2026-10-28 02:24:53', 1),
(96, NULL, 262, 1, NULL, NULL, '168', '2026-10-28 02:24:53', 1),
(97, NULL, 263, 1, NULL, NULL, '27', '2026-10-28 02:24:53', 1),
(98, NULL, 264, 1, NULL, NULL, '76', '2026-10-28 02:24:53', 1),
(99, NULL, 268, 1, NULL, NULL, '67', '2026-10-28 02:24:53', 1),
(100, NULL, 270, 1, NULL, NULL, '172', '2026-10-28 02:24:53', 1),
(101, NULL, 271, 1, NULL, NULL, '312', '2026-10-28 02:24:53', 1),
(102, NULL, 276, 1, NULL, NULL, '255', '2026-10-28 02:24:53', 1),
(103, NULL, 281, 1, NULL, NULL, '71', '2026-10-28 02:24:53', 1),
(104, NULL, 282, 1, NULL, NULL, '330', '2026-10-28 02:24:53', 1),
(105, NULL, 284, 1, NULL, NULL, '331', '2026-10-28 02:24:53', 1),
(106, NULL, 285, 1, NULL, NULL, '332', '2026-10-28 02:24:53', 1),
(107, 3, 289, 1, NULL, NULL, '303', '2026-10-28 02:24:53', 1),
(108, NULL, 292, 1, NULL, NULL, '197', '2026-10-28 02:24:53', 1),
(109, NULL, 296, 1, NULL, NULL, '54', '2026-10-28 02:24:53', 1),
(110, NULL, 301, 1, NULL, NULL, '269', '2026-10-28 02:24:53', 1),
(111, NULL, 324, 1, NULL, NULL, '227', '2026-10-28 02:24:53', 1),
(112, NULL, 308, 1, NULL, NULL, '336', '2026-10-28 02:24:53', 1),
(113, NULL, 494, 1, NULL, NULL, '28', '2026-10-28 02:24:53', 1),
(114, NULL, 314, 1, NULL, NULL, '91', '2026-10-28 02:24:53', 1),
(115, NULL, 321, 1, NULL, NULL, '318', '2026-10-28 02:24:53', 1),
(116, NULL, 329, 1, NULL, NULL, '131', '2026-10-28 02:24:53', 1),
(117, NULL, 334, 1, NULL, NULL, '236', '2026-10-28 02:24:53', 1),
(118, NULL, 351, 1, NULL, NULL, '306', '2026-10-28 02:24:53', 1),
(119, NULL, 352, 1, NULL, NULL, '222', '2026-10-28 02:24:53', 1),
(120, NULL, 358, 1, NULL, NULL, '211', '2026-10-28 02:24:53', 1),
(121, NULL, 359, 1, NULL, NULL, '234', '2026-10-28 02:24:53', 1),
(122, NULL, 362, 1, NULL, NULL, '208', '2026-10-28 02:24:53', 1),
(123, NULL, 365, 1, NULL, NULL, '210', '2026-10-28 02:24:53', 1),
(124, NULL, 368, 1, NULL, NULL, '196', '2026-10-28 02:24:53', 1),
(125, NULL, 371, 1, NULL, NULL, '34', '2026-10-28 02:24:53', 1),
(126, NULL, 379, 1, NULL, NULL, '239', '2026-10-28 02:24:53', 1),
(127, NULL, 476, 1, NULL, NULL, '192', '2026-10-28 02:24:53', 1),
(128, NULL, 384, 1, NULL, NULL, '48', '2026-10-28 02:24:53', 1),
(129, NULL, 391, 1, NULL, NULL, '58', '2026-10-28 02:24:53', 1),
(130, NULL, 395, 1, NULL, NULL, '108', '2026-10-28 02:24:53', 1),
(131, NULL, 400, 1, NULL, NULL, '233', '2026-10-28 02:24:53', 1),
(132, NULL, 404, 1, NULL, NULL, '199', '2026-10-28 02:24:53', 1),
(133, 5, 409, 1, NULL, NULL, '201', '2026-10-28 02:24:53', 1),
(134, 5, 414, 1, NULL, NULL, '202', '2026-10-28 02:24:53', 1),
(135, 5, 422, 1, NULL, NULL, '212', '2026-10-28 02:24:53', 1),
(136, NULL, 429, 1, NULL, NULL, '113', '2026-10-28 02:24:53', 1),
(137, NULL, 431, 1, NULL, NULL, '195', '2026-10-28 02:24:53', 1),
(138, NULL, 433, 1, NULL, NULL, '184', '2026-10-28 02:24:53', 1),
(139, NULL, 435, 1, NULL, NULL, '182', '2026-10-28 02:24:53', 1),
(140, NULL, 436, 1, NULL, NULL, '203', '2026-10-28 02:24:53', 1),
(141, NULL, 440, 1, NULL, NULL, '152', '2026-10-28 02:24:53', 1),
(142, NULL, 443, 1, NULL, NULL, '296', '2026-10-28 02:24:53', 1),
(143, NULL, 449, 1, NULL, NULL, '240', '2026-10-28 02:24:53', 1),
(144, NULL, 455, 1, NULL, NULL, '36', '2026-10-28 02:24:53', 1),
(145, NULL, 464, 1, NULL, NULL, '41', '2026-10-28 02:24:53', 1),
(146, NULL, 474, 1, NULL, NULL, '101', '2026-10-28 02:24:53', 1),
(147, NULL, 480, 1, NULL, NULL, '344', '2026-10-28 02:24:53', 1),
(148, NULL, 487, 1, NULL, NULL, '242', '2026-10-28 02:24:53', 1),
(149, NULL, 489, 1, NULL, NULL, '361', '2026-10-28 02:24:53', 1),
(150, NULL, 500, 1, NULL, NULL, '229', '2026-10-28 02:24:53', 1),
(151, NULL, 506, 1, NULL, NULL, '10', '2026-10-28 02:24:53', 1),
(152, NULL, 514, 1, NULL, NULL, '276', '2026-10-28 02:24:53', 1),
(153, NULL, 519, 1, NULL, NULL, '337', '2026-10-28 02:24:53', 1),
(154, NULL, 521, 1, NULL, NULL, '372', '2026-10-28 02:24:53', 1),
(155, NULL, 523, 1, NULL, NULL, '342', '2026-10-28 02:24:53', 1),
(156, NULL, 531, 1, NULL, NULL, '301', '2026-10-28 02:24:53', 1),
(157, NULL, 536, 1, NULL, NULL, '418', '2026-10-28 02:24:53', 1),
(158, NULL, 538, 1, NULL, NULL, '340', '2026-10-28 02:24:53', 1),
(159, NULL, 539, 1, NULL, NULL, '341', '2026-10-28 02:24:53', 1),
(160, NULL, 540, 1, NULL, NULL, '235', '2026-10-28 02:24:53', 1),
(161, NULL, 544, 1, NULL, NULL, '288', '2026-10-28 02:24:53', 1),
(162, NULL, 552, 1, NULL, NULL, '272', '2026-10-28 02:24:53', 1),
(163, NULL, 556, 1, NULL, NULL, '105', '2026-10-28 02:24:53', 1),
(164, 5, 560, 1, NULL, NULL, '106', '2026-10-28 02:24:53', 1),
(165, NULL, 563, 1, NULL, NULL, '109', '2026-10-28 02:24:53', 1),
(166, NULL, 568, 1, NULL, NULL, '24', '2026-10-28 02:24:53', 1),
(167, NULL, 575, 1, NULL, NULL, '83', '2026-10-28 02:24:53', 1),
(168, NULL, 576, 2, NULL, NULL, '50', '2026-10-28 02:24:53', 1),
(169, NULL, 581, 2, NULL, NULL, '45', '2026-10-28 02:24:53', 1),
(170, 3, 585, 2, NULL, NULL, '25', '2026-10-28 02:24:53', 1),
(171, NULL, 586, 2, NULL, NULL, '46', '2026-10-28 02:24:53', 1),
(172, 3, 595, 2, NULL, NULL, '80', '2026-10-28 02:24:53', 1),
(173, NULL, 602, 2, NULL, NULL, '6', '2026-10-28 02:24:53', 1),
(174, NULL, 609, 2, NULL, NULL, '14', '2026-10-28 02:24:53', 1),
(175, NULL, 610, 2, NULL, NULL, '105', '2026-10-28 02:24:53', 1),
(176, NULL, 615, 2, NULL, NULL, '51', '2026-10-28 02:24:53', 1),
(177, NULL, 616, 2, NULL, NULL, '11', '2026-10-28 02:24:53', 1),
(178, NULL, 622, 2, NULL, NULL, '16', '2026-10-28 02:24:53', 1),
(179, NULL, 627, 2, NULL, NULL, '367', '2026-10-28 02:24:53', 1),
(180, 5, 632, 2, NULL, NULL, '22', '2026-10-28 02:24:53', 1),
(181, NULL, 636, 2, NULL, NULL, '17', '2026-10-28 02:24:53', 1),
(182, NULL, 642, 2, NULL, NULL, '19', '2026-10-28 02:24:53', 1),
(183, NULL, 643, 2, NULL, NULL, '21', '2026-10-28 02:24:53', 1),
(184, NULL, 648, 2, NULL, NULL, '84', '2026-10-28 02:24:53', 1),
(185, 4, 651, 2, NULL, NULL, '39', '2026-10-28 02:24:53', 1),
(186, 4, 658, 2, NULL, NULL, '40', '2026-10-28 02:24:53', 1),
(187, 4, 659, 2, NULL, NULL, '59', '2026-10-28 02:24:53', 1),
(188, NULL, 667, 2, NULL, NULL, '88', '2026-10-28 02:24:53', 1),
(189, NULL, 669, 2, NULL, NULL, '41', '2026-10-28 02:24:53', 1),
(190, NULL, 673, 2, NULL, NULL, '20', '2026-10-28 02:24:53', 1),
(191, NULL, 677, 2, NULL, NULL, '67', '2026-10-28 02:24:53', 1),
(192, 4, 681, 2, NULL, NULL, '37', '2026-10-28 02:24:53', 1),
(193, NULL, 687, 2, NULL, NULL, '319', '2026-10-28 02:24:53', 1),
(194, NULL, 688, 2, NULL, NULL, '61', '2026-10-28 02:24:53', 1),
(195, NULL, 692, 2, NULL, NULL, '53', '2026-10-28 02:24:53', 1),
(196, NULL, 693, 2, NULL, NULL, '62', '2026-10-28 02:24:53', 1),
(197, NULL, 698, 2, NULL, NULL, '92', '2026-10-28 02:24:53', 1),
(198, NULL, 702, 2, NULL, NULL, '69', '2026-10-28 02:24:53', 1),
(199, NULL, 708, 2, NULL, NULL, '55', '2026-10-28 02:24:53', 1),
(200, NULL, 712, 2, NULL, NULL, '135', '2026-10-28 02:24:53', 1),
(201, NULL, 718, 2, NULL, NULL, '76', '2026-10-28 02:24:53', 1),
(202, NULL, 721, 2, NULL, NULL, '86', '2026-10-28 02:24:53', 1),
(203, NULL, 725, 2, NULL, NULL, '13', '2026-10-28 02:24:53', 1),
(204, NULL, 731, 2, NULL, NULL, '27', '2026-10-28 02:24:53', 1),
(205, NULL, 737, 2, NULL, NULL, '48', '2026-10-28 02:24:53', 1),
(206, NULL, 740, 2, NULL, NULL, '100', '2026-10-28 02:24:53', 1),
(207, NULL, 741, 2, NULL, NULL, '7', '2026-10-28 02:24:53', 1),
(208, NULL, 742, 2, NULL, NULL, '113', '2026-10-28 02:24:53', 1),
(209, NULL, 746, 2, NULL, NULL, '57', '2026-10-28 02:24:53', 1),
(210, NULL, 750, 2, NULL, NULL, '56', '2026-10-28 02:24:53', 1),
(211, NULL, 751, 2, NULL, NULL, '77', '2026-10-28 02:24:53', 1),
(212, NULL, 752, 2, NULL, NULL, '10', '2026-10-28 02:24:53', 1),
(213, 3, 759, 2, NULL, NULL, '52', '2026-10-28 02:24:53', 1),
(214, 3, 763, 2, NULL, NULL, '2', '2026-10-28 02:24:53', 1),
(215, 3, 768, 2, NULL, NULL, '1', '2026-10-28 02:24:53', 1),
(216, 3, 771, 2, NULL, NULL, '44', '2026-10-28 02:24:53', 1),
(217, 3, 775, 2, NULL, NULL, '18', '2026-10-28 02:24:53', 1),
(218, NULL, 781, 2, NULL, NULL, '12', '2026-10-28 02:24:53', 1),
(219, 4, 785, 2, NULL, NULL, '102', '2026-10-28 02:24:53', 1),
(220, 4, 786, 2, NULL, NULL, '103', '2026-10-28 02:24:53', 1),
(221, 4, 787, 2, NULL, NULL, '65', '2026-10-28 02:24:53', 1),
(222, 3, 794, 2, NULL, NULL, '82', '2026-10-28 02:24:53', 1),
(223, NULL, 796, 2, NULL, NULL, '3', '2026-10-28 02:24:53', 1),
(224, NULL, 801, 2, NULL, NULL, '98', '2026-10-28 02:24:53', 1),
(225, NULL, 805, 2, NULL, NULL, '68', '2026-10-28 02:24:53', 1),
(226, NULL, 810, 2, NULL, NULL, '15', '2026-10-28 02:24:53', 1),
(227, NULL, 1096, 3, NULL, NULL, '5', '2026-10-28 02:24:53', 1),
(228, NULL, 816, 3, NULL, NULL, '10', '2026-10-28 02:24:53', 1),
(229, 5, 820, 3, NULL, NULL, '51', '2026-10-28 02:24:53', 1),
(230, NULL, 828, 3, NULL, NULL, '73', '2026-10-28 02:24:53', 1),
(231, NULL, 825, 3, NULL, NULL, '193', '2026-10-28 02:24:53', 1),
(232, NULL, 829, 3, NULL, NULL, '72', '2026-10-28 02:24:53', 1),
(233, NULL, 827, 3, NULL, NULL, '75', '2026-10-28 02:24:53', 1),
(234, NULL, 834, 3, NULL, NULL, '90', '2026-10-28 02:24:53', 1),
(235, NULL, 839, 3, NULL, NULL, '110', '2026-10-28 02:24:53', 1),
(236, NULL, 845, 3, NULL, NULL, '155', '2026-10-28 02:24:53', 1),
(237, NULL, 851, 3, NULL, NULL, '99', '2026-10-28 02:24:53', 1),
(238, NULL, 856, 3, NULL, NULL, '15A', '2026-10-28 02:24:53', 1),
(239, NULL, 862, 3, NULL, NULL, '120', '2026-10-28 02:24:53', 1),
(240, NULL, 868, 3, NULL, NULL, '181', '2026-10-28 02:24:53', 1),
(241, NULL, 874, 3, NULL, NULL, '134', '2026-10-28 02:24:53', 1),
(242, NULL, 878, 3, NULL, NULL, '82', '2026-10-28 02:24:53', 1),
(243, NULL, 881, 3, NULL, NULL, '111', '2026-10-28 02:24:53', 1),
(244, NULL, 885, 3, NULL, NULL, '74', '2026-10-28 02:24:53', 1),
(245, NULL, 886, 3, NULL, NULL, '189', '2026-10-28 02:24:53', 1),
(246, NULL, 896, 3, NULL, NULL, '69', '2026-10-28 02:24:53', 1),
(247, NULL, 901, 3, NULL, NULL, '162', '2026-10-28 02:24:53', 1),
(248, 5, 905, 3, NULL, NULL, '101', '2026-10-28 02:24:53', 1),
(249, 5, 906, 3, NULL, NULL, '54', '2026-10-28 02:24:53', 1),
(250, NULL, 914, 3, NULL, NULL, '121', '2026-10-28 02:24:53', 1),
(251, NULL, 920, 3, NULL, NULL, '159', '2026-10-28 02:24:53', 1),
(252, 5, 925, 3, NULL, NULL, '192', '2026-10-28 02:24:53', 1),
(253, 5, 930, 3, NULL, NULL, '50', '2026-10-28 02:24:53', 1),
(254, 5, 938, 3, NULL, NULL, '26', '2026-10-28 02:24:53', 1),
(255, NULL, 942, 3, NULL, NULL, '80', '2026-10-28 02:24:53', 1),
(256, NULL, 947, 3, NULL, NULL, '141', '2026-10-28 02:24:53', 1),
(257, NULL, 953, 3, NULL, NULL, '137', '2026-10-28 02:24:53', 1),
(258, NULL, 1049, 3, NULL, NULL, '2', '2026-10-28 02:24:53', 1),
(259, NULL, 961, 3, NULL, NULL, '19', '2026-10-28 02:24:53', 1),
(260, NULL, 962, 3, NULL, NULL, '105', '2026-10-28 02:24:53', 1),
(261, NULL, 965, 3, NULL, NULL, '83', '2026-10-28 02:24:53', 1),
(262, NULL, 972, 3, NULL, NULL, '102', '2026-10-28 02:24:53', 1),
(263, NULL, 1080, 3, NULL, NULL, '154', '2026-10-28 02:24:53', 1),
(264, NULL, 986, 3, NULL, NULL, '106', '2026-10-28 02:24:53', 1),
(265, NULL, 994, 3, NULL, NULL, '164', '2026-10-28 02:24:53', 1),
(266, NULL, 997, 3, NULL, NULL, '204', '2026-10-28 02:24:53', 1),
(267, NULL, 1102, 3, NULL, NULL, '44', '2026-10-28 02:24:53', 1),
(268, NULL, 1002, 3, NULL, NULL, '55', '2026-10-28 02:24:53', 1),
(269, NULL, 1006, 3, NULL, NULL, '89', '2026-10-28 02:24:53', 1),
(270, NULL, 1022, 3, NULL, NULL, '67', '2026-10-28 02:24:53', 1),
(271, NULL, 1026, 3, NULL, NULL, '124', '2026-10-28 02:24:53', 1),
(272, NULL, 1033, 3, NULL, NULL, '114', '2026-10-28 02:24:53', 1),
(273, NULL, 1038, 3, NULL, NULL, '3', '2026-10-28 02:24:53', 1),
(274, NULL, 1050, 3, NULL, NULL, '157', '2026-10-28 02:24:53', 1),
(275, 5, 1057, 3, NULL, NULL, '27', '2026-10-28 02:24:53', 1),
(276, NULL, 1063, 3, NULL, NULL, '29', '2026-10-28 02:24:53', 1),
(277, NULL, 1067, 3, NULL, NULL, '30', '2026-10-28 02:24:53', 1),
(278, NULL, 1078, 3, NULL, NULL, '186', '2026-10-28 02:24:53', 1),
(279, NULL, 1091, 3, NULL, NULL, '91', '2026-10-28 02:24:53', 1),
(280, 5, 1106, 3, NULL, NULL, '23', '2026-10-28 02:24:53', 1),
(281, NULL, 1112, 3, NULL, NULL, '35', '2026-10-28 02:24:53', 1),
(282, NULL, 1121, 3, NULL, NULL, '168', '2026-10-28 02:24:53', 1),
(283, NULL, 1126, 3, NULL, NULL, '173', '2026-10-28 02:24:53', 1),
(284, NULL, 1131, 3, NULL, NULL, '175', '2026-10-28 02:24:53', 1),
(285, NULL, 1136, 3, NULL, NULL, '87', '2026-10-28 02:24:53', 1),
(286, NULL, 1140, 3, NULL, NULL, '177', '2026-10-28 02:24:53', 1),
(287, NULL, 1141, 3, NULL, NULL, '79', '2026-10-28 02:24:53', 1),
(288, NULL, 1144, 3, NULL, NULL, '88', '2026-10-28 02:24:53', 1),
(289, 5, 1148, 4, NULL, NULL, '22', '2026-10-28 02:24:53', 1),
(290, NULL, 1215, 4, NULL, NULL, '20', '2026-10-28 02:24:53', 1),
(291, NULL, 1151, 4, NULL, NULL, '70', '2026-10-28 02:24:53', 1),
(292, 5, 1154, 4, NULL, NULL, '6', '2026-10-28 02:24:53', 1),
(293, 5, 1155, 4, NULL, NULL, '21', '2026-10-28 02:24:53', 1),
(294, 5, 1156, 4, NULL, NULL, '18', '2026-10-28 02:24:53', 1),
(295, 5, 1157, 4, NULL, NULL, '19', '2026-10-28 02:24:53', 1),
(296, 5, 1196, 4, NULL, NULL, '5', '2026-10-28 02:24:53', 1),
(297, 5, 1163, 4, NULL, NULL, '44', '2026-10-28 02:24:53', 1),
(298, 5, 1164, 4, NULL, NULL, '43', '2026-10-28 02:24:53', 1),
(299, NULL, 1165, 4, NULL, NULL, '33', '2026-10-28 02:24:53', 1),
(300, NULL, 1171, 4, NULL, NULL, '3', '2026-10-28 02:24:53', 1),
(301, NULL, 1174, 4, NULL, NULL, '4', '2026-10-28 02:24:53', 1),
(302, NULL, 1175, 4, NULL, NULL, '72', '2026-10-28 02:24:53', 1),
(303, NULL, 1180, 4, NULL, NULL, '60', '2026-10-28 02:24:53', 1),
(304, NULL, 1185, 4, NULL, NULL, '59', '2026-10-28 02:24:53', 1),
(305, NULL, 1186, 4, NULL, NULL, '7', '2026-10-28 02:24:53', 1),
(306, NULL, 1191, 4, NULL, NULL, '1', '2026-10-28 02:24:53', 1),
(307, 5, 1197, 4, NULL, NULL, '76', '2026-10-28 02:24:53', 1),
(308, 5, 1201, 4, NULL, NULL, '35', '2026-10-28 02:24:53', 1),
(309, NULL, 1202, 4, NULL, NULL, '78', '2026-10-28 02:24:53', 1),
(310, NULL, 1207, 4, NULL, NULL, '359', '2026-10-28 02:24:53', 1),
(311, NULL, 1211, 4, NULL, NULL, '38', '2026-10-28 02:24:53', 1),
(312, NULL, 1216, 4, NULL, NULL, '83', '2026-10-28 02:24:53', 1),
(313, NULL, 1220, 4, NULL, NULL, '77', '2026-10-28 02:24:53', 1),
(314, 5, 1225, 4, NULL, NULL, '49', '2026-10-28 02:24:53', 1),
(315, NULL, 1236, 4, NULL, NULL, '31', '2026-10-28 02:24:53', 1),
(316, NULL, 1238, 4, NULL, NULL, '9', '2026-10-28 02:24:53', 1),
(317, NULL, 1243, 4, NULL, NULL, '73', '2026-10-28 02:24:53', 1),
(318, NULL, 1247, 4, NULL, NULL, '74', '2026-10-28 02:24:53', 1),
(319, NULL, 1251, 4, NULL, NULL, '10', '2026-10-28 02:24:53', 1),
(320, NULL, 1252, 4, NULL, NULL, '34', '2026-10-28 02:24:53', 1),
(321, NULL, 1255, 4, NULL, NULL, '50', '2026-10-28 02:24:53', 1),
(322, 5, 1260, 4, NULL, NULL, '24', '2026-10-28 02:24:53', 1),
(323, 5, 1265, 4, NULL, NULL, '23', '2026-10-28 02:24:53', 1),
(324, NULL, 1270, 4, NULL, NULL, '54', '2026-10-28 02:24:53', 1);

-- --------------------------------------------------------

--
-- Table structure for table `household_questions`
--

DROP TABLE IF EXISTS `household_questions`;
CREATE TABLE IF NOT EXISTS `household_questions` (
  `household_questions_id` int NOT NULL AUTO_INCREMENT,
  `ownership_of_housing_unit_id` int NOT NULL,
  `ownership_of_lot_id` int NOT NULL,
  `fuel_type_for_lighting_id` int NOT NULL,
  `fuel_type_for_cooking_id` int NOT NULL,
  `main_source_drinking_water_id` int NOT NULL,
  `kitchen_garbage_disposal_id` int NOT NULL,
  `perform_garbage_seggragation` tinyint(1) NOT NULL,
  `toilet_facility_type_id` int NOT NULL,
  `type_of_building_house_id` int NOT NULL,
  `construction_material_outer_wall_id` int NOT NULL,
  `household_id` int NOT NULL,
  PRIMARY KEY (`household_questions_id`),
  KEY `building_house_type` (`type_of_building_house_id`),
  KEY `construction_material` (`construction_material_outer_wall_id`),
  KEY `fuel_type_cooking` (`fuel_type_for_cooking_id`),
  KEY `fuel_type_lighting` (`fuel_type_for_lighting_id`),
  KEY `household_id` (`household_id`),
  KEY `kitchen_garbage_disposal` (`kitchen_garbage_disposal_id`),
  KEY `ownership_type_lot` (`ownership_of_lot_id`),
  KEY `ownership_type_unit` (`ownership_of_housing_unit_id`),
  KEY `toilet_facility_type` (`toilet_facility_type_id`),
  KEY `water_source` (`main_source_drinking_water_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `household_status`
--

DROP TABLE IF EXISTS `household_status`;
CREATE TABLE IF NOT EXISTS `household_status` (
  `household_status_id` int NOT NULL AUTO_INCREMENT,
  `household_status` varchar(45) NOT NULL,
  PRIMARY KEY (`household_status_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `household_status`
--

INSERT INTO `household_status` (`household_status_id`, `household_status`) VALUES
(1, 'Active'),
(2, 'Inactive'),
(3, 'Archive');

-- --------------------------------------------------------

--
-- Table structure for table `immunization`
--

DROP TABLE IF EXISTS `immunization`;
CREATE TABLE IF NOT EXISTS `immunization` (
  `immunization_id` int NOT NULL AUTO_INCREMENT,
  `immunization` varchar(45) NOT NULL,
  PRIMARY KEY (`immunization_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `immunization`
--

INSERT INTO `immunization` (`immunization_id`, `immunization`) VALUES
(1, 'None'),
(2, 'BCG Vaccine'),
(3, 'Hepatitis B (HepB)'),
(4, 'Influenza (Flu)'),
(5, 'MMR (Measles, Mumps, Rubella)');

-- --------------------------------------------------------

--
-- Table structure for table `infant_health`
--

DROP TABLE IF EXISTS `infant_health`;
CREATE TABLE IF NOT EXISTS `infant_health` (
  `infant_health_id` int NOT NULL AUTO_INCREMENT,
  `place_of_delivery_id` int NOT NULL,
  `birth_attendant_id` int NOT NULL,
  `immunization_id` int NOT NULL,
  `resident_id` int NOT NULL,
  PRIMARY KEY (`infant_health_id`),
  KEY `birth_attendant` (`birth_attendant_id`),
  KEY `place_of_delivery` (`place_of_delivery_id`),
  KEY `resident_infant_health` (`resident_id`),
  KEY `immunization` (`immunization_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `intend_to_stay_5yrs_from_now`
--

DROP TABLE IF EXISTS `intend_to_stay_5yrs_from_now`;
CREATE TABLE IF NOT EXISTS `intend_to_stay_5yrs_from_now` (
  `intend_to_stay_id` int NOT NULL AUTO_INCREMENT,
  `intend_to_stay_brgy` varchar(45) NOT NULL,
  `intend_to_stay_municipality` varchar(45) NOT NULL,
  `intend_to_stay_province` varchar(45) NOT NULL,
  `household_question_id` int NOT NULL,
  PRIMARY KEY (`intend_to_stay_id`),
  KEY `household_question1` (`household_question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kitchen_garbage_disposal`
--

DROP TABLE IF EXISTS `kitchen_garbage_disposal`;
CREATE TABLE IF NOT EXISTS `kitchen_garbage_disposal` (
  `kitchen_garbage_disposal_id` int NOT NULL AUTO_INCREMENT,
  `kitchen_garbage_disposal` varchar(45) NOT NULL,
  PRIMARY KEY (`kitchen_garbage_disposal_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kitchen_garbage_disposal`
--

INSERT INTO `kitchen_garbage_disposal` (`kitchen_garbage_disposal_id`, `kitchen_garbage_disposal`) VALUES
(1, 'Feeding to animals'),
(2, 'Burying'),
(3, 'Composting'),
(4, 'Burning'),
(5, 'Dumping individual pit (not burned)'),
(6, 'Picked-up by garbage truck');

-- --------------------------------------------------------

--
-- Table structure for table `login_status`
--

DROP TABLE IF EXISTS `login_status`;
CREATE TABLE IF NOT EXISTS `login_status` (
  `login_status_id` int NOT NULL AUTO_INCREMENT,
  `login_status` varchar(45) NOT NULL,
  PRIMARY KEY (`login_status_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `login_status`
--

INSERT INTO `login_status` (`login_status_id`, `login_status`) VALUES
(1, 'Success'),
(2, 'Invalid Password'),
(3, 'Invalid Username'),
(4, 'Account Locked');

-- --------------------------------------------------------

--
-- Table structure for table `marital_status`
--

DROP TABLE IF EXISTS `marital_status`;
CREATE TABLE IF NOT EXISTS `marital_status` (
  `marital_status_id` int NOT NULL AUTO_INCREMENT,
  `marital_status` varchar(45) NOT NULL,
  PRIMARY KEY (`marital_status_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `marital_status`
--

INSERT INTO `marital_status` (`marital_status_id`, `marital_status`) VALUES
(1, 'Single'),
(2, 'Married'),
(3, 'Living-in'),
(4, 'Widowed'),
(5, 'Separated'),
(6, 'Divorced'),
(7, 'Unknown');

-- --------------------------------------------------------

--
-- Table structure for table `migration`
--

DROP TABLE IF EXISTS `migration`;
CREATE TABLE IF NOT EXISTS `migration` (
  `migration_id` int NOT NULL AUTO_INCREMENT,
  `date_of_transfer_in_brgy` date NOT NULL,
  `resident_type_id` int NOT NULL,
  `reason_for_leaving_id` int NOT NULL,
  `will_return_to_previous_residence` tinyint(1) NOT NULL,
  `reason_for_transfer_id` int NOT NULL,
  `duration_of_stay` date DEFAULT NULL,
  `resident_id` int NOT NULL,
  PRIMARY KEY (`migration_id`),
  KEY `reason_for_leaving` (`reason_for_leaving_id`),
  KEY `reason_for_transfer` (`reason_for_transfer_id`),
  KEY `resident_type` (`resident_type_id`),
  KEY `resident_migration` (`resident_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nationality`
--

DROP TABLE IF EXISTS `nationality`;
CREATE TABLE IF NOT EXISTS `nationality` (
  `nationality_id` int NOT NULL AUTO_INCREMENT,
  `nationality` varchar(45) NOT NULL,
  PRIMARY KEY (`nationality_id`),
  UNIQUE KEY `uq_nationality` (`nationality`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `nationality`
--

INSERT INTO `nationality` (`nationality_id`, `nationality`) VALUES
(5, 'American'),
(4, 'Chinese'),
(1, 'Filipino'),
(3, 'Japanese'),
(2, 'Korean');

-- --------------------------------------------------------

--
-- Table structure for table `ownership_type`
--

DROP TABLE IF EXISTS `ownership_type`;
CREATE TABLE IF NOT EXISTS `ownership_type` (
  `ownership_type_id` int NOT NULL AUTO_INCREMENT,
  `ownership_type` varchar(45) NOT NULL,
  PRIMARY KEY (`ownership_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `ownership_type`
--

INSERT INTO `ownership_type` (`ownership_type_id`, `ownership_type`) VALUES
(1, 'Rent-free without consent of owner'),
(2, 'Rent-free with consent of owner'),
(3, 'Rented'),
(4, 'Owned/being amortized');

-- --------------------------------------------------------

--
-- Table structure for table `permission`
--

DROP TABLE IF EXISTS `permission`;
CREATE TABLE IF NOT EXISTS `permission` (
  `permission_id` int NOT NULL AUTO_INCREMENT,
  `permission` varchar(45) NOT NULL,
  PRIMARY KEY (`permission_id`),
  UNIQUE KEY `uq_permission` (`permission`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `permission`
--

INSERT INTO `permission` (`permission_id`, `permission`) VALUES
(18, 'auditlog.view'),
(77, 'backup.export'),
(78, 'backup.restore'),
(74, 'certificate.create'),
(76, 'certificate.edit'),
(75, 'certificate.view'),
(62, 'ctc.create'),
(63, 'ctc.update'),
(61, 'ctc.view'),
(44, 'economic.create'),
(45, 'economic.update'),
(43, 'economic.view'),
(41, 'education.create'),
(42, 'education.update'),
(40, 'education.view'),
(31, 'ethnicity.create'),
(32, 'ethnicity.delete'),
(30, 'ethnicity.view'),
(50, 'health.create'),
(51, 'health.update'),
(49, 'health.view'),
(23, 'household.create'),
(24, 'household.update'),
(22, 'household.view'),
(26, 'householdquestion.create'),
(73, 'householdquestion.update'),
(25, 'householdquestion.view'),
(47, 'infanthealth.create'),
(48, 'infanthealth.update'),
(46, 'infanthealth.view'),
(59, 'migration.create'),
(60, 'migration.update'),
(58, 'migration.view'),
(28, 'nationality.create'),
(29, 'nationality.delete'),
(27, 'nationality.view'),
(9, 'personnel.create'),
(11, 'personnel.update'),
(10, 'personnel.view'),
(72, 'petcensus.create'),
(71, 'petcensus.update'),
(70, 'petcensus.view'),
(12, 'pposition.create'),
(14, 'pposition.delete'),
(13, 'pposition.view'),
(37, 'religion.create'),
(38, 'religion.delete'),
(36, 'religion.view'),
(68, 'report.create'),
(69, 'report.update'),
(67, 'report.view'),
(34, 'resident.create'),
(35, 'resident.update'),
(33, 'resident.view'),
(81, 'retention.archive'),
(80, 'retention.update'),
(79, 'retention.view'),
(16, 'setting.update'),
(15, 'setting.view'),
(65, 'skills.create'),
(66, 'skills.update'),
(64, 'skills.view'),
(56, 'sociocivic.create'),
(57, 'sociocivic.update'),
(55, 'sociocivic.view'),
(20, 'street.create'),
(21, 'street.delete'),
(19, 'street.view'),
(17, 'user.changepassword'),
(1, 'user.create'),
(82, 'user.delete'),
(3, 'user.resetpassword'),
(4, 'user.updatestatus'),
(2, 'user.view'),
(5, 'userlog.view'),
(6, 'userrole.create'),
(8, 'userrole.updatestatus'),
(7, 'userrole.view'),
(53, 'womanhealth.create'),
(54, 'womanhealth.update'),
(52, 'womanhealth.view');

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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `personnel_position`
--

INSERT INTO `personnel_position` (`position_id`, `position_name`) VALUES
(1, 'Barangay Secretary'),
(6, 'Committee On Peace And Order'),
(7, 'Kagawad On Finance And Education'),
(4, 'Kagawad On Health And Nutrition'),
(8, 'Kagawad On Social Services'),
(9, 'President Of Solo Parent'),
(5, 'Punong Barangay'),
(2, 'SK Chairperson');

-- --------------------------------------------------------

--
-- Table structure for table `personnel_status`
--

DROP TABLE IF EXISTS `personnel_status`;
CREATE TABLE IF NOT EXISTS `personnel_status` (
  `personnel_status_id` int NOT NULL AUTO_INCREMENT,
  `personnel_status` varchar(45) NOT NULL,
  PRIMARY KEY (`personnel_status_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `personnel_status`
--

INSERT INTO `personnel_status` (`personnel_status_id`, `personnel_status`) VALUES
(1, 'Active'),
(2, 'Inactive'),
(3, 'Resigned'),
(4, 'Retired'),
(5, 'On Leave');

-- --------------------------------------------------------

--
-- Table structure for table `pet_census`
--

DROP TABLE IF EXISTS `pet_census`;
CREATE TABLE IF NOT EXISTS `pet_census` (
  `pet_census_id` int NOT NULL AUTO_INCREMENT,
  `household_id` int NOT NULL,
  `specie_id` int NOT NULL,
  `breed_id` int NOT NULL,
  `sex_id` int NOT NULL,
  `pet_date_of_birth` date DEFAULT NULL,
  `is_spay_neuter` tinyint(1) NOT NULL,
  `rabies_vaccination_date` date DEFAULT NULL,
  `pet_status_id` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`pet_census_id`),
  KEY `house_pet` (`household_id`),
  KEY `specie` (`specie_id`),
  KEY `breed` (`breed_id`),
  KEY `pet_sex` (`sex_id`),
  KEY `pet_current_status` (`pet_status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pet_status`
--

DROP TABLE IF EXISTS `pet_status`;
CREATE TABLE IF NOT EXISTS `pet_status` (
  `pet_status_id` int NOT NULL AUTO_INCREMENT,
  `pet_status` varchar(45) NOT NULL,
  PRIMARY KEY (`pet_status_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pet_status`
--

INSERT INTO `pet_status` (`pet_status_id`, `pet_status`) VALUES
(1, 'Active'),
(2, 'Diseased');

-- --------------------------------------------------------

--
-- Table structure for table `place_of_delivery`
--

DROP TABLE IF EXISTS `place_of_delivery`;
CREATE TABLE IF NOT EXISTS `place_of_delivery` (
  `place_of_delivery_id` int NOT NULL AUTO_INCREMENT,
  `place_of_delivery` varchar(45) NOT NULL,
  PRIMARY KEY (`place_of_delivery_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `place_of_delivery`
--

INSERT INTO `place_of_delivery` (`place_of_delivery_id`, `place_of_delivery`) VALUES
(1, 'Public hospital'),
(2, 'Private hospital'),
(3, 'Lying-in clinic'),
(4, 'Home');

-- --------------------------------------------------------

--
-- Table structure for table `primary_need`
--

DROP TABLE IF EXISTS `primary_need`;
CREATE TABLE IF NOT EXISTS `primary_need` (
  `primary_need_id` int NOT NULL,
  `household_question_id` int NOT NULL,
  KEY `primary_need` (`primary_need_id`),
  KEY `household_question4` (`household_question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `primary_need_of_brgy`
--

DROP TABLE IF EXISTS `primary_need_of_brgy`;
CREATE TABLE IF NOT EXISTS `primary_need_of_brgy` (
  `primary_need_id` int NOT NULL AUTO_INCREMENT,
  `primary_need` varchar(45) NOT NULL,
  PRIMARY KEY (`primary_need_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `primary_need_of_brgy`
--

INSERT INTO `primary_need_of_brgy` (`primary_need_id`, `primary_need`) VALUES
(1, 'Streetlights'),
(2, 'Events'),
(3, 'Safety Measures');

-- --------------------------------------------------------

--
-- Table structure for table `reason_for_leaving`
--

DROP TABLE IF EXISTS `reason_for_leaving`;
CREATE TABLE IF NOT EXISTS `reason_for_leaving` (
  `reason_for_leaving_id` int NOT NULL AUTO_INCREMENT,
  `reason_for_leaving` varchar(45) NOT NULL,
  PRIMARY KEY (`reason_for_leaving_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `reason_for_leaving`
--

INSERT INTO `reason_for_leaving` (`reason_for_leaving_id`, `reason_for_leaving`) VALUES
(1, 'Lack of employment'),
(2, 'Perception of better income in other place'),
(3, 'Schooling'),
(4, 'Presence of relatives and friends in other pl'),
(5, 'Employment/Job Relocation'),
(6, 'Disaster-related Relocation'),
(7, 'Retirement'),
(8, 'To live with Parents'),
(9, 'To live with Children'),
(10, 'Marriage'),
(11, 'Annulment/Divorce/Separation'),
(12, 'Community-related Reasons'),
(13, 'Health-related Reasons'),
(14, 'Peace and Security'),
(15, 'Others'),
(16, 'Not Applicable');

-- --------------------------------------------------------

--
-- Table structure for table `reason_for_transfer`
--

DROP TABLE IF EXISTS `reason_for_transfer`;
CREATE TABLE IF NOT EXISTS `reason_for_transfer` (
  `reason_for_transfer_id` int NOT NULL AUTO_INCREMENT,
  `reason_for_transfer` varchar(45) NOT NULL,
  PRIMARY KEY (`reason_for_transfer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `reason_for_transfer`
--

INSERT INTO `reason_for_transfer` (`reason_for_transfer_id`, `reason_for_transfer`) VALUES
(1, 'Availability of jobs'),
(2, 'Higher wage'),
(3, 'Presence of schools or universities'),
(4, 'Presence of relatives and friends in other pl'),
(5, 'Housing'),
(6, 'Not Applicable');

-- --------------------------------------------------------

--
-- Table structure for table `relationship_to_hh`
--

DROP TABLE IF EXISTS `relationship_to_hh`;
CREATE TABLE IF NOT EXISTS `relationship_to_hh` (
  `relationship_to_hh_id` int NOT NULL AUTO_INCREMENT,
  `relationship_to_hh` varchar(45) NOT NULL,
  PRIMARY KEY (`relationship_to_hh_id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `relationship_to_hh`
--

INSERT INTO `relationship_to_hh` (`relationship_to_hh_id`, `relationship_to_hh`) VALUES
(1, 'Head'),
(2, 'Spouse'),
(3, 'Son'),
(4, 'Daughter'),
(5, 'Stepson'),
(6, 'Stepdaughter'),
(7, 'Son-in-law'),
(8, 'Daughter-in-law'),
(9, 'Grandson'),
(10, 'Granddaughter'),
(11, 'Father'),
(12, 'Mother'),
(13, 'Brother'),
(14, 'Sister'),
(15, 'Uncle'),
(16, 'Aunt'),
(17, 'Nephew'),
(18, 'Niece'),
(19, 'Other-relative'),
(20, 'Non-relative'),
(21, 'Boarder'),
(22, 'Domestic Helper'),
(23, 'Sister-in-law'),
(24, 'Brother-in-law'),
(25, 'Granddaughter-in-law'),
(26, 'Grandson-in-law');

-- --------------------------------------------------------

--
-- Table structure for table `religion`
--

DROP TABLE IF EXISTS `religion`;
CREATE TABLE IF NOT EXISTS `religion` (
  `religion_id` int NOT NULL AUTO_INCREMENT,
  `religion` varchar(45) NOT NULL,
  PRIMARY KEY (`religion_id`),
  UNIQUE KEY `uq_religion` (`religion`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `religion`
--

INSERT INTO `religion` (`religion_id`, `religion`) VALUES
(4, '7th Day Adventist'),
(5, 'Aglipay'),
(6, 'Agnostic'),
(7, 'Anglican'),
(8, 'Assembly of God'),
(9, 'Baptist'),
(3, 'Born Again'),
(10, 'Brethren'),
(11, 'Christian'),
(12, 'Christian Spiritist Incorporation'),
(13, 'Crusaders'),
(14, 'Espiritista'),
(15, 'Evangelical'),
(16, 'Free Believer in Christ Fellowship'),
(2, 'Iglesia ni Cristo'),
(17, 'Jehova\'s Witnesses'),
(18, 'Lutheran'),
(19, 'New Apostolic Church'),
(20, 'New Hope Christian'),
(21, 'New Testament Assemblies of God'),
(22, 'Pentecost'),
(23, 'Pinnacle of Grace Communion'),
(24, 'Protestant'),
(25, 'Reformed Born Again'),
(1, 'Roman Catholic'),
(29, 'UCCFI'),
(28, 'UCCP'),
(26, 'United Church of Christ'),
(30, 'UPC'),
(27, 'Wesleyan');

-- --------------------------------------------------------

--
-- Table structure for table `resident`
--

DROP TABLE IF EXISTS `resident`;
CREATE TABLE IF NOT EXISTS `resident` (
  `resident_id` int NOT NULL AUTO_INCREMENT,
  `last_name` varchar(45) NOT NULL,
  `first_name` varchar(45) NOT NULL,
  `middle_name` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `suffix` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `relationship_to_hh_id` int DEFAULT NULL,
  `sex_id` int DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `birth_city_municipality` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `birth_province` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `birth_country` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `nationality_id` int DEFAULT NULL,
  `religion_id` int DEFAULT NULL,
  `ethnicity_id` int DEFAULT NULL,
  `marital_status_id` int DEFAULT NULL,
  `clan_id` int DEFAULT NULL,
  `resident_status_id` int NOT NULL DEFAULT '1',
  `household_id` int NOT NULL,
  PRIMARY KEY (`resident_id`),
  KEY `ethnicity` (`ethnicity_id`),
  KEY `household_resident` (`household_id`),
  KEY `marital_status` (`marital_status_id`),
  KEY `nationality` (`nationality_id`),
  KEY `relationship` (`relationship_to_hh_id`),
  KEY `religion` (`religion_id`),
  KEY `resident_clan` (`clan_id`),
  KEY `resident_status` (`resident_status_id`),
  KEY `sex` (`sex_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1293 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `resident`
--

INSERT INTO `resident` (`resident_id`, `last_name`, `first_name`, `middle_name`, `suffix`, `relationship_to_hh_id`, `sex_id`, `date_of_birth`, `birth_city_municipality`, `birth_province`, `birth_country`, `nationality_id`, `religion_id`, `ethnicity_id`, `marital_status_id`, `clan_id`, `resident_status_id`, `household_id`) VALUES
(1, 'Abuan', 'Fabiola', 'D.', NULL, 1, 2, '1960-02-25', NULL, NULL, NULL, NULL, NULL, 9, 2, NULL, 1, 1),
(2, 'Adawi', 'Martes', 'Belino', NULL, 1, 1, '1965-01-12', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 2),
(3, 'Adawi', 'Lolita', 'Palunan', NULL, 2, 2, '1970-12-20', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 2),
(4, 'Adawi', 'Cylon', 'Palunan', NULL, 3, 1, NULL, NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 2),
(5, 'Adawi', 'Charlot', 'M.', NULL, NULL, 2, '1998-05-02', NULL, NULL, NULL, NULL, NULL, 9, 2, NULL, 1, 2),
(6, 'Adawi', 'Clarisa', 'M.', NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, 9, 1, NULL, 1, 2),
(7, 'Adawi', 'Laila', 'Palunan', NULL, 4, 2, '2002-07-04', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 2),
(8, 'Aglasi', 'Jun', 'C.', NULL, 1, 1, '1970-09-10', NULL, NULL, NULL, NULL, 3, 16, NULL, NULL, 1, 3),
(9, 'Agravante', 'Robert', NULL, NULL, 1, 1, '1971-08-29', NULL, NULL, NULL, NULL, 21, 44, NULL, NULL, 1, 4),
(10, 'Aguilan', 'Simplicio', NULL, NULL, 1, 1, '1958-03-06', NULL, NULL, NULL, NULL, NULL, 9, NULL, NULL, 1, 5),
(11, 'Agyamoc', 'Emilio', 'Emilio', NULL, 1, 1, '1977-10-22', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 6),
(12, 'Agyamoc', 'Concepcion', 'Aceron', NULL, 2, 2, '1974-12-08', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 6),
(13, 'Agyamoc', 'Sabrina faye', 'Aceron', NULL, 4, 2, '2008-12-21', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 6),
(14, 'Agyamoc', 'Sebastian fritz', 'Aceron', NULL, 3, 1, '2011-11-24', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 6),
(15, 'Aceron', 'Belena', 'Arce', NULL, 19, 2, '1954-01-19', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 6),
(16, 'Alamar', 'Crisanto jr.', NULL, NULL, 1, 1, '1977-08-10', NULL, NULL, NULL, NULL, 1, 52, NULL, NULL, 1, 7),
(17, 'Almag', 'Marlon', NULL, NULL, 1, 1, '1971-11-22', NULL, NULL, NULL, NULL, 7, 16, NULL, NULL, 1, 8),
(18, 'Alonzo', 'Rolly', 'Bantillo', NULL, 1, 1, '1978-11-29', NULL, NULL, NULL, NULL, NULL, 56, 2, NULL, 1, 9),
(19, 'Alonzo', 'Mary jane', 'Ciano', NULL, 2, 2, '1975-01-29', NULL, NULL, NULL, NULL, NULL, 52, 2, NULL, 1, 9),
(20, 'Alonzo', 'Christine joy', 'Dodon', NULL, 4, 2, '2003-10-03', NULL, NULL, NULL, NULL, NULL, 72, 1, NULL, 1, 9),
(21, 'Alonzo', 'Rhayan jhay', 'Dodon', NULL, 3, 1, '2013-09-12', NULL, NULL, NULL, NULL, NULL, 72, 1, NULL, 1, 9),
(22, 'Altiyen', 'John', 'Miguel', NULL, 1, 1, '1952-04-08', NULL, NULL, NULL, NULL, 27, 9, 4, NULL, 1, 10),
(23, 'Altiyen', 'Jireh lyn', 'Bunagyen', NULL, 4, 2, '1984-12-04', NULL, NULL, NULL, NULL, 27, 9, 1, NULL, 1, 10),
(24, 'Bunagyen', 'Aziel', 'Galong', NULL, 4, 2, '1993-03-20', NULL, NULL, NULL, NULL, 27, NULL, 1, NULL, 1, 10),
(25, 'Aluyen', 'Levita', 'K.', NULL, 1, 2, '1975-02-20', NULL, NULL, NULL, NULL, 28, 9, NULL, NULL, 1, 11),
(26, 'Aluyen', 'Josefa', 'Calantas', NULL, 1, 2, '1950-03-19', NULL, NULL, NULL, NULL, 1, 9, 4, NULL, 1, 12),
(27, 'Buligan', 'Deligth hope', 'Aluyen', NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 12),
(28, 'Buligan', 'John wesley', 'Aluyen', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 12),
(29, 'Corpuz', 'Judith', 'Aluyen', NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 1, 9, 5, NULL, 1, 12),
(30, 'Alvero', 'Mario', 'B.', NULL, 1, 1, '1968-01-28', NULL, NULL, NULL, NULL, 1, 52, NULL, NULL, 1, 13),
(31, 'Amides', 'Melchor', 'Esteban', NULL, 1, 1, '1990-12-29', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 14),
(32, 'Andrada', 'Arnold', 'C.', NULL, 1, 1, '1965-02-07', NULL, NULL, NULL, NULL, 1, 52, NULL, NULL, 1, 15),
(33, 'Aniban', 'Patrick', 'Subukan', NULL, 1, 1, '1980-09-15', NULL, NULL, NULL, NULL, NULL, 1, 2, NULL, 1, 16),
(34, 'Aniban', 'Ester', 'Maslian', NULL, 2, 2, '1983-10-15', NULL, NULL, NULL, NULL, NULL, 1, 2, NULL, 1, 16),
(35, 'Aniban', 'Althea jael', 'Maslian', NULL, 4, 2, '2010-09-13', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 1, 16),
(36, 'Aniban', 'Alexa gail', 'Maslian', NULL, 4, 2, '2012-10-05', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 1, 16),
(37, 'Anno', 'Evaristo', 'J.', NULL, 1, 1, '1965-12-05', NULL, NULL, NULL, NULL, 3, 59, NULL, NULL, 1, 17),
(38, 'Cotaran', 'Evelyn', 'Chinay-yeo', NULL, 1, 2, '1947-12-15', NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, 1, 18),
(39, 'Cotaran', 'Fidel', 'Chinay-yeo', NULL, 13, 1, '1954-04-24', NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, 1, 18),
(40, 'Antonio', 'Throcy', 'L.', NULL, 1, 1, '1983-08-28', NULL, NULL, NULL, NULL, NULL, 9, NULL, NULL, 1, 19),
(41, 'Antonio', 'Melanie', 'D.', NULL, 2, 2, '1983-09-08', NULL, NULL, NULL, NULL, NULL, 16, NULL, NULL, 1, 19),
(42, 'Antonio', 'Hadriel usher', 'D.', NULL, 3, 1, '2014-04-16', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 19),
(43, 'Antonio', 'Yeshua', 'D.', NULL, 3, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 19),
(44, 'Aoanan', 'Bienvienindo', NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, 16, NULL, NULL, 1, 20),
(45, 'Aoanan', 'Mac domino', 'C.', NULL, 1, 1, '1983-10-18', NULL, NULL, NULL, NULL, 1, 16, NULL, NULL, 1, 21),
(46, 'Atiwag', 'Edmund', 'Sanapao', NULL, 1, 1, '1954-09-28', NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, 22),
(47, 'Atiwag', 'Agnes', 'Barotilla', NULL, 2, 2, '1977-04-17', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 22),
(48, 'Atiwag', 'Jay-ar', 'Barotilla', NULL, 3, 1, '2004-05-11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 22),
(49, 'Atiwag', 'John lester', 'Barotilla', NULL, 3, 1, '2010-07-29', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 22),
(50, 'Ayugat', 'Gilbert', 'Ventura', NULL, 1, 1, '1962-01-21', NULL, NULL, NULL, NULL, 7, 9, 2, NULL, 1, 23),
(51, 'Ayugat', 'Susanne', 'Simongo', NULL, 2, 2, '1962-08-12', NULL, NULL, NULL, NULL, 7, 9, 2, NULL, 1, 23),
(52, 'Ayugat', 'Flynn', 'Simongo', NULL, 4, 2, '1991-05-29', NULL, NULL, NULL, NULL, 7, 9, 1, NULL, 1, 23),
(53, 'Bagbagen', 'Chester', 'S.', NULL, 1, 1, '1980-07-05', NULL, NULL, NULL, NULL, 1, 9, NULL, NULL, 1, 24),
(54, 'Baguis', 'Angelita', 'Nisperos', NULL, 1, 2, '1955-02-18', NULL, NULL, NULL, NULL, 1, 52, 4, NULL, 1, 25),
(55, 'Baguis', 'Marivic', 'Nisperos', NULL, NULL, 2, '1978-10-11', NULL, NULL, NULL, NULL, 1, 52, 1, NULL, 1, 25),
(56, 'Baguis', 'Nerissa', 'Nisperos', NULL, NULL, 2, '1988-11-23', NULL, NULL, NULL, NULL, 1, 52, 1, NULL, 1, 25),
(57, 'Bautista', 'Demeterio', 'S.', NULL, 1, 1, '1985-12-25', NULL, NULL, NULL, NULL, NULL, 9, 1, NULL, 1, 26),
(58, 'Bautista', 'Rudy', 'Samuel', NULL, 1, 1, '1974-11-24', NULL, NULL, NULL, NULL, NULL, 9, 1, NULL, 1, 27),
(59, 'Bautista', 'Martin', 'B.', NULL, 1, 1, '1963-03-08', NULL, NULL, NULL, NULL, NULL, 9, 2, NULL, 1, 28),
(60, 'Bautista', 'Leticia', 'I.', NULL, 2, 2, '1964-09-03', NULL, NULL, NULL, NULL, NULL, 9, 2, NULL, 1, 28),
(61, 'Bautista', 'Ivanjun', 'B.', NULL, 3, 1, '1990-07-24', NULL, NULL, NULL, NULL, NULL, 9, 1, NULL, 1, 28),
(62, 'Bautista', 'Justin', 'B.', NULL, 3, 1, '1997-09-25', NULL, NULL, NULL, NULL, NULL, 9, 1, NULL, 1, 28),
(63, 'Bautista', 'Mija viel', 'B.', NULL, 4, 2, '2009-08-26', NULL, NULL, NULL, NULL, NULL, 9, 1, NULL, 1, 28),
(64, 'Bautista', 'Teofilo', 'B.', NULL, 1, 1, '1965-02-12', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 29),
(65, 'Bautista', 'Sucena', 'Sacla', NULL, 2, 2, '1981-01-05', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 29),
(66, 'Bautista', 'Jorina', 'Sacla', NULL, 4, 2, '2013-07-28', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 29),
(67, 'Bautista', 'Edgardo', 'A.', NULL, 1, 1, '1933-03-28', NULL, NULL, NULL, NULL, 1, 9, NULL, NULL, 1, 30),
(68, 'Bautista', 'Aspril', 'S.', NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, 1, 9, NULL, NULL, 1, 31),
(69, 'Faustino', 'Magie', 'B.', NULL, 1, 2, '1982-09-07', NULL, NULL, NULL, NULL, 1, 9, NULL, NULL, 1, 32),
(70, 'Bedana', 'William', 'G.', NULL, 1, 1, '1961-08-04', NULL, NULL, NULL, NULL, 1, 72, NULL, NULL, 1, 33),
(71, 'Benosa', 'Alex', 'Balbin', NULL, 1, 1, '1964-02-26', NULL, NULL, NULL, NULL, 22, 52, 2, NULL, 1, 34),
(72, 'Benosa', 'Alex', 'Ciano', NULL, 3, 1, '1992-09-18', NULL, NULL, NULL, NULL, 22, 18, 1, NULL, 1, 34),
(73, 'Benosa', 'Lesly ann', 'Ciano', NULL, 4, 2, '1994-12-05', NULL, NULL, NULL, NULL, 22, 18, 1, NULL, 1, 34),
(74, 'Benosa', 'Ellen margaret', 'Billawac', NULL, NULL, 2, '2001-12-15', NULL, NULL, NULL, NULL, 22, 52, 1, NULL, 1, 34),
(75, 'Benosa', 'Lenard', 'Ciano', NULL, 3, 1, '2006-05-03', NULL, NULL, NULL, NULL, 22, 18, 1, NULL, 1, 34),
(76, 'Bernal', 'Ruben', 'S.', NULL, 1, 1, '1977-12-21', NULL, NULL, NULL, NULL, 28, 16, NULL, NULL, 1, 35),
(77, 'Bernal.', 'Erickson', 'D.', NULL, 1, 1, '1985-04-04', NULL, NULL, NULL, NULL, 1, 16, NULL, NULL, 1, 36),
(78, 'Bernal', 'Joseph', 'C.', NULL, 1, 1, '1959-12-28', NULL, NULL, NULL, NULL, 1, 16, NULL, NULL, 1, 37),
(79, 'Bigya', 'Noriel', 'D.', NULL, 1, 1, '1993-07-27', NULL, NULL, NULL, NULL, 11, 9, NULL, NULL, 1, 38),
(80, 'Madlang bayan', 'Casmin', 'D.', NULL, 2, 2, '1994-07-17', NULL, NULL, NULL, NULL, 11, 53, NULL, NULL, 1, 38),
(81, 'Bernal', 'Julia', NULL, NULL, 1, 2, '1952-07-23', NULL, NULL, NULL, NULL, 22, 16, NULL, NULL, 1, 39),
(82, 'Billy', 'Rommel', 'C.', NULL, 1, 1, '1976-12-20', NULL, NULL, NULL, NULL, 3, 18, NULL, NULL, 1, 40),
(83, 'Binaliw', 'Armando', 'M.', NULL, 1, 1, '1979-01-05', NULL, NULL, NULL, NULL, 27, 9, 2, NULL, 1, 41),
(84, 'Binaliw', 'Marilyn', 'G.', NULL, 2, 2, '1978-09-02', NULL, NULL, NULL, NULL, 27, 9, 2, NULL, 1, 41),
(85, 'Binaliw', 'Lee', 'G.', NULL, 3, 1, '2001-09-08', NULL, NULL, NULL, NULL, 27, 9, 1, NULL, 1, 41),
(86, 'Biinaliw', 'Liezel', 'G.', NULL, 4, 2, '2003-06-13', NULL, NULL, NULL, NULL, 27, 9, 1, NULL, 1, 41),
(87, 'Biinaliw', 'Luigi', 'G.', NULL, 3, 1, NULL, NULL, NULL, NULL, NULL, 27, 9, 1, NULL, 1, 41),
(88, 'Galay', 'Reneboy', 'D.', NULL, 13, 1, '1991-10-13', NULL, NULL, NULL, NULL, 27, 9, 1, NULL, 1, 41),
(89, 'Watti', 'Abegail', 'C.', NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 27, 9, 1, NULL, 1, 41),
(90, 'Binwag', 'Lolita', 'Ferrer', NULL, 1, 2, '1980-09-08', NULL, NULL, NULL, NULL, 1, 71, 4, NULL, 1, 42),
(91, 'Binwag', 'Angel eve', 'Ferrer', NULL, 4, 2, '2003-12-09', NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 42),
(92, 'Binwag', 'Maria abegail', 'Ferrer', NULL, 4, 2, '2005-09-04', NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 42),
(93, 'Binwag', 'Patricia', 'Ferrer', NULL, 4, 2, '2008-01-30', NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 42),
(94, 'Binwag', 'Ezekiel', 'Ferrer', NULL, 3, 1, '2012-01-04', NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 42),
(95, 'Bolaton', 'Bherne', 'Ordones', NULL, 1, 1, '1985-11-07', NULL, NULL, NULL, NULL, 1, 52, 2, NULL, 1, 43),
(96, 'Bolaton', 'Michell', 'Aquino', NULL, 21, 2, '1988-10-05', NULL, NULL, NULL, NULL, 1, 52, 2, NULL, 1, 43),
(97, 'Bolaton', 'Bryan', 'Aquino', NULL, 21, 1, '2015-08-25', NULL, NULL, NULL, NULL, NULL, 52, 1, NULL, 1, 43),
(98, 'Rico', 'Rec emel', 'Bolaton', NULL, 21, 1, '2000-03-28', NULL, NULL, NULL, NULL, NULL, 52, 1, NULL, 1, 43),
(99, 'Ordonez', 'Joe', 'Navelgas', NULL, 21, 1, '1998-12-10', NULL, NULL, NULL, NULL, NULL, 52, 1, NULL, 1, 43),
(100, 'Espanol', 'Joseph', NULL, NULL, 21, 1, NULL, NULL, NULL, NULL, NULL, NULL, 52, 1, NULL, 1, 43),
(101, 'Bolidan', 'Fernando', NULL, NULL, 1, 1, '1976-02-21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 44),
(102, 'Busay', 'Mario', 'Bugtong', NULL, 1, 1, '1970-09-21', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 45),
(103, 'Busay', 'Jane', 'Mariano', NULL, 2, 2, '1970-10-01', NULL, NULL, NULL, NULL, 1, 18, 2, NULL, 1, 45),
(104, 'Busay', 'Marion jones', 'Mariano', NULL, 3, 1, '1994-10-07', NULL, NULL, NULL, NULL, 1, 18, 1, NULL, 1, 45),
(105, 'Busay', 'Mark lester', 'Mariano', NULL, 3, 1, '2002-03-19', NULL, NULL, NULL, NULL, 1, 18, 1, NULL, 1, 45),
(106, 'Busay', 'Desiree', 'Mariano', NULL, 4, 2, '2005-10-07', NULL, NULL, NULL, NULL, 1, 18, 1, NULL, 1, 45),
(107, 'Langfisan', 'Jhaymar', 'Busay', NULL, 4, 2, '1991-10-21', NULL, NULL, NULL, NULL, 1, 18, 2, NULL, 1, 45),
(108, 'Langfisan', 'Ralph ethan', 'Busay', NULL, 9, 1, '2013-02-25', NULL, NULL, NULL, NULL, 1, 18, 1, NULL, 1, 45),
(109, 'Langfisan', 'Jel cyril', 'Busay', NULL, 9, 1, '2015-10-14', NULL, NULL, NULL, NULL, 1, 18, 1, NULL, 1, 45),
(110, 'Mendoza', 'Ernie', 'Busay', NULL, NULL, 1, '2018-07-10', NULL, NULL, NULL, NULL, 1, 18, 1, NULL, 1, 45),
(111, 'Butoy', 'Nathaniel', 'L.', NULL, 1, 1, '1957-12-02', NULL, NULL, NULL, NULL, 8, 57, NULL, NULL, 1, 46),
(112, 'Buyao', 'Willier', 'C.', NULL, 1, 1, '1976-12-22', NULL, NULL, NULL, NULL, 12, 9, 2, NULL, 1, 47),
(113, 'Buyao', 'Carolina', 'C.', NULL, 2, 2, '1939-08-05', NULL, NULL, NULL, NULL, 12, 9, 2, NULL, 1, 47),
(114, 'Buyao', 'Krizia mae', 'C.', NULL, 4, 2, '2001-08-26', NULL, NULL, NULL, NULL, 12, 9, 1, NULL, 1, 47),
(115, 'Buyao', 'Jhodel', 'C.', NULL, 3, 1, '2004-06-01', NULL, NULL, NULL, NULL, 12, 9, 1, NULL, 1, 47),
(116, 'Buyao', 'Harrison', 'C.', NULL, 3, 1, '2005-09-30', NULL, NULL, NULL, NULL, 12, 9, 1, NULL, 1, 47),
(117, 'Buyao', 'A-iah faith', 'B.', NULL, 4, 2, '2013-07-02', NULL, NULL, NULL, NULL, 12, 18, 1, NULL, 1, 47),
(118, 'Boyao', 'Carmen', 'O.', NULL, 1, 2, '1952-10-06', NULL, NULL, NULL, NULL, 1, 50, 2, NULL, 1, 48),
(119, 'Buyocan', 'Leo', 'Osting', NULL, 1, 1, '1977-10-22', NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, 49),
(120, 'Buyocan', 'Susing', 'Pocol', NULL, 2, 2, '1978-01-17', NULL, NULL, NULL, NULL, NULL, 16, 2, NULL, 1, 49),
(121, 'Buyocan', 'Lance', 'Pocol', NULL, 3, 1, '2013-10-01', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 49),
(122, 'Buyocan', 'Lilian', 'O.', NULL, 1, 2, '1979-12-28', NULL, NULL, NULL, NULL, 1, 50, NULL, NULL, 1, 50),
(123, 'Buyocan', 'Joel', 'O.', NULL, 1, 1, '1975-06-07', NULL, NULL, NULL, NULL, 1, 50, 2, NULL, 1, 51),
(124, 'Buyocan', 'Peter', 'Baogan', NULL, 1, 1, '1950-05-01', NULL, NULL, NULL, NULL, 1, 49, 2, NULL, 1, 52),
(125, 'Buyocan', 'Carmen', 'Osting', NULL, 2, 2, '1952-06-01', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 52),
(126, 'Buyocan', 'Joden', 'Monayao', NULL, NULL, 1, '2006-08-01', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 52),
(127, 'Cadiogan', 'Anastacia', NULL, NULL, 1, 2, NULL, NULL, NULL, NULL, NULL, 1, 9, NULL, NULL, 1, 53),
(128, 'Calapiao', 'Novie ann', NULL, NULL, 1, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 54),
(129, 'Calantas', 'Norma', 'Caligtan', NULL, 1, 2, '1979-08-29', NULL, NULL, NULL, NULL, 1, 55, NULL, NULL, 1, 55),
(130, 'Singaben', 'Lionard', 'Calantas', NULL, 3, 1, '2006-12-02', NULL, NULL, NULL, NULL, 1, 55, 1, NULL, 1, 55),
(131, 'Singaben', 'Leonor', 'Calantas', NULL, 4, 2, '2010-10-15', NULL, NULL, NULL, NULL, 1, 55, 1, NULL, 1, 55),
(132, 'Singaben', 'Leo', 'Calantas', NULL, 3, 1, '2012-10-13', NULL, NULL, NULL, NULL, 1, 55, 1, NULL, 1, 55),
(133, 'Calantas', 'Benito', 'Caligtan', NULL, 1, 1, '1972-02-16', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 56),
(134, 'Calantas', 'Eufemia', 'Colintas', NULL, 2, 2, '1978-02-18', NULL, NULL, NULL, NULL, 1, 18, 2, NULL, 1, 56),
(135, 'Calantas', 'Juliet', 'Colintas', NULL, 4, 2, '1999-07-30', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 56),
(136, 'Calantas', 'Benito', 'Colintas', NULL, 3, 1, '2001-10-10', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 56),
(137, 'Calantas', 'Clarence', 'Caligtan', NULL, 1, 1, '1983-08-29', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 57),
(138, 'Calantas', 'Merlanie', 'Mangatam', NULL, 2, 2, '1973-12-20', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 57),
(139, 'Calantas', 'Cherry mae', 'Mangatam', NULL, 4, 2, '2000-01-17', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 57),
(140, 'Calantas', 'Sherwin', 'Mangatam', NULL, 3, 1, '2007-02-17', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 57),
(141, 'Caline', 'Jimmy', NULL, NULL, 1, 1, '1986-09-23', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 58),
(142, 'Prudente', 'Teodora', 'Adornado', NULL, 2, 2, '1983-09-11', NULL, NULL, NULL, NULL, NULL, 72, NULL, NULL, 1, 58),
(143, 'Caline', 'Jessie', 'Prudente', NULL, 3, 1, '2017-07-03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 58),
(144, 'Caline', 'James', 'Prudente', NULL, 3, 1, '2017-07-03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 58),
(145, 'Prudente', 'Jovie', 'Adornado', NULL, 14, 2, '1996-08-13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 58),
(146, 'Lagmay', 'Jhosua', 'Prudente', NULL, NULL, 1, '2010-01-11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 58),
(147, 'Campos', 'Wilfredo', 'Marcena', NULL, 1, 1, '1969-01-13', NULL, NULL, NULL, NULL, 19, 52, 2, NULL, 1, 59),
(148, 'Campos', 'Rosalyn', 'Paytocan', NULL, 2, 2, '1970-04-10', NULL, NULL, NULL, NULL, 19, 9, 2, 3, 1, 59),
(149, 'Campos', 'Adrian', 'Danalo', NULL, 3, 1, '2007-06-10', NULL, NULL, NULL, NULL, 19, 9, 1, NULL, 1, 59),
(150, 'Campos', 'Rowell', 'Danalo', NULL, 1, 1, '1993-07-18', NULL, NULL, NULL, NULL, 19, 55, 2, NULL, 1, 60),
(151, 'Campos', 'Leny', 'Lamsis', NULL, 2, 2, '1992-02-14', NULL, NULL, NULL, NULL, 19, 16, 2, NULL, 1, 60),
(152, 'Campos', 'Rhynwell kurt', 'Lamsis', NULL, 3, 1, '2015-11-21', NULL, NULL, NULL, NULL, 19, 9, 1, NULL, 1, 60),
(153, 'Campos', 'Lhenzy mae', 'Lamsis', NULL, 4, 2, '2018-11-23', NULL, NULL, NULL, NULL, 19, 9, 1, NULL, 1, 60),
(154, 'Campos', 'Wilbert', 'Danalo', NULL, 1, 1, '1998-09-14', NULL, NULL, NULL, NULL, 19, 55, 1, NULL, 1, 61),
(155, 'Arciaga', 'Jorge', 'Campos', NULL, NULL, 1, '1998-10-22', NULL, NULL, NULL, NULL, 19, 52, 1, NULL, 1, 61),
(156, 'Carantes', 'Angeline', 'B.', NULL, 1, 2, '1963-08-05', NULL, NULL, NULL, NULL, 1, 9, NULL, NULL, 1, 62),
(157, 'Catbagan', 'Eric', NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 63),
(158, 'Ciano', 'Arjay', 'Donato', NULL, 1, 1, '1986-08-30', NULL, NULL, NULL, NULL, 1, 18, 2, NULL, 1, 64),
(159, 'Ciano', 'Marife', 'Wansi', NULL, 2, 2, '1989-03-21', NULL, NULL, NULL, NULL, 1, 20, 2, NULL, 1, 64),
(160, 'Ciano', 'Elijah', 'Wansi', NULL, 3, 1, '2018-04-18', NULL, NULL, NULL, NULL, 1, 18, 1, NULL, 1, 64),
(161, 'Ciano', 'Marcial', 'Mariano', NULL, 1, 1, '1961-06-30', NULL, NULL, NULL, NULL, 3, 16, 2, NULL, 1, 65),
(162, 'Ciano', 'S', 'Donato', NULL, 2, 2, '1965-04-26', NULL, NULL, NULL, NULL, 3, 9, 2, NULL, 1, 65),
(163, 'Ciano', 'Jayson', 'Donato', NULL, 3, 1, '1984-06-21', NULL, NULL, NULL, NULL, 3, 18, 1, NULL, 1, 65),
(164, 'Ciano', 'Jasmin', 'Donato', NULL, 4, 2, '1988-07-07', NULL, NULL, NULL, NULL, 3, 18, 1, NULL, 1, 65),
(165, 'Ciano', 'Jordan', 'Donato', NULL, 3, 1, '1989-11-22', NULL, NULL, NULL, NULL, 3, 18, 1, NULL, 1, 65),
(166, 'Cobebe', 'Domingo', 'A.', NULL, 1, 1, '1939-06-12', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 3, 66),
(167, 'Cobebe', 'Peter', 'Taco-eng', NULL, 1, 1, '1966-06-16', NULL, NULL, NULL, NULL, 1, 50, 2, NULL, 1, 67),
(168, 'Cobebe', 'Doris', 'Milles', NULL, 2, 2, '1974-12-05', NULL, NULL, NULL, NULL, 1, 50, 2, NULL, 1, 67),
(169, 'Cobebe', 'Melody', 'Bantasan', NULL, 4, 2, '1991-11-29', NULL, NULL, NULL, NULL, 1, 50, 1, NULL, 1, 67),
(170, 'Cobebe', 'Josielyn', 'Bantasan', NULL, 4, 2, '1994-01-13', NULL, NULL, NULL, NULL, 1, 50, 1, NULL, 1, 67),
(171, 'Cobebe', 'Cristine joy', 'Bantasan', NULL, 4, 2, '1996-05-16', NULL, NULL, NULL, NULL, 1, 50, 1, NULL, 1, 67),
(172, 'Cobebe', 'John peter', 'Bantasan', NULL, 3, 1, '1999-01-10', NULL, NULL, NULL, NULL, 1, 50, 1, NULL, 1, 67),
(173, 'Cobebe', 'Requinio', 'T.', NULL, 1, 1, '1963-07-09', NULL, NULL, NULL, NULL, 1, 9, NULL, NULL, 1, 68),
(174, 'Coga-ay', 'Lourdes', 'Ngoay', NULL, 1, 2, '1961-12-25', NULL, NULL, NULL, NULL, 1, 16, 4, NULL, 1, 69),
(175, 'Coga-ay', 'Roben', 'N.', NULL, 3, 1, '1986-04-15', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 69),
(176, 'Coga-ay', 'Alvrin dave', NULL, NULL, 9, 1, '2008-04-12', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 69),
(177, 'Corpuz', 'Judith', 'A.', NULL, 1, 2, NULL, NULL, NULL, NULL, NULL, 1, 9, NULL, NULL, 1, 70),
(178, 'Cuanso', 'Walter', 'L.', NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 71),
(179, 'Cuanso', 'Leonor', 'B.', NULL, 1, 2, '1970-03-10', NULL, NULL, NULL, NULL, 7, NULL, 5, NULL, 1, 72),
(180, 'Cuanso', 'Erlen', 'B.', NULL, 3, 1, '1996-10-04', NULL, NULL, NULL, NULL, 7, NULL, 1, NULL, 1, 72),
(181, 'Cuanso', 'Norale', 'B.', NULL, 4, 2, '2002-02-20', NULL, NULL, NULL, NULL, 7, NULL, 1, NULL, 1, 72),
(182, 'Cominga', 'Emilia', 'B.', NULL, 12, 2, '1930-00-00', NULL, NULL, NULL, NULL, 7, NULL, 4, NULL, 1, 72),
(183, 'Cuyasan', 'Bejaro', 'A.', NULL, 1, 1, '1982-09-11', NULL, NULL, NULL, NULL, NULL, 9, NULL, NULL, 1, 73),
(184, 'Cuyasan', 'Heidi', 'Farnican', NULL, NULL, 2, '1988-10-25', NULL, NULL, NULL, NULL, 7, 9, NULL, NULL, 1, 73),
(185, 'Cuyasan', 'Hitchzber', 'Farnican', NULL, NULL, 1, '2011-12-02', NULL, NULL, NULL, NULL, NULL, 9, NULL, NULL, 1, 73),
(186, 'Cuyasan', 'Heidelbrey', 'Farnican', NULL, NULL, 1, '2015-02-01', NULL, NULL, NULL, NULL, NULL, 9, NULL, NULL, 1, 73),
(187, 'Cuyasan', 'Brenhyle', 'Farnican', NULL, NULL, 1, '2017-01-01', NULL, NULL, NULL, NULL, NULL, 9, NULL, NULL, 1, 73),
(188, 'Cuyasan', 'Hayabrelle', 'Farnican', NULL, NULL, 2, '2020-02-01', NULL, NULL, NULL, NULL, NULL, 9, NULL, NULL, 1, 73),
(189, 'Cuyasan', 'Bolito', 'B.', NULL, 1, 1, '1954-06-01', NULL, NULL, NULL, NULL, 1, 9, NULL, NULL, 1, 74),
(190, 'Cuyasan', 'Harriet', 'A.', NULL, 2, 2, '1957-03-28', NULL, NULL, NULL, NULL, 1, 9, NULL, NULL, 1, 74),
(191, 'Cuyasan', 'Juller', 'A.', NULL, 3, 1, '1988-07-16', NULL, NULL, NULL, NULL, 1, 9, NULL, NULL, 1, 74),
(192, 'Cuyasan', 'Dervin', 'A.', NULL, 3, 1, '1989-12-05', NULL, NULL, NULL, NULL, 1, 9, NULL, NULL, 1, 74),
(193, 'Cuyasan', 'Gilford', 'A.', NULL, 1, 1, '1984-05-12', NULL, NULL, NULL, NULL, 1, 9, NULL, NULL, 1, 75),
(194, 'Bontayon', 'Marry- an', 'T.', NULL, 2, 2, '1992-01-10', NULL, NULL, NULL, NULL, 1, 9, NULL, NULL, 1, 75),
(195, 'Cuyasan', 'Faith gayle', 'A.', NULL, 4, 2, '2005-07-09', NULL, NULL, NULL, NULL, 1, 9, NULL, NULL, 1, 75),
(196, 'Cuyasan', 'Fender hanzael', 'B.', NULL, 3, 1, '2022-03-01', NULL, NULL, NULL, NULL, 1, 9, NULL, NULL, 1, 75),
(197, 'Dacsig', 'Elizabeth', NULL, NULL, 1, 2, '1943-03-28', NULL, NULL, NULL, NULL, 1, 18, NULL, NULL, 1, 76),
(198, 'Dacsig', 'Edwin', 'Danglasi', NULL, 1, 1, '1967-04-27', NULL, NULL, NULL, NULL, 1, 18, 2, NULL, 1, 77),
(199, 'Dacsig', 'Diana', 'Wayaan', NULL, 2, 2, '1981-12-20', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 77),
(200, 'Dacsig', 'Edison', 'Wayaan', NULL, 3, 1, '2003-12-06', NULL, NULL, NULL, NULL, 1, 18, 1, NULL, 1, 77),
(201, 'Dacsig', 'Charlie', 'Wayaan', NULL, 3, 1, '2005-04-09', NULL, NULL, NULL, NULL, 1, 18, 1, NULL, 1, 77),
(202, 'Dacsig', 'Megan', 'Wayaan', NULL, 4, 2, '2008-02-07', NULL, NULL, NULL, NULL, 1, 18, 1, NULL, 1, 77),
(203, 'Dacsig', 'Charise', 'Wayaan', NULL, 4, 2, '2011-05-01', NULL, NULL, NULL, NULL, 1, 18, 1, NULL, 1, 77),
(204, 'Dacsig', 'Jack', 'Wayaan', NULL, 3, 1, '2015-05-13', NULL, NULL, NULL, NULL, 1, 18, 1, NULL, 1, 77),
(205, 'Dagasen', 'Bickert', 'G.', NULL, 1, 1, '1978-10-04', NULL, NULL, NULL, NULL, 27, 9, 2, NULL, 1, 78),
(206, 'Dagasen', 'Isabel', 'S.', NULL, 2, 2, '1979-10-10', NULL, NULL, NULL, NULL, 27, NULL, 2, NULL, 1, 78),
(207, 'Dagasen', 'Zyph', 'S.', NULL, 3, 1, '2007-01-21', NULL, NULL, NULL, NULL, 27, 9, 1, NULL, 1, 78),
(208, 'Dagasen', 'Zain', 'S.', NULL, 3, 1, '2008-09-01', NULL, NULL, NULL, NULL, 27, 9, 1, NULL, 1, 78),
(209, 'Dagasen', 'Zawe', 'S.', NULL, 4, 2, '2013-04-23', NULL, NULL, NULL, NULL, 27, 9, 1, NULL, 1, 78),
(210, 'Dagasen', 'Blessildo', 'Galdoen', NULL, 1, 1, '1981-06-29', NULL, NULL, NULL, NULL, 27, 9, 2, NULL, 1, 79),
(211, 'Dagasen', 'Melodia lin', 'Lawangen', NULL, 2, 2, '1983-06-02', NULL, NULL, NULL, NULL, 27, 9, 2, NULL, 1, 79),
(212, 'Dagasen', 'Shaun', 'Lawangen', NULL, 3, 1, '2011-01-05', NULL, NULL, NULL, NULL, 27, 9, 1, NULL, 1, 79),
(213, 'Dagasen', 'Hesed', 'Lawangen', NULL, 3, 1, '2014-05-25', NULL, NULL, NULL, NULL, 27, 9, 1, NULL, 1, 79),
(214, 'Dagasen', 'Joen harmony', 'Lawangen', NULL, 4, 2, '2017-02-25', NULL, NULL, NULL, NULL, 27, 9, 1, NULL, 1, 79),
(215, 'Daguyen', 'Emilio', 'B.', NULL, 1, 1, '1972-01-29', NULL, NULL, NULL, NULL, 9, 52, 2, NULL, 1, 80),
(216, 'Daguyen', 'Vivian', 'C.', NULL, 2, 2, '1968-02-14', NULL, NULL, NULL, NULL, 9, NULL, 2, NULL, 1, 80),
(217, 'Daguyen', 'Emerson', 'C.', NULL, 3, 1, '2000-11-07', NULL, NULL, NULL, NULL, 9, NULL, 1, NULL, 1, 80),
(218, 'Dakiwas', 'Tamson', NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, 1, 9, NULL, NULL, 1, 81),
(219, 'Damino', 'Noli', 'Coytop', NULL, 1, 1, '1985-11-24', NULL, NULL, NULL, NULL, 1, 50, 2, NULL, 1, 82),
(220, 'Damino', 'Paloma', 'Descio', NULL, 2, 2, '1989-10-20', NULL, NULL, NULL, NULL, 1, 50, 2, NULL, 1, 82),
(221, 'Damino', 'Dexter', 'Descio', NULL, 3, 1, '2009-04-14', NULL, NULL, NULL, NULL, 1, 50, 1, NULL, 1, 82),
(222, 'Damino', 'Chloe shane', 'Descio', NULL, 4, 2, '2012-03-29', NULL, NULL, NULL, NULL, NULL, 50, 1, NULL, 1, 82),
(223, 'Gavino', 'Marson', 'Bernal', NULL, NULL, 1, '1976-03-23', NULL, NULL, NULL, NULL, NULL, 50, 1, NULL, 1, 82),
(224, 'Danalo', 'Santos', 'Damaso', NULL, 1, 1, '1966-12-12', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 83),
(225, 'Danalo', 'Lorna', 'Damaso', NULL, 2, 2, '1975-01-31', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 83),
(226, 'Danalo', 'Christine', 'Damaso', NULL, 4, 2, '1995-08-09', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 83),
(227, 'Danalo', 'Cathy claire', 'Damaso', NULL, 4, 2, '1998-09-20', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 83),
(228, 'Danalo', 'Crissamae', 'Damaso', NULL, 4, 2, '2000-08-23', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 83),
(229, 'Danalo', 'Crisanto', 'Damaso', NULL, 1, 1, '1993-02-12', NULL, NULL, NULL, NULL, 1, 20, 2, NULL, 1, 84),
(230, 'Danalo', 'Manilyn', 'Finmon-as', NULL, 2, 2, '1992-09-10', NULL, NULL, NULL, NULL, 1, 8, 2, NULL, 1, 84),
(231, 'Danalo', 'Dexter', 'Finmon-as', NULL, 3, 1, '2016-11-09', NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 84),
(232, 'Dangli', 'Francis', 'A.', NULL, 1, 1, '1974-06-30', NULL, NULL, NULL, NULL, 3, 9, 2, NULL, 1, 85),
(233, 'Dangli', 'Leonora may', 'A.', NULL, 2, 2, '1988-11-01', NULL, NULL, NULL, NULL, 3, 52, 2, NULL, 1, 85),
(234, 'Dangli', 'Linden', 'A.', NULL, 3, 1, '2011-05-29', NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, 1, 85),
(235, 'Dangli', 'Lendra', 'A.', NULL, 4, 2, '2015-02-22', NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, 1, 85),
(236, 'Danglose', 'Lorenzo', NULL, NULL, 1, 1, '1947-06-01', NULL, NULL, NULL, NULL, 22, 9, NULL, NULL, 1, 86),
(237, 'Sabino', 'Hellen', 'D.', NULL, 1, 2, '1985-01-02', NULL, NULL, NULL, NULL, 9, 18, NULL, NULL, 1, 87),
(238, 'Dangpa', 'Allan', 'A.', NULL, 1, 1, '1976-08-10', NULL, NULL, NULL, NULL, 1, 16, NULL, NULL, 1, 88),
(239, 'Dasi', 'Julie', 'O.', NULL, 1, 2, '1977-02-16', NULL, NULL, NULL, NULL, 9, 16, NULL, NULL, 1, 89),
(240, 'Dawat', 'Erlinda', 'De guzman', NULL, 1, 2, '1953-10-21', NULL, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, 90),
(241, 'Dawat', 'Rolando', 'De guzman', NULL, 3, 1, '1979-09-14', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 90),
(242, 'Dawat', 'Rose marie ann', 'De guzman', NULL, NULL, 2, '1990-08-24', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 90),
(243, 'Dawat', 'Rachel mae', 'Tanedo', NULL, NULL, 2, '2004-12-21', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 90),
(244, 'Manalo', 'Roella', 'Dawat', NULL, NULL, 2, '1985-01-13', NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, 90),
(245, 'Manalo', 'Noburo', 'Cabanezas', NULL, NULL, 1, '1984-09-17', NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, 90),
(246, 'Abrajano', 'Alejo', 'Floria', NULL, NULL, 1, '1982-01-05', NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, 90),
(247, 'Abrajano', 'Ronalyn', 'Dawat', NULL, NULL, 2, '1982-07-27', NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, 90),
(248, 'Abrajano', 'Allen zane', 'Dawat', NULL, NULL, 1, '2003-04-15', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 90),
(249, 'Abrajano', 'Athalie irene', 'Dawat', NULL, NULL, 2, '2010-09-09', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 90),
(250, 'Manalo', 'Rhurorio', 'Dawat', NULL, NULL, 2, '2013-07-12', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 90),
(251, 'Manalo', 'Rhureria', 'Dawat', NULL, NULL, 2, '2015-05-04', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 90),
(252, 'Dayawen', 'Andrew', 'L.', NULL, 1, 1, '1945-03-12', NULL, NULL, NULL, NULL, 22, 16, 2, NULL, 1, 91),
(253, 'Dayawen', 'Hilaria', 'Kidmano', NULL, 2, 2, '1954-08-22', NULL, NULL, NULL, NULL, 22, 16, 2, NULL, 1, 91),
(254, 'Dayawen', 'Andrea', 'Kidmano', NULL, 4, 2, '1989-07-21', NULL, NULL, NULL, NULL, 22, 16, 1, NULL, 1, 91),
(255, 'Dayawen', 'Reynaldo', 'Kidmano', NULL, 3, 1, '1993-12-02', NULL, NULL, NULL, NULL, 22, 16, 1, NULL, 1, 91),
(256, 'Sabado', 'Khaleen', 'Dayawen', NULL, 4, 2, '2014-08-04', NULL, NULL, NULL, NULL, 22, 16, 1, NULL, 1, 91),
(257, 'Dayawen', 'Siera nissi', 'Dinamling', NULL, 4, 2, '2020-10-23', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 92),
(258, 'Dayawen', 'Seith andrew', 'Dimaling', NULL, 3, 1, '2022-01-07', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 92),
(259, 'Dayawen', 'Joel', 'K.', NULL, 1, 1, '1985-10-27', NULL, NULL, NULL, NULL, 3, NULL, NULL, NULL, 1, 93),
(260, 'De guzman', 'Teresita', 'Depiason', NULL, 1, 2, '1954-01-03', NULL, NULL, NULL, NULL, 14, 16, 2, NULL, 1, 94),
(261, 'De guzman', 'Virginia', NULL, NULL, 1, 2, '1955-07-13', NULL, NULL, NULL, NULL, 1, 52, NULL, NULL, 1, 95),
(262, 'De guzman', 'Mark anthony', 'D.', NULL, 1, 1, '1986-07-01', NULL, NULL, NULL, NULL, 1, 52, NULL, NULL, 1, 96),
(263, 'Della', 'Cesar', NULL, NULL, 1, 1, '1959-02-28', NULL, NULL, NULL, NULL, 3, NULL, NULL, NULL, 1, 97),
(264, 'Dela cruz', 'Dexter', 'Villena', NULL, 1, 1, '1988-02-06', NULL, NULL, NULL, NULL, NULL, 72, 2, NULL, 1, 98),
(265, 'Dela cruz', 'Shannon', 'Ayugat', NULL, 2, 2, '1984-07-04', NULL, NULL, NULL, NULL, NULL, 9, 2, NULL, 1, 98),
(266, 'Dela cruz', 'Mackenzie', 'Ayugat', NULL, 4, 2, '2015-04-14', NULL, NULL, NULL, NULL, NULL, 9, 1, NULL, 1, 98),
(267, 'Dela cruz', 'Skylar', 'Ayugat', NULL, 4, 2, '2018-04-16', NULL, NULL, NULL, NULL, NULL, 9, 1, NULL, 1, 98),
(268, 'Denesia', 'Sebio', 'B.', NULL, 1, 1, '1950-09-23', NULL, NULL, NULL, NULL, 1, 9, NULL, NULL, 1, 99),
(269, 'Denesia', 'Seny boy', NULL, NULL, 1, 1, '1982-09-12', NULL, NULL, NULL, NULL, 1, 18, NULL, NULL, 1, 92),
(270, 'Descio', 'Mark', NULL, NULL, 1, 1, '1987-03-02', NULL, NULL, NULL, NULL, 1, 16, NULL, NULL, 1, 100),
(271, 'Dio-alan', 'Delio', 'Bayawa', NULL, 1, 1, '1981-09-04', NULL, NULL, NULL, NULL, 3, 9, 2, NULL, 1, 101),
(272, 'Dio-alan', 'Diana', 'Lawan', NULL, 2, 2, '1985-01-05', NULL, NULL, NULL, NULL, 3, 9, 2, NULL, 1, 101),
(273, 'Dio-alan', 'Darryl', 'Darryl', NULL, 3, 1, '2004-11-17', NULL, NULL, NULL, NULL, 3, 9, 1, NULL, 1, 101),
(274, 'Dio-alan', 'Dylan', 'Dylan', NULL, 3, 1, '2006-12-02', NULL, NULL, NULL, NULL, 3, 9, 1, NULL, 1, 101),
(275, 'Dio-alan', 'Casey', 'Casey', NULL, 4, 2, '2015-01-31', NULL, NULL, NULL, NULL, 3, 9, 1, NULL, 1, 101),
(276, 'Dizon', 'Joselito', 'S.', NULL, 1, 1, '1974-12-03', NULL, NULL, NULL, NULL, 15, 20, 2, NULL, 1, 102),
(277, 'Bagtangan', 'Aurora', 'K.', NULL, 2, 2, '1978-08-12', NULL, NULL, NULL, NULL, 15, 9, 2, NULL, 1, 102),
(278, 'Dizon', 'Daisy beth', 'B.', NULL, 4, 2, '2004-08-08', NULL, NULL, NULL, NULL, 15, 9, 1, NULL, 1, 102),
(279, 'Dizon', 'Dunnjoe', 'B.', NULL, 3, 1, '2009-01-02', NULL, NULL, NULL, NULL, 15, 9, 1, NULL, 1, 102),
(280, 'Dizon', 'Dezza joy', 'B.', NULL, 4, 2, '2011-10-20', NULL, NULL, NULL, NULL, 15, 9, 1, NULL, 1, 102),
(281, 'Duclayan', 'Norma', NULL, NULL, 1, 2, '1968-05-15', NULL, NULL, NULL, NULL, 1, 16, NULL, NULL, 1, 103),
(282, 'Duclayan', 'Benjie', NULL, NULL, 1, 1, '1993-07-28', NULL, NULL, NULL, NULL, NULL, 44, 1, NULL, 1, 104),
(283, 'Aghan', 'Leziel', 'Cosep', NULL, 2, 2, '1996-10-27', NULL, NULL, NULL, NULL, NULL, 60, 1, NULL, 1, 104),
(284, 'Duclayan', 'Mary grace', 'Danalo', NULL, 1, 2, '1995-05-28', NULL, NULL, NULL, NULL, NULL, 44, 1, NULL, 1, 105),
(285, 'Duclayan', 'Mary jane', 'Danalo', NULL, 1, 2, '1995-05-28', NULL, NULL, NULL, NULL, NULL, 44, 1, NULL, 1, 106),
(286, 'Espiritu', 'Shonn', 'Duclayan', NULL, 3, 1, '2014-08-12', NULL, NULL, NULL, NULL, NULL, 44, NULL, NULL, 1, 106),
(287, 'Duclayan', 'Rhayver ace', NULL, NULL, 3, 1, '2016-04-13', NULL, NULL, NULL, NULL, NULL, 44, NULL, NULL, 1, 106),
(288, 'Duclayan', 'Roselyn', NULL, NULL, 4, 2, '2018-06-13', NULL, NULL, NULL, NULL, NULL, 44, NULL, NULL, 1, 106),
(289, 'Duclayan', 'Norma', 'Paytocan', NULL, 1, 2, '1968-05-15', NULL, NULL, NULL, NULL, NULL, 16, NULL, 3, 1, 107),
(290, 'Duclayan', 'Joel', 'Danalo', NULL, NULL, 1, '2004-11-12', NULL, NULL, NULL, NULL, NULL, 44, 1, NULL, 1, 107),
(291, 'Ennangen', 'Beatriz', 'G.', NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, 1, 72),
(292, 'Esteban', 'Noel', 'Mariano', NULL, 1, 1, '1983-12-13', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 108),
(293, 'Alkera', 'Tasty fabio', 'Mariano', NULL, NULL, 1, '1994-01-26', NULL, NULL, NULL, NULL, NULL, 16, 2, NULL, 1, 108),
(294, 'Alkera', 'Jessica', 'Esteban', NULL, NULL, 2, '1991-01-10', NULL, NULL, NULL, NULL, NULL, 16, 2, NULL, 1, 108),
(295, 'Alkera', 'Fabio jace', 'Esteban', NULL, NULL, 1, '2014-01-23', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 108),
(296, 'Estilong', 'Elvira', 'Langpawen', NULL, 1, 2, '1952-10-10', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 109),
(297, 'Usher', 'Babylyn', 'Estilong', NULL, NULL, 2, '1984-03-15', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 109),
(298, 'De la cruz', 'Marry ann', 'Estilong', NULL, NULL, 2, '1978-05-27', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 109),
(299, 'De la cruz', 'Princess', 'Estilong', NULL, NULL, 2, '2003-09-05', NULL, NULL, NULL, NULL, 1, 47, 1, NULL, 1, 109),
(300, 'Falyao', 'Ariane', NULL, NULL, NULL, 2, '1985-12-29', NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, 1, 58),
(301, 'Galdoen', 'Dominador', 'Sumabat', NULL, 1, 1, '1972-06-14', NULL, NULL, NULL, NULL, 3, 52, 2, NULL, 1, 110),
(302, 'Galdoen', 'Beaulahlyn', 'Castro', NULL, 2, 2, '1976-07-24', NULL, NULL, NULL, NULL, 3, 11, 2, NULL, 1, 110),
(303, 'Galdoen', 'Daniel jun', 'Castro', NULL, 3, 1, '1997-03-08', NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, 1, 110),
(304, 'Galdoen', 'Noemi faith', 'Castro', NULL, 4, 2, '1999-02-02', NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, 1, 110),
(305, 'Galdoen', 'Princess may', 'Castro', NULL, 4, 2, '2000-09-25', NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, 1, 110),
(306, 'Galdoen', 'Madeleine', 'Castro', NULL, 4, 2, '2005-04-07', NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, 1, 110),
(307, 'Aton', 'Jesebel', 'T.', NULL, NULL, 2, '1974-09-09', NULL, NULL, NULL, NULL, 1, 72, NULL, NULL, 1, 111),
(308, 'Garcia', 'Ronald', 'A.', NULL, 1, 1, '1982-04-08', NULL, NULL, NULL, NULL, NULL, 52, 2, NULL, 1, 112),
(309, 'Garcia', 'Juliet', 'T.', NULL, 2, 2, '1989-06-17', NULL, NULL, NULL, NULL, NULL, 9, 2, NULL, 1, 112),
(310, 'Ibusag', 'Arnel', 'Jacala', NULL, NULL, 1, '1984-10-01', NULL, NULL, NULL, NULL, 1, 52, 2, NULL, 1, 113),
(311, 'Ibusag', 'Beverlyn', 'Agbunag', NULL, 21, 2, '1991-10-30', NULL, NULL, NULL, NULL, 1, 52, 2, NULL, 1, 113),
(312, 'Ibusag', 'Arben nellyn', 'Agbunag', NULL, 21, 2, '2012-11-06', NULL, NULL, NULL, NULL, 1, 52, 1, NULL, 1, 113),
(313, 'Ibusag', 'Arrianna', 'Agbunag', NULL, 21, 2, '2018-05-04', NULL, NULL, NULL, NULL, 1, 52, 1, NULL, 1, 113),
(314, 'Inway', 'Adoracion', 'B.', NULL, 1, 2, '1937-07-01', NULL, NULL, NULL, NULL, 1, 18, 4, NULL, 1, 114),
(315, 'Inway', 'Teresita', NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 114),
(316, 'Inway', 'Christopher', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 114),
(317, 'Inway', 'Derrick', 'B.', NULL, NULL, 1, '1981-07-01', NULL, NULL, NULL, NULL, 1, 18, 1, NULL, 1, 114),
(318, 'Inway', 'Anna belinda', 'B.', NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 1, 18, 1, NULL, 1, 114),
(319, 'Reyes', 'Charen', 'I.', NULL, NULL, 2, '1985-07-01', NULL, NULL, NULL, NULL, 1, 18, 1, NULL, 1, 114),
(320, 'Reyes', 'Freneil', 'I.', NULL, NULL, 1, '1987-07-01', NULL, NULL, NULL, NULL, 1, 18, 1, NULL, 1, 114),
(321, 'Ibanan', 'Joel', 'Bernal', NULL, 1, 1, '1981-07-23', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 115),
(322, 'Ibanan', 'Brigitte ann', 'Kitiman', NULL, 2, 2, '1995-03-13', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 115),
(323, 'Ibanan', 'Johannah', 'Kitiman', NULL, 4, 2, '2014-02-21', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 115),
(324, 'Ismael', 'Marvin', 'Mercado', NULL, 1, 1, '1979-11-28', NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, 111),
(325, 'Ismael', 'Angel', 'Rimorin', NULL, 21, 2, '1975-11-26', NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, 111),
(326, 'Ismael', 'Marc vincent', 'Rimorin', NULL, 21, 1, '2005-03-16', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 111),
(327, 'Ismael', 'John wendelle', 'Rimorin', NULL, 21, 1, '2010-06-03', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 111),
(328, 'Ismael', 'Gelvin france', 'Rimorin', NULL, 21, 1, '2014-02-08', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 111),
(329, 'Jose', 'Darwin', 'Agyamoc', NULL, 1, 1, '1984-03-17', NULL, NULL, NULL, NULL, 1, NULL, 2, NULL, 1, 116),
(330, 'Jose', 'Analyn', 'Bantowag', NULL, 2, 2, '1988-02-10', NULL, NULL, NULL, NULL, 1, 55, 2, NULL, 1, 116),
(331, 'Jose', 'Jherwin', 'Bantowag', NULL, 3, 1, '2007-12-06', NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 116),
(332, 'Jose', 'Johnwin', 'Bantowag', NULL, 3, 1, '2009-12-31', NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 116),
(333, 'Bantowag', 'Emiliano', 'Suoc', NULL, 19, 1, '1927-04-04', NULL, NULL, NULL, NULL, 1, NULL, 4, NULL, 1, 116),
(334, 'Jose', 'Valentino', 'C.', NULL, 1, 1, '1965-02-14', NULL, NULL, NULL, NULL, 3, 53, 2, NULL, 1, 117),
(335, 'Jose', 'Trinidad', 'D.', NULL, 2, 2, '1965-02-16', NULL, NULL, NULL, NULL, 3, 52, 2, NULL, 1, 117),
(336, 'Jose', 'Archie kitch', 'D.', NULL, 3, 1, '1988-10-01', NULL, NULL, NULL, NULL, 3, 52, 2, NULL, 1, 117),
(337, 'Jose', 'Rocklyn', 'V.', NULL, 4, 2, '1991-06-12', NULL, NULL, NULL, NULL, 3, 52, 2, NULL, 1, 117),
(338, 'Jose', 'Aaliyah khane', 'V.', NULL, NULL, 2, '2010-10-06', NULL, NULL, NULL, NULL, 3, 52, 1, NULL, 1, 117),
(339, 'Jose', 'Alicia khate', 'V.', NULL, NULL, 2, '2014-07-19', NULL, NULL, NULL, NULL, 3, 52, 1, NULL, 1, 117),
(340, 'Pilit', 'Marjorie', 'J.', NULL, NULL, 2, '1990-06-29', NULL, NULL, NULL, NULL, 3, 52, 2, NULL, 1, 117),
(341, 'Pilit', 'Chase', NULL, NULL, NULL, 1, '1989-07-29', NULL, NULL, NULL, NULL, 3, 52, 2, NULL, 1, 117),
(342, 'Pilit', 'Zoe', 'J.', NULL, NULL, 2, '2016-11-10', NULL, NULL, NULL, NULL, 3, 52, 1, NULL, 1, 117),
(343, 'Melendres', 'Gretchen', 'J.', NULL, NULL, 2, '1992-06-18', NULL, NULL, NULL, NULL, 3, 52, 2, NULL, 1, 117),
(344, 'Melendres', 'Andrew', NULL, NULL, NULL, 1, '1991-02-15', NULL, NULL, NULL, NULL, 3, 52, 2, NULL, 1, 117),
(345, 'Dazon', 'Precious jane', 'B.', NULL, NULL, 2, '2002-03-25', NULL, NULL, NULL, NULL, 3, 52, 1, NULL, 1, 117),
(346, 'Tobias', 'Ericka', 'J.', NULL, NULL, 2, '2000-10-04', NULL, NULL, NULL, NULL, 3, 52, 1, NULL, 1, 117),
(347, 'Comediam', 'Maridel', 'D.', NULL, NULL, 2, '1993-09-08', NULL, NULL, NULL, NULL, 3, 52, 2, NULL, 1, 117),
(348, 'Comediam', 'Jerome', 'D.', NULL, NULL, 1, '1993-05-17', NULL, NULL, NULL, NULL, 3, 52, 1, NULL, 1, 117),
(349, 'Segundo', 'Venus', 'T.', NULL, NULL, 2, '2000-05-06', NULL, NULL, NULL, NULL, 3, 52, 1, NULL, 1, 117),
(350, 'Segundo', 'Jennifer', 'D.', NULL, NULL, 2, '1977-05-17', NULL, NULL, NULL, NULL, 3, 52, 2, NULL, 1, 117),
(351, 'Kidmano', 'Freddie lessking', NULL, NULL, 1, 1, '1979-07-01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 118),
(352, 'Kidmano', 'Peter', 'Mariano', NULL, 1, 1, '1964-10-29', NULL, NULL, NULL, NULL, 1, 9, NULL, NULL, 1, 119),
(353, 'Kidmano', 'Jayrald', 'Agyamoc', NULL, 3, 1, '1987-04-15', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 119),
(354, 'Kidmano', 'Pamela joy', 'Agyamoc', NULL, 4, 2, '1989-03-06', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 119),
(355, 'Pulido', 'Rena', 'Kidmano', NULL, 4, 2, '1995-08-01', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 119),
(356, 'Kidmano', 'Carlo', 'Kidmano', NULL, 3, 1, '1997-01-14', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 119),
(357, 'Kidmano', 'Jennifer', NULL, NULL, NULL, 2, '1979-08-27', NULL, NULL, NULL, NULL, 11, 16, NULL, NULL, 1, 68),
(358, 'Kinuan', 'Melchor', 'Cayohan', NULL, 1, 1, '1966-01-06', NULL, NULL, NULL, NULL, 1, 16, NULL, NULL, 1, 120),
(359, 'Kinwan', 'James', 'Cayuhan', NULL, 1, 1, '1974-08-15', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 121),
(360, 'Kinwan', 'Herminia', 'Aplaten', NULL, 2, 2, NULL, NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 121),
(361, 'Kinwan', 'Jhame rex', 'Aplaten', NULL, 3, 1, '2004-10-08', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 121),
(362, 'Kinwan', 'Mario', 'Cayuhan', NULL, 1, 1, '1954-03-07', NULL, NULL, NULL, NULL, NULL, 16, 4, NULL, 1, 122),
(363, 'Kinuan', 'Robert', 'Cuaban', NULL, 3, 1, '1980-10-08', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 122),
(364, 'Kinuan', 'Rowena', 'Cuaban', NULL, 4, 2, '1985-08-01', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 122),
(365, 'Kinwan', 'Victor', 'Cayuhan', NULL, 1, 1, '1952-07-02', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 123),
(366, 'Kinwan', 'Bernard', 'I.', NULL, 3, 1, '1988-02-28', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 123),
(367, 'Kinwan', 'Bernadeth', 'I.', NULL, 4, 2, '1990-05-11', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 123),
(368, 'Labbot', 'Gabriel', 'Omas', NULL, 1, 1, '1992-04-20', NULL, NULL, NULL, NULL, NULL, 50, 1, NULL, 1, 124),
(369, 'Dumelod', 'Eric', 'Cavinte', NULL, 21, 1, '1985-01-16', NULL, NULL, NULL, NULL, NULL, 50, 1, NULL, 1, 124),
(370, 'Dumelod', 'Nico', 'Dumayag', NULL, 21, 1, '2010-03-27', NULL, NULL, NULL, NULL, NULL, 50, 1, NULL, 1, 124),
(371, 'Laconsay', 'Mauro', 'Bunoy', NULL, 1, 1, '1963-10-14', NULL, NULL, NULL, NULL, 3, 7, 2, NULL, 1, 125),
(372, 'Laconsay', 'Mary jane', 'Cacapit', NULL, 2, 2, '1976-10-16', NULL, NULL, NULL, NULL, 3, 9, 2, NULL, 1, 125),
(373, 'Laconsay', 'Teryl josiah', 'Cacapit', NULL, 3, 1, '2003-07-11', NULL, NULL, NULL, NULL, 3, 9, 1, NULL, 1, 125),
(374, 'Laconsay', 'Jedidah vine', 'Cacapit', NULL, 4, 2, '2007-10-07', NULL, NULL, NULL, NULL, 3, 9, 1, NULL, 1, 125),
(375, 'Laconsay', 'Mauro', 'Cacapit', NULL, 3, 1, '2010-04-02', NULL, NULL, NULL, NULL, 3, 9, 1, NULL, 1, 125),
(376, 'Cula-ag', 'Lavi joy', 'Cacapit', NULL, 4, 2, '1996-09-19', NULL, NULL, NULL, NULL, 3, 9, 2, NULL, 1, 125),
(377, 'Cula-ag', 'Lavis heron', 'Cacapit', NULL, 9, 1, '2014-12-27', NULL, NULL, NULL, NULL, 3, 9, 1, NULL, 1, 125),
(378, 'Pagulongan', 'Judy', NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 1, 124),
(379, 'Landisan', 'Dominique', 'Jacinto', NULL, 1, 1, '1976-12-05', NULL, NULL, NULL, NULL, 1, NULL, 2, NULL, 1, 126),
(380, 'Landisan', 'Ma. concepcion', 'Pe benito', NULL, 2, 2, '1976-12-09', NULL, NULL, NULL, NULL, 1, NULL, 2, NULL, 1, 126),
(381, 'Landisan', 'Monica angel', 'Pe benito', NULL, 4, 2, '1997-05-20', NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 126),
(382, 'Landisan', 'Jean nichole', 'Pe benito', NULL, 3, 1, '1999-04-10', NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 126),
(383, 'Lapastora', 'Jennifer', NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 1, 16, NULL, NULL, 1, 127),
(384, 'Layog', 'Gaspar', 'Balteng', NULL, 1, 1, '1957-01-06', NULL, NULL, NULL, NULL, NULL, 9, 2, NULL, 1, 128),
(385, 'Layog', 'Mary', 'Pelingan', NULL, 2, 2, '1963-12-24', NULL, NULL, NULL, NULL, NULL, 55, 2, NULL, 1, 128),
(386, 'Layog', 'Geraldson', 'Pelingan', NULL, 3, 1, '1996-11-27', NULL, NULL, NULL, NULL, NULL, 55, 1, NULL, 1, 128),
(387, 'Layog', 'Zhaiden Jhed', 'Pelingan', NULL, 9, 1, '2019-01-23', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 128),
(388, 'Pelingan', 'Gleireca', NULL, NULL, NULL, 2, '2003-03-03', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 128),
(389, 'Kimayong', 'Analiza', NULL, NULL, NULL, 2, '1992-03-23', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 128),
(390, 'Magsingit', 'Edna', 'D.', NULL, NULL, 2, '1958-08-21', NULL, NULL, NULL, NULL, 5, 9, NULL, NULL, 1, 41),
(391, 'Manaois', 'Teresa', 'Langpaoen', NULL, 1, 2, '1964-09-05', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 129),
(392, 'Manaois', 'Dante', 'Danalo', NULL, 3, 1, '1993-07-11', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 129),
(393, 'Manaois', 'Marvin', 'Danalo', NULL, 3, 1, '1994-12-23', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 129),
(394, 'Manaois', 'Delfin', 'Danalo', NULL, 3, 1, '1997-09-30', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 129),
(395, 'Manganip', 'Vicente', 'Daowan', NULL, 1, 1, '1968-09-13', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 130),
(396, 'Manganip', 'Mitchel', 'Wansi', NULL, 2, 2, '1973-06-14', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 130),
(397, 'Manganip', 'Summy', 'Wansi', NULL, 3, 1, '1994-09-29', NULL, NULL, NULL, NULL, 1, 18, 1, NULL, 1, 130),
(398, 'Manganip', 'Ivy', 'Wansi', NULL, 4, 2, '1997-10-23', NULL, NULL, NULL, NULL, 1, 18, 1, NULL, 1, 130),
(399, 'Manganip', 'Rose lily', 'Wansi', NULL, 4, 2, '2009-02-27', NULL, NULL, NULL, NULL, 1, 18, 1, NULL, 1, 130),
(400, 'Manuel', 'Alberto', 'D.', NULL, 1, 1, '1971-03-31', NULL, NULL, NULL, NULL, 21, 73, 2, NULL, 1, 131),
(401, 'Manuel', 'Margaret', 'A.', NULL, 2, 2, '1959-01-23', NULL, NULL, NULL, NULL, 21, 9, 2, NULL, 1, 131),
(402, 'Manuel', 'Charlie rendo', 'A.', NULL, 3, 1, '1998-07-31', NULL, NULL, NULL, NULL, 21, 9, 1, NULL, 1, 131),
(403, 'Lozano', 'Roscel', 'A.', NULL, 19, 2, '1998-12-05', NULL, NULL, NULL, NULL, 21, 9, 1, NULL, 1, 131),
(404, 'Marano', 'Ronald', 'Galletes', NULL, 1, 1, '1984-02-12', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 132),
(405, 'Marano', 'Juvylyn', 'Mariano', NULL, 2, 2, '1990-06-30', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 132),
(406, 'Marano', 'Rhenzelle', 'Mariano', NULL, 3, 1, '2009-05-17', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 132),
(407, 'Marano', 'Jyrish', 'Mariano', NULL, 3, 1, '2013-09-05', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 132),
(408, 'Marano', 'Zafirah', 'Mariano', NULL, 4, 2, '2018-10-05', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 132),
(409, 'Mariano', 'Dictor', 'Canuto', NULL, 1, 1, '1982-12-27', NULL, NULL, NULL, NULL, 18, 16, 2, 5, 1, 133),
(410, 'Mariano', 'Jean', 'Balahiw', NULL, 2, 2, '1988-01-16', NULL, NULL, NULL, NULL, 18, 16, 2, NULL, 1, 133),
(411, 'Mariano', 'Keith', 'Balahiw', NULL, 3, 1, '2011-02-22', NULL, NULL, NULL, NULL, 18, 16, 1, NULL, 1, 133),
(412, 'Mariano', 'Irish gail', 'Balahiw', NULL, 4, 2, '2013-12-17', NULL, NULL, NULL, NULL, 18, 16, 1, NULL, 1, 133),
(413, 'Mariano', 'Blake kienzel', 'Balahiw', NULL, 3, 1, '2018-01-03', NULL, NULL, NULL, NULL, 18, 16, 1, NULL, 1, 133),
(414, 'Mariano', 'Mely', 'Canuto', NULL, 1, 2, '1979-05-09', NULL, NULL, NULL, NULL, NULL, 18, 4, 5, 1, 134),
(415, 'Eslao', 'Trisha kate', 'Mariano', NULL, 4, 2, '2000-03-03', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 134),
(416, 'Eslao', 'Ivan lendl', 'Mariano', NULL, 3, 1, '2002-03-03', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 134),
(417, 'Eslao', 'Aldrien', 'Mariano', NULL, 3, 1, '2006-05-10', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 134),
(418, 'Eslao', 'Junn hyden', 'Mariano', NULL, 3, 1, '2008-10-03', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 134),
(419, 'Eslao', 'Zian kirvy', 'Mariano', NULL, 3, 1, '2011-11-03', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 134),
(420, 'Balangue', 'Zcakira miel', 'Mariano', NULL, NULL, NULL, '2018-12-30', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 134),
(421, 'Mariano', 'Dexter', NULL, NULL, NULL, 1, '1986-12-24', NULL, NULL, NULL, NULL, 1, 16, NULL, NULL, 1, 133),
(422, 'Mariano', 'Mario', 'Canuto', NULL, 1, 1, '1962-02-04', NULL, NULL, NULL, NULL, 1, 16, 2, 5, 1, 135),
(423, 'Mariano', 'Dorothy', 'Kinwan', NULL, 2, 2, '1962-02-02', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 135),
(424, 'Mariano', 'Jomar', 'Kinwan', NULL, 3, 1, '1991-12-20', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 135),
(425, 'Mariano', 'Donna marie', 'Kinwan', NULL, 4, 2, '1993-10-07', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 135),
(426, 'Mariano', 'Roderick', 'Kinwan', NULL, 3, 1, '1997-12-05', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 135),
(427, 'Mariano', 'Lennox', 'Kinwan', NULL, 3, 1, '2002-06-01', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 135),
(428, 'Marrero', 'Charlotte marie', NULL, NULL, NULL, 2, '1994-06-25', NULL, NULL, NULL, NULL, 1, 18, NULL, NULL, 1, 43),
(429, 'Martin', 'Alex', NULL, NULL, 1, 1, '1962-05-24', NULL, NULL, NULL, NULL, 1, 16, NULL, NULL, 1, 136),
(430, 'Martinez', 'Florence', 'B.', NULL, NULL, 2, '1990-04-27', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 137),
(431, 'Martinez', 'William', 'T.', NULL, 1, 1, '1965-05-30', NULL, NULL, NULL, NULL, NULL, 16, 2, NULL, 1, 137),
(432, 'Martinez', 'Judivina', 'O.', NULL, 2, 2, '1963-06-16', NULL, NULL, NULL, NULL, NULL, 9, 2, NULL, 1, 137),
(433, 'Maslian', 'Agnes', 'P.', NULL, 1, 2, '1958-04-19', NULL, NULL, NULL, NULL, NULL, 9, 4, NULL, 1, 138),
(434, 'Maslian', 'Riza kay', 'P.', NULL, 4, 2, '1993-10-22', NULL, NULL, NULL, NULL, NULL, 9, 1, NULL, 1, 138),
(435, 'Maslian', 'Jennilyn', 'P.', NULL, 1, 2, '1985-06-15', NULL, NULL, NULL, NULL, NULL, 9, 1, NULL, 1, 139),
(436, 'Maslian', 'Nena', 'Mariano', NULL, 1, 2, '1972-11-30', NULL, NULL, NULL, NULL, 1, 18, 4, NULL, 1, 140),
(437, 'Maslian', 'Nickel jeff', 'Mariano', NULL, 3, 1, '1993-10-22', NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 140),
(438, 'Maslian', 'Andrei lemor', 'Mariano', NULL, 3, 1, '1995-12-11', NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 140),
(439, 'Lomanog', 'Mc clint', 'Mariano', NULL, 3, 1, '2000-02-29', NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 140),
(440, 'Mateo', 'Imelda', NULL, NULL, 1, 2, '1962-11-23', NULL, NULL, NULL, NULL, 22, 60, NULL, NULL, 1, 141),
(441, 'Mendoza', 'Joel', NULL, NULL, NULL, 1, '2004-11-12', NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, 1, 107),
(442, 'Nana', 'Christian', NULL, NULL, NULL, 1, '1991-09-08', NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, 1, 95),
(443, 'Nayosan', 'Amon', 'Osdong', NULL, 1, 1, '1981-06-12', NULL, NULL, NULL, NULL, 1, 20, 2, NULL, 1, 142),
(444, 'Nayosan', 'Nelda', 'Estilong', NULL, 2, 2, '1980-11-09', NULL, NULL, NULL, NULL, 1, 55, 2, NULL, 1, 142),
(445, 'Nayosan', 'Christian dave', 'Estilong', NULL, 3, 1, '2002-10-12', NULL, NULL, NULL, NULL, 1, 44, 1, NULL, 1, 142),
(446, 'Nayosan', 'Kevin', 'Estilong', NULL, 3, 1, '2005-03-21', NULL, NULL, NULL, NULL, 1, 44, 1, NULL, 1, 142),
(447, 'Nayosan', 'Mon jehiel', 'Estilong', NULL, 3, 1, '2007-08-26', NULL, NULL, NULL, NULL, 1, 44, 1, NULL, 1, 142),
(448, 'Nayosan', 'Nayley', 'Estilong', NULL, 4, 2, '2014-03-15', NULL, NULL, NULL, NULL, 1, 44, 1, NULL, 1, 142);
INSERT INTO `resident` (`resident_id`, `last_name`, `first_name`, `middle_name`, `suffix`, `relationship_to_hh_id`, `sex_id`, `date_of_birth`, `birth_city_municipality`, `birth_province`, `birth_country`, `nationality_id`, `religion_id`, `ethnicity_id`, `marital_status_id`, `clan_id`, `resident_status_id`, `household_id`) VALUES
(449, 'Nicoleta', 'Norlen', 'Blanca', NULL, 1, 1, '1986-11-12', NULL, NULL, NULL, NULL, 1, NULL, 3, NULL, 1, 143),
(450, 'Moises', 'Imee', 'Lipata', NULL, 2, 2, '1987-01-18', NULL, NULL, NULL, NULL, 1, 9, 3, NULL, 1, 143),
(451, 'Moises', 'Raven', 'Lipata', NULL, 3, 1, '2005-07-19', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 143),
(452, 'Nicoleta', 'Jhay patrick', 'Moises', NULL, 3, 1, '2009-11-28', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 143),
(453, 'Nicoleta', 'Erwin josh', 'Moises', NULL, 3, 1, '2012-03-31', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 143),
(454, 'Nicoleta', 'Noimee jean', 'Moises', NULL, 4, 2, '2016-09-07', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 143),
(455, 'Olsim', 'Daniel', 'B.', NULL, 1, 1, '1969-06-01', NULL, NULL, NULL, NULL, 22, 60, 2, NULL, 1, 144),
(456, 'Olsim', 'Myla', 'C.', NULL, 2, 2, '1970-08-30', NULL, NULL, NULL, NULL, 22, 9, 2, NULL, 1, 144),
(457, 'Olsim', 'Jubelyn', 'C.', NULL, 4, 2, '1989-07-20', NULL, NULL, NULL, NULL, 22, 9, 1, NULL, 1, 144),
(458, 'Olsim', 'Meranel', 'C.', NULL, 4, 2, '1999-05-30', NULL, NULL, NULL, NULL, 22, 9, 1, NULL, 1, 144),
(459, 'Olsim', 'Leah', 'C.', NULL, 4, 2, '2004-06-20', NULL, NULL, NULL, NULL, 22, 9, 1, NULL, 1, 144),
(460, 'Olsim', 'Deborah', 'C.', NULL, 4, 2, '2006-01-09', NULL, NULL, NULL, NULL, 22, 9, 1, NULL, 1, 144),
(461, 'Olsim', 'Rebecca', 'C.', NULL, 4, 2, '2008-07-20', NULL, NULL, NULL, NULL, 22, 9, 1, NULL, 1, 144),
(462, 'Olsim', 'Sarah', 'C.', NULL, 4, 2, '2013-08-29', NULL, NULL, NULL, NULL, 22, 9, 1, NULL, 1, 144),
(463, 'Olsim', 'Atheny', 'C.', NULL, 4, 2, '2017-04-27', NULL, NULL, NULL, NULL, 22, 9, 1, NULL, 1, 144),
(464, 'Oppin', 'Joseph', 'Daewey', NULL, 1, 1, '1979-08-29', NULL, NULL, NULL, NULL, 1, 52, 2, NULL, 1, 145),
(465, 'Oppin', 'Namey', 'Calantas', NULL, 2, 2, '1975-12-24', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 145),
(466, 'Idor', 'Arnan', 'Calantas', NULL, NULL, 1, '1994-09-14', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 145),
(467, 'Kiwang', 'Joan', 'Wacay', NULL, NULL, 2, '2002-08-26', NULL, NULL, NULL, NULL, 1, 18, 1, NULL, 1, 145),
(468, 'Idor', 'Arnold', 'Calantas', NULL, NULL, 1, '1996-08-19', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 145),
(469, 'Guiwao', 'Grail', 'Sunga-ab', NULL, NULL, 2, '1997-06-10', NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 145),
(470, 'Calantas', 'Jerome', NULL, NULL, NULL, 1, '2001-11-10', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 145),
(471, 'Oppin', 'Jhonan', 'Calantas', NULL, NULL, 1, '2005-07-09', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 145),
(472, 'Oppin', 'Mary joy', 'Calantas', NULL, NULL, 2, '2007-06-29', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 145),
(473, 'Oppin', 'Joseph iii', 'Calantas', NULL, NULL, 1, '2014-10-01', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 145),
(474, 'Oreno', 'Mercedes', 'Eday', NULL, 1, 2, '1939-04-19', NULL, NULL, NULL, NULL, 1, 9, 4, NULL, 1, 146),
(475, 'Oreno', 'Mary jane', 'Segundo', NULL, NULL, 2, '1989-05-14', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 146),
(476, 'Oros', 'Carlito', 'A.', NULL, 1, 1, '1964-11-11', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 127),
(477, 'Lapastora', 'Jenny', 'O.', NULL, 19, 2, '1984-06-25', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 127),
(478, 'Lapastora', 'Angel', 'O.', NULL, 19, 2, '1997-11-04', NULL, NULL, NULL, NULL, 1, 16, NULL, NULL, 1, 127),
(479, 'Oros', 'Mergie', NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 127),
(480, 'Paclayan', 'Edgar', 'C.', NULL, 1, 1, '1985-01-24', NULL, NULL, NULL, NULL, 11, 9, NULL, NULL, 1, 147),
(481, 'Paclayan', 'Joyce', 'B.', NULL, 2, 2, '1984-07-05', NULL, NULL, NULL, NULL, 11, 9, NULL, NULL, 1, 147),
(482, 'Paclayan', 'Lance khobe', 'B.', NULL, 3, 1, '2007-04-14', NULL, NULL, NULL, NULL, 11, 9, NULL, NULL, 1, 147),
(483, 'Paclayan', 'Khaven khane', 'B.', NULL, 3, 1, '2010-02-19', NULL, NULL, NULL, NULL, 11, 9, NULL, NULL, 1, 147),
(484, 'Paclayan', 'Khirk aron', 'B.', NULL, 3, 1, '2012-12-24', NULL, NULL, NULL, NULL, 11, 9, NULL, NULL, 1, 147),
(485, 'Padre', 'Avelyn', NULL, NULL, NULL, 2, '1982-10-27', NULL, NULL, NULL, NULL, 3, 9, NULL, NULL, 1, 116),
(486, 'Palangeo', 'Maureen', NULL, NULL, NULL, 2, '1976-05-04', NULL, NULL, NULL, NULL, 21, 9, NULL, NULL, 1, 86),
(487, 'Palingit', 'Percival', 'Lehoc', NULL, 1, 1, '1986-11-14', NULL, NULL, NULL, NULL, 2, 49, 2, NULL, 1, 148),
(488, 'Ignacio', 'Crispino', 'Pindog', NULL, NULL, 1, '1993-08-20', NULL, NULL, NULL, NULL, 2, 49, 2, NULL, 1, 148),
(489, 'Pamani', 'Rey', 'D.', NULL, 1, 1, '1975-02-12', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 149),
(490, 'Dangatan', 'Melicia', 'S.', NULL, NULL, 2, '1979-02-17', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 149),
(491, 'Dangatan', 'Gilbert', 'E.', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 149),
(492, 'Pasigon', 'Menchie', 'O.', NULL, NULL, 2, '1993-03-17', NULL, NULL, NULL, NULL, 1, 16, NULL, NULL, 1, 127),
(493, 'Piluden', 'Chester', NULL, NULL, NULL, 1, '1984-10-04', NULL, NULL, NULL, NULL, 7, 9, NULL, NULL, 1, 16),
(494, 'Ponchaohan', 'Lito', 'A', NULL, 1, 1, '1980-07-03', NULL, NULL, NULL, NULL, 2, 1, 3, NULL, 1, 113),
(495, 'Balajo', 'Laila', 'B', NULL, 2, 2, '1986-08-13', NULL, NULL, NULL, NULL, 1, 1, 3, NULL, 1, 113),
(496, 'Ponchaohan', 'Recham', 'B', NULL, 3, 1, '2011-11-03', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 1, 113),
(497, 'Ponchaohan', 'Zea', 'B', NULL, 4, 2, '2016-09-22', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 1, 113),
(498, 'Ponchaohan', 'Zoe', 'B', NULL, 4, 2, '2018-03-08', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 1, 113),
(499, 'Balajo', 'Gloria', 'B', NULL, 19, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 1, 113),
(500, 'Ramirez', 'Roger', 'Dongla', NULL, 1, 1, '1969-10-14', NULL, NULL, NULL, NULL, 9, NULL, 2, NULL, 1, 150),
(501, 'Ramirez', 'Josie', 'Esteban', NULL, 2, 2, '1971-05-20', NULL, NULL, NULL, NULL, 9, NULL, 2, NULL, 1, 150),
(502, 'Ramirez', 'Ronel', 'Esteban', NULL, 3, 1, '1996-08-11', NULL, NULL, NULL, NULL, 9, NULL, 1, NULL, 1, 150),
(503, 'Ramirez', 'Eliezer', 'Esteban', NULL, 3, 1, '1998-01-25', NULL, NULL, NULL, NULL, 9, NULL, 1, NULL, 1, 150),
(504, 'Ramirez', 'Ryan', 'Esteban', NULL, 3, 1, '2000-04-17', NULL, NULL, NULL, NULL, 9, NULL, 1, NULL, 1, 150),
(505, 'Ramirez', 'Eloisa', 'Esteban', NULL, 4, 2, '2002-02-12', NULL, NULL, NULL, NULL, 9, NULL, 1, NULL, 1, 150),
(506, 'Rosalada', 'Erwin', 'Josol', NULL, 1, 1, '1977-05-27', NULL, NULL, NULL, NULL, 1, NULL, 2, NULL, 1, 151),
(507, 'Rosalada', 'Rowena', 'Bedania', NULL, 2, 2, '1979-03-05', NULL, NULL, NULL, NULL, 1, NULL, 2, NULL, 1, 151),
(508, 'Rosalada', 'John paul', 'Bedania', NULL, 3, 1, '2003-02-26', NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 151),
(509, 'Rosalada', 'Katrina paula', 'Bedania', NULL, 4, 2, '2005-10-29', NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 151),
(510, 'Rosalada', 'Lara nicole', 'Bedania', NULL, 4, 2, '2006-11-30', NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 151),
(511, 'Rosalada', 'Alexia mae', 'Bedania', NULL, 4, 2, '2012-09-10', NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 151),
(512, 'Rosalada', 'Ervin josh', 'Bedania', NULL, 3, 1, '2012-07-25', NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 151),
(513, 'Sabino', 'Mayette', NULL, NULL, NULL, 2, '1987-03-06', NULL, NULL, NULL, NULL, 1, 18, NULL, NULL, 1, 114),
(514, 'Saya-an', 'Bert', 'Chadanag', NULL, 1, 1, '1972-05-18', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 152),
(515, 'Saya-an', 'Cleiria', 'Sao-an', NULL, 2, 2, '1972-11-27', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 152),
(516, 'Saya-an', 'Clariz', 'Sao-an', NULL, 4, 2, '2006-02-11', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 152),
(517, 'Saya-an', 'Cleah', 'Sao-an', NULL, 4, 2, '2009-08-18', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 152),
(518, 'Saya-an', 'Irvin', 'Sao-an', NULL, 3, 1, '1994-08-27', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 152),
(519, 'Saganib', 'Samuel', 'Lutong', NULL, 1, 1, '1956-02-29', NULL, NULL, NULL, NULL, 4, 6, 2, NULL, 1, 153),
(520, 'Saganib', 'Lydia', 'Atiteo', NULL, 2, 2, '1952-09-04', NULL, NULL, NULL, NULL, 4, 6, 2, NULL, 1, 153),
(521, 'Sambrano', 'Randy', 'G.', NULL, 1, 1, '1971-10-29', NULL, NULL, NULL, NULL, 8, 9, 2, NULL, 1, 154),
(522, 'Sambrano', 'Edna', 'L.', NULL, 2, 2, '1972-08-28', NULL, NULL, NULL, NULL, 8, 9, 2, NULL, 1, 154),
(523, 'Segundo', 'Rudy', 'G.', NULL, 1, 1, '1958-01-05', NULL, NULL, NULL, NULL, 1, 60, 2, NULL, 1, 155),
(524, 'Segundo', 'Marlene', 'M.', NULL, 2, 2, '1964-03-24', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 155),
(525, 'Segundo', 'Jundo', 'M.', NULL, 3, 1, '1983-06-02', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 155),
(526, 'Segundo', 'Jovelyn', 'M.', NULL, 4, 2, '1987-06-16', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 155),
(527, 'Segundo', 'Ariel', 'M.', NULL, 3, 1, '1989-04-27', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 155),
(528, 'Segundo', 'Ferrylyn', 'M.', NULL, 4, 2, '1992-02-26', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 155),
(529, 'Segundo', 'Neven', 'M.', NULL, 3, 1, '1993-11-27', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 155),
(530, 'Segundo', 'Frecious ferlyn', 'S.', NULL, 10, 2, '2010-03-06', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 155),
(531, 'Sergio', 'Zaldy', 'B.', NULL, 1, 1, '1978-02-13', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 156),
(532, 'Sergio', 'Janice', 'A.', NULL, 2, 2, '1981-12-29', NULL, NULL, NULL, NULL, 22, 16, 2, NULL, 1, 156),
(533, 'Sergio', 'John henry', 'A.', NULL, 3, 1, '2009-07-30', NULL, NULL, NULL, NULL, 22, 16, 1, NULL, 1, 156),
(534, 'Sergio', 'Jaz joward', 'A.', NULL, 3, 1, '2013-01-08', NULL, NULL, NULL, NULL, 22, 16, 1, NULL, 1, 156),
(535, 'Sergio', 'Zenah', 'A.', NULL, 4, 2, '2020-02-26', NULL, NULL, NULL, NULL, 22, 16, 1, NULL, 1, 156),
(536, 'Shaley', 'Raymond', 'A.', NULL, 1, 1, '1992-04-22', NULL, NULL, NULL, NULL, 22, 16, 2, NULL, 1, 157),
(537, 'Shaley', 'Kolleen', 'L.', NULL, 2, 2, '1992-04-01', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 157),
(538, 'Solano', 'Elvira', 'B.', NULL, 1, 2, '2001-05-16', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 158),
(539, 'Solano', 'Iverr', 'B.', NULL, 1, 1, '2002-08-17', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 159),
(540, 'Solano', 'John john', 'E.', NULL, 1, 1, '1972-06-28', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 160),
(541, 'Solano', 'Tina', 'B.', NULL, 2, 2, '1973-11-06', NULL, NULL, NULL, NULL, 1, 49, 2, NULL, 1, 160),
(542, 'Solano', 'Derrick', 'B.', NULL, 3, 1, '2009-06-08', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 160),
(543, 'Ticol', 'Nita', NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 1, 9, NULL, NULL, 1, 161),
(544, 'Tinong', 'Romel', 'Sayok', NULL, 1, 1, '1972-03-28', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 161),
(545, 'Tinong', 'Balbina', 'Calantas', NULL, 2, 2, '1987-06-21', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 161),
(546, 'Calantas', 'Aiden bhert', NULL, NULL, 4, 2, '2010-04-11', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 161),
(547, 'Tinong', 'Kathleen', 'Calantas', NULL, 4, 2, '2014-01-29', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 161),
(548, 'Tinong', 'Byron', 'Calantas', NULL, 3, 1, '2017-02-21', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 161),
(549, 'Ubongen', 'Gigi', 'Olivar', NULL, NULL, 1, '1979-02-14', NULL, NULL, NULL, NULL, 1, 52, 1, NULL, 1, 63),
(550, 'Jacala', 'Regina', 'Munio', NULL, 21, 2, '1977-07-20', NULL, NULL, NULL, NULL, 1, 52, 1, NULL, 1, 63),
(551, 'Ubongen', 'Kristhel shane', 'Jacala', NULL, 21, 2, '2010-10-28', NULL, NULL, NULL, NULL, 1, 52, 1, NULL, 1, 63),
(552, 'Valenzuela', 'Chernaly', NULL, NULL, 1, 1, '1977-05-04', NULL, NULL, NULL, NULL, 22, 9, 2, NULL, 1, 162),
(553, 'Valenzuela', 'Saturnina', NULL, NULL, 2, 2, '1969-04-12', NULL, NULL, NULL, NULL, 22, 9, 2, NULL, 1, 162),
(554, 'Valenzuela', 'Stephen john', NULL, NULL, 3, 1, '2000-11-15', NULL, NULL, NULL, NULL, 22, 9, 1, NULL, 1, 162),
(555, 'Valenzuela', 'Valerie', NULL, NULL, 4, 2, '2007-04-11', NULL, NULL, NULL, NULL, 22, 9, 1, NULL, 1, 162),
(556, 'Catao', 'Benigno', NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 2, NULL, 1, 163),
(557, 'Catao', 'Patricia', 'Wansi', NULL, 2, 2, '1991-09-14', NULL, NULL, NULL, NULL, 1, 20, 2, NULL, 1, 163),
(558, 'Catao', 'Brently', 'Wansi', NULL, 3, 1, '2015-11-01', NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 163),
(559, 'Catao', 'Xean', 'Wansi', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 163),
(560, 'Wansi', 'Pedro', 'Lam-ing', NULL, 1, 1, '1949-11-06', NULL, NULL, NULL, NULL, 1, 60, 2, NULL, 1, 164),
(561, 'Wansi', 'Pany', 'Canuto', NULL, 2, 2, '1951-07-27', NULL, NULL, NULL, NULL, 1, 16, 2, 5, 1, 164),
(562, 'Wansi', 'Valdez', 'Canuto', NULL, 3, 1, '1997-07-11', NULL, NULL, NULL, NULL, 1, 16, 1, 5, 1, 164),
(563, 'Wansi', 'Refero', 'C.', NULL, 1, 1, '1975-02-27', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 165),
(564, 'Wansi', 'Vicky', 'D.', NULL, 2, 2, '1976-09-19', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 165),
(565, 'Wansi', 'Ashley', 'D.', NULL, 4, 2, '2008-02-21', NULL, NULL, NULL, NULL, 1, 47, 1, NULL, 1, 165),
(566, 'Tam-od,', 'Daisy', NULL, NULL, NULL, 2, '1988-12-25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 76),
(567, 'Calapiao,', 'Editha m.', NULL, NULL, NULL, 2, '1991-11-22', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 54),
(568, 'Dumallig', 'Ronie', NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, 22, NULL, 2, NULL, 1, 166),
(569, 'Pan-oy', 'Myrah vae', 'G.', NULL, 18, 2, NULL, NULL, NULL, NULL, NULL, 22, NULL, 1, NULL, 1, 166),
(570, 'Dumallig', 'Danielle maeve', 'G.', NULL, 18, 2, NULL, NULL, NULL, NULL, NULL, 22, NULL, 1, NULL, 1, 166),
(571, 'Bagtang', 'Gybson', 'B.', NULL, 17, 1, NULL, NULL, NULL, NULL, NULL, 22, NULL, 1, NULL, 1, 166),
(572, 'Sia-ed', 'Irene', 'L.', NULL, 23, 2, '1992-07-01', NULL, NULL, NULL, NULL, 22, NULL, 1, NULL, 1, 166),
(573, 'Sagun', 'Harriet', 'S.', NULL, 23, 2, '1987-09-30', NULL, NULL, NULL, NULL, 22, NULL, 2, NULL, 1, 166),
(574, 'Sagun', 'Chris', NULL, NULL, 24, 1, NULL, NULL, NULL, NULL, NULL, 22, NULL, 2, NULL, 1, 166),
(575, 'Oreno', 'Mercedes', 'E', NULL, 1, NULL, '1939-04-19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 167),
(576, 'Ananayo', 'Robert', 'G.', NULL, 1, 1, '1977-11-02', NULL, NULL, NULL, NULL, 1, 18, 2, NULL, 1, 168),
(577, 'Ananayo', 'Esperanza', 'D.', NULL, 2, 2, '1979-05-25', NULL, NULL, NULL, NULL, 1, 18, 2, NULL, 1, 168),
(578, 'Ananayo', 'Ezrah allysa', 'D.', NULL, 4, 2, '1999-11-09', NULL, NULL, NULL, NULL, 1, 18, 1, NULL, 1, 168),
(579, 'Ananayo', 'Angelo', 'D.', NULL, 3, 1, '2001-12-19', NULL, NULL, NULL, NULL, 1, 18, 1, NULL, 1, 168),
(580, 'Ananayo', 'Yasha mae', 'D.', NULL, 4, 2, '2004-02-01', NULL, NULL, NULL, NULL, 1, 18, 1, NULL, 1, 168),
(581, 'Aquision', 'Bryan', 'Menzi', NULL, 1, 1, '1987-10-31', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 169),
(582, 'Aquision', 'Jennifer', 'Claro', NULL, 2, 2, '1990-08-22', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 169),
(583, 'Aquison', 'Jheryan kyle', 'Claro', NULL, 3, 1, '2009-02-01', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 169),
(584, 'Aquison', 'Kamiya', 'Claro', NULL, 4, 2, '2020-01-05', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 169),
(585, 'Gamboa', 'Lourdes', 'Paytocan', NULL, 1, 2, '1958-06-05', NULL, NULL, NULL, NULL, NULL, 16, 4, 3, 1, 170),
(586, 'Barani', 'Omar', 'Dima', NULL, 1, 1, '1975-12-25', NULL, NULL, NULL, NULL, NULL, 68, 2, NULL, 1, 171),
(587, 'Mondoy', 'Mary-ann', 'Mahinay', NULL, 2, 2, '1980-12-01', NULL, NULL, NULL, NULL, NULL, 75, 2, NULL, 1, 171),
(588, 'Ngoay', 'Jomary', 'Mondoy', NULL, NULL, 1, '1999-09-01', NULL, NULL, NULL, NULL, NULL, 50, 1, NULL, 1, 171),
(589, 'Ngoay', 'James', 'Mondoy', NULL, NULL, 1, '2001-04-29', NULL, NULL, NULL, NULL, NULL, 50, 1, NULL, 1, 171),
(590, 'Ngoay', 'John mark', 'Mondoy', NULL, NULL, 1, '2003-01-01', NULL, NULL, NULL, NULL, NULL, 50, 1, NULL, 1, 171),
(591, 'Barani', 'Joharah', 'Mondoy', NULL, NULL, 2, '2005-09-03', NULL, NULL, NULL, NULL, NULL, 68, 1, NULL, 1, 171),
(592, 'Barani', 'Omar', 'Mondoy', NULL, NULL, 1, '2010-08-10', NULL, NULL, NULL, NULL, NULL, 68, 1, NULL, 1, 171),
(593, 'Barani', 'Alnor', 'Mondoy', NULL, NULL, 1, '2015-02-10', NULL, NULL, NULL, NULL, NULL, 68, 1, NULL, 1, 171),
(594, 'Barani', 'Jofainah', 'Mondoy', NULL, NULL, 2, '2016-11-03', NULL, NULL, NULL, NULL, NULL, 68, 1, NULL, 1, 171),
(595, 'Bayanes', 'Jerome', 'Allaga', NULL, 1, 1, '1971-11-02', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 172),
(596, 'Bayanes', 'Gina', 'Paytocan', NULL, 2, 2, '1974-09-18', NULL, NULL, NULL, NULL, 1, 16, 2, 3, 1, 172),
(597, 'Bayanes', 'Gerona', 'Paytocan', NULL, 4, 2, '1995-08-27', NULL, NULL, NULL, NULL, 1, 18, 1, 3, 1, 172),
(598, 'Bayanes', 'Ginalyn', 'Paytocan', NULL, 4, 2, '1996-02-05', NULL, NULL, NULL, NULL, 1, 18, 1, 3, 1, 172),
(599, 'Bayanes', 'Jerome', 'Paytocan', NULL, 3, 1, '1999-06-16', NULL, NULL, NULL, NULL, 1, 18, 1, 3, 1, 172),
(600, 'Bayanes', 'Geraldine', 'Paytocan', NULL, 4, 2, '2001-05-22', NULL, NULL, NULL, NULL, 1, 18, 1, 3, 1, 172),
(601, 'Bayanes', 'Jerald', 'Paytocan', NULL, 3, 1, '2006-04-06', NULL, NULL, NULL, NULL, 1, 18, 1, 3, 1, 172),
(602, 'Biag', 'Jordan', 'Degay', NULL, 1, 1, '1956-05-29', NULL, NULL, NULL, NULL, NULL, 1, 2, NULL, 1, 173),
(603, 'Biag', 'Alpha rita', 'Deligen', NULL, 2, 2, '1954-04-02', NULL, NULL, NULL, NULL, NULL, 1, 2, NULL, 1, 173),
(604, 'Laddaran', 'Rhedalyn kate', 'Biag', NULL, NULL, 2, '2009-01-01', NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 1, 173),
(605, 'Laddaran', 'Cxeryl jones', 'Biag', NULL, NULL, 1, '2012-02-14', NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 1, 173),
(606, 'Caligiw', 'Myra', 'Autriz', NULL, NULL, 2, '1997-03-06', NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 1, 173),
(607, 'Caligiw', 'Eriel france', 'Autriz', NULL, NULL, 1, '2001-07-15', NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 1, 173),
(608, 'Caligiw', 'Saty lynne', 'Autriz', NULL, NULL, 2, '2003-08-09', NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 1, 173),
(609, 'Ciano', 'Valentine', 'Mariano', NULL, 1, 1, '1963-02-14', NULL, NULL, NULL, NULL, 28, 9, 1, NULL, 1, 174),
(610, 'Damian', 'Muriel winnie', 'Dilla', NULL, 1, 2, '1984-02-04', NULL, NULL, NULL, NULL, NULL, 9, 4, NULL, 1, 175),
(611, 'Damian', 'Brandon', 'Dilla', NULL, 3, 1, '2015-08-06', NULL, NULL, NULL, NULL, NULL, 9, 1, NULL, 1, 175),
(612, 'Damian', 'Geremy', 'Dilla', NULL, 3, 1, '2018-02-21', NULL, NULL, NULL, NULL, NULL, 9, 1, NULL, 1, 175),
(613, 'Balisongen', 'Ashley', 'Dilla', NULL, NULL, 2, '2005-01-13', NULL, NULL, NULL, NULL, NULL, 9, 1, NULL, 1, 175),
(614, 'Dilla', 'Marvin', 'Pangda', NULL, 13, 1, '2001-02-14', NULL, NULL, NULL, NULL, NULL, 9, 1, NULL, 1, 175),
(615, 'Damian', 'Pilar', 'Jose', NULL, 1, 2, '1988-01-28', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 176),
(616, 'Damian', 'William', 'Jose', NULL, 1, 1, '1975-04-12', NULL, NULL, NULL, NULL, 1, 18, 2, NULL, 1, 177),
(617, 'Damian', 'Gina', 'Capito', NULL, 2, 2, '1981-06-26', NULL, NULL, NULL, NULL, 1, NULL, 2, NULL, 1, 177),
(618, 'Damian', 'Mae jean', 'Capito', NULL, 4, 2, '2005-05-19', NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 177),
(619, 'Damian', 'Mel', 'Capito', NULL, 3, 1, '2007-02-15', NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 177),
(620, 'Damian', 'Felisa mae', 'Capito', NULL, 4, 2, '2009-02-27', NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 177),
(621, 'Damian', 'Bill james', 'Capito', NULL, 3, 1, '2012-01-10', NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 177),
(622, 'Dangpa', 'Enrique', 'Amante', NULL, 1, 1, '1974-07-09', NULL, NULL, NULL, NULL, 22, 18, 2, NULL, 1, 178),
(623, 'Dangpa', 'Julia', 'Cobebe', NULL, 2, 2, '1973-05-31', NULL, NULL, NULL, NULL, 22, 1, 2, NULL, 1, 178),
(624, 'Rance', 'Eunice', 'Cobebe', NULL, 2, 2, '2002-04-30', NULL, NULL, NULL, NULL, 22, 2, 2, NULL, 1, 178),
(625, 'Dangpa', 'Kristine emerald', 'Cobebe', NULL, 4, 2, '2003-11-04', NULL, NULL, NULL, NULL, 22, 2, 1, NULL, 1, 178),
(626, 'Dangpa', 'Eirene nicole', 'Cobebe', NULL, 4, 2, '2005-01-17', NULL, NULL, NULL, NULL, 22, 2, 1, NULL, 1, 178),
(627, 'Delos santos', 'Rey', NULL, NULL, 1, 1, '1976-04-26', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 179),
(628, 'Suclad', 'Rocklyn', NULL, NULL, NULL, 2, '1992-08-05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 179),
(629, 'Delos santos', 'Rhea lyn', NULL, NULL, NULL, 2, '2011-07-09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 179),
(630, 'Delos santos', 'Raelhyn', NULL, NULL, NULL, 2, '2013-06-10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 179),
(631, 'Delos santos', 'Rhyne', NULL, NULL, NULL, 2, '2015-01-01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 179),
(632, 'Diano', 'Glenda', 'Canuto', NULL, 1, 2, '1983-09-09', NULL, NULL, NULL, NULL, 1, 16, 4, 5, 1, 180),
(633, 'Diano', 'Khurt', 'Canuto', NULL, 3, 1, '2003-11-11', NULL, NULL, NULL, NULL, 1, 16, 1, 5, 1, 180),
(634, 'Diano', 'Khyle', 'Canuto', NULL, 4, 2, '2007-08-27', NULL, NULL, NULL, NULL, 1, 16, 1, 5, 1, 180),
(635, 'Smith', 'Mark', 'Macay', NULL, NULL, 1, '1980-07-18', NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, 180),
(636, 'Diano', 'Oscar', 'Macay', NULL, 1, 1, '1976-09-27', NULL, NULL, NULL, NULL, 1, 43, 2, NULL, 1, 181),
(637, 'Diano', 'Aladina', 'Damian', NULL, 2, 2, '1981-03-14', NULL, NULL, NULL, NULL, 1, 18, 2, NULL, 1, 181),
(638, 'Diano', 'Lennox', 'Damian', NULL, 3, 1, '2002-05-10', NULL, NULL, NULL, NULL, 1, 19, 1, NULL, 1, 181),
(639, 'Diano', 'Dixie', 'Damian', NULL, 4, 2, '2004-08-02', NULL, NULL, NULL, NULL, 1, 19, 1, NULL, 1, 181),
(640, 'Diano', 'Oscar', 'Damian', NULL, 3, 1, '2006-04-18', NULL, NULL, NULL, NULL, 1, 19, 1, NULL, 1, 181),
(641, 'Awilan', 'Jonathan', 'Bayas', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, 9, 1, NULL, 1, 181),
(642, 'Diano', 'Roullie', 'Lopez', NULL, 1, 1, '1997-06-13', NULL, NULL, NULL, NULL, 1, 3, 1, NULL, 1, 182),
(643, 'Emiliano', 'Arnold', 'Calixto', NULL, 1, 1, '1986-08-25', NULL, NULL, NULL, NULL, NULL, 18, 2, NULL, 1, 183),
(644, 'Emiliano', 'Genevieve', 'Diano', NULL, 2, 2, '1986-11-27', NULL, NULL, NULL, NULL, NULL, 19, 2, NULL, 1, 183),
(645, 'Emiliano', 'Dwight', 'Diano', NULL, 3, 1, '2011-05-29', NULL, NULL, NULL, NULL, NULL, 18, 1, NULL, 1, 183),
(646, 'Emiliano', 'Rhed', 'Diano', NULL, 3, 1, '2018-07-13', NULL, NULL, NULL, NULL, NULL, 18, 1, NULL, 1, 183),
(647, 'Emiliano', 'Raje mark', 'Calixto', NULL, 13, 1, '1998-11-01', NULL, NULL, NULL, NULL, NULL, 18, 1, NULL, 1, 183),
(648, 'Gamboa', 'Jim paul', 'P.', NULL, 1, 1, '1981-02-19', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 184),
(649, 'Gamboa', 'Janice', 'C.', NULL, 2, 2, '1990-09-24', NULL, NULL, NULL, NULL, NULL, 16, 2, NULL, 1, 184),
(650, 'Cacananto', 'Deo', 'G.', NULL, 13, 1, '1989-05-24', NULL, NULL, NULL, NULL, NULL, 16, 2, NULL, 1, 184),
(651, 'Gamboa', 'Camilo', 'Estilong', NULL, 1, 1, '1956-03-08', NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, 185),
(652, 'Gamboa', 'Merlinda', 'Pitlongay', NULL, 2, 2, '1946-08-06', NULL, NULL, NULL, NULL, NULL, NULL, 2, 4, 1, 185),
(653, 'Gamboa', 'Philipp', 'Pitlongay', NULL, 3, 1, '1987-02-01', NULL, NULL, NULL, NULL, NULL, NULL, 2, 4, 1, 185),
(654, 'Gamboa', 'Joan marie', 'Bautista', NULL, 8, 2, '1991-11-19', NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, 185),
(655, 'Gamboa', 'Trixie jhayne', 'Bautista', NULL, 10, 2, '2010-01-24', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 185),
(656, 'Gamboa', 'Tristan joerge', 'Bautista', NULL, 9, 1, '2014-02-15', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 185),
(657, 'Gamboa', 'Trixia joy', 'Bautista', NULL, 10, 2, '2015-12-23', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 185),
(658, 'Gasingan', 'Edward', 'Pitlongay', NULL, 1, 1, '1973-10-20', NULL, NULL, NULL, NULL, 1, 16, 1, 4, 1, 186),
(659, 'Gasingan', 'Constancio', 'Damian', NULL, 1, 1, '1947-03-05', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 187),
(660, 'Gasingan', 'Lilia', 'Ponso', NULL, 2, 2, '1948-05-05', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 187),
(661, 'Gasingan', 'Conrad', 'Pitlongay', NULL, 3, 1, '1990-01-19', NULL, NULL, NULL, NULL, 1, 16, 1, 4, 1, 187),
(662, 'Gasingan', 'Murphy', 'Pitlongay', NULL, 3, 1, '1992-08-16', NULL, NULL, NULL, NULL, 1, 16, 1, 4, 1, 187),
(663, 'Rullan', 'Deborah', 'Gasingan', NULL, 4, 2, '1992-08-16', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 187),
(664, 'Gasingan', 'Arjelene', 'Porcare', NULL, 10, 2, '2014-12-24', NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 187),
(665, 'Lando', 'Benson', 'A.', NULL, 19, 1, '1995-01-13', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 187),
(666, 'Gasingan', 'Sylvia', 'Pitlongay', NULL, 4, 2, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 4, 1, 187),
(667, 'Gayamo', 'Kaeleen', 'Gom-ed', NULL, 1, 2, '1995-09-11', NULL, NULL, NULL, NULL, NULL, 43, 1, NULL, 1, 188),
(668, 'Gayamo', 'Aaron zeus', NULL, NULL, 3, 1, '2017-06-04', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 188),
(669, 'Gom-ed', 'Nena', 'Gasingan', NULL, 1, 2, '1940-11-20', NULL, NULL, NULL, NULL, 1, 16, 4, NULL, 1, 189),
(670, 'Gayamo', 'Brigitte', 'Gom-ed', NULL, 4, 2, '1975-09-29', NULL, NULL, NULL, NULL, 1, 16, 5, NULL, 1, 189),
(671, 'Gayamo', 'Keano', 'Gom-ed', NULL, 3, 1, '1998-05-04', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 189),
(672, 'Gayamo', 'Kevin', 'Gom-ed', NULL, 3, 1, '1997-04-23', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 189),
(673, 'Kidkid', 'Eduard', 'Anaban', NULL, 1, 1, '1987-06-14', NULL, NULL, NULL, NULL, NULL, 9, 2, NULL, 1, 190),
(674, 'Kidkid', 'Camille keith', 'Diano', NULL, 2, 2, '1995-03-14', NULL, NULL, NULL, NULL, 1, 3, 2, NULL, 1, 190),
(675, 'Kidkid', 'Xyver cas', 'Diano', NULL, 3, 1, '2015-07-11', NULL, NULL, NULL, NULL, NULL, 42, 1, NULL, 1, 190),
(676, 'Flores', 'Jelyn', 'Lopez', NULL, NULL, 2, '2005-02-10', NULL, NULL, NULL, NULL, NULL, 4, 1, NULL, 1, 190),
(677, 'Lacaden', 'Berta', 'Estong', NULL, 1, 2, '1971-06-06', NULL, NULL, NULL, NULL, 1, 16, 4, NULL, 1, 191),
(678, 'Lacaden', 'Adelfa', 'Estong', NULL, 4, 2, '2000-09-01', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 191),
(679, 'Lacaden', 'Daisybel', 'Estong', NULL, 4, 2, '2002-03-05', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 191),
(680, 'Lacaden', 'Danica mae', 'Estong', NULL, 4, 2, '2008-05-09', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 191),
(681, 'Lacaden', 'Fidel', 'Pitlongay', NULL, 1, 1, '1972-12-30', NULL, NULL, NULL, NULL, 1, 16, 2, 4, 1, 192),
(682, 'Lacaden', 'Fedalyn', 'Evangelista', NULL, 2, 2, '1980-11-27', NULL, NULL, NULL, NULL, 1, 52, 2, NULL, 1, 192),
(683, 'Lacaden', 'Nuben', 'Evangelista', NULL, 3, 1, '2004-03-27', NULL, NULL, NULL, NULL, 1, 52, 1, NULL, 1, 192),
(684, 'Lacaden', 'Delzar', 'Evangelista', NULL, 3, 1, '2005-06-30', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 192),
(685, 'Lacaden', 'Ashlyn', 'Evangelista', NULL, 4, 2, '2009-11-05', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 192),
(686, 'Lacaden', 'Arlyn', 'Evangelista', NULL, 4, 2, '2013-12-12', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 192),
(687, 'Lando', 'Manny', 'S.', NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, 1, 44, 2, NULL, 1, 193),
(688, 'Lamsis', 'Eric', 'Ponso', NULL, 1, 1, '1972-11-12', NULL, NULL, NULL, NULL, NULL, 16, 2, NULL, 1, 194),
(689, 'Lamsis', 'Miko hannah', 'Guay', NULL, 2, 2, '1981-04-25', NULL, NULL, NULL, NULL, NULL, 49, 2, NULL, 1, 194),
(690, 'Lamsis', 'Katriona allizaeh', 'Guay', NULL, 4, 2, '2005-02-06', NULL, NULL, NULL, NULL, NULL, 43, 1, NULL, 1, 194),
(691, 'Lamsis', 'Tracey eric', 'Guay', NULL, 3, 1, '2015-12-05', NULL, NULL, NULL, NULL, NULL, 43, 1, NULL, 1, 194),
(692, 'Lamsis', 'Douglas', 'Ponso', NULL, 1, 1, '1970-04-24', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 195),
(693, 'Lamsis', 'Oswaldo', 'Ponso', NULL, 1, 1, '1968-01-18', NULL, NULL, NULL, NULL, NULL, 16, 2, NULL, 1, 196),
(694, 'Lamsis', 'Jeanibe', 'Melod', NULL, 2, 2, '1975-08-02', NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, 196),
(695, 'Lamsis', 'Tanya', 'Melod', NULL, 4, 2, '1999-04-03', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 196),
(696, 'Lamsis', 'Cyrus', 'Melod', NULL, 3, 1, '2001-12-06', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 196),
(697, 'Lamsis', 'Justin', 'Melod', NULL, 3, 1, '2004-06-19', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 196),
(698, 'Ligante', 'Norton', 'Lacaden', NULL, 1, 1, '1987-11-28', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 197),
(699, 'Ligante', 'Joanne', 'Roque', NULL, 2, 2, '1991-03-26', NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, 197),
(700, 'Ligante', 'Johanna nicole', 'Roque', NULL, 4, 2, '2015-02-16', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 197),
(701, 'Ligante', 'Jennica', 'Roque', NULL, 4, 2, '2018-12-29', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 197),
(702, 'Ligante', 'Joel', 'Lacaden', NULL, 1, 1, '1986-01-27', NULL, NULL, NULL, NULL, NULL, 16, 2, NULL, 1, 198),
(703, 'Ligante', 'Bingbing', 'Escobar', NULL, 2, 2, '1983-04-01', NULL, NULL, NULL, NULL, NULL, 73, 2, NULL, 1, 198),
(704, 'Ligante', 'Joela', 'Escobar', NULL, 4, 2, '2015-09-06', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 198),
(705, 'Escobar', 'Aurea ashley', 'Sagudang', NULL, 19, 2, '2002-12-13', NULL, NULL, NULL, NULL, NULL, 73, 1, NULL, 1, 198),
(706, 'Escobar', 'Nicholai', 'Sagudang', NULL, 19, 1, '2006-02-11', NULL, NULL, NULL, NULL, NULL, 73, 1, NULL, 1, 198),
(707, 'Dangsi', 'Cris', 'Escobar', NULL, 19, 1, '2004-07-11', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 198),
(708, 'Ligante', 'Thomas', 'Gangod', NULL, 1, 1, '1955-11-25', NULL, NULL, NULL, NULL, NULL, 16, 2, NULL, 1, 199),
(709, 'Ligante', 'Betty', 'Lacaden', NULL, 2, 2, '1960-01-30', NULL, NULL, NULL, NULL, NULL, 16, 2, NULL, 1, 199),
(710, 'Ligante', 'Melanie', 'Lacaden', NULL, 4, 2, '1990-03-28', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 199),
(711, 'Ligante', 'Horace', 'Lacaden', NULL, 3, 1, '1996-03-12', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 199),
(712, 'Liis', 'Michael', 'Maitang', NULL, 1, 1, '1971-03-25', NULL, NULL, NULL, NULL, NULL, 9, 2, NULL, 1, 200),
(713, 'Liis', 'Jennifer', 'Baguilod', NULL, 2, 2, '1976-01-04', NULL, NULL, NULL, NULL, NULL, 9, 2, NULL, 1, 200),
(714, 'Liis', 'Michelyn', 'Baguilod', NULL, 4, 2, '2005-05-17', NULL, NULL, NULL, NULL, NULL, 9, 1, NULL, 1, 200),
(715, 'Liis', 'Jaemiacalyn', 'Baguilod', NULL, 4, 2, '2006-08-25', NULL, NULL, NULL, NULL, NULL, 9, 1, NULL, 1, 200),
(716, 'Liis', 'Shawn michael', 'Baguilod', NULL, 3, 1, '2008-02-04', NULL, NULL, NULL, NULL, NULL, 9, 1, NULL, 1, 200),
(717, 'Liis', 'Shane jean', 'Baguilod', NULL, 3, 1, '2012-09-15', NULL, NULL, NULL, NULL, NULL, 9, 1, NULL, 1, 200),
(718, 'Mariano', 'Ferry', 'Gom-ed', NULL, 1, 1, '1985-02-09', NULL, NULL, NULL, NULL, NULL, 16, 2, NULL, 1, 201),
(719, 'Mariano', 'Mebelyn', 'Dagdagen', NULL, 2, 2, '1997-09-07', NULL, NULL, NULL, NULL, NULL, 16, 2, NULL, 1, 201),
(720, 'Mariano', 'Rhegan', 'Dagdagen', NULL, 3, 1, '2018-05-07', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 201),
(721, 'Mariano', 'Jayfred', 'Gom-ed', NULL, 1, 1, '1990-02-13', NULL, NULL, NULL, NULL, NULL, 16, 2, NULL, 1, 202),
(722, 'Mariano', 'Mayjoy', 'Adawi', NULL, 2, 2, '1991-05-14', NULL, NULL, NULL, NULL, NULL, 18, 2, NULL, 1, 202),
(723, 'Mariano', 'Jemaybelle', 'Adawi', NULL, 4, 2, '2009-10-19', NULL, NULL, NULL, NULL, NULL, 18, 1, NULL, 1, 202),
(724, 'Mariano', 'Tyler dwayne', 'Adawi', NULL, 3, 1, '2012-12-14', NULL, NULL, NULL, NULL, NULL, 18, 1, NULL, 1, 202),
(725, 'Mesa', 'Antonio', 'Albin', NULL, 1, 1, '1957-11-12', NULL, NULL, NULL, NULL, 3, 16, 2, NULL, 1, 203),
(726, 'Mesa', 'Helen', 'Ciano', NULL, 2, 2, '1959-07-08', NULL, NULL, NULL, NULL, 3, NULL, 2, NULL, 1, 203),
(727, 'Mesa', 'Albert', 'Ciano', NULL, 3, 1, '1983-06-03', NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, 1, 203),
(728, 'Mesa', 'Hector', 'Ciano', NULL, 3, 1, '1985-10-13', NULL, NULL, NULL, NULL, 3, 9, 2, NULL, 1, 203),
(729, 'Mesa', 'Gina', 'Dagiw-a', NULL, 8, 2, '1987-09-05', NULL, NULL, NULL, NULL, 3, 60, 2, NULL, 1, 203),
(730, 'Bugtong', 'Marnilyn', 'Guzman', NULL, 19, 2, '2000-10-01', NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, 1, 203),
(731, 'Nabaysa', 'Arwin', NULL, NULL, 1, 1, '1986-10-16', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 204),
(732, 'Lando', 'Catherine', NULL, NULL, NULL, 2, '1986-03-29', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 204),
(733, 'Lando', 'Rolly', NULL, NULL, 3, 1, NULL, NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 204),
(734, 'Lando', 'Christine joy', NULL, NULL, 4, 2, '2005-11-24', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 204),
(735, 'Nabaysa', 'Adrian james', NULL, NULL, 3, 1, '2012-07-05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 204),
(736, 'Nabaysa', 'Chloe', NULL, NULL, 4, 2, '2016-01-01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 204),
(737, 'Ngoay', 'Marlina', 'Olsim', NULL, 1, 2, '1969-04-03', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 205),
(738, 'Castaneda', 'John anthony', 'Madrid', NULL, NULL, 1, '1966-12-09', NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, 205),
(739, 'Castaneda', 'Leonard', 'Torres', NULL, NULL, 1, '1997-06-29', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 205),
(740, 'Ngoay', 'Kidcarson', 'Olsem', NULL, 1, 1, '1963-10-21', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 206),
(741, 'Oyad', 'Danny', 'Oloan', NULL, 1, 1, '1971-07-30', NULL, NULL, NULL, NULL, 7, 50, 2, NULL, 1, 207),
(742, 'Oyangwa', 'Byrd', 'Loyosen', NULL, 1, 1, '1973-12-14', NULL, NULL, NULL, NULL, 1, NULL, 2, NULL, 1, 208),
(743, 'Oyangwa', 'Chrisma', 'Damian', NULL, 2, 2, '1982-12-23', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 208),
(744, 'Oyangwa', 'Wyet', 'Damian', NULL, 3, 1, '2014-12-23', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 208),
(745, 'Oyangwa', 'Hennessy', 'Damian', NULL, 4, 2, '2016-09-15', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 208),
(746, 'Padalla', 'Edward', 'Bayeng', NULL, 1, 1, '1956-04-05', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 209),
(747, 'Padalla', 'Lovely', 'Gasingan', NULL, 4, 2, '1990-12-31', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 209),
(748, 'Padalla', 'Kiefer', 'Gasingan', NULL, 3, 1, '1992-10-08', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 209),
(749, 'Padalla', 'Kensley', 'Gasingan', NULL, 3, 1, '1996-02-25', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 209),
(750, 'Padalla', 'Kenny', 'Gasingan', NULL, 1, 1, '1987-05-19', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 210),
(751, 'Padalla', 'Shirley', 'Gasingan', NULL, 1, 2, '1965-09-26', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 211),
(752, 'Palunan', 'Soriano', 'Abante', NULL, 1, 1, '1958-03-19', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 212),
(753, 'Palunan', 'Jane', 'Ugalde', NULL, 2, 2, '1970-01-12', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 212),
(754, 'Palunan', 'Ryan', 'Ugalde', NULL, 3, 1, '1996-09-15', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 212),
(755, 'Palunan', 'Jed', 'Ugalde', NULL, 3, 1, '1998-01-09', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 212),
(756, 'Palunan', 'Rexton', 'Ugalde', NULL, 3, 1, '2000-01-29', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 212),
(757, 'Palunan', 'Janet', 'Ugalde', NULL, 4, 2, '2005-10-02', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 212),
(758, 'Palunan', 'Katrina', 'Ugalde', NULL, 4, 2, '2007-07-02', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 212),
(759, 'Paytocan', 'Franky', 'Gamboc', NULL, 1, 1, '1990-08-20', NULL, NULL, NULL, NULL, 1, 16, 2, 3, 1, 213),
(760, 'Paytocan', 'Jiji', 'Cadley', NULL, 2, 2, '1988-12-06', NULL, NULL, NULL, NULL, 1, 16, 2, 3, 1, 213),
(761, 'Paytocan', 'Bea', 'Cadley', NULL, 4, 2, '2006-09-14', NULL, NULL, NULL, NULL, 1, 16, 1, 3, 1, 213),
(762, 'Paytocan', 'Kristel', 'Cadley', NULL, 4, 2, '2009-06-04', NULL, NULL, NULL, NULL, 1, 16, 1, 3, 1, 213),
(763, 'Paytocan', 'Jeffry', 'Gamboc', NULL, 1, 1, '1977-06-02', NULL, NULL, NULL, NULL, 1, 16, 2, 3, 1, 214),
(764, 'Paytocan', 'Sylvia', 'Canuto', NULL, 2, 2, '1981-05-17', NULL, NULL, NULL, NULL, 1, 16, 2, 3, 1, 214),
(765, 'Paytocan', 'Shane', 'Canuto', NULL, 4, 2, '2002-09-25', NULL, NULL, NULL, NULL, 1, 16, 1, 3, 1, 214),
(766, 'Paytocan', 'Kenjie', 'Canuto', NULL, 3, 1, '2004-11-29', NULL, NULL, NULL, NULL, 1, 16, 1, 3, 1, 214),
(767, 'Paytocan', 'Soshmita', 'Canuto', NULL, 4, 2, '2007-09-27', NULL, NULL, NULL, NULL, 1, 16, 1, 3, 1, 214),
(768, 'Paytocan', 'Marzan', 'Gamboc', NULL, 1, 1, '1982-03-07', NULL, NULL, NULL, NULL, 1, 16, 2, 3, 1, 215),
(769, 'Villanueva', 'Eunice', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 215),
(770, 'Paytocan', 'Marz russell', 'Villanueva', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, 1, 215),
(771, 'Paytocan', 'Peter', 'Gangod', NULL, 1, 1, '1963-12-05', NULL, NULL, NULL, NULL, 1, 18, 2, 3, 1, 216),
(772, 'Paytocan', 'Regina', 'Balangue', NULL, 2, 2, '1965-09-10', NULL, NULL, NULL, NULL, 1, 61, 2, 3, 1, 216),
(773, 'Paytocan', 'Rexter', 'Balangue', NULL, 3, 1, '1999-09-19', NULL, NULL, NULL, NULL, 1, 18, 1, 3, 1, 216),
(774, 'Paytocan', 'Pearly-joy', 'Balangue', NULL, 4, 2, '2001-05-02', NULL, NULL, NULL, NULL, 1, 18, 1, 3, 1, 216),
(775, 'Paytocan', 'Robert', 'Gamboc', NULL, 1, 1, '1972-07-02', NULL, NULL, NULL, NULL, 1, 16, 2, 3, 1, 217),
(776, 'Paytocan', 'Rosalyn', 'Rebleza', NULL, 2, 2, '1971-10-07', NULL, NULL, NULL, NULL, 1, 51, 2, 3, 1, 217),
(777, 'Paytocan', 'Gordon', 'Rebleza', NULL, 3, 1, '1995-11-06', NULL, NULL, NULL, NULL, 1, 16, 1, 3, 1, 217),
(778, 'Paytocan', 'Carmela', 'Rebleza', NULL, 4, 2, '1996-10-26', NULL, NULL, NULL, NULL, 1, 16, 1, 3, 1, 217),
(779, 'Paytocan', 'Janwin', 'Rebleza', NULL, 3, 1, '2001-03-08', NULL, NULL, NULL, NULL, 1, 16, 1, 3, 1, 217),
(780, 'Paytocan', 'Mylyn', 'Rebleza', NULL, 4, 2, '2005-05-23', NULL, NULL, NULL, NULL, 1, 48, 1, 3, 1, 217),
(781, 'Pedring', 'Gilbert', 'Siano', NULL, 1, 1, '1972-11-05', NULL, NULL, NULL, NULL, 1, 61, 2, NULL, 1, 218),
(782, 'Pedring', 'Lina', 'Igamas', NULL, 2, 2, '1980-03-05', NULL, NULL, NULL, NULL, 1, NULL, 2, NULL, 1, 218),
(783, 'Pedring', 'Joshua', 'Agamas', NULL, 3, 1, '2001-06-02', NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 218),
(784, 'Pedring', 'Berlyn', 'Agamas', NULL, 4, 2, '2004-09-15', NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 218),
(785, 'Pitlongay', 'Darwin', 'Gom-ed', NULL, 1, 1, '1985-01-30', NULL, NULL, NULL, NULL, NULL, 16, 5, 4, 1, 219),
(786, 'Pitlongay', 'Marcela', 'Gom-ed', NULL, 1, 2, '1959-10-03', NULL, NULL, NULL, NULL, 1, 16, 4, 4, 1, 220),
(787, 'Pitlongay', 'Alberto', 'Gom-ed', NULL, 1, 1, '1975-03-18', NULL, NULL, NULL, NULL, 23, 18, 2, 4, 1, 221),
(788, 'Pitlongay', 'Brinda louise', 'Baguiwen', NULL, 2, 2, '1973-10-10', NULL, NULL, NULL, NULL, 23, 1, 2, 4, 1, 221),
(789, 'Pitlongay', 'Cherry ann', 'Baguiwen', NULL, 4, 2, '1997-11-26', NULL, NULL, NULL, NULL, 23, 2, 1, 4, 1, 221),
(790, 'Pitlongay', 'Darren breal', 'Baguiwen', NULL, 3, 1, '1999-06-20', NULL, NULL, NULL, NULL, 23, 2, 1, 4, 1, 221),
(791, 'Pitlongay', 'Eugene', 'Baguiwen', NULL, 3, 1, '2000-11-30', NULL, NULL, NULL, NULL, 23, 2, 1, 4, 1, 221),
(792, 'Boquio', 'Ryan michael', 'Collado', NULL, NULL, 1, '1995-11-01', NULL, NULL, NULL, NULL, 23, 71, 1, NULL, 1, 221),
(793, 'Boquio', 'Zayn michael', 'Pitlongay', NULL, NULL, 1, '2019-01-28', NULL, NULL, NULL, NULL, 23, 46, 1, 4, 1, 221),
(794, 'Sacla', 'Juliana', 'Paytocan', NULL, 1, 2, '1945-03-11', NULL, NULL, NULL, NULL, 17, 18, 4, 3, 1, 222),
(795, 'Olpindo', 'Andrey', 'Sacla', NULL, 9, 1, '2002-12-06', NULL, NULL, NULL, NULL, NULL, 44, 1, NULL, 1, 222),
(796, 'Suclad', 'Recto', 'Pucay', NULL, 1, 1, '1980-04-18', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 223),
(797, 'Suclad', 'Harriet', 'Paterno', NULL, 2, 2, '1976-10-13', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 223),
(798, 'Suclad', 'Harrelson', 'Paterno', NULL, 3, 1, '1999-05-13', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 223),
(799, 'Suclad', 'Regie', 'Paterno', NULL, 3, 1, '2011-01-08', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 223),
(800, 'Suclad', 'Erich', 'Paterno', NULL, 4, 2, '2005-02-20', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 223),
(801, 'Tamulang', 'Jhonie', 'Julian', NULL, 1, 1, '1983-02-15', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 224),
(802, 'Tamulang', 'Donesia', 'Damian', NULL, 2, 2, '1986-12-03', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 224),
(803, 'Tamulang', 'Dj rose', 'Damian', NULL, 4, 2, '2008-06-03', NULL, NULL, NULL, NULL, 1, 18, 1, NULL, 1, 224),
(804, 'Tamulang', 'Jhoniella zia', 'Damian', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 224),
(805, 'Tan', 'James', 'David', NULL, 1, 1, '1961-03-09', NULL, NULL, NULL, NULL, 1, 72, 2, NULL, 1, 225),
(806, 'Tan', 'Clara', 'Lacaden', NULL, 2, 2, '1962-03-25', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 225),
(807, 'Tan', 'Donna mae', 'Lacaden', NULL, 4, 2, '1999-01-08', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 225),
(808, 'Tan', 'Luz clarita', 'Lacaden', NULL, 4, 2, '2000-12-29', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 225),
(809, 'Tan', 'Jefferson', 'Lacaden', NULL, 3, 1, '2005-09-08', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 225),
(810, 'Tangoyab', 'Aspinas', NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, 226),
(811, 'Peralta', 'Freddie', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 183),
(812, 'Peralta', 'Cecile', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 183),
(813, 'Abuan', 'Flora', 'S.', NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 9, NULL, 2, NULL, 1, 227),
(814, 'Abuan', 'Frederick', 'Segundo', NULL, 2, 1, '1984-02-02', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 3, 228),
(815, 'Abuan', 'Marvin', 'Rimas', NULL, NULL, 1, '1980-03-05', NULL, NULL, NULL, NULL, NULL, 52, NULL, NULL, 1, 228),
(816, 'Abuan', 'Liberty', 'Castillones', NULL, 1, 2, '1989-12-26', NULL, NULL, NULL, NULL, NULL, 52, NULL, NULL, 1, 228),
(817, 'Abuan', 'Mark steven', 'Castillones', NULL, NULL, 1, '2010-05-19', NULL, NULL, NULL, NULL, NULL, 52, NULL, NULL, 1, 228),
(818, 'Abuan', 'James ivan', 'Castillones', NULL, NULL, 1, '2012-07-27', NULL, NULL, NULL, NULL, NULL, 52, NULL, NULL, 1, 228),
(819, 'Abuan', 'Zoe nathalie', 'Castillones', NULL, NULL, 2, '2018-09-25', NULL, NULL, NULL, NULL, NULL, 52, NULL, NULL, 1, 228),
(820, 'Albin', 'Bill', 'Godio', NULL, 1, 1, '1978-03-25', NULL, NULL, NULL, NULL, 9, 16, 2, NULL, 1, 229),
(821, 'Albin', 'Magdalyn', 'Canuto', NULL, 2, 2, '1978-11-19', NULL, NULL, NULL, NULL, 9, 16, 2, 5, 1, 229),
(822, 'Albin', 'Billie may', 'Canuto', NULL, 4, 2, '2000-03-14', NULL, NULL, NULL, NULL, 9, 16, 1, 5, 1, 229),
(823, 'Albin', 'Phoebe', 'Canuto', NULL, 4, 2, '2005-07-25', NULL, NULL, NULL, NULL, 9, 16, 1, 5, 1, 229),
(824, 'Alota', 'Lorimy nachel', NULL, NULL, NULL, 2, '1999-02-13', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 230),
(825, 'Alperez', 'Rosemary', 'Cadeza', NULL, 1, 2, '1947-01-09', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 231),
(826, 'Alota', 'Delmar', 'M.', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 232),
(827, 'Andersen', 'Elric', 'Alota', NULL, 1, 1, '1980-11-13', NULL, NULL, NULL, NULL, 6, NULL, 1, NULL, 1, 233),
(828, 'Andersen', 'Meldred', 'Alota', NULL, 1, 2, '1959-11-12', NULL, NULL, NULL, NULL, 1, 12, 4, NULL, 1, 230),
(829, 'Angaga', 'Earl', 'N.', NULL, 1, 1, '1982-12-21', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 232),
(830, 'Angaga', 'Jeanie', 'A.', NULL, 2, 2, '1984-04-06', NULL, NULL, NULL, NULL, 1, 12, 2, NULL, 1, 232),
(831, 'Angaga', 'Nigel', 'A.', NULL, 3, 1, '2008-06-04', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 232),
(832, 'Angaga', 'Allyana', 'A.', NULL, 4, 2, '2011-11-18', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 232),
(833, 'Angaga', 'Svend', 'A', NULL, 3, 1, '2017-10-03', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 232),
(834, 'Aquino', 'William', 'Naveros', NULL, 1, 1, '1967-01-15', NULL, NULL, NULL, NULL, 1, 50, 2, NULL, 1, 234),
(835, 'Aquino', 'Maria relinda', 'Rollo', NULL, 2, 2, '1964-05-07', NULL, NULL, NULL, NULL, 1, 72, 2, NULL, 1, 234),
(836, 'Aquino', 'Kenneth', 'Rollo', NULL, 3, 1, '1992-10-18', NULL, NULL, NULL, NULL, 1, 50, 1, NULL, 1, 234),
(837, 'Aquino', 'Kurt lee', 'Rollo', NULL, 3, 1, '1994-03-16', NULL, NULL, NULL, NULL, 1, 50, 1, NULL, 1, 234),
(838, 'Galang', 'Angelica', NULL, NULL, 19, 2, NULL, NULL, NULL, NULL, NULL, 1, 72, 1, NULL, 1, 234),
(839, 'Baldongan', 'Stephen', 'Ngayodan', NULL, 1, 1, '1961-01-28', NULL, NULL, NULL, NULL, 7, 1, 2, NULL, 1, 235),
(840, 'Baldongan', 'Caroline', 'Addon', NULL, 2, 2, '1970-05-19', NULL, NULL, NULL, NULL, 7, 1, 2, NULL, 1, 235),
(841, 'Baldongan', 'Steven', 'Addon', NULL, 3, 1, '1997-11-02', NULL, NULL, NULL, NULL, 7, 1, 1, NULL, 1, 235),
(842, 'Baldongan', 'Sherwin', 'Addon', NULL, 3, 1, '1999-10-16', NULL, NULL, NULL, NULL, 7, 1, 1, NULL, 1, 235),
(843, 'Baldongan', 'Shirlyne', 'Addon', NULL, 4, 2, '2002-12-17', NULL, NULL, NULL, NULL, 7, 1, 1, NULL, 1, 235),
(844, 'Baldongan', 'Stephany', 'Addon', NULL, 4, 2, '2004-06-20', NULL, NULL, NULL, NULL, 7, 1, 1, NULL, 1, 235),
(845, 'Bag-ayan', 'Danilo', 'Alicop', NULL, 1, 1, '1963-10-15', NULL, NULL, NULL, NULL, 7, 9, 2, NULL, 1, 236),
(846, 'Bag-ayan', 'Jordan', 'Bumacas', NULL, 3, 1, '1997-02-10', NULL, NULL, NULL, NULL, 7, 9, 1, NULL, 1, 236),
(847, 'Bag-ayan', 'Charlie', 'Bumacas', NULL, 3, 1, '1999-04-08', NULL, NULL, NULL, NULL, 7, 9, 1, NULL, 1, 236),
(848, 'Bag-ayan', 'Angelica', 'Bumacas', NULL, 4, 2, '2001-06-02', NULL, NULL, NULL, NULL, 7, 9, 1, NULL, 1, 236),
(849, 'Bag-ayan', 'Nicole', 'Bumacas', NULL, 4, 2, '2006-06-05', NULL, NULL, NULL, NULL, 7, 9, 1, NULL, 1, 236),
(850, 'Bag-ayan', 'Vallery', 'Bumacas', NULL, 4, 2, '2009-08-23', NULL, NULL, NULL, NULL, 7, 9, 1, NULL, 1, 236),
(851, 'Banganan', 'Edward', 'K.', NULL, 1, 1, '1971-11-10', NULL, NULL, NULL, NULL, 7, NULL, NULL, NULL, 1, 237),
(852, 'Banganan', 'Elmer', 'M.', NULL, 3, 1, '1995-04-03', NULL, NULL, NULL, NULL, 7, NULL, NULL, NULL, 1, 237),
(853, 'Banganan', 'Mable grail', 'M.', NULL, 4, 2, '1997-03-30', NULL, NULL, NULL, NULL, 7, NULL, 1, NULL, 1, 237),
(854, 'Banganan', 'Novalyn', 'M.', NULL, 4, 2, '2002-11-01', NULL, NULL, NULL, NULL, 7, NULL, 1, NULL, 1, 237),
(855, 'Banganan', 'John michael', 'M.', NULL, 3, 1, '1999-05-10', NULL, NULL, NULL, NULL, 7, NULL, 1, NULL, 1, 237),
(856, 'Banganan', 'Larry', 'Parang', NULL, 1, 1, '1966-12-24', NULL, NULL, NULL, NULL, 7, 9, 2, NULL, 1, 238),
(857, 'Banganan', 'Juliet', 'Mascay', NULL, 2, 2, NULL, NULL, NULL, NULL, NULL, 7, 9, 2, NULL, 1, 238),
(858, 'Banganan', 'Jhonalyn', 'Mascay', NULL, 4, 2, '1992-03-02', NULL, NULL, NULL, NULL, 7, 9, 1, NULL, 1, 238),
(859, 'Banganan', 'Larry', 'Mascay', NULL, 3, 1, '1994-11-09', NULL, NULL, NULL, NULL, 7, NULL, 2, NULL, 1, 238),
(860, 'Banganan', 'Melody', 'Napaan', NULL, 8, 2, NULL, NULL, NULL, NULL, NULL, 7, NULL, 2, NULL, 1, 238),
(861, 'Banganan', 'Benjie', 'Napaan', NULL, 9, 1, NULL, NULL, NULL, NULL, NULL, 7, NULL, 1, NULL, 1, 238),
(862, 'Banganan', 'Lhyman', 'Catiwao', NULL, 1, 1, '1974-10-04', NULL, NULL, NULL, NULL, 7, NULL, NULL, NULL, 1, 239),
(863, 'Banganan', 'Lhyman', 'Catiwao', NULL, 3, 1, '1998-07-23', NULL, NULL, NULL, NULL, 7, NULL, 1, NULL, 1, 239),
(864, 'Banganan', 'Kelvin', 'Catiwao', NULL, 3, 1, '1996-01-29', NULL, NULL, NULL, NULL, 7, NULL, 1, NULL, 1, 239),
(865, 'Banganan', 'Sherwood', 'Catiwao', NULL, 3, 1, '2000-11-07', NULL, NULL, NULL, NULL, 7, NULL, 1, NULL, 1, 239),
(866, 'Banganan', 'Marcial', 'Catiwao', NULL, 3, 1, '2005-05-29', NULL, NULL, NULL, NULL, 7, NULL, 1, NULL, 1, 239),
(867, 'Banganan', 'Clear', 'Catiwao', NULL, 4, 2, '2007-09-28', NULL, NULL, NULL, NULL, 7, NULL, 1, NULL, 1, 239),
(868, 'Besset', 'Clayton', 'Kindas', NULL, 1, 1, '1981-08-30', NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, 240),
(869, 'Besset', 'Esther', NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 240),
(870, 'Besset', 'Nichole', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 240),
(871, 'Besset', 'Maria ericca', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 240),
(872, 'Besset', 'Claresse mae', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 240),
(873, 'Besset', 'Miguel luis', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 240),
(874, 'Bitol', 'Albert', 'Chapdian', NULL, 1, 1, '1986-11-07', NULL, NULL, NULL, NULL, 9, 18, 2, NULL, 1, 241),
(875, 'Bitol', 'Shally mar', 'Kingay', NULL, 2, 2, '1985-08-29', NULL, NULL, NULL, NULL, 9, 16, 2, NULL, 1, 241),
(876, 'Bitol', 'Rav crawder', 'Kingay', NULL, 3, 1, '2011-06-11', NULL, NULL, NULL, NULL, 9, NULL, 1, NULL, 1, 241),
(877, 'Bitol', 'Zoeyjen', 'Kingay', NULL, 4, 2, '2018-08-14', NULL, NULL, NULL, NULL, 9, NULL, 1, NULL, 1, 241),
(878, 'Blanco', 'Alfredo', 'Marides', NULL, 1, 1, '1948-09-03', NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, 242),
(879, 'Blanco', 'Alexis', 'De guzman', NULL, 3, 1, '1995-06-04', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 242),
(880, 'Blanco', 'Erika paz', 'De guzman', NULL, 4, 2, '1998-10-25', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 242),
(881, 'Buenaventura', 'Regor dominic', 'Cabanes', NULL, 1, 1, '1983-04-24', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 243),
(882, 'Buenaventura', 'Rolyn', 'Oros', NULL, 2, 2, '1982-05-12', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 243),
(883, 'Buenaventura', 'Ivan joshua', 'Oros', NULL, 3, 1, '2004-06-26', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 243),
(884, 'Buenaventura', 'Marrianne', 'Oros', NULL, 4, 2, '2011-11-10', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 243),
(885, 'Burkett', 'Evelyn', 'Duran', NULL, 1, 2, '1968-06-14', NULL, NULL, NULL, NULL, NULL, NULL, 4, NULL, 1, 244),
(886, 'Bustos', 'Rean', 'P.', NULL, 1, 1, '1982-06-08', NULL, NULL, NULL, NULL, 9, 69, 2, NULL, 1, 245),
(887, 'Bustos', 'Jeanny', 'S.', NULL, 2, 2, '1975-07-27', NULL, NULL, NULL, NULL, 9, 16, 2, NULL, 1, 245),
(888, 'Segundo', 'Joevimar', 'T.', NULL, NULL, 1, '1995-06-05', NULL, NULL, NULL, NULL, 9, 16, 1, NULL, 1, 245),
(889, 'Segundo', 'Reen mar', 'T.', NULL, NULL, 2, '2000-03-02', NULL, NULL, NULL, NULL, 9, 16, 1, NULL, 1, 245),
(890, 'Bustos', 'Eisen russel', 'S.', NULL, NULL, 1, '2004-06-25', NULL, NULL, NULL, NULL, 9, 16, 1, NULL, 1, 245),
(891, 'Bustos', 'Reajean', 'S.', NULL, NULL, 2, '2005-10-19', NULL, NULL, NULL, NULL, 9, 16, 1, NULL, 1, 245),
(892, 'Bustos', 'Aaron jhone', 'S.', NULL, NULL, 1, '2006-11-13', NULL, NULL, NULL, NULL, 9, 16, 1, NULL, 1, 245),
(893, 'Bustos', 'Reese rean', 'S.', NULL, NULL, 2, '2008-12-31', NULL, NULL, NULL, NULL, 9, 16, 1, NULL, 1, 245),
(894, 'Bustos', 'Vicrejul grant', 'S.', NULL, NULL, 1, '2011-12-08', NULL, NULL, NULL, NULL, 9, 16, 1, NULL, 1, 245),
(895, 'Bustos', 'Omega roun', 'S.', NULL, NULL, 2, '2013-08-05', NULL, NULL, NULL, NULL, 9, 16, 1, NULL, 1, 245);
INSERT INTO `resident` (`resident_id`, `last_name`, `first_name`, `middle_name`, `suffix`, `relationship_to_hh_id`, `sex_id`, `date_of_birth`, `birth_city_municipality`, `birth_province`, `birth_country`, `nationality_id`, `religion_id`, `ethnicity_id`, `marital_status_id`, `clan_id`, `resident_status_id`, `household_id`) VALUES
(896, 'Catores', 'Ador', 'Bantiding', NULL, 1, 1, '1959-01-15', NULL, NULL, NULL, NULL, 17, 16, 2, NULL, 1, 246),
(897, 'Catores', 'Jane', 'Pacsi', NULL, 2, 2, '1963-06-01', NULL, NULL, NULL, NULL, 17, 9, 2, NULL, 1, 246),
(898, 'Catores', 'Lanie', 'Pacsi', NULL, 4, 2, '1991-07-14', NULL, NULL, NULL, NULL, 17, NULL, 1, NULL, 1, 246),
(899, 'Catores', 'Alyssa', 'Pacsi', NULL, 4, 2, '1996-05-11', NULL, NULL, NULL, NULL, 17, NULL, 1, NULL, 1, 246),
(900, 'Catores', 'Rowela', 'Pacsi', NULL, 4, 2, '1997-11-09', NULL, NULL, NULL, NULL, 17, NULL, 1, NULL, 1, 246),
(901, 'Catores', 'Robert', 'Pacsi', NULL, 1, 1, '1988-07-24', NULL, NULL, NULL, NULL, 1, 18, 2, NULL, 1, 247),
(902, 'Sibayan', 'Rosalin', 'Capsula', NULL, 2, 2, '1986-12-27', NULL, NULL, NULL, NULL, 1, NULL, 2, NULL, 1, 247),
(903, 'Catores', 'Ridick', 'Sibayan', NULL, 3, 1, '2009-12-15', NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 247),
(904, 'Catores', 'Rieshe ann', 'Sibayan', NULL, 4, 2, '2012-07-26', NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 247),
(905, 'Canuto', 'Hilario', 'Paytocan', NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, 9, 18, 1, 5, 1, 248),
(906, 'Canuto', 'Bobby', 'Panta', NULL, 1, 1, '1972-05-11', NULL, NULL, NULL, NULL, 9, 16, 2, 5, 1, 249),
(907, 'Canuto', 'Philip', 'Panta', NULL, NULL, 1, '1984-04-24', NULL, NULL, NULL, NULL, 9, 16, 1, 5, 1, 249),
(908, 'Sison', 'Rosemarie', 'Canuto', NULL, NULL, 2, '1973-07-12', NULL, NULL, NULL, NULL, 9, 16, 2, 5, 1, 249),
(909, 'Sison', 'Roosevelt', 'Canuto', NULL, NULL, 1, '1993-02-25', NULL, NULL, NULL, NULL, 9, 16, 1, 5, 1, 249),
(910, 'Sison', 'Ruth', 'Canuto', NULL, NULL, 2, '1994-06-27', NULL, NULL, NULL, NULL, 9, 16, 1, 5, 1, 249),
(911, 'Sison', 'Sharon rose', 'Canuto', NULL, NULL, 2, '1996-02-07', NULL, NULL, NULL, NULL, 9, 16, 1, 5, 1, 249),
(912, 'Sison', 'Lovelle faith', 'Canuto', NULL, NULL, 2, '1997-02-19', NULL, NULL, NULL, NULL, 9, 16, 1, 5, 1, 249),
(913, 'Sison', 'Jireh', 'Canuto', NULL, NULL, 2, '1998-03-07', NULL, NULL, NULL, NULL, 9, 16, 1, 5, 1, 249),
(914, 'Carino', 'Segundo', 'C.', NULL, 1, 1, '1955-03-13', NULL, NULL, NULL, NULL, 1, 52, 2, NULL, 1, 250),
(915, 'Carino', 'Luz', 'V.', NULL, 2, 2, '1962-06-28', NULL, NULL, NULL, NULL, 1, 52, 2, NULL, 1, 250),
(916, 'Carino', 'Roy', NULL, NULL, 3, 1, '1985-03-13', NULL, NULL, NULL, NULL, 1, 52, 1, NULL, 1, 250),
(917, 'Carino', 'Radewtor', 'V', NULL, 3, 1, '1987-04-20', NULL, NULL, NULL, NULL, 1, 52, 3, NULL, 1, 250),
(918, 'Viduya', 'Marites', 'G.', NULL, 25, 2, '1986-07-02', NULL, NULL, NULL, NULL, 1, 52, 3, NULL, 1, 250),
(919, 'Carino', 'Zhion rhadez', 'V.', NULL, 9, 1, '2022-10-28', NULL, NULL, NULL, NULL, 1, 52, 1, NULL, 1, 250),
(920, 'Chakas', 'Silvestre', 'Likkab', NULL, 1, 1, '1965-10-04', NULL, NULL, NULL, NULL, NULL, 50, 2, NULL, 1, 251),
(921, 'Chakas', 'Pacita', 'Tafaleng', NULL, 2, 2, '1965-04-08', NULL, NULL, NULL, NULL, NULL, 50, 2, NULL, 1, 251),
(922, 'Chakas', 'Shan jay', 'Tafaleng', NULL, 3, 1, '1993-11-05', NULL, NULL, NULL, NULL, NULL, 50, 1, NULL, 1, 251),
(923, 'Chakas', 'Shary gayle', 'Tafaleng', NULL, 4, 2, '1997-08-20', NULL, NULL, NULL, NULL, NULL, 50, 1, NULL, 1, 251),
(924, 'Harnon', 'Zyra ayesha', NULL, NULL, 10, 2, '2014-11-02', NULL, NULL, NULL, NULL, NULL, 50, 1, NULL, 1, 251),
(925, 'Cot-ong', 'Orly', 'Paay', NULL, 1, 1, '1983-10-20', NULL, NULL, NULL, NULL, 22, 60, 2, NULL, 1, 252),
(926, 'Cot-ong', 'Jean', 'Pilo', NULL, 2, 2, '1983-05-10', NULL, NULL, NULL, NULL, 22, 16, 2, NULL, 1, 252),
(927, 'Canuto', 'Cindy', 'Baiwes', NULL, NULL, 2, '2000-04-26', NULL, NULL, NULL, NULL, 22, NULL, 1, 5, 1, 252),
(928, 'Tuangan', 'Melody', 'Canuto', NULL, NULL, 2, '2004-01-21', NULL, NULL, NULL, NULL, 22, NULL, 1, 5, 1, 252),
(929, 'Cot-ong', 'Jordan', 'Canuto', NULL, NULL, 1, '2011-01-13', NULL, NULL, NULL, NULL, 22, NULL, 1, 5, 1, 252),
(930, 'Cuanso', 'Johnny', 'Lardizabal', NULL, 1, 1, '1957-09-21', NULL, NULL, NULL, NULL, 22, 9, 2, NULL, 1, 253),
(931, 'Cuanso', 'Lolita', 'Canuto', NULL, 2, 2, '1970-08-20', NULL, NULL, NULL, NULL, 22, 16, 2, 5, 1, 253),
(932, 'Cueto', 'Joan', 'Cuanso', NULL, 4, 2, '1990-07-13', NULL, NULL, NULL, NULL, 22, 16, 2, NULL, 1, 253),
(933, 'Cuanso', 'Marco', 'Canuto', NULL, 3, 1, '1992-09-04', NULL, NULL, NULL, NULL, 22, 16, 1, 5, 1, 253),
(934, 'Cuanso', 'Jonathan', 'Canuto', NULL, 3, 1, '1997-10-03', NULL, NULL, NULL, NULL, 22, 16, 1, 5, 1, 253),
(935, 'Cuanso', 'Mae ann', 'Canuto', NULL, 4, 2, '2000-08-19', NULL, NULL, NULL, NULL, 22, 16, 1, 5, 1, 253),
(936, 'Cueto', 'Jammellert', 'Cuanso', NULL, 9, 1, '2010-05-06', NULL, NULL, NULL, NULL, 22, 16, 1, NULL, 1, 253),
(937, 'Cueto', 'John sean', 'Cuanso', NULL, 9, 1, '2011-09-29', NULL, NULL, NULL, NULL, 22, 16, 1, NULL, 1, 253),
(938, 'Denon', 'Mary', 'Canuto', NULL, 1, 2, '1977-03-02', NULL, NULL, NULL, NULL, 22, 16, 4, 5, 1, 254),
(939, 'Denon', 'Kharen', 'Canuto', NULL, 4, 2, '1998-09-02', NULL, NULL, NULL, NULL, 22, 16, 1, 5, 1, 254),
(940, 'Denon', 'Kay', 'Canuto', NULL, 4, 2, '2002-11-25', NULL, NULL, NULL, NULL, 22, 16, 1, 5, 1, 254),
(941, 'Denon', 'Jacob', 'Canuto', NULL, 3, 1, '2008-08-16', NULL, NULL, NULL, NULL, 22, 16, 1, 5, 1, 254),
(942, 'Donglal', 'Gemma', 'Pacsi', NULL, 1, 2, '1967-01-01', NULL, NULL, NULL, NULL, 9, 9, 4, NULL, 1, 255),
(943, 'Donglal', 'Edwin', 'Pacsi', NULL, 3, 1, '1997-09-10', NULL, NULL, NULL, NULL, 9, 9, 1, NULL, 1, 255),
(944, 'Donglal', 'Gina', 'Pacsi', NULL, 4, 2, '2000-09-23', NULL, NULL, NULL, NULL, 9, 9, 1, NULL, 1, 255),
(945, 'Cawayan', 'Philex', NULL, NULL, 7, 1, NULL, NULL, NULL, NULL, NULL, 9, 9, 1, NULL, 1, 255),
(946, 'Cawayan', 'Jenlex', 'Pacsi', NULL, 10, 2, '2018-12-09', NULL, NULL, NULL, NULL, 9, 9, 1, NULL, 1, 255),
(947, 'Domogen', 'Wilson', 'Parang', NULL, 1, 1, '1965-03-25', NULL, NULL, NULL, NULL, 7, NULL, 2, NULL, 1, 256),
(948, 'Domogen', 'Meriam', 'Pachao', NULL, 2, 2, '1971-10-12', NULL, NULL, NULL, NULL, 7, NULL, 2, NULL, 1, 256),
(949, 'Domogen', 'Kimberly', 'Pachao', NULL, 4, 2, '1995-06-02', NULL, NULL, NULL, NULL, 7, NULL, 1, NULL, 1, 256),
(950, 'Domogen', 'Jovelyn', 'Pachao', NULL, 4, 2, '1997-11-12', NULL, NULL, NULL, NULL, 7, NULL, 1, NULL, 1, 256),
(951, 'Domogen', 'Mark', 'Pachao', NULL, 3, 1, '2004-11-08', NULL, NULL, NULL, NULL, 7, NULL, 1, NULL, 1, 256),
(952, 'Ebba', 'Isabela', NULL, NULL, NULL, 2, '1945-11-21', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 257),
(953, 'Faraon', 'Rowell', 'A.', NULL, 1, 1, '1976-03-31', NULL, NULL, NULL, NULL, 4, 52, 2, NULL, 1, 257),
(954, 'Faraon', 'Jasmine', 'S.', NULL, NULL, 2, '2000-07-15', NULL, NULL, NULL, NULL, 4, 52, 1, NULL, 1, 257),
(955, 'Faraon', 'Yuri', 'E.', NULL, NULL, 2, '2006-07-07', NULL, NULL, NULL, NULL, 4, 52, 1, NULL, 1, 257),
(956, 'Faraon', 'Markus', 'E.', NULL, NULL, 1, '2010-03-16', NULL, NULL, NULL, NULL, 4, 9, 1, NULL, 1, 257),
(957, 'Ebba', 'Ma. petra', 'V.', NULL, NULL, 2, '1976-06-27', NULL, NULL, NULL, NULL, 4, 9, 1, NULL, 1, 257),
(958, 'Ebba', 'Sarah isabel', 'V.', NULL, NULL, 2, '2000-09-27', NULL, NULL, NULL, NULL, 4, 9, 1, NULL, 1, 257),
(959, 'Ebba', 'Ramon luis', 'V.', NULL, NULL, 1, '2002-08-24', NULL, NULL, NULL, NULL, 4, 9, 1, NULL, 1, 257),
(960, 'Charlotte', 'Mae', NULL, NULL, NULL, NULL, '2004-10-30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 258),
(961, 'Gayudan', 'Lilibeth', 'Bawas', NULL, 1, 2, '1968-06-13', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 259),
(962, 'Gueco', 'Vida', 'Lambanicio', NULL, 1, 2, '1956-05-27', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 260),
(963, 'Estacio', 'Randy', 'Dela cruz', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, 260),
(964, 'Sunga', 'Marco', 'Espinoza', NULL, NULL, 1, '1997-09-02', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 260),
(965, 'Langgoyan', 'Manuel', 'Lino', NULL, 1, 1, '1952-04-26', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 261),
(966, 'Langgoyan', 'Magdalena', 'Gumilao', NULL, 2, 2, '1967-11-05', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 261),
(967, 'Langgoyan', 'Sherly', 'Gumilao', NULL, 4, 2, '1988-01-10', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 261),
(968, 'Langgoyan', 'Wela', 'Gumilao', NULL, 4, 2, '1990-04-16', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 261),
(969, 'Langgoyan', 'Manuel', 'Gumilao', NULL, 3, 1, '1992-05-02', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 261),
(970, 'Langgoyan', 'Heil', 'Gumilao', NULL, 3, 1, '2001-01-12', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 261),
(971, 'Langgoyan', 'Eleigh', 'Gumilao', NULL, 4, 2, '2002-09-06', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 261),
(972, 'Langgoyan', 'Segundo', 'Lino', NULL, 1, 1, '1945-05-20', NULL, NULL, NULL, NULL, 22, NULL, 4, NULL, 1, 262),
(973, 'Langgoyan', 'Alice', 'Balusdan', NULL, 4, 2, '1996-09-02', NULL, NULL, NULL, NULL, 22, NULL, 1, NULL, 1, 262),
(974, 'Langgoyan', 'Andrew', 'Balusdan', NULL, 3, 1, '1991-10-19', NULL, NULL, NULL, NULL, 22, NULL, 1, NULL, 1, 262),
(975, 'Langgoyan', 'Ariel', 'Balusdan', NULL, 3, 1, '1985-07-01', NULL, NULL, NULL, NULL, 22, NULL, 1, NULL, 1, 262),
(976, 'Gonzales', 'Bobby jane', 'Balusdan', NULL, 9, 1, '2007-08-01', NULL, NULL, NULL, NULL, 22, NULL, 1, NULL, 1, 262),
(977, 'Bulay', 'Jun jun', NULL, NULL, 9, 1, NULL, NULL, NULL, NULL, NULL, 22, NULL, 1, NULL, 1, 262),
(978, 'Langgoyan', 'Allan', 'Balusdan', NULL, 3, 1, '1992-06-17', NULL, NULL, NULL, NULL, 22, NULL, 1, NULL, 1, 262),
(979, 'Legaspi', 'Amy', 'L.', NULL, NULL, 2, '1988-02-29', NULL, NULL, NULL, NULL, 1, NULL, 2, NULL, 1, 262),
(980, 'Magallis', 'Pacita', 'D.', NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 1, NULL, 2, NULL, 1, 263),
(981, 'Magallis', 'Daryl', 'D.', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 263),
(982, 'Magallis', 'Roberto', 'D.', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 263),
(983, 'Lucas', 'Yhvonne', 'M.', NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 263),
(984, 'Garcia', 'Zhyril', 'M.', NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 263),
(985, 'Garcia', 'Zhyra', 'M.', NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 263),
(986, 'Man-a', 'Peter', 'P.', NULL, 1, 1, '1958-07-01', NULL, NULL, NULL, NULL, 1, 50, 2, NULL, 1, 264),
(987, 'Man-a', 'Maria paz', 'C.', NULL, 2, 2, '1960-11-28', NULL, NULL, NULL, NULL, 1, 50, 2, NULL, 1, 264),
(988, 'Man-a', 'Norman', 'P.', NULL, 3, 1, '1996-03-15', NULL, NULL, NULL, NULL, 1, 50, 1, NULL, 1, 264),
(989, 'Man-a', 'Phranel', 'P.', NULL, 4, 2, '1990-03-11', NULL, NULL, NULL, NULL, 1, 50, 1, NULL, 1, 264),
(990, 'Man-a', 'Melanie', 'P.', NULL, 4, 2, NULL, NULL, NULL, NULL, NULL, 1, 50, 1, NULL, 1, 264),
(991, 'Man-a', 'Adler', 'P.', NULL, 9, 1, '2009-09-14', NULL, NULL, NULL, NULL, 1, 50, 1, NULL, 1, 264),
(992, 'Man-a', 'Eduardo', 'P.', NULL, 3, 1, '1994-05-26', NULL, NULL, NULL, NULL, 1, 50, 1, NULL, 1, 264),
(993, 'Man-a', 'Norma', 'P.', NULL, 4, 2, '1996-03-15', NULL, NULL, NULL, NULL, 1, 50, 1, NULL, 1, 264),
(994, 'Massagan', 'Albert', 'Cayabo', NULL, 1, 1, '1992-05-16', NULL, NULL, NULL, NULL, 9, NULL, 2, NULL, 1, 265),
(995, 'Massagan', 'Geraldine', 'Caparas', NULL, 2, 2, '1992-10-20', NULL, NULL, NULL, NULL, 9, 16, 2, NULL, 1, 265),
(996, 'Massagan', 'Aning', 'Ventura', NULL, 4, 2, NULL, NULL, NULL, NULL, NULL, 9, 16, 1, NULL, 1, 265),
(997, 'Malindao', 'Teofilo', 'M.', NULL, 1, 1, '1905-05-15', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 266),
(998, 'Tumayan', 'Mobil', 'D.', NULL, 7, 1, '1988-12-20', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 266),
(999, 'Tumayan', 'Bernalyn', 'M.', NULL, 4, 2, '1991-06-14', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 266),
(1000, 'Tumayan', 'Biru', 'M.', NULL, 11, 1, '2022-02-28', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 266),
(1001, 'Navor', 'Rhoda', 'E.', NULL, NULL, 2, '1974-05-04', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 267),
(1002, 'Ngo-ay', 'Vilma', 'Akiapas', NULL, 1, 2, '1972-06-22', NULL, NULL, NULL, NULL, NULL, 16, 2, NULL, 1, 268),
(1003, 'Akiapas', 'Vanessa', 'Quintol', NULL, 4, 2, '1991-02-10', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 268),
(1004, 'Ngo-ay', 'Gladys', 'Akiapas', NULL, 4, 2, '1995-07-09', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 268),
(1005, 'Marias', 'Irish cassandra', 'Ngo-ay', NULL, 4, 2, '2015-01-27', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 268),
(1006, 'Ngoay', 'Ramon', 'Olsem', NULL, 1, 1, '1967-06-03', NULL, NULL, NULL, NULL, NULL, 16, 2, NULL, 1, 269),
(1007, 'Ngoay', 'Sherylyn', 'Elacion', NULL, 2, 2, '1987-07-29', NULL, NULL, NULL, NULL, NULL, 75, 2, NULL, 1, 269),
(1008, 'Ngaoy', 'Regine', 'Frank', NULL, NULL, 2, '2001-08-27', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 269),
(1009, 'Ngaoy', 'Kirsten', 'Elacion', NULL, NULL, 2, '2009-10-20', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 269),
(1010, 'Ngoay', 'Angeline', 'Elacion', NULL, NULL, 2, '2010-10-02', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 269),
(1011, 'Ngoay', 'Matt', 'Elacion', NULL, NULL, 1, '2014-11-30', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 269),
(1012, 'Ngoay', 'John mark', NULL, NULL, NULL, 1, '2003-01-01', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 269),
(1013, 'Elacion', 'Francisco', 'Diamata', NULL, NULL, 1, '1977-05-02', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 269),
(1014, 'Hillario', 'Conchita', NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, 269),
(1015, 'Elacion', 'John paul', 'Symballa', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 269),
(1016, 'Elacion', 'Raquel', 'Diamata', NULL, NULL, 1, '1992-02-14', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 269),
(1017, 'Crujedo', 'Marissa', NULL, NULL, NULL, 2, '1995-02-20', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 269),
(1018, 'Elacion', 'Katelyn', 'Crujedo', NULL, NULL, 2, '2016-06-10', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 269),
(1019, 'Elacion', 'Gwyneth', 'Crujedo', NULL, NULL, 2, '2017-10-22', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 269),
(1020, 'Diamata', 'Agapita', 'Robias', NULL, NULL, 2, '1945-03-25', NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, 269),
(1021, 'Elacion', 'Francisco', 'Sumalia', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 269),
(1022, 'Ngoay', 'Margie', 'Pacsi', NULL, 1, 2, '1977-03-04', NULL, NULL, NULL, NULL, 9, 9, 2, NULL, 1, 270),
(1023, 'Ngoay', 'Kyla isabel', 'Pacsi', NULL, 4, 2, '2005-09-30', NULL, NULL, NULL, NULL, 9, NULL, 1, NULL, 1, 270),
(1024, 'Miguel', 'Ethan rain', 'Pacsi', NULL, NULL, 1, '2010-06-24', NULL, NULL, NULL, NULL, 9, NULL, 1, NULL, 1, 270),
(1025, 'Miguel', 'Mika jezrelle', 'Pacsi', NULL, NULL, 2, '2014-06-09', NULL, NULL, NULL, NULL, 9, NULL, 1, NULL, 1, 270),
(1026, 'Junatas', 'Noly', 'De vera', NULL, 1, 1, '1971-12-21', NULL, NULL, NULL, NULL, 17, NULL, 2, NULL, 1, 271),
(1027, 'Junatas', 'Edna', 'Malicdem', NULL, 2, 2, '1974-04-26', NULL, NULL, NULL, NULL, 17, NULL, 2, NULL, 1, 271),
(1028, 'Junatas', 'Joedeo jay', 'Malicdem', NULL, 3, 1, '2000-09-19', NULL, NULL, NULL, NULL, 17, NULL, 1, NULL, 1, 271),
(1029, 'Junatas', 'Samuel mark', 'Malicdem', NULL, 3, 1, '2006-12-31', NULL, NULL, NULL, NULL, 17, NULL, 1, NULL, 1, 271),
(1030, 'Ramos', 'Edilberto', 'Enciso', NULL, NULL, 1, '1979-12-20', NULL, NULL, NULL, NULL, 17, NULL, 2, NULL, 1, 271),
(1031, 'Ramos', 'Lilia', 'Malicdem', NULL, NULL, 2, '1978-06-23', NULL, NULL, NULL, NULL, 17, NULL, 2, NULL, 1, 271),
(1032, 'Ramos', 'Lorenz emman', 'Malicdem', NULL, NULL, 1, '2011-09-01', NULL, NULL, NULL, NULL, 17, NULL, 1, NULL, 1, 271),
(1033, 'Ongyod', 'Robert', 'Fangroy', NULL, 1, 1, '1967-02-07', NULL, NULL, NULL, NULL, 7, 9, 2, NULL, 1, 272),
(1034, 'Ongyod', 'Carina', 'Caoili', NULL, 2, 2, '1963-07-06', NULL, NULL, NULL, NULL, 7, 52, 2, NULL, 1, 272),
(1035, 'Ongyod', 'Michelle', 'Caoili', NULL, 4, 2, '1999-12-24', NULL, NULL, NULL, NULL, 7, NULL, 1, NULL, 1, 272),
(1036, 'Ongyod', 'Bryan', 'Caoili', NULL, 3, 1, '2001-06-17', NULL, NULL, NULL, NULL, 7, NULL, 1, NULL, 1, 272),
(1037, 'Ongyod', 'Bobby lloyd', 'Caoili', NULL, 3, 1, '2004-03-05', NULL, NULL, NULL, NULL, 7, NULL, 1, NULL, 1, 272),
(1038, 'Oros', 'Jones', 'Albano', NULL, 1, 1, '1958-02-02', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 273),
(1039, 'Oros', 'Marina', 'Palunan', NULL, 2, 2, '1964-01-05', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 273),
(1040, 'Oros', 'James', 'Palunan', NULL, 3, 1, '1989-06-04', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 273),
(1041, 'Oros', 'Irene', 'Omaging', NULL, 8, 2, NULL, NULL, NULL, NULL, NULL, NULL, 9, 2, NULL, 1, 273),
(1042, 'Oros', 'Jessica', 'Ogues', NULL, 8, 2, '2006-12-29', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 273),
(1043, 'Oros', 'Rolly', 'Palunan', NULL, 3, 1, '1985-11-09', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 273),
(1044, 'Oros', 'Susanna', 'Sumilao', NULL, NULL, 2, '1986-08-01', NULL, NULL, NULL, NULL, NULL, 9, 2, NULL, 1, 273),
(1045, 'Oros', 'Arllan drew', 'Sumilao', NULL, NULL, 1, '2017-01-26', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 273),
(1046, 'Almasan', 'Eddie', 'Tado', NULL, 7, 1, '1990-07-01', NULL, NULL, NULL, NULL, NULL, 16, 2, NULL, 1, 273),
(1047, 'Almasan', 'Maricel', 'Oros', NULL, 4, 2, '1992-02-16', NULL, NULL, NULL, NULL, NULL, 16, 2, NULL, 1, 273),
(1048, 'Almasan', 'Xean wyath', 'Oros', NULL, 9, 1, '2014-11-25', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 273),
(1049, 'Oros', 'Danny', 'Palunan', NULL, 1, 1, '1983-09-03', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 258),
(1050, 'Palangeo', 'Efren', 'Olowan', NULL, 1, 2, '1974-04-02', NULL, NULL, NULL, NULL, NULL, 9, 2, NULL, 1, 274),
(1051, 'Palangeo', 'Mary', 'Pespes', NULL, 2, 1, '1979-10-08', NULL, NULL, NULL, NULL, NULL, 9, 2, NULL, 1, 274),
(1052, 'Palangeo', 'Mark lester', 'Pespes', NULL, 3, 1, '2002-04-03', NULL, NULL, NULL, NULL, NULL, 9, 1, NULL, 1, 274),
(1053, 'Palangeo', 'Meylanie', 'Pespes', NULL, 4, 2, '2003-05-24', NULL, NULL, NULL, NULL, NULL, 9, 1, NULL, 1, 274),
(1054, 'Palangeo', 'Jillian kate', 'Pespes', NULL, 4, 2, '2006-01-18', NULL, NULL, NULL, NULL, NULL, 9, 1, NULL, 1, 274),
(1055, 'Palangeo', 'Dwayne', 'Pespes', NULL, 3, 1, '2013-07-31', NULL, NULL, NULL, NULL, NULL, 9, 1, NULL, 1, 274),
(1056, 'Pespes', 'Shany rose', 'Manengyao', NULL, 19, 2, '1994-04-13', NULL, NULL, NULL, NULL, NULL, 9, 1, NULL, 1, 274),
(1057, 'Paoay', 'Peter', 'Salda', NULL, 1, 1, '1963-01-06', NULL, NULL, NULL, NULL, 22, 16, 2, NULL, 1, 275),
(1058, 'Paoay', 'Rica', 'Pilo', NULL, 2, 2, '1975-11-20', NULL, NULL, NULL, NULL, 22, 16, 2, NULL, 1, 275),
(1059, 'Paoay', 'Genesis', 'Canuto', NULL, NULL, 1, '1995-12-08', NULL, NULL, NULL, NULL, 22, 16, 1, 5, 1, 275),
(1060, 'Paoay', 'Harold', 'Canuto', NULL, NULL, 1, '1998-04-27', NULL, NULL, NULL, NULL, 22, 16, 2, 5, 1, 275),
(1061, 'Paoay', 'Janet', 'Canuto', NULL, NULL, 2, '2000-09-09', NULL, NULL, NULL, NULL, 22, 16, 1, 5, 1, 275),
(1062, 'Paoay', 'Jason', 'Canuto', NULL, NULL, 1, '2003-07-07', NULL, NULL, NULL, NULL, 22, 16, 1, 5, 1, 275),
(1063, 'Pataras', 'Joseph', 'Tacio', NULL, 1, 1, '1967-12-14', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 276),
(1064, 'Pataras', 'Vilma', 'Bassit', NULL, 2, 2, '1965-05-06', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 276),
(1065, 'Pataras', 'Raquel', 'Bassit', NULL, 4, 2, '1995-03-29', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 276),
(1066, 'Pataras', 'Lander', 'Bassit', NULL, 3, 1, '1996-09-07', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 276),
(1067, 'Pataras', 'Victor', 'A.', NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, 16, 2, NULL, 1, 277),
(1068, 'Pataras', 'Mary', 'A.', NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, 16, 2, NULL, 1, 277),
(1069, 'Pataras', 'Tyroll', 'G.', NULL, NULL, 1, '1981-10-07', NULL, NULL, NULL, NULL, NULL, 16, 2, NULL, 1, 277),
(1070, 'Ursua', 'Esteven', 'I.', NULL, NULL, 1, '1980-04-28', NULL, NULL, NULL, NULL, NULL, 52, 2, NULL, 1, 277),
(1071, 'Ursua', 'Shalanie', 'P.', NULL, NULL, 2, '1983-03-10', NULL, NULL, NULL, NULL, NULL, 16, 2, NULL, 1, 277),
(1072, 'Ursua', 'Steven', 'P.', NULL, NULL, 1, '2016-04-16', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 277),
(1073, 'Piano', 'Carl joaquin', 'B.', NULL, NULL, 1, '2007-04-08', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 277),
(1074, 'Pataras', 'Xena leigh', 'C.', NULL, NULL, 2, '2005-05-11', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 277),
(1075, 'Pataras', 'Yuan reign', 'P.', NULL, NULL, 2, '2011-05-14', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 277),
(1076, 'Gasic', 'Glenda', 'G.', NULL, NULL, 2, '1984-05-06', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 277),
(1077, 'Chale', 'Shaerton', 'N.', NULL, NULL, 1, '1994-01-13', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 277),
(1078, 'Pataras', 'Rizaldo', 'Basilio', NULL, 1, 1, '1978-06-21', NULL, NULL, NULL, NULL, NULL, 16, 2, NULL, 1, 278),
(1079, 'Pataras', 'Sonia', 'Andawey', NULL, 2, 2, '1983-11-11', NULL, NULL, NULL, NULL, NULL, 9, 2, NULL, 1, 278),
(1080, 'Podes', 'Mark', 'Cayabas', NULL, 1, 1, '1954-07-01', NULL, NULL, NULL, NULL, 3, 9, 2, NULL, 1, 263),
(1081, 'Podes', 'Martha', 'Liis', NULL, 2, 2, '1962-07-01', NULL, NULL, NULL, NULL, 3, 9, 2, NULL, 1, 263),
(1082, 'Podes', 'Mark', 'Liis', NULL, 3, 1, '1988-07-01', NULL, NULL, NULL, NULL, 3, 9, 1, NULL, 1, 263),
(1083, 'Podes', 'Beverlyn', 'Liis', NULL, 4, 2, '1990-07-01', NULL, NULL, NULL, NULL, 3, 9, 1, NULL, 1, 263),
(1084, 'Podes', 'Yeamalyn', 'Liis', NULL, 4, 2, '1993-07-01', NULL, NULL, NULL, NULL, 3, 9, 1, NULL, 1, 263),
(1085, 'Podes', 'Zion del mar', 'Liis', NULL, 3, 1, '1994-07-17', NULL, NULL, NULL, NULL, 3, 9, 1, NULL, 1, 263),
(1086, 'Podes', 'Gideon jess mar', 'Liis', NULL, 3, 1, '1997-07-08', NULL, NULL, NULL, NULL, 3, 9, 1, NULL, 1, 263),
(1087, 'Sagaysay', 'Jaime', 'Maitang', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 3, 9, 2, NULL, 1, 263),
(1088, 'Chongpopen', 'Benson', NULL, NULL, 7, 1, NULL, NULL, NULL, NULL, NULL, 3, 9, 2, NULL, 1, 263),
(1089, 'Chongpopen', 'Jenielyn', 'Podes', NULL, 4, 2, NULL, NULL, NULL, NULL, NULL, 3, 9, 2, NULL, 1, 263),
(1090, 'Chongpopen', 'Psalmuelle', 'Podes', NULL, 9, 1, NULL, NULL, NULL, NULL, NULL, 3, 9, 1, NULL, 1, 263),
(1091, 'Purgatorio', 'Tarciano', 'Lagunero', NULL, 1, 1, '1952-01-12', NULL, NULL, NULL, NULL, 3, NULL, 2, NULL, 1, 279),
(1092, 'Purgatorio', 'Leonila', 'Estrera', NULL, 2, 2, '1962-10-22', NULL, NULL, NULL, NULL, 3, NULL, 2, NULL, 1, 279),
(1093, 'Purgatorio', 'Adan', 'Estrera', NULL, 3, 1, '1985-12-30', NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, 1, 279),
(1094, 'Otarra', 'Eva', 'Purgatorio', NULL, 4, 2, '1981-09-28', NULL, NULL, NULL, NULL, 3, NULL, 2, NULL, 1, 279),
(1095, 'Joice', 'Flordeliza', 'Purgatorio', NULL, 4, 2, '1983-05-18', NULL, NULL, NULL, NULL, 3, NULL, 2, NULL, 1, 279),
(1096, 'Quanguey', 'Susana', 'Palunan', NULL, 1, 2, '1959-07-10', NULL, NULL, NULL, NULL, 1, 16, 4, NULL, 1, 227),
(1097, 'Quanguey', 'Aiza', 'Palunan', NULL, 4, 2, '1998-10-19', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 227),
(1098, 'Concepcion', 'Aarron james', 'Quanguey', NULL, NULL, 1, '2015-01-18', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 227),
(1099, 'Concepcion', 'James brian', 'Menez', NULL, NULL, 1, '1997-07-28', NULL, NULL, NULL, NULL, NULL, 52, 1, NULL, 1, 227),
(1100, 'Oros', 'Ernie', 'Palunan', NULL, NULL, 1, '1987-03-11', NULL, NULL, NULL, NULL, NULL, 16, 2, NULL, 1, 227),
(1101, 'Oros', 'Jhon redick', 'Honorio', NULL, NULL, 1, '2012-03-21', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 227),
(1102, 'Quioas', 'Salvador', 'Siloy', NULL, 1, 1, '1950-05-03', NULL, NULL, NULL, NULL, 9, 9, 4, NULL, 1, 267),
(1103, 'Quioas', 'Sedver', 'Nebsan', NULL, NULL, 1, '2004-09-04', NULL, NULL, NULL, NULL, 9, 16, 1, NULL, 1, 267),
(1104, 'Rimando', 'Jerry', 'Baradi', NULL, NULL, 1, '1971-03-30', NULL, NULL, NULL, NULL, 1, NULL, 2, NULL, 1, 274),
(1105, 'Rimando', 'Mila', 'Hidalgo', NULL, 21, 2, '1969-08-08', NULL, NULL, NULL, NULL, 1, NULL, 2, NULL, 1, 274),
(1106, 'Rosimo', 'Xerces', 'Alegre', NULL, 1, 1, '1966-08-06', NULL, NULL, NULL, NULL, 22, 52, 2, NULL, 1, 280),
(1107, 'Rosimo', 'Eliza', 'Canuto', NULL, 2, 2, NULL, NULL, NULL, NULL, NULL, 22, 16, 2, 5, 1, 280),
(1108, 'Rosimo', 'Lizalyn', 'Canuto', NULL, 4, 2, '1998-10-16', NULL, NULL, NULL, NULL, 22, 16, 1, 5, 1, 280),
(1109, 'Rosimo', 'Joshua', 'Canuto', NULL, 3, 1, '2001-04-08', NULL, NULL, NULL, NULL, 22, 16, 1, 5, 1, 280),
(1110, 'Rosimo', 'Xerces', 'Canuto', NULL, 3, 1, '2003-09-09', NULL, NULL, NULL, NULL, 22, 16, 1, 5, 1, 280),
(1111, 'Rosimo', 'Angel mae', 'Canuto', NULL, 4, 2, '2007-10-29', NULL, NULL, NULL, NULL, 22, 16, 1, 5, 1, 280),
(1112, 'Sacley', 'Joseph', 'A.', NULL, 1, 1, '1950-07-22', NULL, NULL, NULL, NULL, 9, 9, 2, NULL, 1, 281),
(1113, 'Sacley', 'Mercedes', 'S.', NULL, 2, 2, '1953-02-20', NULL, NULL, NULL, NULL, 9, NULL, 2, NULL, 1, 281),
(1114, 'Sacley', 'Michael', 'S.', NULL, 3, 1, '1980-12-20', NULL, NULL, NULL, NULL, 9, 18, 2, NULL, 1, 281),
(1115, 'Sacley', 'Laverne', 'C.', NULL, 8, 2, NULL, NULL, NULL, NULL, NULL, 9, 20, 2, NULL, 1, 281),
(1116, 'Sacley', 'Niexes', 'C.', NULL, 10, 2, '2010-05-06', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 281),
(1117, 'Sacley', 'Zhensey', 'C.', NULL, 10, 2, '2012-02-20', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 281),
(1118, 'Sacley', 'Milaniezhe rhean', 'C.', NULL, 10, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 281),
(1119, 'Sacley', 'Michaela marlys', 'C.', NULL, 10, 2, '2018-02-10', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 281),
(1120, 'Montemayor', 'Danilo', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 1, NULL, 2, NULL, 1, 260),
(1121, 'Segundo', 'Don jezreel', 'Oropel', NULL, 1, 1, '1983-03-24', NULL, NULL, NULL, NULL, 9, 16, 2, NULL, 1, 282),
(1122, 'Segundo', 'Sharlene', 'Ablao', NULL, 2, 2, '1984-08-22', NULL, NULL, NULL, NULL, 9, 16, 2, NULL, 1, 282),
(1123, 'Segundo', 'Jason jezreel', 'Ablao', NULL, 3, 1, '2007-07-01', NULL, NULL, NULL, NULL, 9, 16, 1, NULL, 1, 282),
(1124, 'Segundo', 'Mildred', 'P.', NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 28, NULL, 2, NULL, 1, 230),
(1125, 'Soltez', 'Carmela', 'E.', NULL, NULL, 2, '1970-03-12', NULL, NULL, NULL, NULL, 1, NULL, 2, NULL, 1, 257),
(1126, 'Tanglod', 'Leonard', 'Aligo', NULL, 1, 1, '1978-04-09', NULL, NULL, NULL, NULL, 1, 50, 2, NULL, 1, 283),
(1127, 'Osben', 'Maritess', 'Waclin', NULL, 2, 2, '1983-12-08', NULL, NULL, NULL, NULL, 1, 50, NULL, NULL, 1, 283),
(1128, 'Tanglod', 'Rose anne', 'Osben', NULL, 4, 2, '2016-01-25', NULL, NULL, NULL, NULL, 1, 50, 1, NULL, 1, 283),
(1129, 'Tanglod', 'Elmha', 'Osben', NULL, 4, 2, '2018-01-18', NULL, NULL, NULL, NULL, 1, 50, 1, NULL, 1, 283),
(1130, 'Aligo', 'Moses', 'Malingda', NULL, 19, NULL, NULL, NULL, NULL, NULL, NULL, 1, 50, NULL, NULL, 1, 283),
(1131, 'Tawanga', 'Richard', 'Millet', NULL, 1, 1, '1979-09-12', NULL, NULL, NULL, NULL, 3, NULL, 2, NULL, 1, 284),
(1132, 'Tawanga', 'Josephine', 'Gang-ao', NULL, 2, 2, '1980-03-20', NULL, NULL, NULL, NULL, 3, NULL, 2, NULL, 1, 284),
(1133, 'Tawanga', 'Rachel', 'Gang-ao', NULL, 4, 2, '2000-05-03', NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, 1, 284),
(1134, 'Tawanga', 'Richard', 'Gang-ao', NULL, 3, 1, '2001-10-02', NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, 1, 284),
(1135, 'Tawanga', 'Ray', 'Gang-ao', NULL, 3, 1, '2003-02-10', NULL, NULL, NULL, NULL, 3, NULL, 1, NULL, 1, 284),
(1136, 'Valle', 'Moises', 'Tayne', NULL, 1, 1, '1966-05-23', NULL, NULL, NULL, NULL, 11, 9, 2, NULL, 1, 285),
(1137, 'Gabuyog', 'Phoebe', 'Sianen', NULL, 19, 2, NULL, NULL, NULL, NULL, NULL, 11, 9, 2, NULL, 1, 285),
(1138, 'Gabuyog', 'Jaireh', 'Segundo', NULL, 19, 1, NULL, NULL, NULL, NULL, NULL, 11, 16, 2, NULL, 1, 285),
(1139, 'Gabuyog', 'Drecle', 'Sianen', NULL, 10, 2, NULL, NULL, NULL, NULL, NULL, 11, NULL, 1, NULL, 1, 285),
(1140, 'Valdez', 'Katrina', 'L.', NULL, 1, 2, '1993-08-21', NULL, NULL, NULL, NULL, NULL, 52, 1, NULL, 1, 286),
(1141, 'Watawat', 'Melene', 'Pacsi', NULL, 1, 2, '1965-12-25', NULL, NULL, NULL, NULL, 9, 9, 2, NULL, 1, 287),
(1142, 'Watawat', 'Marilyn', 'Pacsi', NULL, 4, 2, '1987-08-09', NULL, NULL, NULL, NULL, 9, 9, 1, NULL, 1, 287),
(1143, 'Watawat', 'Ramon', 'Pacsi', NULL, 3, 1, '1988-09-29', NULL, NULL, NULL, NULL, 9, 9, 1, NULL, 1, 287),
(1144, 'Wanden', 'Johnssen', 'Eustaquio', NULL, 1, 1, '1986-07-13', NULL, NULL, NULL, NULL, 1, NULL, 2, NULL, 1, 288),
(1145, 'Wanden', 'Mildred', 'Matda', NULL, 2, 2, '1988-11-28', NULL, NULL, NULL, NULL, 1, NULL, 2, NULL, 1, 288),
(1146, 'Wanden', 'Johnssen elrry', 'Matda', NULL, 3, 1, '2011-06-29', NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 288),
(1147, 'Wanden', 'Johnssen claign', 'Matda', NULL, 3, 1, '2015-12-03', NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 288),
(1148, 'Abance', 'Richard', 'Ayaman', NULL, 1, 1, '1973-08-26', NULL, NULL, NULL, NULL, 1, 18, 2, NULL, 1, 289),
(1149, 'Abance', 'Judith', 'Canuto', NULL, 2, 2, '1963-09-28', NULL, NULL, NULL, NULL, 1, 18, 2, 5, 1, 289),
(1150, 'Abance', 'Julieray', 'Canuto', NULL, 4, 2, '2002-06-07', NULL, NULL, NULL, NULL, 1, 18, 1, 5, 1, 289),
(1151, 'Bangaan', 'Jane', 'Ramos', NULL, 1, 2, '1965-07-20', NULL, NULL, NULL, NULL, 3, 9, 2, NULL, 1, 291),
(1152, 'Sagud', 'Sherwin', 'Bangaan', NULL, NULL, 1, '1989-09-02', NULL, NULL, NULL, NULL, 3, 9, 1, NULL, 1, 291),
(1153, 'Dazon', 'Jennifer', 'Bangaan', NULL, NULL, 2, '1999-01-07', NULL, NULL, NULL, NULL, 3, 9, 1, NULL, 1, 291),
(1154, 'Canuto', 'Francis', 'Lactawen', NULL, 1, 1, '1967-11-02', NULL, NULL, NULL, NULL, NULL, 16, 2, 5, 1, 292),
(1155, 'Canuto', 'Dominga', 'Killi', NULL, 1, 2, '1945-12-23', NULL, NULL, NULL, NULL, NULL, 16, 4, 5, 1, 293),
(1156, 'Canuto', 'Ronnie', NULL, NULL, 1, 1, '1958-09-09', NULL, NULL, NULL, NULL, 1, 16, 2, 5, 1, 294),
(1157, 'Canuto', 'Jonuven', 'Killi', NULL, 1, 1, '1981-02-01', NULL, NULL, NULL, NULL, 1, 16, 2, 5, 1, 295),
(1158, 'Canuto', 'Gema', 'Lag-asan', NULL, 2, 2, '1983-07-31', NULL, NULL, NULL, NULL, 1, 9, 2, 5, 1, 295),
(1159, 'Canuto', 'Brainer', 'Lag-asan', NULL, 3, 1, '2001-03-11', NULL, NULL, NULL, NULL, 1, NULL, 1, 5, 1, 295),
(1160, 'Canuto', 'Rio', 'Lag-asan', NULL, 3, 1, '2003-05-03', NULL, NULL, NULL, NULL, 1, NULL, 1, 5, 1, 295),
(1161, 'Canuto', 'Shaira', 'Lag-asan', NULL, 4, 2, '2006-08-02', NULL, NULL, NULL, NULL, 1, NULL, 1, 5, 1, 295),
(1162, 'Canuto', 'Gejun', 'Lag-asan', NULL, NULL, NULL, '2019-12-27', NULL, NULL, NULL, NULL, NULL, NULL, 1, 5, 1, 296),
(1163, 'Canuto', 'Wilmer', 'Alicnas', NULL, 1, 1, '1987-01-16', NULL, NULL, NULL, NULL, 1, 16, 1, 5, 1, 297),
(1164, 'Canuto', 'Juner', NULL, NULL, 1, 1, '1993-06-06', NULL, NULL, NULL, NULL, 1, 16, 1, 5, 1, 298),
(1165, 'Concepcion', 'Leonardo', 'Del carpio', NULL, 1, 1, '1963-06-28', NULL, NULL, NULL, NULL, 1, 72, 2, NULL, 1, 299),
(1166, 'Concepcion', 'Jenny', 'Linglingan', NULL, 2, 2, '1980-01-04', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 299),
(1167, 'Concepcion', 'John paul', 'Linglingan', NULL, 3, 1, '2002-03-16', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 299),
(1168, 'Concepcion', 'Jeremy', 'Linglingan', NULL, 3, 1, '2003-12-20', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 299),
(1169, 'Concepcion', 'Jake', 'Linglingan', NULL, 3, 1, '2006-12-14', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 299),
(1170, 'Concepcion', 'Jam', 'Linglingan', NULL, 3, 1, '2009-05-11', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 299),
(1171, 'Dangatan', 'Cris', 'Jose', NULL, 1, 1, '1984-12-29', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 300),
(1172, 'Dangatan', 'Myla', 'Mateo', NULL, 2, 2, NULL, NULL, NULL, NULL, NULL, NULL, 16, 2, NULL, 1, 300),
(1173, 'Dangatan', 'Kate', 'Mateo', NULL, 4, 2, '1998-07-03', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 300),
(1174, 'Dangatan', 'Dan', 'Jose', NULL, 1, 1, '1987-07-01', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 301),
(1175, 'Dangpa', 'Elmo', 'Abante', NULL, 1, 1, '1978-02-24', NULL, NULL, NULL, NULL, 8, 18, 2, NULL, 1, 302),
(1176, 'Dangpa', 'Jodelyn', 'Veloso', NULL, 2, 2, '1985-10-24', NULL, NULL, NULL, NULL, 9, 18, 2, NULL, 1, 302),
(1177, 'Dangpa', 'Elhyane', 'Veloso', NULL, 4, 2, '2004-07-29', NULL, NULL, NULL, NULL, 8, 18, 1, NULL, 1, 302),
(1178, 'Dangpa', 'Charlyne', 'Veloso', NULL, 4, 2, '2008-12-18', NULL, NULL, NULL, NULL, 8, 18, 1, NULL, 1, 302),
(1179, 'Dangpa', 'Ejhay', 'Veloso', NULL, 4, 2, '2011-02-10', NULL, NULL, NULL, NULL, 8, 18, 1, NULL, 1, 302),
(1180, 'Dizon', 'Eugene', 'R.', NULL, 1, 1, '1969-01-02', NULL, NULL, NULL, NULL, 9, 16, 2, NULL, 1, 303),
(1181, 'Dizon', 'Joy', 'Macay', NULL, 2, 2, '1905-05-28', NULL, NULL, NULL, NULL, 9, 16, 2, NULL, 1, 303),
(1182, 'Dizon', 'Eunice', 'Macay', NULL, 4, 2, '1905-06-19', NULL, NULL, NULL, NULL, 9, 16, 1, NULL, 1, 303),
(1183, 'Dizon', 'Graham jee', 'Macay', NULL, 3, 1, '1905-06-21', NULL, NULL, NULL, NULL, 9, 16, 1, NULL, 1, 303),
(1184, 'Dizon', 'Elaijah', 'Macay', NULL, 9, 1, '1905-07-10', NULL, NULL, NULL, NULL, 9, 16, 1, NULL, 1, 303),
(1185, 'Gasic', 'Bingkio', 'Marino', NULL, 1, 1, '1952-11-27', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 304),
(1186, 'Jose', 'Billy', 'Bendasen', NULL, 1, 1, '1949-05-09', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 305),
(1187, 'Jose', 'Maria', 'Aguirre', NULL, 2, 2, '1965-04-07', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 305),
(1188, 'Jose', 'Billy', 'Aguirre', NULL, 3, 1, '1994-07-09', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 305),
(1189, 'Jose', 'Bona deve', 'Capsil', NULL, 8, 2, '1996-10-31', NULL, NULL, NULL, NULL, NULL, 16, 2, NULL, 1, 305),
(1190, 'Jose', 'Terry', 'Capsil', NULL, 9, 1, '2016-07-05', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 305),
(1191, 'Jose', 'Johnny', 'B', NULL, 1, 1, '1951-12-03', NULL, NULL, NULL, NULL, 3, 16, 2, NULL, 1, 306),
(1192, 'Jose', 'Angelyn', 'A', NULL, 2, 2, '1980-08-29', NULL, NULL, NULL, NULL, 3, 16, NULL, NULL, 1, 306),
(1193, 'Quilladas', 'Froilan', 'A', NULL, NULL, 1, '1975-03-30', NULL, NULL, NULL, NULL, 3, 9, NULL, NULL, 1, 306),
(1194, 'Casila', 'John paul', 'J', NULL, NULL, 1, '2014-03-09', NULL, NULL, NULL, NULL, 3, 16, 1, NULL, 1, 306),
(1195, 'Quilladas', 'Jhervingirl', NULL, NULL, NULL, 1, '2014-03-11', NULL, NULL, NULL, NULL, 3, 18, 1, NULL, 1, 306),
(1196, 'Jose', 'Mondrill', 'A', NULL, 1, 1, '1988-01-28', NULL, NULL, NULL, NULL, 3, 16, 1, NULL, 1, 296),
(1197, 'Libayo', 'Jackson', 'Dumani', NULL, 1, 1, '1990-10-21', NULL, NULL, NULL, NULL, NULL, 49, 2, NULL, 1, 307),
(1198, 'Libayo', 'Leizel', 'Canuto', NULL, 2, 2, '1990-12-03', NULL, NULL, NULL, NULL, NULL, 16, 2, 5, 1, 307),
(1199, 'Libayo', 'Zinglie', 'Canuto', NULL, 4, 2, '2018-10-31', NULL, NULL, NULL, NULL, NULL, NULL, 1, 5, 1, 307),
(1200, 'Libayo', 'Bruce', 'Canuto', NULL, 3, 1, '2015-02-24', NULL, NULL, NULL, NULL, NULL, NULL, 1, 5, 1, 307),
(1201, 'Linglingan', 'Amado', 'Canuto', NULL, 1, 1, '1950-04-09', NULL, NULL, NULL, NULL, 1, 16, 2, 5, 1, 308),
(1202, 'Linglingan', 'Mico', 'Eddio', NULL, 1, 1, '1977-09-01', NULL, NULL, NULL, NULL, 1, 18, 2, NULL, 1, 309),
(1203, 'Linglingan', 'Marnelie', 'Comofay', NULL, 2, 2, '1989-08-31', NULL, NULL, NULL, NULL, 1, NULL, 2, NULL, 1, 309),
(1204, 'Linglingan', 'John michael', 'Comofay', NULL, 3, 1, '2005-05-08', NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 309),
(1205, 'Linglingan', 'Aeon nica', 'Comofay', NULL, 4, 2, '2010-10-06', NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, 1, 309),
(1206, 'Linglingan', 'Shayenne evolet', 'Comofay', NULL, 4, 2, '2013-06-30', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 309),
(1207, 'Lipaen', 'Ricky', 'Licoben', NULL, 1, 1, '1973-11-10', NULL, NULL, NULL, NULL, NULL, 9, 2, NULL, 1, 310),
(1208, 'Simsim', 'Georgina', 'Bernal', NULL, 2, 2, '1974-06-01', NULL, NULL, NULL, NULL, NULL, 16, 2, NULL, 1, 310),
(1209, 'Lipaen', 'Ricky', 'Bernal', NULL, 3, 1, '2012-11-16', NULL, NULL, NULL, NULL, NULL, 18, 1, NULL, 1, 310),
(1210, 'Marrero', 'Klent carl', 'Bernal', NULL, 24, 1, '2000-06-06', NULL, NULL, NULL, NULL, NULL, 18, 1, NULL, 1, 310),
(1211, 'Mo-oy', 'Philip', 'Laos', NULL, 1, 1, '1989-02-25', NULL, NULL, NULL, NULL, 1, 9, 2, NULL, 1, 311),
(1212, 'Mo-oy', 'Abegail', 'Jose', NULL, 2, 2, '1992-04-09', NULL, NULL, NULL, NULL, 1, 18, 2, NULL, 1, 311),
(1213, 'Mo-oy', 'Philip paul', 'Jose', NULL, 3, 1, '2013-05-24', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 311),
(1214, 'Mo-oy', 'Geymar', 'Jose', NULL, 3, 1, '2015-03-31', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 311),
(1215, 'Ngo-ay', 'Christopher', 'Kelly', NULL, 1, 1, '1965-12-12', NULL, NULL, NULL, NULL, NULL, 16, 2, NULL, 1, 290),
(1216, 'Padilla', 'Bigno', NULL, NULL, 1, 1, '1945-12-20', NULL, NULL, NULL, NULL, 14, 9, 2, NULL, 1, 312),
(1217, 'Padilla', 'Felicidad', 'Sagada', NULL, 2, 2, '1954-11-24', NULL, NULL, NULL, NULL, 14, 9, 2, NULL, 1, 312),
(1218, 'Padilla', 'Bernard', 'Sagada', NULL, 3, 1, '1994-10-19', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 312),
(1219, 'Padilla', 'Benigno', 'Sagada', NULL, 3, 1, '1989-06-13', NULL, NULL, NULL, NULL, 1, 9, 1, NULL, 1, 312),
(1220, 'Pangket', 'Benito', 'Cor-oyen', NULL, 1, 1, '1963-04-02', NULL, NULL, NULL, NULL, NULL, 9, 2, NULL, 1, 313),
(1221, 'Pangket', 'Lucia', 'Fucasan', NULL, 2, 2, '1965-04-29', NULL, NULL, NULL, NULL, NULL, 9, 2, NULL, 1, 313),
(1222, 'Pangket', 'Sarina', 'Fucasan', NULL, 4, 2, '1992-05-07', NULL, NULL, NULL, NULL, NULL, 9, 1, NULL, 1, 313),
(1223, 'Pangket', 'Eurile', 'Fucasan', NULL, 3, 1, '1990-11-07', NULL, NULL, NULL, NULL, NULL, 9, 1, NULL, 1, 313),
(1224, 'Pangket', 'Joan', 'Fucasan', NULL, 4, 2, '2004-12-13', NULL, NULL, NULL, NULL, NULL, 9, 1, NULL, 1, 313),
(1225, 'Paoay', 'Bonie', 'Salda', NULL, 1, 1, '1962-08-17', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 314),
(1226, 'Paoay', 'Rosalin', 'Canuto', NULL, 2, 2, '1974-04-03', NULL, NULL, NULL, NULL, 1, 16, 2, 5, 1, 314),
(1227, 'Paoay', 'Aphrilone', 'Canuto', NULL, NULL, 1, '1995-04-09', NULL, NULL, NULL, NULL, 1, 16, 1, 5, 1, 314),
(1228, 'Ubina', 'Ronalyn kim', 'Paoay', NULL, NULL, 2, '1996-08-06', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 314),
(1229, 'Paoay', 'Regina', 'Canuto', NULL, NULL, 2, '1998-08-19', NULL, NULL, NULL, NULL, 1, 16, 1, 5, 1, 314),
(1230, 'Paoay', 'Nicole dianne', 'Canuto', NULL, NULL, 2, '2000-09-03', NULL, NULL, NULL, NULL, 1, 16, 1, 5, 1, 314),
(1231, 'Paoay', 'Geraldine ane', 'Canuto', NULL, NULL, 2, '2004-03-15', NULL, NULL, NULL, NULL, 1, 16, 1, 5, 1, 314),
(1232, 'Paoay', 'Gracelyn mea', 'Canuto', NULL, NULL, 2, '2004-03-15', NULL, NULL, NULL, NULL, 1, 16, 1, 5, 1, 314),
(1233, 'Paoay', 'Dember', 'Canuto', NULL, NULL, 1, '2005-12-29', NULL, NULL, NULL, NULL, 1, 16, 1, 5, 1, 314),
(1234, 'Paoay', 'Bonie', 'Canuto', NULL, NULL, 1, '2007-11-17', NULL, NULL, NULL, NULL, 1, 16, 1, 5, 1, 314),
(1235, 'Quilladas', 'Angelyn', NULL, NULL, NULL, 2, '1980-08-29', NULL, NULL, NULL, NULL, 3, NULL, 2, NULL, 1, 306),
(1236, 'Ricablanca', 'Carmelito', 'Miller', NULL, 1, 1, '1951-02-28', NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, 1, 315),
(1237, 'Ricablanca', 'Terre', 'Sebio', NULL, 2, 2, '1968-02-14', NULL, NULL, NULL, NULL, 10, 9, 4, NULL, 1, 315),
(1238, 'Veloso', 'Joel', 'Sacla', NULL, 1, 1, '1960-06-06', NULL, NULL, NULL, NULL, 9, 16, 2, NULL, 1, 316),
(1239, 'Veloso', 'Herminia', 'Ngo-ay', NULL, 2, 2, '1960-01-15', NULL, NULL, NULL, NULL, NULL, 16, 2, NULL, 1, 316),
(1240, 'Lumiwes', 'Christy', 'Veloso', NULL, 4, 2, '1995-12-25', NULL, NULL, NULL, NULL, NULL, 16, 2, NULL, 1, 316),
(1241, 'Lumiwes', 'Marvin', 'Veloso', NULL, 7, 1, NULL, NULL, NULL, NULL, NULL, NULL, 16, 2, NULL, 1, 316),
(1242, 'Lumiwes', 'Crimson mark', 'Veloso', NULL, 9, 1, NULL, NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 316),
(1243, 'Veloso', 'Jonathan', 'Ngo-ay', NULL, 1, 1, '1990-09-19', NULL, NULL, NULL, NULL, NULL, 16, 2, NULL, 1, 317),
(1244, 'Veloso', 'Gemma', 'Pileyen', NULL, 2, 2, '1982-10-15', NULL, NULL, NULL, NULL, NULL, 9, 2, NULL, 1, 317),
(1245, 'Veloso', 'Queenie', 'Pileyen', NULL, 4, 2, '2016-03-08', NULL, NULL, NULL, NULL, NULL, 18, 1, NULL, 1, 317),
(1246, 'Veloso', 'Princess joy', 'Pileyen', NULL, 4, 2, '2018-07-14', NULL, NULL, NULL, NULL, NULL, 18, 1, NULL, 1, 317),
(1247, 'Veloso', 'Tenis', 'Ngo-ay', NULL, 1, 1, '1984-01-10', NULL, NULL, NULL, NULL, 9, 16, 2, NULL, 1, 318),
(1248, 'Veloso', 'Jocelyn', 'Bahug', NULL, 2, 2, '1978-11-24', NULL, NULL, NULL, NULL, 9, 49, 2, NULL, 1, 318),
(1249, 'Veloso', 'Jhonhiste', 'Ngo-ay', NULL, 3, 1, '2005-01-01', NULL, NULL, NULL, NULL, 9, 43, 1, NULL, 1, 318),
(1250, 'Veloso', 'Nhysselyne', 'Ngo-ay', NULL, 4, 2, '2007-06-15', NULL, NULL, NULL, NULL, 9, 43, 1, NULL, 1, 318),
(1251, 'Veloso', 'Jimmy', 'Ngo-ay', NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 319),
(1252, 'Wakit', 'Ronald', 'Vicente', NULL, 1, 1, '1989-09-17', NULL, NULL, NULL, NULL, 9, 16, 2, NULL, 1, 320),
(1253, 'Wakit', 'Judy vina', 'Linglingan', NULL, 2, 2, '1984-12-14', NULL, NULL, NULL, NULL, 9, 16, 2, NULL, 1, 320),
(1254, 'Wakit', 'Jhun rey', 'Linglingan', NULL, 3, 1, '2010-06-22', NULL, NULL, NULL, NULL, 9, 16, 1, NULL, 1, 320),
(1255, 'Wansi', 'Moises', 'C.', NULL, 1, 1, '1979-09-13', NULL, NULL, NULL, NULL, NULL, 16, 2, NULL, 1, 321),
(1256, 'Wansi', 'Elvie', 'E.', NULL, 2, 2, '1982-12-14', NULL, NULL, NULL, NULL, NULL, 16, 2, NULL, 1, 321),
(1257, 'Wansi', 'Monsour', 'E.', NULL, 3, 1, '2004-09-23', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 321),
(1258, 'Wansi', 'Ian', 'E.', NULL, 3, 1, '2006-03-16', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 321),
(1259, 'Wansi', 'Tyrel melvin', 'E.', NULL, 3, 1, '2009-12-15', NULL, NULL, NULL, NULL, NULL, 16, 1, NULL, 1, 321),
(1260, 'Wansi', 'Winnie', 'Canuto', NULL, 1, 1, '1977-06-13', NULL, NULL, NULL, NULL, 1, 16, 2, 5, 1, 322),
(1261, 'Wansi', 'Annalyn', 'Alberto', NULL, 2, 2, '1988-06-16', NULL, NULL, NULL, NULL, 1, 16, 2, NULL, 1, 322),
(1262, 'Wansi', 'Reynold', 'Tipucia', NULL, 3, 1, '2007-01-09', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 322),
(1263, 'Wansi', 'Allyson', 'Tipucia', NULL, 3, 1, '2009-04-26', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 322),
(1264, 'Wansi', 'Hannah', 'Tipucia', NULL, 4, 2, '2011-06-17', NULL, NULL, NULL, NULL, 1, 16, 1, NULL, 1, 322),
(1265, 'Wansi', 'Leo', 'Canuto', NULL, 1, 1, '1981-05-21', NULL, NULL, NULL, NULL, 1, 20, 2, 5, 1, 323),
(1266, 'Wansi', 'Mary', 'Lucas', NULL, 2, 2, '1983-04-10', NULL, NULL, NULL, NULL, 2, 60, 2, NULL, 1, 323),
(1267, 'Wansi', 'Kynsha', 'Lucas', NULL, 4, 2, '2010-05-17', NULL, NULL, NULL, NULL, NULL, 60, 1, NULL, 1, 323),
(1268, 'Wansi', 'Earlie mae', 'Lucas', NULL, 4, 2, '2013-12-27', NULL, NULL, NULL, NULL, NULL, 60, 1, NULL, 1, 323),
(1269, 'Wansi', 'Louie mar', 'Lucas', NULL, 3, 1, '2018-12-12', NULL, NULL, NULL, NULL, NULL, 60, 1, NULL, 1, 323),
(1270, 'Ricablanca', 'Terry', 'Sebio', NULL, 1, 2, '1968-02-14', NULL, NULL, NULL, NULL, 10, 9, 4, NULL, 1, 324),
(1271, 'Barani', 'Akisha Luna', NULL, NULL, NULL, 2, '2022-12-16', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 171),
(1272, 'Bayanes', 'Callix Zane', NULL, NULL, NULL, 2, '2026-06-03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 172),
(1273, 'Benosa', 'Axel Jace', NULL, NULL, NULL, 1, '2024-08-07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 34),
(1274, 'Benosa', 'Lance Jaiden', NULL, NULL, NULL, 1, '2024-08-07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 34),
(1275, 'Besset', 'Miguel Luis', NULL, NULL, NULL, 1, '2021-11-11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 240),
(1276, 'Bisen', 'Avy Myzyn', NULL, NULL, NULL, 2, '2026-05-21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 144),
(1277, 'Bumehya', 'Gianna Gayle Paoay', NULL, NULL, NULL, 2, '2026-01-20', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 314),
(1278, 'Busoy', 'Jarizia Queen', NULL, NULL, NULL, 2, '2024-12-03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 253),
(1279, 'Catao', 'Brayden', NULL, NULL, NULL, 1, '2021-03-18', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 163),
(1280, 'Ciano', 'Travis', NULL, NULL, NULL, 1, '2021-02-13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 64),
(1281, 'Cot-ong', 'Jairah Joy', NULL, NULL, NULL, 2, '2023-01-16', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 252),
(1282, 'Doclos', 'Reina Leigh Pataras', NULL, NULL, NULL, 2, '2026-01-30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 277),
(1283, 'Emiliano', 'Arvie Diano', NULL, NULL, NULL, 2, '2026-01-12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 183),
(1284, 'Felicitas', 'Jaina Brielle', NULL, NULL, NULL, 2, '2024-03-25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 173),
(1285, 'Jose', 'Alexa Khloe', NULL, NULL, NULL, 2, '2022-12-18', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 117),
(1286, 'Libayo', 'Baldwyn', NULL, NULL, NULL, 1, '2023-11-21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 307),
(1287, 'Linglingan', 'Mica Yesha', NULL, NULL, NULL, 2, '2023-02-27', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 309),
(1288, 'Mariano', 'Reu', NULL, NULL, NULL, 1, '2025-11-24', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 201),
(1289, 'Quilladas', 'Jezzabel J.', NULL, NULL, NULL, 2, '2023-01-26', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 306),
(1290, 'Tamulang', 'Jhoniella', NULL, NULL, NULL, 2, '2022-10-06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 224),
(1291, 'Tumayan', 'Biru', NULL, NULL, NULL, 1, '2022-02-28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 266),
(1292, 'Layog', 'Zhaira Gae', 'Kimmayong', NULL, 10, 2, '2025-08-20', 'Baguio', 'Benguet', 'Philippines', NULL, NULL, NULL, 1, NULL, 1, 128);

--
-- Triggers `resident`
--
DROP TRIGGER IF EXISTS `trg_resident_birthdate_insert`;
DELIMITER $$
CREATE TRIGGER `trg_resident_birthdate_insert` BEFORE INSERT ON `resident` FOR EACH ROW BEGIN
    IF NEW.date_of_birth > CURDATE() THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Date of birth cannot be later than today.';
    END IF;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `trg_resident_birthdate_update`;
DELIMITER $$
CREATE TRIGGER `trg_resident_birthdate_update` BEFORE UPDATE ON `resident` FOR EACH ROW BEGIN
    IF NEW.date_of_birth > CURDATE() THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Date of birth cannot be later than today.';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `resident_status`
--

DROP TABLE IF EXISTS `resident_status`;
CREATE TABLE IF NOT EXISTS `resident_status` (
  `resident_status_id` int NOT NULL AUTO_INCREMENT,
  `resident_status` varchar(45) NOT NULL,
  PRIMARY KEY (`resident_status_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `resident_status`
--

INSERT INTO `resident_status` (`resident_status_id`, `resident_status`) VALUES
(1, 'Active'),
(2, 'Moved Out'),
(3, 'Deceased'),
(4, 'Archive');

-- --------------------------------------------------------

--
-- Table structure for table `resident_type`
--

DROP TABLE IF EXISTS `resident_type`;
CREATE TABLE IF NOT EXISTS `resident_type` (
  `resident_type_id` int NOT NULL AUTO_INCREMENT,
  `resident_type` varchar(45) NOT NULL,
  PRIMARY KEY (`resident_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `resident_type`
--

INSERT INTO `resident_type` (`resident_type_id`, `resident_type`) VALUES
(1, 'Non-Migrant'),
(2, 'Migrant'),
(3, 'Transient');

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

DROP TABLE IF EXISTS `role`;
CREATE TABLE IF NOT EXISTS `role` (
  `role_id` int NOT NULL AUTO_INCREMENT,
  `role_name` varchar(45) NOT NULL,
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`role_id`, `role_name`) VALUES
(1, 'Encoder'),
(2, 'Admin'),
(3, 'Super Admin'),
(4, 'Guest');

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
) ENGINE=InnoDB AUTO_INCREMENT=190 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `role_permission`
--

INSERT INTO `role_permission` (`role_permission_id`, `role_id`, `permission_id`) VALUES
(19, 1, 17),
(44, 1, 19),
(45, 1, 20),
(46, 1, 22),
(47, 1, 23),
(62, 1, 24),
(48, 1, 25),
(49, 1, 26),
(50, 1, 27),
(51, 1, 28),
(52, 1, 30),
(53, 1, 31),
(54, 1, 33),
(55, 1, 34),
(63, 1, 35),
(56, 1, 36),
(57, 1, 37),
(91, 1, 40),
(92, 1, 41),
(117, 1, 42),
(93, 1, 43),
(94, 1, 44),
(95, 1, 45),
(96, 1, 46),
(97, 1, 47),
(98, 1, 48),
(99, 1, 49),
(100, 1, 50),
(101, 1, 51),
(102, 1, 52),
(103, 1, 53),
(104, 1, 54),
(105, 1, 55),
(106, 1, 56),
(107, 1, 57),
(108, 1, 58),
(109, 1, 59),
(110, 1, 60),
(111, 1, 61),
(112, 1, 62),
(113, 1, 63),
(114, 1, 64),
(115, 1, 65),
(116, 1, 66),
(163, 1, 70),
(164, 1, 71),
(165, 1, 72),
(171, 1, 73),
(177, 1, 74),
(179, 1, 75),
(178, 1, 76),
(16, 2, 15),
(17, 2, 16),
(18, 2, 17),
(23, 2, 18),
(24, 2, 19),
(25, 2, 20),
(26, 2, 21),
(27, 2, 22),
(28, 2, 23),
(29, 2, 24),
(30, 2, 25),
(31, 2, 26),
(32, 2, 27),
(33, 2, 28),
(34, 2, 29),
(35, 2, 30),
(36, 2, 31),
(37, 2, 32),
(38, 2, 33),
(39, 2, 34),
(40, 2, 35),
(41, 2, 36),
(42, 2, 37),
(43, 2, 38),
(64, 2, 40),
(65, 2, 41),
(66, 2, 42),
(67, 2, 43),
(68, 2, 44),
(69, 2, 45),
(70, 2, 46),
(71, 2, 47),
(72, 2, 48),
(73, 2, 49),
(74, 2, 50),
(75, 2, 51),
(76, 2, 52),
(77, 2, 53),
(78, 2, 54),
(79, 2, 55),
(80, 2, 56),
(81, 2, 57),
(82, 2, 58),
(83, 2, 59),
(84, 2, 60),
(85, 2, 61),
(86, 2, 62),
(87, 2, 63),
(88, 2, 64),
(89, 2, 65),
(90, 2, 66),
(157, 2, 67),
(158, 2, 68),
(159, 2, 69),
(160, 2, 70),
(161, 2, 71),
(162, 2, 72),
(170, 2, 73),
(174, 2, 74),
(176, 2, 75),
(175, 2, 76),
(188, 2, 77),
(182, 2, 79),
(184, 2, 80),
(186, 2, 81),
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
(58, 3, 15),
(59, 3, 16),
(20, 3, 17),
(22, 3, 18),
(180, 3, 77),
(181, 3, 78),
(183, 3, 79),
(185, 3, 80),
(187, 3, 81),
(189, 3, 82),
(21, 4, 17),
(124, 4, 19),
(125, 4, 20),
(126, 4, 22),
(127, 4, 23),
(128, 4, 25),
(129, 4, 26),
(130, 4, 27),
(131, 4, 28),
(132, 4, 30),
(133, 4, 31),
(134, 4, 33),
(135, 4, 34),
(136, 4, 36),
(137, 4, 37),
(138, 4, 40),
(139, 4, 41),
(140, 4, 43),
(141, 4, 44),
(142, 4, 46),
(143, 4, 47),
(154, 4, 49),
(155, 4, 50),
(144, 4, 52),
(145, 4, 53),
(146, 4, 55),
(147, 4, 56),
(148, 4, 58),
(149, 4, 59),
(150, 4, 61),
(151, 4, 62),
(152, 4, 64),
(153, 4, 65),
(166, 4, 70),
(167, 4, 72);

-- --------------------------------------------------------

--
-- Table structure for table `school_lvl`
--

DROP TABLE IF EXISTS `school_lvl`;
CREATE TABLE IF NOT EXISTS `school_lvl` (
  `school_lvl_id` int NOT NULL AUTO_INCREMENT,
  `school_lvl` varchar(45) NOT NULL,
  PRIMARY KEY (`school_lvl_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `school_lvl`
--

INSERT INTO `school_lvl` (`school_lvl_id`, `school_lvl`) VALUES
(1, 'Pre-school'),
(2, 'Elementary'),
(3, 'Junior High School'),
(4, 'Senior High School'),
(5, 'Vocational/Technical'),
(6, 'College/University'),
(7, 'Not Applicable');

-- --------------------------------------------------------

--
-- Table structure for table `sex`
--

DROP TABLE IF EXISTS `sex`;
CREATE TABLE IF NOT EXISTS `sex` (
  `sex_id` int NOT NULL AUTO_INCREMENT,
  `sex` varchar(10) NOT NULL,
  PRIMARY KEY (`sex_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sex`
--

INSERT INTO `sex` (`sex_id`, `sex`) VALUES
(1, 'Male'),
(2, 'Female'),
(3, 'LGBTQIA+');

-- --------------------------------------------------------

--
-- Table structure for table `skills_development`
--

DROP TABLE IF EXISTS `skills_development`;
CREATE TABLE IF NOT EXISTS `skills_development` (
  `skills_development_id` int NOT NULL AUTO_INCREMENT,
  `skills_development_training` varchar(45) NOT NULL,
  `skill_type_id` int NOT NULL,
  `resident_id` int NOT NULL,
  PRIMARY KEY (`skills_development_id`),
  KEY `resident_skills` (`resident_id`),
  KEY `skill_type` (`skill_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `skill_type`
--

DROP TABLE IF EXISTS `skill_type`;
CREATE TABLE IF NOT EXISTS `skill_type` (
  `skill_type_id` int NOT NULL AUTO_INCREMENT,
  `skill_type` varchar(45) NOT NULL,
  PRIMARY KEY (`skill_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `skill_type`
--

INSERT INTO `skill_type` (`skill_type_id`, `skill_type`) VALUES
(1, 'Refrigeration and Airconditioning'),
(2, 'Automotive/Heavy Equipment Servicing'),
(3, 'Metal Worker'),
(4, 'Building Wiring'),
(5, 'Installation'),
(6, 'Heavy Equipment Operation'),
(7, 'Plumbing'),
(8, 'Welding'),
(9, 'Carpentry'),
(10, 'Baking'),
(11, 'Dressmaking'),
(12, 'Linguist'),
(13, 'Computer Graphics'),
(14, 'Painting'),
(15, 'Beauty Care'),
(16, 'Commercial Cooking'),
(17, 'Housekeeping'),
(18, 'Massage Therapy'),
(19, 'Others');

-- --------------------------------------------------------

--
-- Table structure for table `sociocivic`
--

DROP TABLE IF EXISTS `sociocivic`;
CREATE TABLE IF NOT EXISTS `sociocivic` (
  `sociocivic_id` int NOT NULL AUTO_INCREMENT,
  `solo_parent_status_id` int NOT NULL,
  `ncsc_rrn_id_number` int DEFAULT NULL,
  `osca_id_number` varchar(45) DEFAULT NULL,
  `solo_parent_id_number` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `registered_barangay_voter` varchar(45) DEFAULT NULL,
  `resident_id` int NOT NULL,
  PRIMARY KEY (`sociocivic_id`),
  KEY `resident_sociocivic` (`resident_id`),
  KEY `solo_parent_status` (`solo_parent_status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `solo_parent_status`
--

DROP TABLE IF EXISTS `solo_parent_status`;
CREATE TABLE IF NOT EXISTS `solo_parent_status` (
  `solo_parent_status_id` int NOT NULL AUTO_INCREMENT,
  `solo_parent_status` varchar(45) NOT NULL,
  PRIMARY KEY (`solo_parent_status_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `solo_parent_status`
--

INSERT INTO `solo_parent_status` (`solo_parent_status_id`, `solo_parent_status`) VALUES
(1, 'Registered Solo Parent'),
(2, 'Non-Solo Parent'),
(3, 'Unregistered Solo Parent');

-- --------------------------------------------------------

--
-- Table structure for table `source_of_fp_method`
--

DROP TABLE IF EXISTS `source_of_fp_method`;
CREATE TABLE IF NOT EXISTS `source_of_fp_method` (
  `source_of_fp_method_id` int NOT NULL AUTO_INCREMENT,
  `source_of_fp_method` varchar(45) NOT NULL,
  PRIMARY KEY (`source_of_fp_method_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `source_of_fp_method`
--

INSERT INTO `source_of_fp_method` (`source_of_fp_method_id`, `source_of_fp_method`) VALUES
(1, 'Government hospital'),
(2, 'RHU/Health center'),
(3, 'Brgy. Health Station'),
(4, 'Private hospital'),
(5, 'Pharmacy'),
(6, 'Not Applicable');

-- --------------------------------------------------------

--
-- Table structure for table `source_of_income`
--

DROP TABLE IF EXISTS `source_of_income`;
CREATE TABLE IF NOT EXISTS `source_of_income` (
  `source_of_income_id` int NOT NULL AUTO_INCREMENT,
  `source_of_income` varchar(45) NOT NULL,
  PRIMARY KEY (`source_of_income_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `source_of_income`
--

INSERT INTO `source_of_income` (`source_of_income_id`, `source_of_income`) VALUES
(1, 'Employment'),
(2, 'Business'),
(3, 'Remittance'),
(4, 'Investments'),
(5, 'Others');

-- --------------------------------------------------------

--
-- Table structure for table `specie`
--

DROP TABLE IF EXISTS `specie`;
CREATE TABLE IF NOT EXISTS `specie` (
  `specie_id` int NOT NULL AUTO_INCREMENT,
  `specie` varchar(45) NOT NULL,
  PRIMARY KEY (`specie_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `specie`
--

INSERT INTO `specie` (`specie_id`, `specie`) VALUES
(1, 'Dog'),
(2, 'Cat');

-- --------------------------------------------------------

--
-- Table structure for table `status_of_work_business`
--

DROP TABLE IF EXISTS `status_of_work_business`;
CREATE TABLE IF NOT EXISTS `status_of_work_business` (
  `status_of_work_business_id` int NOT NULL AUTO_INCREMENT,
  `status_of_work_business` varchar(45) NOT NULL,
  PRIMARY KEY (`status_of_work_business_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `status_of_work_business`
--

INSERT INTO `status_of_work_business` (`status_of_work_business_id`, `status_of_work_business`) VALUES
(1, 'Permanent Work'),
(2, 'Casual Work'),
(3, 'Contractual Work'),
(4, 'Individually Owned Business'),
(5, 'Shared/Partnership Business'),
(6, 'Corporate Business'),
(7, 'Not Applicable'),
(8, 'Term-Based');

-- --------------------------------------------------------

--
-- Table structure for table `street`
--

DROP TABLE IF EXISTS `street`;
CREATE TABLE IF NOT EXISTS `street` (
  `street_id` int NOT NULL AUTO_INCREMENT,
  `street_name` varchar(45) NOT NULL,
  PRIMARY KEY (`street_id`),
  UNIQUE KEY `uq_street` (`street_name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `street`
--

INSERT INTO `street` (`street_id`, `street_name`) VALUES
(1, 'Purok 1'),
(2, 'Purok 2'),
(3, 'Purok 3'),
(4, 'Purok 4');

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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `system_setting`
--

INSERT INTO `system_setting` (`setting_id`, `setting_key`, `setting_value`, `data_type`, `description`) VALUES
(1, 'city_name', 'Baguio City', 'string', 'Official City Name'),
(2, 'barangay_name', 'Barangay Happy Hallow', 'string', 'Official Barangay Name'),
(3, 'barangay_code', '1430300006', 'string', 'Philippine Standard Geographic Code'),
(4, 'barangay_address', 'Barangay Happy Hallow, Baguio City, Benguet, ', 'string', 'Complete Address'),
(5, 'barangay_contact_no', '09123456789', 'string', 'Contact Number'),
(6, 'barangay_email', 'bchallowhappy@gmail.com', 'string', 'Official Email Address'),
(7, 'default_password', 'Temp12345', 'string', 'Default Password'),
(8, 'password_min_length', '8', 'int', 'Minimum length of password'),
(9, 'max_login_attempts', '5', 'int', 'Maximum attempts of login'),
(10, 'account_lockout_minutes', '10', 'int', 'Account lockout minutes duration'),
(11, 'session_timeout_minutes', '30', 'int', 'Session timeout minutes when idle'),
(12, 'audit_log_retention_days', '365', 'int', 'Audit log retention days'),
(13, 'punong_barangay_name', 'Soriano A. Palunan', 'string', 'Punong Barangay Name'),
(14, 'punong_barangay_signature', 'signatures/punong_barangay.png', 'string', 'Punong Barangay Signature File Path'),
(15, 'user_log_retention_days', '365', 'int', 'User log retention days');

-- --------------------------------------------------------

--
-- Table structure for table `toilet_facility_type`
--

DROP TABLE IF EXISTS `toilet_facility_type`;
CREATE TABLE IF NOT EXISTS `toilet_facility_type` (
  `toilet_facility_type_id` int NOT NULL AUTO_INCREMENT,
  `toilet_facility_type` varchar(45) NOT NULL,
  PRIMARY KEY (`toilet_facility_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `toilet_facility_type`
--

INSERT INTO `toilet_facility_type` (`toilet_facility_type_id`, `toilet_facility_type`) VALUES
(1, 'Open pit'),
(2, 'Close pit'),
(3, 'Water sealed, other depository, shared'),
(4, 'Water sealed, other depository, exclusive'),
(5, 'Water sealed, sewer septic tank, shared'),
(6, 'Water sealed, sewer septic tank, exclusive'),
(7, 'Others');

-- --------------------------------------------------------

--
-- Table structure for table `type_of_certificate`
--

DROP TABLE IF EXISTS `type_of_certificate`;
CREATE TABLE IF NOT EXISTS `type_of_certificate` (
  `type_of_certificate_id` int NOT NULL AUTO_INCREMENT,
  `type_of_certificate` varchar(45) NOT NULL,
  PRIMARY KEY (`type_of_certificate_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `type_of_certificate`
--

INSERT INTO `type_of_certificate` (`type_of_certificate_id`, `type_of_certificate`) VALUES
(1, 'Certificate of Residency'),
(2, 'Barangay Clearance'),
(3, 'First Time Job Seeker'),
(4, 'Low Income Certification (with amount)'),
(6, 'Affidavit of Solo Parent'),
(7, 'Low Income Certification'),
(8, 'Death Certificate'),
(9, 'Good Moral Certification');

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
  `avatar_path` varchar(255) DEFAULT NULL,
  `avatar_preset` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `uq_username` (`username`),
  UNIQUE KEY `uq_personnel` (`personnel_id`),
  KEY `user_status` (`user_status_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `username`, `password_hash`, `created_at`, `user_status_id`, `personnel_id`, `must_change_password`, `avatar_path`, `avatar_preset`) VALUES
(1, 'ellen', '$2y$12$u3zD2jX/B4be7SrQI6q2C.22iS5c6zvqlBiGccL3e0e9ylN9nuumW', '2026-08-06 22:47:52', 1, 1, 0, NULL, NULL),
(2, 'laila', '$2y$12$GsvVr.zfB06NZ3qvsOTNOOFhuIL7tIhneSyD39ol2NoZMw7gbS71O', '2026-08-06 22:48:05', 1, 2, 0, NULL, NULL),
(3, 'deborah', '$2y$12$yVCqPIuEZH8C2l7LUEFRseVb2OsDPJLp5iu9TGAqcc8zxl4YvHWs6', '2026-08-31 19:29:46', 1, 3, 0, NULL, NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_log`
--

INSERT INTO `user_log` (`user_log_id`, `user_id`, `login_time`, `logout_time`, `login_status_id`, `ip_address`, `device`) VALUES
(1, 1, '2026-08-31 19:24:57', '2026-10-05 14:52:07', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(2, 1, '2026-08-31 19:25:07', '2026-08-31 19:29:50', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(3, 3, '2026-08-31 19:29:59', '2026-08-31 19:30:16', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(4, 3, '2026-08-31 19:30:26', '2026-08-31 19:30:38', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(5, 2, '2026-08-31 19:30:44', '2026-08-31 19:30:44', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(6, 2, '2026-08-31 19:30:47', '2026-10-05 10:44:53', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(11, 2, '2026-10-05 10:44:53', '2026-10-05 10:50:56', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(12, 2, '2026-10-05 11:08:55', '2026-10-05 11:25:29', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(13, 2, '2026-10-05 11:40:42', '2026-10-05 14:53:10', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(14, 1, '2026-10-05 14:52:06', '2026-10-05 14:53:00', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(15, 2, '2026-10-05 14:53:10', '2026-10-05 15:01:37', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(16, 2, '2026-10-05 15:01:48', '2026-10-05 15:17:10', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(17, 2, '2026-10-05 15:18:36', '2026-10-07 16:32:50', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(18, 2, '2026-10-07 16:32:50', '2026-10-07 16:36:14', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(19, 3, '2026-10-07 16:36:21', '2026-10-07 16:36:21', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(20, 2, '2026-10-07 20:45:12', '2026-10-07 20:48:10', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(21, 1, '2026-10-07 20:48:20', '2026-10-07 20:49:04', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(22, 2, '2026-10-07 20:49:14', '2026-10-07 21:19:59', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(23, 2, '2026-10-09 19:37:20', '2026-10-09 20:55:24', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(24, 2, '2026-10-09 20:55:23', '2026-10-09 21:24:37', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(25, 2, '2026-10-09 22:18:01', '2026-10-09 22:25:04', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(26, 1, '2026-10-09 22:25:25', '2026-10-09 22:26:42', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(27, 2, '2026-10-09 22:26:50', '2026-10-09 22:35:02', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(28, 1, '2026-10-09 22:47:09', '2026-10-09 22:58:38', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(29, 2, '2026-10-09 23:07:59', '2026-10-09 23:26:24', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(30, 1, '2026-10-09 23:50:06', '2026-10-09 23:57:07', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(31, 2, '2026-10-15 19:09:26', '2026-10-28 03:13:43', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(32, 2, '2026-10-28 03:13:41', '2026-10-28 03:40:28', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(33, 1, '2026-10-28 03:40:42', '2026-10-28 03:47:35', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(34, 1, '2026-10-28 03:47:44', '2026-10-28 04:33:12', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(35, 1, '2026-10-28 21:15:38', '2026-10-28 21:15:38', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App');

-- --------------------------------------------------------

--
-- Table structure for table `user_log_archive`
--

DROP TABLE IF EXISTS `user_log_archive`;
CREATE TABLE IF NOT EXISTS `user_log_archive` (
  `user_log_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `login_time` datetime NOT NULL,
  `logout_time` datetime DEFAULT NULL,
  `login_status_id` int NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `device` varchar(45) NOT NULL,
  `archived_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `archived_by` int NOT NULL,
  PRIMARY KEY (`user_log_id`),
  KEY `login_time` (`login_time`),
  KEY `user_log_archive_user` (`user_id`),
  KEY `user_log_archive_status` (`login_status_id`),
  KEY `user_log_archive_archived_by` (`archived_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
(1, 1, 2, '2026-08-06 22:49:17', 1, 0),
(2, 2, 1, '2026-08-06 22:49:17', 1, 0),
(3, 1, 3, '2026-08-06 22:57:33', 1, 1),
(4, 1, 1, '2026-08-06 23:13:42', 1, 1),
(6, 2, 2, '2026-08-31 19:27:42', 1, 1),
(7, 3, 1, '2026-08-31 19:29:46', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_status`
--

DROP TABLE IF EXISTS `user_status`;
CREATE TABLE IF NOT EXISTS `user_status` (
  `user_status_id` int NOT NULL AUTO_INCREMENT,
  `user_status` varchar(45) NOT NULL,
  `can_login` tinyint(1) NOT NULL,
  PRIMARY KEY (`user_status_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_status`
--

INSERT INTO `user_status` (`user_status_id`, `user_status`, `can_login`) VALUES
(1, 'Active', 1),
(2, 'Disabled', 0),
(3, 'Locked', 0),
(4, 'Suspended', 0),
(8, 'Inactive', 0);

-- --------------------------------------------------------

--
-- Table structure for table `water_source`
--

DROP TABLE IF EXISTS `water_source`;
CREATE TABLE IF NOT EXISTS `water_source` (
  `water_source_id` int NOT NULL AUTO_INCREMENT,
  `water_source` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`water_source_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `water_source`
--

INSERT INTO `water_source` (`water_source_id`, `water_source`) VALUES
(1, 'Lake, river, rain, others'),
(2, 'Dug well'),
(3, 'Unprotected spring'),
(4, 'Protected spring'),
(5, 'Peddlers'),
(6, 'Tubed/Piped shallow well'),
(7, 'Shared, tubed/pipped deep well'),
(8, 'Own use, tubed/pipped deep well'),
(9, 'Shared, faucet community water system'),
(10, 'Own use, faucet community water system'),
(11, 'Bottled water'),
(12, 'Others');

-- --------------------------------------------------------

--
-- Table structure for table `women_health`
--

DROP TABLE IF EXISTS `women_health`;
CREATE TABLE IF NOT EXISTS `women_health` (
  `women_health_id` int NOT NULL AUTO_INCREMENT,
  `number_pregnancies` int NOT NULL,
  `living_children` int NOT NULL,
  `family_planning_method_id` int NOT NULL,
  `source_of_fp_method_id` int NOT NULL,
  `have_intention_to_use_fp` tinyint(1) NOT NULL,
  `health_id` int NOT NULL,
  PRIMARY KEY (`women_health_id`),
  KEY `source_of_fp_method` (`source_of_fp_method_id`),
  KEY `fp_method` (`family_planning_method_id`) USING BTREE,
  KEY `health` (`health_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_log_archive`
--
ALTER TABLE `audit_log_archive`
  ADD CONSTRAINT `audit_archive_action` FOREIGN KEY (`action_id`) REFERENCES `action` (`action_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `audit_archive_archived_by` FOREIGN KEY (`archived_by`) REFERENCES `user` (`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `audit_archive_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `barangay_personnel`
--
ALTER TABLE `barangay_personnel`
  ADD CONSTRAINT `personnel_position` FOREIGN KEY (`position_id`) REFERENCES `personnel_position` (`position_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `personnel_sex` FOREIGN KEY (`sex_id`) REFERENCES `sex` (`sex_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `personnel_status` FOREIGN KEY (`personnel_status_id`) REFERENCES `personnel_status` (`personnel_status_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `breed`
--
ALTER TABLE `breed`
  ADD CONSTRAINT `breed_specie` FOREIGN KEY (`specie_id`) REFERENCES `specie` (`specie_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `certificate`
--
ALTER TABLE `certificate`
  ADD CONSTRAINT `certificate_type` FOREIGN KEY (`type_of_certificate_id`) REFERENCES `type_of_certificate` (`type_of_certificate_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `issuer` FOREIGN KEY (`issuer_id`) REFERENCES `user` (`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `requester` FOREIGN KEY (`resident_id`) REFERENCES `resident` (`resident_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `certificate_solo_parent`
--
ALTER TABLE `certificate_solo_parent`
  ADD CONSTRAINT `certificate_solo_parent_certificate` FOREIGN KEY (`certificate_id`) REFERENCES `certificate` (`certificate_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `certificate_solo_parent_child`
--
ALTER TABLE `certificate_solo_parent_child`
  ADD CONSTRAINT `certificate_solo_parent_child_certificate` FOREIGN KEY (`certificate_id`) REFERENCES `certificate` (`certificate_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `economic`
--
ALTER TABLE `economic`
  ADD CONSTRAINT `resident_economic` FOREIGN KEY (`resident_id`) REFERENCES `resident` (`resident_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `source_of_income` FOREIGN KEY (`source_of_income_id`) REFERENCES `source_of_income` (`source_of_income_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `status_of_work_business` FOREIGN KEY (`status_of_work_business_id`) REFERENCES `status_of_work_business` (`status_of_work_business_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `education`
--
ALTER TABLE `education`
  ADD CONSTRAINT `current_enrollment_status` FOREIGN KEY (`current_enrollement_status_id`) REFERENCES `current_enrollment_status` (`current_enrollment_status_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `highest_lvl_of_educ` FOREIGN KEY (`highest_lvl_of_educ_id`) REFERENCES `highest_lvl_of_educ` (`highest_lvl_of_educ_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `resident_education` FOREIGN KEY (`resident_id`) REFERENCES `resident` (`resident_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `school_lvl` FOREIGN KEY (`school_lvl_id`) REFERENCES `school_lvl` (`school_lvl_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `female_hhm_died`
--
ALTER TABLE `female_hhm_died`
  ADD CONSTRAINT `household_question2` FOREIGN KEY (`household_question_id`) REFERENCES `household_questions` (`household_questions_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `health`
--
ALTER TABLE `health`
  ADD CONSTRAINT `disability` FOREIGN KEY (`disability_id`) REFERENCES `disability` (`disability_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `facility_visit_reason` FOREIGN KEY (`facility_visit_reason_id`) REFERENCES `facility_visit_reason` (`facility_visit_reason_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `facility_visited` FOREIGN KEY (`facility_visited_past_12mos_id`) REFERENCES `facility_visited_past_12mos` (`facility_visited_past_12mos_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `health_insurance` FOREIGN KEY (`health_insurance_id`) REFERENCES `health_insurance` (`health_insurance_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `resident_health` FOREIGN KEY (`resident_id`) REFERENCES `resident` (`resident_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `household`
--
ALTER TABLE `household`
  ADD CONSTRAINT `house_clan` FOREIGN KEY (`clan_id`) REFERENCES `clan` (`clan_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `house_head` FOREIGN KEY (`head_resident_id`) REFERENCES `resident` (`resident_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `house_status` FOREIGN KEY (`household_status_id`) REFERENCES `household_status` (`household_status_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `house_street` FOREIGN KEY (`street_id`) REFERENCES `street` (`street_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `household_questions`
--
ALTER TABLE `household_questions`
  ADD CONSTRAINT `building_house_type` FOREIGN KEY (`type_of_building_house_id`) REFERENCES `building_house_type` (`building_house_type_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `construction_material` FOREIGN KEY (`construction_material_outer_wall_id`) REFERENCES `construction_material_outer_wall` (`construction_material_outer_wall_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `fuel_type_cooking` FOREIGN KEY (`fuel_type_for_cooking_id`) REFERENCES `fuel_type` (`fuel_type_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `fuel_type_lighting` FOREIGN KEY (`fuel_type_for_lighting_id`) REFERENCES `fuel_type` (`fuel_type_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `household_id` FOREIGN KEY (`household_id`) REFERENCES `household` (`household_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `kitchen_garbage_disposal` FOREIGN KEY (`kitchen_garbage_disposal_id`) REFERENCES `kitchen_garbage_disposal` (`kitchen_garbage_disposal_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `ownership_type_lot` FOREIGN KEY (`ownership_of_lot_id`) REFERENCES `ownership_type` (`ownership_type_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `ownership_type_unit` FOREIGN KEY (`ownership_of_housing_unit_id`) REFERENCES `ownership_type` (`ownership_type_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `toilet_facility_type` FOREIGN KEY (`toilet_facility_type_id`) REFERENCES `toilet_facility_type` (`toilet_facility_type_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `water_source` FOREIGN KEY (`main_source_drinking_water_id`) REFERENCES `water_source` (`water_source_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `infant_health`
--
ALTER TABLE `infant_health`
  ADD CONSTRAINT `birth_attendant` FOREIGN KEY (`birth_attendant_id`) REFERENCES `birth_attendant` (`birth_attendant_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `immunization` FOREIGN KEY (`immunization_id`) REFERENCES `immunization` (`immunization_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `place_of_delivery` FOREIGN KEY (`place_of_delivery_id`) REFERENCES `place_of_delivery` (`place_of_delivery_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `resident_infant_health` FOREIGN KEY (`resident_id`) REFERENCES `resident` (`resident_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `intend_to_stay_5yrs_from_now`
--
ALTER TABLE `intend_to_stay_5yrs_from_now`
  ADD CONSTRAINT `household_question1` FOREIGN KEY (`household_question_id`) REFERENCES `household_questions` (`household_questions_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `migration`
--
ALTER TABLE `migration`
  ADD CONSTRAINT `reason_for_leaving` FOREIGN KEY (`reason_for_leaving_id`) REFERENCES `reason_for_leaving` (`reason_for_leaving_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `reason_for_transfer` FOREIGN KEY (`reason_for_transfer_id`) REFERENCES `reason_for_transfer` (`reason_for_transfer_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `resident_migration` FOREIGN KEY (`resident_id`) REFERENCES `resident` (`resident_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `resident_type` FOREIGN KEY (`resident_type_id`) REFERENCES `resident_type` (`resident_type_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `pet_census`
--
ALTER TABLE `pet_census`
  ADD CONSTRAINT `breed` FOREIGN KEY (`breed_id`) REFERENCES `breed` (`breed_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `house_pet` FOREIGN KEY (`household_id`) REFERENCES `household` (`household_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `pet_current_status` FOREIGN KEY (`pet_status_id`) REFERENCES `pet_status` (`pet_status_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `pet_sex` FOREIGN KEY (`sex_id`) REFERENCES `sex` (`sex_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `specie` FOREIGN KEY (`specie_id`) REFERENCES `specie` (`specie_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `primary_need`
--
ALTER TABLE `primary_need`
  ADD CONSTRAINT `household_question4` FOREIGN KEY (`household_question_id`) REFERENCES `household_questions` (`household_questions_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `primary_need` FOREIGN KEY (`primary_need_id`) REFERENCES `primary_need_of_brgy` (`primary_need_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `resident`
--
ALTER TABLE `resident`
  ADD CONSTRAINT `ethnicity` FOREIGN KEY (`ethnicity_id`) REFERENCES `ethnicity` (`ethnicity_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `household_resident` FOREIGN KEY (`household_id`) REFERENCES `household` (`household_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `marital_status` FOREIGN KEY (`marital_status_id`) REFERENCES `marital_status` (`marital_status_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `nationality` FOREIGN KEY (`nationality_id`) REFERENCES `nationality` (`nationality_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `relationship` FOREIGN KEY (`relationship_to_hh_id`) REFERENCES `relationship_to_hh` (`relationship_to_hh_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `religion` FOREIGN KEY (`religion_id`) REFERENCES `religion` (`religion_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `resident_clan` FOREIGN KEY (`clan_id`) REFERENCES `clan` (`clan_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `resident_status` FOREIGN KEY (`resident_status_id`) REFERENCES `resident_status` (`resident_status_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `sex` FOREIGN KEY (`sex_id`) REFERENCES `sex` (`sex_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `role_permission`
--
ALTER TABLE `role_permission`
  ADD CONSTRAINT `permission_role` FOREIGN KEY (`permission_id`) REFERENCES `permission` (`permission_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `role_permission` FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `skills_development`
--
ALTER TABLE `skills_development`
  ADD CONSTRAINT `resident_skills` FOREIGN KEY (`resident_id`) REFERENCES `resident` (`resident_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `skill_type` FOREIGN KEY (`skill_type_id`) REFERENCES `skill_type` (`skill_type_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `sociocivic`
--
ALTER TABLE `sociocivic`
  ADD CONSTRAINT `resident_sociocivic` FOREIGN KEY (`resident_id`) REFERENCES `resident` (`resident_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `solo_parent_status` FOREIGN KEY (`solo_parent_status_id`) REFERENCES `solo_parent_status` (`solo_parent_status_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

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
-- Constraints for table `user_log_archive`
--
ALTER TABLE `user_log_archive`
  ADD CONSTRAINT `user_log_archive_archived_by` FOREIGN KEY (`archived_by`) REFERENCES `user` (`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `user_log_archive_status` FOREIGN KEY (`login_status_id`) REFERENCES `login_status` (`login_status_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `user_log_archive_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `user_role`
--
ALTER TABLE `user_role`
  ADD CONSTRAINT `role_assignor` FOREIGN KEY (`assigned_by`) REFERENCES `user` (`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `user_assigned` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `user_role` FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `women_health`
--
ALTER TABLE `women_health`
  ADD CONSTRAINT `fp_method` FOREIGN KEY (`family_planning_method_id`) REFERENCES `family_planning_method` (`family_planning_method_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `health` FOREIGN KEY (`health_id`) REFERENCES `health` (`health_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `source_of_fp_method` FOREIGN KEY (`source_of_fp_method_id`) REFERENCES `source_of_fp_method` (`source_of_fp_method_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
