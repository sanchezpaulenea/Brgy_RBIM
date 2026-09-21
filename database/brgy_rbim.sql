-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 21, 2026 at 11:54 PM
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
-- Database: `v3_rbim`
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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
(13, 3, 2, 3, 'User changed password', '[REDACTED]', '[REDACTED]', '2026-08-31 19:30:16', 'password', 'user');

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `barangay_personnel`
--

INSERT INTO `barangay_personnel` (`personnel_id`, `position_id`, `personnel_last_name`, `personnel_first_name`, `personnel_middle_name`, `personnel_suffix`, `personnel_status_id`, `personnel_date_of_birth`) VALUES
(1, 1, 'Salibad', 'Ellen', NULL, NULL, 1, '1986-08-21'),
(2, 2, 'Adawi', 'Laila', NULL, NULL, 1, '2000-08-04'),
(3, 4, 'Rullan', 'Deborah', NULL, NULL, 1, '1987-01-02');

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
  PRIMARY KEY (`breed_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
  `monthly_income` decimal(10,2) NOT NULL,
  `source_of_income_id` int NOT NULL,
  `status_of_work_business_id` int NOT NULL,
  `place_of_work_business` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
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
  `education_id` int NOT NULL,
  `resident_id` int NOT NULL,
  `highest_lvl_of_educ_id` int NOT NULL,
  `current_enrollement_status_id` int NOT NULL,
  `school_lvl_id` int NOT NULL,
  `place_of_school_brgy` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `place_of_school_city_municipality` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
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
  `clan_id` int NOT NULL,
  `head_resident_id` int NOT NULL,
  `street_id` int NOT NULL,
  `number_of_house_story` int NOT NULL,
  `number_of_basement_level` int DEFAULT NULL,
  `house_lot` varchar(45) DEFAULT NULL,
  `registration_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `household_status_id` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`household_id`),
  UNIQUE KEY `uq_address` (`street_id`,`house_lot`),
  KEY `house_street` (`street_id`),
  KEY `house_clan` (`clan_id`),
  KEY `house_status` (`household_status_id`),
  KEY `house_head` (`head_resident_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `household`
--

INSERT INTO `household` (`household_id`, `clan_id`, `head_resident_id`, `street_id`, `number_of_house_story`, `number_of_basement_level`, `house_lot`, `registration_date`, `household_status_id`) VALUES
(1, 1, 1, 1, 1, 0, 'Lot 15', '2026-08-15 18:21:34', 1),
(2, 2, 2, 2, 1, 0, 'Lot 4', '2026-08-15 18:25:41', 1);

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
  KEY `ownership_type_unit` (`ownership_of_housing_unit_id`),
  KEY `ownership_type_lot` (`ownership_of_lot_id`),
  KEY `fuel_type_lighting` (`fuel_type_for_lighting_id`),
  KEY `fuel_type_cooking` (`fuel_type_for_cooking_id`),
  KEY `water_source` (`main_source_drinking_water_id`),
  KEY `kitchen_garbage_disposal` (`kitchen_garbage_disposal_id`),
  KEY `toilet_facility_type` (`toilet_facility_type_id`),
  KEY `building_house_type` (`type_of_building_house_id`),
  KEY `construction_material` (`construction_material_outer_wall_id`),
  KEY `household_id` (`household_id`)
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
  `previous_residence_6mos_brgy` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `previous_residence_6mos_city_municipality` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `previous_residence_5yrs_brgy` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `previous_residence_5yrs_city_municipality` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `date_of_transfer_in_brgy` date DEFAULT NULL,
  `resident_type_id` int NOT NULL,
  `reason_for_leaving_id` int NOT NULL,
  `will_return_to_previous_residence` tinyint(1) NOT NULL,
  `reason_for_transfer_id` int NOT NULL,
  `duration_of_stay` date DEFAULT NULL,
  `resident_id` int NOT NULL,
  PRIMARY KEY (`migration_id`),
  KEY `reason_for_leaving` (`reason_for_leaving_id`),
  KEY `reason_for_transfer` (`reason_for_transfer_id`),
  KEY `resident_type` (`resident_type_id`)
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
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `permission`
--

INSERT INTO `permission` (`permission_id`, `permission`) VALUES
(18, 'auditlog.view'),
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
(39, 'householdquestion.update'),
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
(12, 'pposition.create'),
(14, 'pposition.delete'),
(13, 'pposition.view'),
(37, 'religion.create'),
(38, 'religion.delete'),
(36, 'religion.view'),
(34, 'resident.create'),
(35, 'resident.update'),
(33, 'resident.view'),
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `personnel_position`
--

INSERT INTO `personnel_position` (`position_id`, `position_name`) VALUES
(1, 'Barangay Secretary'),
(4, 'Kagawad On Health And Nutrition'),
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
  `pet_date_of_birth` date NOT NULL,
  `is_spay_neuter` tinyint(1) NOT NULL,
  `rabies_vaccination_date` date DEFAULT NULL,
  PRIMARY KEY (`pet_census_id`),
  KEY `house_pet` (`household_id`),
  KEY `specie` (`specie_id`),
  KEY `breed` (`breed_id`),
  KEY `pet_sex` (`sex_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
  KEY `household_questions` (`household_question_id`)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
(22, 'Domestic Helper');

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
  KEY `household_resident` (`household_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `resident`
--

INSERT INTO `resident` (`resident_id`, `last_name`, `first_name`, `middle_name`, `suffix`, `relationship_to_hh_id`, `sex_id`, `date_of_birth`, `birth_city_municipality`, `birth_province`, `birth_country`, `nationality_id`, `religion_id`, `ethnicity_id`, `marital_status_id`, `clan_id`, `resident_status_id`, `household_id`) VALUES
(1, 'Doe', 'John', NULL, NULL, 1, 1, '1975-08-13', 'Ilagan', 'Isabela', 'Philippines', 1, 1, 2, 2, 1, 1, 1),
(2, 'Dela Cruz', 'Juan', NULL, NULL, 1, 1, '1969-08-08', 'Baguio', 'Benguet', 'Philippines', 1, 1, 2, 2, 2, 1, 2),
(3, 'Dela Cruz', 'Jay', NULL, NULL, 3, 1, '2011-08-04', 'Baguio City', 'Benguet', 'Philippines', 1, 1, 2, 1, 2, 1, 2),
(5, 'Dela Cruz', 'May', NULL, NULL, 4, 2, '2026-01-01', 'Baguio City', 'Benguet', 'Philippines', 1, 1, 2, 1, 2, 1, 2),
(6, 'Dela Cruz', 'Pia', NULL, NULL, 4, 2, '2016-04-13', 'Baguio City', 'Benguet', 'Philippines', 1, 1, 2, 1, 2, 1, 2),
(7, 'Dela Cruz', 'Jim', NULL, NULL, 11, 1, '2016-04-13', 'Baguio City', 'Benguet', 'Philippines', 1, 1, 2, 4, 2, 1, 2);

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
) ENGINE=InnoDB AUTO_INCREMENT=157 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
(61, 1, 39),
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
(60, 2, 39),
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
(153, 4, 65);

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sex`
--

INSERT INTO `sex` (`sex_id`, `sex`) VALUES
(1, 'Male'),
(2, 'Female');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `status_of_work_business`
--

DROP TABLE IF EXISTS `status_of_work_business`;
CREATE TABLE IF NOT EXISTS `status_of_work_business` (
  `status_of_work_business_id` int NOT NULL AUTO_INCREMENT,
  `status_of_work_business` varchar(45) NOT NULL,
  PRIMARY KEY (`status_of_work_business_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
(7, 'Not Applicable');

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `username`, `password_hash`, `created_at`, `user_status_id`, `personnel_id`, `must_change_password`) VALUES
(1, 'ellen', '$2y$12$u3zD2jX/B4be7SrQI6q2C.22iS5c6zvqlBiGccL3e0e9ylN9nuumW', '2026-08-06 22:47:52', 1, 1, 0),
(2, 'laila', '$2y$12$GsvVr.zfB06NZ3qvsOTNOOFhuIL7tIhneSyD39ol2NoZMw7gbS71O', '2026-08-06 22:48:05', 1, 2, 0),
(3, 'deborah', '$2y$12$yVCqPIuEZH8C2l7LUEFRseVb2OsDPJLp5iu9TGAqcc8zxl4YvHWs6', '2026-08-31 19:29:46', 1, 3, 0);

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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_log`
--

INSERT INTO `user_log` (`user_log_id`, `user_id`, `login_time`, `logout_time`, `login_status_id`, `ip_address`, `device`) VALUES
(1, 1, '2026-08-31 19:24:57', '2026-08-31 19:24:57', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(2, 1, '2026-08-31 19:25:07', '2026-08-31 19:29:50', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(3, 3, '2026-08-31 19:29:59', '2026-08-31 19:30:16', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(4, 3, '2026-08-31 19:30:26', '2026-08-31 19:30:38', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(5, 2, '2026-08-31 19:30:44', '2026-08-31 19:30:44', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App'),
(6, 2, '2026-08-31 19:30:47', '2026-08-31 19:30:47', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) App');

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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_role`
--

INSERT INTO `user_role` (`user_role_id`, `user_id`, `role_id`, `assigned_at`, `assigned_by`, `enable`) VALUES
(1, 1, 2, '2026-08-06 22:49:17', 1, 0),
(2, 2, 1, '2026-08-06 22:49:17', 1, 0),
(3, 1, 3, '2026-08-06 22:57:33', 1, 1),
(4, 1, 1, '2026-08-06 23:13:42', 1, 0),
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
  `water_source` int NOT NULL AUTO_INCREMENT,
  `water_source_id` varchar(45) NOT NULL,
  PRIMARY KEY (`water_source`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `water_source`
--

INSERT INTO `water_source` (`water_source`, `water_source_id`) VALUES
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
  KEY `fp_method` (`family_planning_method_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
-- Constraints for table `child_hhm_died`
--
ALTER TABLE `child_hhm_died`
  ADD CONSTRAINT `household_question3` FOREIGN KEY (`household_question_id`) REFERENCES `household_questions` (`household_questions_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `common_disease`
--
ALTER TABLE `common_disease`
  ADD CONSTRAINT `common_diseases` FOREIGN KEY (`common_disease_id`) REFERENCES `common_disease_cause_death_in_brgy` (`common_disease_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `household_question` FOREIGN KEY (`household_question_id`) REFERENCES `household_questions` (`household_questions_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `community_tax_cert`
--
ALTER TABLE `community_tax_cert`
  ADD CONSTRAINT `residentctc` FOREIGN KEY (`resident_id`) REFERENCES `resident` (`resident_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

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
  ADD CONSTRAINT `water_source` FOREIGN KEY (`main_source_drinking_water_id`) REFERENCES `water_source` (`water_source`) ON DELETE RESTRICT ON UPDATE RESTRICT;

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
  ADD CONSTRAINT `resident_type` FOREIGN KEY (`resident_type_id`) REFERENCES `resident_type` (`resident_type_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `pet_census`
--
ALTER TABLE `pet_census`
  ADD CONSTRAINT `breed` FOREIGN KEY (`breed_id`) REFERENCES `breed` (`breed_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `house_pet` FOREIGN KEY (`household_id`) REFERENCES `household` (`household_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `pet_sex` FOREIGN KEY (`sex_id`) REFERENCES `sex` (`sex_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `specie` FOREIGN KEY (`specie_id`) REFERENCES `specie` (`specie_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `primary_need`
--
ALTER TABLE `primary_need`
  ADD CONSTRAINT `household_questions` FOREIGN KEY (`household_question_id`) REFERENCES `household_questions` (`household_questions_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
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
  ADD CONSTRAINT `fpmethod` FOREIGN KEY (`family_planning_method_id`) REFERENCES `family_planning_method` (`family_planning_method_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `sourceoffpmethod` FOREIGN KEY (`source_of_fp_method_id`) REFERENCES `source_of_fp_method` (`source_of_fp_method_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
