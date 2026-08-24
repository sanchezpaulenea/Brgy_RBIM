-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Aug 24, 2026 at 02:31 PM
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
-- Database: `v2_rbim`
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `audit_log`
--

INSERT INTO `audit_log` (`audit_id`, `user_id`, `action_id`, `record_id`, `description`, `old_value`, `new_value`, `performed_at`, `target`, `entity`) VALUES
(1, 1, 1, 1, 'Create user account', NULL, 'ellen', '2026-08-06 23:56:57', 'account', 'user'),
(2, 1, 1, 2, 'Create user account', NULL, 'laila', '2026-08-06 23:56:57', 'account', 'user'),
(3, 1, 1, 1, 'Create barangay personnel record', NULL, 'ellen', '2026-08-06 23:59:23', 'record', 'barangay personnel'),
(4, 1, 1, 2, 'Create barangay personnel record', NULL, 'laila', '2026-08-06 23:59:23', 'record', 'barangay personnel'),
(5, 1, 2, 4, 'Updated user role status', '1', '0', '2026-08-07 00:14:57', 'status', 'user role');

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
-- Table structure for table `census_status`
--

DROP TABLE IF EXISTS `census_status`;
CREATE TABLE IF NOT EXISTS `census_status` (
  `census_status_id` int NOT NULL AUTO_INCREMENT,
  `status_code` varchar(10) NOT NULL,
  `status_name` varchar(45) NOT NULL,
  PRIMARY KEY (`census_status_id`),
  UNIQUE KEY `uq_status_code` (`status_code`),
  UNIQUE KEY `uq_status_name` (`status_name`),
  UNIQUE KEY `uq_census_status` (`status_code`,`status_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `census_status`
--

INSERT INTO `census_status` (`census_status_id`, `status_code`, `status_name`) VALUES
(1, 'C', 'Completed'),
(2, 'CB', 'Callback'),
(3, 'R', 'Refused');

-- --------------------------------------------------------

--
-- Table structure for table `clan`
--

DROP TABLE IF EXISTS `clan`;
CREATE TABLE IF NOT EXISTS `clan` (
  `clan_id` int NOT NULL AUTO_INCREMENT,
  `clan_name` varchar(45) NOT NULL,
  PRIMARY KEY (`clan_id`),
  UNIQUE KEY `uq_clan` (`clan_name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `clan`
--

INSERT INTO `clan` (`clan_id`, `clan_name`) VALUES
(5, 'Canuto'),
(6, 'Liwan'),
(2, 'Otinguey'),
(3, 'Paytocan'),
(4, 'Pitlongay'),
(1, 'Siso');

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `ethnicity`
--

INSERT INTO `ethnicity` (`ethnicity_id`, `ethnicity`) VALUES
(4, 'Ibaloi'),
(2, 'Ilocano'),
(3, 'Kankanaey'),
(1, 'Tagalog');

-- --------------------------------------------------------

--
-- Table structure for table `household`
--

DROP TABLE IF EXISTS `household`;
CREATE TABLE IF NOT EXISTS `household` (
  `household_id` int NOT NULL AUTO_INCREMENT,
  `clan_id` int NOT NULL,
  `head_resident_id` int NOT NULL,
  `street_id` int NOT NULL,
  `house_lot` varchar(45) DEFAULT NULL,
  `block_num` varchar(45) DEFAULT NULL,
  `building_name` varchar(45) DEFAULT NULL,
  `unit_num` varchar(45) DEFAULT NULL,
  `registration_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `household_status_id` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`household_id`),
  KEY `house_street` (`street_id`),
  KEY `house_clan` (`clan_id`),
  KEY `house_status` (`household_status_id`),
  KEY `house_head` (`head_resident_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `household`
--

INSERT INTO `household` (`household_id`, `clan_id`, `head_resident_id`, `street_id`, `house_lot`, `block_num`, `building_name`, `unit_num`, `registration_date`, `household_status_id`) VALUES
(1, 1, 1, 1, 'Lot 15', 'Blk 4', 'Antonio', '3', '2026-08-15 18:21:34', 1),
(2, 2, 2, 2, 'Lot 4', 'Blk 15', NULL, NULL, '2026-08-15 18:25:41', 1),
(3, 3, 1, 3, 'Lot 7', 'Blk 9', NULL, NULL, '2026-08-15 18:25:56', 1),
(4, 4, 1, 4, 'Lot 3', 'Blk 6', NULL, NULL, '2026-08-15 18:26:17', 1);

-- --------------------------------------------------------

--
-- Table structure for table `household_assessment`
--

DROP TABLE IF EXISTS `household_assessment`;
CREATE TABLE IF NOT EXISTS `household_assessment` (
  `assessment_id` int NOT NULL AUTO_INCREMENT,
  `household_id` int NOT NULL,
  `census_status_id` int NOT NULL,
  `visit_start` datetime NOT NULL,
  `visit_end` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `next_visit_date` date DEFAULT NULL,
  `interviewer_id` int NOT NULL,
  `supervisor_id` int NOT NULL,
  `encoder_id` int NOT NULL,
  `previous_assessment_id` int DEFAULT NULL,
  PRIMARY KEY (`assessment_id`),
  KEY `household` (`household_id`),
  KEY `previous_assessment` (`previous_assessment_id`),
  KEY `interviewer` (`interviewer_id`),
  KEY `supervisor` (`supervisor_id`),
  KEY `encoder` (`encoder_id`),
  KEY `assessment_status` (`census_status_id`)
) ;

--
-- Dumping data for table `household_assessment`
--

INSERT INTO `household_assessment` (`assessment_id`, `household_id`, `census_status_id`, `visit_start`, `visit_end`, `next_visit_date`, `interviewer_id`, `supervisor_id`, `encoder_id`, `previous_assessment_id`) VALUES
(1, 1, 1, '2026-08-15 18:22:21', '2026-08-17 18:21:38', NULL, 2, 1, 2, NULL),
(2, 1, 2, '2026-08-15 18:22:21', '2026-08-15 18:30:00', '2026-08-17', 2, 1, 2, NULL),
(3, 1, 1, '2026-08-15 18:24:53', '2026-08-15 18:30:29', NULL, 2, 1, 2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `household_status`
--

DROP TABLE IF EXISTS `household_status`;
CREATE TABLE IF NOT EXISTS `household_status` (
  `household_status_id` int NOT NULL AUTO_INCREMENT,
  `household_status` varchar(45) NOT NULL,
  PRIMARY KEY (`household_status_id`),
  UNIQUE KEY `uq_household_status` (`household_status`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `household_status`
--

INSERT INTO `household_status` (`household_status_id`, `household_status`) VALUES
(1, 'Active'),
(3, 'Archive'),
(2, 'Inactive');

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
(4, 'Account Locked'),
(2, 'Invalid Password'),
(3, 'Invalid Username'),
(1, 'Success');

-- --------------------------------------------------------

--
-- Table structure for table `marital_status`
--

DROP TABLE IF EXISTS `marital_status`;
CREATE TABLE IF NOT EXISTS `marital_status` (
  `marital_status_id` int NOT NULL AUTO_INCREMENT,
  `marital_status` varchar(45) NOT NULL,
  PRIMARY KEY (`marital_status_id`),
  UNIQUE KEY `uq_marital` (`marital_status`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `marital_status`
--

INSERT INTO `marital_status` (`marital_status_id`, `marital_status`) VALUES
(6, 'Divorced'),
(3, 'Living-in'),
(2, 'Married'),
(5, 'Separated'),
(1, 'Single'),
(7, 'Unknown'),
(4, 'Widowed');

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
-- Table structure for table `permission`
--

DROP TABLE IF EXISTS `permission`;
CREATE TABLE IF NOT EXISTS `permission` (
  `permission_id` int NOT NULL AUTO_INCREMENT,
  `permission` varchar(45) NOT NULL,
  PRIMARY KEY (`permission_id`),
  UNIQUE KEY `uq_permission` (`permission_id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
(17, 'user.changepassword'),
(18, 'auditlog.view'),
(19, 'street.view'),
(20, 'street.create'),
(21, 'street.delete'),
(22, 'household.view'),
(23, 'household.create'),
(24, 'household.update'),
(25, 'householdassessment.view'),
(26, 'householdassessment.create'),
(27, 'nationality.view'),
(28, 'nationality.create'),
(29, 'nationality.delete'),
(30, 'ethnicity.view'),
(31, 'ethnicity.create'),
(32, 'ethnicity.delete'),
(33, 'resident.view'),
(34, 'resident.create'),
(35, 'resident.update'),
(36, 'religion.view'),
(37, 'religion.create'),
(38, 'religion.delete');

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `personnel_position`
--

INSERT INTO `personnel_position` (`position_id`, `position_name`) VALUES
(1, 'Barangay Secretary'),
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
-- Table structure for table `relationship_to_hh`
--

DROP TABLE IF EXISTS `relationship_to_hh`;
CREATE TABLE IF NOT EXISTS `relationship_to_hh` (
  `relationship_to_hh_id` int NOT NULL AUTO_INCREMENT,
  `relationship_to_hh` varchar(45) NOT NULL,
  PRIMARY KEY (`relationship_to_hh_id`),
  UNIQUE KEY `uq_relationship` (`relationship_to_hh`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `relationship_to_hh`
--

INSERT INTO `relationship_to_hh` (`relationship_to_hh_id`, `relationship_to_hh`) VALUES
(16, 'Aunt'),
(21, 'Boarder'),
(13, 'Brother'),
(4, 'Daughter'),
(8, 'Daughter-in-law'),
(22, 'Domestic Helper'),
(11, 'Father'),
(10, 'Granddaughter'),
(9, 'Grandson'),
(1, 'Head'),
(12, 'Mother'),
(17, 'Nephew'),
(18, 'Niece'),
(20, 'Non-relative'),
(19, 'Other-relative'),
(14, 'Sister'),
(3, 'Son'),
(7, 'Son-in-law'),
(2, 'Spouse'),
(6, 'Stepdaughter'),
(5, 'Stepson'),
(15, 'Uncle');

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `religion`
--

INSERT INTO `religion` (`religion_id`, `religion`) VALUES
(3, 'Born Again'),
(2, 'Iglesia ni Cristo'),
(1, 'Roman Catholic');

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
  `relationship_to_hh_id` int NOT NULL,
  `sex_id` int NOT NULL,
  `date_of_birth` date NOT NULL,
  `birth_city_municipality` varchar(45) NOT NULL,
  `birth_province` varchar(45) NOT NULL,
  `birth_country` varchar(45) NOT NULL,
  `nationality_id` int NOT NULL,
  `religion_id` int NOT NULL,
  `ethnicity_id` int NOT NULL,
  `marital_status_id` int NOT NULL,
  `resident_type_id` int NOT NULL,
  `clan_id` int NOT NULL,
  `resident_status_id` int NOT NULL DEFAULT '1',
  `household_id` int NOT NULL,
  PRIMARY KEY (`resident_id`),
  KEY `relationship` (`relationship_to_hh_id`),
  KEY `sex` (`sex_id`),
  KEY `marital_status` (`marital_status_id`),
  KEY `resident_status` (`resident_status_id`),
  KEY `resident_clan` (`clan_id`),
  KEY `nationality` (`nationality_id`),
  KEY `ethnicity` (`ethnicity_id`),
  KEY `religion` (`religion_id`),
  KEY `resident_type` (`resident_type_id`),
  KEY `household_resident` (`household_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `resident`
--

INSERT INTO `resident` (`resident_id`, `last_name`, `first_name`, `middle_name`, `suffix`, `relationship_to_hh_id`, `sex_id`, `date_of_birth`, `birth_city_municipality`, `birth_province`, `birth_country`, `nationality_id`, `religion_id`, `ethnicity_id`, `marital_status_id`, `resident_type_id`, `clan_id`, `resident_status_id`, `household_id`) VALUES
(1, 'Doe', 'John', NULL, NULL, 1, 1, '1975-08-13', 'Ilagan', 'Isabela', 'Philippines', 1, 1, 2, 2, 1, 1, 1, 1),
(2, 'Dela Cruz', 'Juan', NULL, NULL, 1, 1, '1969-08-08', 'Baguio', 'Benguet', 'Philippines', 1, 1, 2, 2, 1, 2, 1, 2);

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
  PRIMARY KEY (`resident_status_id`),
  UNIQUE KEY `uq_resident_status` (`resident_status`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `resident_status`
--

INSERT INTO `resident_status` (`resident_status_id`, `resident_status`) VALUES
(1, 'Active'),
(4, 'Archive'),
(3, 'Deceased'),
(2, 'Moved Out');

-- --------------------------------------------------------

--
-- Table structure for table `resident_type`
--

DROP TABLE IF EXISTS `resident_type`;
CREATE TABLE IF NOT EXISTS `resident_type` (
  `resident_type_id` int NOT NULL AUTO_INCREMENT,
  `resident_type` varchar(45) NOT NULL,
  PRIMARY KEY (`resident_type_id`),
  UNIQUE KEY `uq_resident_type` (`resident_type`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `resident_type`
--

INSERT INTO `resident_type` (`resident_type_id`, `resident_type`) VALUES
(2, 'Migrant'),
(1, 'Permanent Resident'),
(3, 'Transient');

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
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `role_permission`
--

INSERT INTO `role_permission` (`role_permission_id`, `role_id`, `permission_id`) VALUES
(19, 1, 17),
(44, 1, 19),
(45, 1, 20),
(46, 1, 22),
(47, 1, 23),
(48, 1, 25),
(49, 1, 26),
(50, 1, 27),
(51, 1, 28),
(52, 1, 30),
(53, 1, 31),
(54, 1, 33),
(55, 1, 34),
(56, 1, 36),
(57, 1, 37),
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
(22, 3, 18),
(21, 4, 17);

-- --------------------------------------------------------

--
-- Table structure for table `sex`
--

DROP TABLE IF EXISTS `sex`;
CREATE TABLE IF NOT EXISTS `sex` (
  `sex_id` int NOT NULL AUTO_INCREMENT,
  `sex` varchar(10) NOT NULL,
  PRIMARY KEY (`sex_id`),
  UNIQUE KEY `uq_sex` (`sex`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sex`
--

INSERT INTO `sex` (`sex_id`, `sex`) VALUES
(2, 'Female'),
(1, 'Male');

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `username`, `password_hash`, `created_at`, `user_status_id`, `personnel_id`, `must_change_password`) VALUES
(1, 'ellen', '$2y$12$u3zD2jX/B4be7SrQI6q2C.22iS5c6zvqlBiGccL3e0e9ylN9nuumW', '2026-08-06 22:47:52', 1, 1, 0),
(2, 'laila', '$2y$12$GsvVr.zfB06NZ3qvsOTNOOFhuIL7tIhneSyD39ol2NoZMw7gbS71O', '2026-08-06 22:48:05', 1, 2, 0);

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_role`
--

INSERT INTO `user_role` (`user_role_id`, `user_id`, `role_id`, `assigned_at`, `assigned_by`, `enable`) VALUES
(1, 1, 2, '2026-08-06 22:49:17', 1, 1),
(2, 2, 1, '2026-08-06 22:49:17', 1, 1),
(3, 1, 3, '2026-08-06 22:57:33', 1, 1),
(4, 1, 1, '2026-08-06 23:13:42', 1, 0);

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
-- Constraints for table `household`
--
ALTER TABLE `household`
  ADD CONSTRAINT `house_clan` FOREIGN KEY (`clan_id`) REFERENCES `clan` (`clan_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `house_head` FOREIGN KEY (`head_resident_id`) REFERENCES `resident` (`resident_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `house_status` FOREIGN KEY (`household_status_id`) REFERENCES `household_status` (`household_status_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `house_street` FOREIGN KEY (`street_id`) REFERENCES `street` (`street_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `household_assessment`
--
ALTER TABLE `household_assessment`
  ADD CONSTRAINT `assessment_status` FOREIGN KEY (`census_status_id`) REFERENCES `census_status` (`census_status_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `encoder` FOREIGN KEY (`encoder_id`) REFERENCES `barangay_personnel` (`personnel_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `household` FOREIGN KEY (`household_id`) REFERENCES `household` (`household_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `interviewer` FOREIGN KEY (`interviewer_id`) REFERENCES `barangay_personnel` (`personnel_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `previous_assessment` FOREIGN KEY (`previous_assessment_id`) REFERENCES `household_assessment` (`assessment_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `supervisor` FOREIGN KEY (`supervisor_id`) REFERENCES `barangay_personnel` (`personnel_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

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
  ADD CONSTRAINT `resident_type` FOREIGN KEY (`resident_type_id`) REFERENCES `resident_type` (`resident_type_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `sex` FOREIGN KEY (`sex_id`) REFERENCES `sex` (`sex_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

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
