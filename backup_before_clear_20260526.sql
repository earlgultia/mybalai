-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: mybalai_db
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
-- Temporary table structure for view `active_users_with_roles`
--

DROP TABLE IF EXISTS `active_users_with_roles`;
/*!50001 DROP VIEW IF EXISTS `active_users_with_roles`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `active_users_with_roles` AS SELECT
 1 AS `user_id`,
  1 AS `username`,
  1 AS `email`,
  1 AS `first_name`,
  1 AS `last_name`,
  1 AS `phone_number`,
  1 AS `is_active`,
  1 AS `last_login`,
  1 AS `primary_role`,
  1 AS `all_roles` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `entity_type` varchar(100) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (1,5,'User logged in','auth',5,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-05-25 23:12:49'),(2,5,'User logged out','auth',5,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-05-25 23:13:18'),(3,2,'User logged in','auth',2,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-05-25 23:13:48'),(4,2,'User logged out','auth',2,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-05-25 23:14:49'),(5,2,'User logged in','auth',2,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-05-25 23:15:49'),(6,2,'User logged out','auth',2,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-05-25 23:16:00'),(7,2,'User logged in','auth',2,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-05-25 23:16:07'),(8,2,'User logged out','auth',2,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-05-25 23:16:14'),(9,1,'User logged in','auth',1,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-05-25 23:18:33'),(10,1,'User logged out','auth',1,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-05-25 23:18:50'),(11,1,'User logged in','auth',1,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-05-25 23:25:00'),(12,1,'User logged out','auth',1,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-05-25 23:25:11'),(13,2,'User logged in','auth',2,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-05-25 23:25:23'),(14,2,'User logged out','auth',2,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-05-25 23:28:10');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `announcements` (
  `announcement_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `views_count` int(11) DEFAULT 0,
  PRIMARY KEY (`announcement_id`),
  KEY `created_by` (`created_by`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_published_date` (`published_date`),
  CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcements`
--

LOCK TABLES `announcements` WRITE;
/*!40000 ALTER TABLE `announcements` DISABLE KEYS */;
INSERT INTO `announcements` VALUES (1,'Barangay Assembly','Please join our monthly barangay assembly on March 30, 2026 at 2 PM at the barangay hall.','event','medium','all',2,'2026-05-25 14:48:13',NULL,1,NULL,0);
/*!40000 ALTER TABLE `announcements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `appointments`
--

DROP TABLE IF EXISTS `appointments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`appointment_id`),
  KEY `user_id` (`user_id`),
  KEY `confirmed_by` (`confirmed_by`),
  KEY `idx_date` (`preferred_date`),
  KEY `idx_status` (`status`),
  KEY `idx_appointments_date_status` (`preferred_date`,`status`),
  CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`confirmed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appointments`
--

LOCK TABLES `appointments` WRITE;
/*!40000 ALTER TABLE `appointments` DISABLE KEYS */;
/*!40000 ALTER TABLE `appointments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `barangay_officials`
--

DROP TABLE IF EXISTS `barangay_officials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `barangay_officials` (
  `official_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `position` varchar(100) NOT NULL,
  `term_start` date NOT NULL,
  `term_end` date DEFAULT NULL,
  `is_current` tinyint(1) DEFAULT 1,
  `responsibilities` text DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`official_id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `idx_position` (`position`),
  KEY `idx_is_current` (`is_current`),
  CONSTRAINT `barangay_officials_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `barangay_officials`
--

LOCK TABLES `barangay_officials` WRITE;
/*!40000 ALTER TABLE `barangay_officials` DISABLE KEYS */;
/*!40000 ALTER TABLE `barangay_officials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `complaints`
--

DROP TABLE IF EXISTS `complaints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `complaints` (
  `complaint_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `resolved_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`complaint_id`),
  UNIQUE KEY `blotter_entry_number` (`blotter_entry_number`),
  KEY `assigned_staff_id` (`assigned_staff_id`),
  KEY `idx_status` (`status`),
  KEY `idx_complaint_type` (`complaint_type`),
  KEY `idx_complaints_complainant` (`complainant_id`),
  CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`complainant_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `complaints_ibfk_2` FOREIGN KEY (`assigned_staff_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `complaints`
--

LOCK TABLES `complaints` WRITE;
/*!40000 ALTER TABLE `complaints` DISABLE KEYS */;
INSERT INTO `complaints` VALUES (1,5,NULL,NULL,'noise',NULL,NULL,NULL,'Excessive noise from neighbor after 10 PM',NULL,'submitted','BLOT-2026-001',NULL,NULL,NULL,NULL,'2026-05-25 14:48:13',NULL);
/*!40000 ALTER TABLE `complaints` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `document_requests`
--

DROP TABLE IF EXISTS `document_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `document_requests` (
  `request_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `payment_status` enum('unpaid','paid','waived') DEFAULT 'unpaid',
  PRIMARY KEY (`request_id`),
  UNIQUE KEY `reference_number` (`reference_number`),
  KEY `processed_by` (`processed_by`),
  KEY `approved_by` (`approved_by`),
  KEY `idx_status` (`status`),
  KEY `idx_document_type` (`document_type`),
  KEY `idx_reference` (`reference_number`),
  KEY `idx_document_requests_user_status` (`user_id`,`status`),
  CONSTRAINT `document_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `document_requests_ibfk_2` FOREIGN KEY (`processed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `document_requests_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_requests`
--

LOCK TABLES `document_requests` WRITE;
/*!40000 ALTER TABLE `document_requests` DISABLE KEYS */;
INSERT INTO `document_requests` VALUES (1,5,'barangay_clearance','Employment requirement',NULL,'pending','BRGY-2026-001',NULL,'2026-05-25 14:48:13',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,'unpaid');
/*!40000 ALTER TABLE `document_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `household_members`
--

DROP TABLE IF EXISTS `household_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `household_members` (
  `member_id` int(11) NOT NULL AUTO_INCREMENT,
  `resident_profile_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `relationship` enum('spouse','child','parent','sibling','grandparent','other') NOT NULL,
  `birth_date` date DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `is_dependent` tinyint(1) DEFAULT 1,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`member_id`),
  KEY `resident_profile_id` (`resident_profile_id`),
  KEY `idx_relationship` (`relationship`),
  CONSTRAINT `household_members_ibfk_1` FOREIGN KEY (`resident_profile_id`) REFERENCES `resident_profiles` (`profile_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `household_members`
--

LOCK TABLES `household_members` WRITE;
/*!40000 ALTER TABLE `household_members` DISABLE KEYS */;
/*!40000 ALTER TABLE `household_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `permission_id` int(11) NOT NULL AUTO_INCREMENT,
  `permission_name` varchar(100) NOT NULL,
  `permission_key` varchar(100) NOT NULL,
  `module` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`permission_id`),
  UNIQUE KEY `permission_name` (`permission_name`),
  UNIQUE KEY `permission_key` (`permission_key`),
  KEY `idx_module` (`module`),
  KEY `idx_permission_key` (`permission_key`),
  KEY `idx_permissions_module` (`module`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'View Dashboard','view_dashboard','dashboard','Access to main dashboard','2026-05-25 14:48:13'),(2,'View Reports','view_reports','reports','Access to reports and analytics','2026-05-25 14:48:13'),(3,'View Users','view_users','users','View user list and details','2026-05-25 14:48:13'),(4,'Create Users','create_users','users','Add new users to the system','2026-05-25 14:48:13'),(5,'Edit Users','edit_users','users','Modify user information','2026-05-25 14:48:13'),(6,'Delete Users','delete_users','users','Remove users from the system','2026-05-25 14:48:13'),(7,'Assign Roles','assign_roles','users','Assign or change user roles','2026-05-25 14:48:13'),(8,'Manage User Permissions','manage_permissions','users','Configure role permissions','2026-05-25 14:48:13'),(9,'View Residents','view_residents','residents','View resident profiles','2026-05-25 14:48:13'),(10,'Add Residents','add_residents','residents','Add new resident profiles','2026-05-25 14:48:13'),(11,'Edit Residents','edit_residents','residents','Modify resident information','2026-05-25 14:48:13'),(12,'Delete Residents','delete_residents','residents','Remove resident profiles','2026-05-25 14:48:13'),(13,'View Documents','view_documents','documents','View document requests','2026-05-25 14:48:13'),(14,'Process Documents','process_documents','documents','Process and approve document requests','2026-05-25 14:48:13'),(15,'Release Documents','release_documents','documents','Release ready documents','2026-05-25 14:48:13'),(16,'Generate QR Codes','generate_qr','documents','Generate QR codes for documents','2026-05-25 14:48:13'),(17,'View Complaints','view_complaints','complaints','View complaint/blotter records','2026-05-25 14:48:13'),(18,'Create Complaints','create_complaints','complaints','File new complaints','2026-05-25 14:48:13'),(19,'Assign Complaints','assign_complaints','complaints','Assign complaints to staff','2026-05-25 14:48:13'),(20,'Resolve Complaints','resolve_complaints','complaints','Mark complaints as resolved','2026-05-25 14:48:13'),(21,'View Appointments','view_appointments','appointments','View all appointments','2026-05-25 14:48:13'),(22,'Manage Appointments','manage_appointments','appointments','Create and modify appointments','2026-05-25 14:48:13'),(23,'Confirm Appointments','confirm_appointments','appointments','Confirm or cancel appointments','2026-05-25 14:48:13'),(24,'View Finances','view_finances','finance','View financial records','2026-05-25 14:48:13'),(25,'Process Payments','process_payments','finance','Process and record payments','2026-05-25 14:48:13'),(26,'Generate Receipts','generate_receipts','finance','Generate official receipts','2026-05-25 14:48:13'),(27,'View Transactions','view_transactions','finance','View transaction history','2026-05-25 14:48:13'),(28,'View Announcements','view_announcements','announcements','View announcements','2026-05-25 14:48:13'),(29,'Create Announcements','create_announcements','announcements','Post new announcements','2026-05-25 14:48:13'),(30,'Edit Announcements','edit_announcements','announcements','Modify announcements','2026-05-25 14:48:13'),(31,'View Settings','view_settings','system','View system settings','2026-05-25 14:48:13'),(32,'Edit Settings','edit_settings','system','Modify system settings','2026-05-25 14:48:13');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qr_logs`
--

DROP TABLE IF EXISTS `qr_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qr_logs` (
  `qr_log_id` int(11) NOT NULL AUTO_INCREMENT,
  `qr_code` varchar(255) NOT NULL,
  `entity_type` enum('resident','document','appointment') NOT NULL,
  `entity_id` int(11) NOT NULL,
  `scanned_by` int(11) DEFAULT NULL,
  `scan_location` varchar(255) DEFAULT NULL,
  `scan_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`qr_log_id`),
  KEY `scanned_by` (`scanned_by`),
  KEY `idx_qr_code` (`qr_code`),
  KEY `idx_scan_timestamp` (`scan_timestamp`),
  CONSTRAINT `qr_logs_ibfk_1` FOREIGN KEY (`scanned_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qr_logs`
--

LOCK TABLES `qr_logs` WRITE;
/*!40000 ALTER TABLE `qr_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `qr_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resident_profiles`
--

DROP TABLE IF EXISTS `resident_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resident_profiles` (
  `profile_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `emergency_contact_relationship` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`profile_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_house_number` (`house_number`),
  KEY `idx_status_flags` (`senior_citizen`,`pwd_status`),
  CONSTRAINT `resident_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resident_profiles`
--

LOCK TABLES `resident_profiles` WRITE;
/*!40000 ALTER TABLE `resident_profiles` DISABLE KEYS */;
INSERT INTO `resident_profiles` VALUES (1,5,'123','Main Street','LATROBE',NULL,NULL,NULL,'1990-01-15',NULL,'male','married',NULL,NULL,0,0,0,0,NULL,NULL,NULL,NULL,NULL),(2,6,'456','Rizal Street','LATROBE',NULL,NULL,NULL,'1955-06-10',NULL,'female','widowed',NULL,NULL,0,0,1,0,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `resident_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_permissions` (
  `role_permission_id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `granted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `granted_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`role_permission_id`),
  UNIQUE KEY `unique_role_permission` (`role_id`,`permission_id`),
  KEY `idx_role` (`role_id`),
  KEY `idx_permission` (`permission_id`),
  KEY `idx_role_permissions_role` (`role_id`),
  CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE,
  CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`permission_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=121 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
INSERT INTO `role_permissions` VALUES (1,1,28,'2026-05-25 14:48:13',NULL),(2,1,29,'2026-05-25 14:48:13',NULL),(3,1,30,'2026-05-25 14:48:13',NULL),(4,1,21,'2026-05-25 14:48:13',NULL),(5,1,22,'2026-05-25 14:48:13',NULL),(6,1,23,'2026-05-25 14:48:13',NULL),(7,1,17,'2026-05-25 14:48:13',NULL),(8,1,18,'2026-05-25 14:48:13',NULL),(9,1,19,'2026-05-25 14:48:13',NULL),(10,1,20,'2026-05-25 14:48:13',NULL),(11,1,1,'2026-05-25 14:48:13',NULL),(12,1,13,'2026-05-25 14:48:13',NULL),(13,1,14,'2026-05-25 14:48:13',NULL),(14,1,15,'2026-05-25 14:48:13',NULL),(15,1,16,'2026-05-25 14:48:13',NULL),(16,1,24,'2026-05-25 14:48:13',NULL),(17,1,25,'2026-05-25 14:48:13',NULL),(18,1,26,'2026-05-25 14:48:13',NULL),(19,1,27,'2026-05-25 14:48:13',NULL),(20,1,2,'2026-05-25 14:48:13',NULL),(21,1,9,'2026-05-25 14:48:13',NULL),(22,1,10,'2026-05-25 14:48:13',NULL),(23,1,11,'2026-05-25 14:48:13',NULL),(24,1,12,'2026-05-25 14:48:13',NULL),(25,1,31,'2026-05-25 14:48:13',NULL),(26,1,32,'2026-05-25 14:48:13',NULL),(27,1,3,'2026-05-25 14:48:13',NULL),(28,1,4,'2026-05-25 14:48:13',NULL),(29,1,5,'2026-05-25 14:48:13',NULL),(30,1,6,'2026-05-25 14:48:13',NULL),(31,1,7,'2026-05-25 14:48:13',NULL),(32,1,8,'2026-05-25 14:48:13',NULL),(64,2,10,'2026-05-25 14:48:13',NULL),(65,2,19,'2026-05-25 14:48:13',NULL),(66,2,23,'2026-05-25 14:48:13',NULL),(67,2,29,'2026-05-25 14:48:13',NULL),(68,2,30,'2026-05-25 14:48:13',NULL),(69,2,11,'2026-05-25 14:48:13',NULL),(70,2,22,'2026-05-25 14:48:13',NULL),(71,2,14,'2026-05-25 14:48:13',NULL),(72,2,15,'2026-05-25 14:48:13',NULL),(73,2,20,'2026-05-25 14:48:13',NULL),(74,2,28,'2026-05-25 14:48:13',NULL),(75,2,21,'2026-05-25 14:48:13',NULL),(76,2,17,'2026-05-25 14:48:13',NULL),(77,2,1,'2026-05-25 14:48:13',NULL),(78,2,13,'2026-05-25 14:48:13',NULL),(79,2,24,'2026-05-25 14:48:13',NULL),(80,2,2,'2026-05-25 14:48:13',NULL),(81,2,9,'2026-05-25 14:48:13',NULL),(82,2,27,'2026-05-25 14:48:13',NULL),(83,2,3,'2026-05-25 14:48:13',NULL),(95,3,10,'2026-05-25 14:48:13',NULL),(96,3,29,'2026-05-25 14:48:13',NULL),(97,3,18,'2026-05-25 14:48:13',NULL),(98,3,11,'2026-05-25 14:48:13',NULL),(99,3,16,'2026-05-25 14:48:13',NULL),(100,3,22,'2026-05-25 14:48:13',NULL),(101,3,14,'2026-05-25 14:48:13',NULL),(102,3,15,'2026-05-25 14:48:13',NULL),(103,3,28,'2026-05-25 14:48:13',NULL),(104,3,21,'2026-05-25 14:48:13',NULL),(105,3,17,'2026-05-25 14:48:13',NULL),(106,3,1,'2026-05-25 14:48:13',NULL),(107,3,13,'2026-05-25 14:48:13',NULL),(108,3,9,'2026-05-25 14:48:13',NULL),(110,4,26,'2026-05-25 14:48:13',NULL),(111,4,25,'2026-05-25 14:48:13',NULL),(112,4,1,'2026-05-25 14:48:13',NULL),(113,4,24,'2026-05-25 14:48:13',NULL),(114,4,2,'2026-05-25 14:48:13',NULL),(115,4,9,'2026-05-25 14:48:13',NULL),(116,4,27,'2026-05-25 14:48:13',NULL),(117,9,18,'2026-05-25 14:48:13',NULL),(118,9,28,'2026-05-25 14:48:13',NULL),(119,9,21,'2026-05-25 14:48:13',NULL),(120,9,1,'2026-05-25 14:48:13',NULL);
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL,
  `role_description` text DEFAULT NULL,
  `role_level` int(11) DEFAULT 1,
  `is_system_role` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `role_name` (`role_name`),
  KEY `idx_role_level` (`role_level`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'super_admin','Full system access with all permissions',100,1,'2026-05-25 14:48:13','2026-05-25 14:48:13'),(2,'barangay_captain','Head of barangay with approval authority',90,1,'2026-05-25 14:48:13','2026-05-25 14:48:13'),(3,'barangay_secretary','Manages documents and appointments',80,1,'2026-05-25 14:48:13','2026-05-25 14:48:13'),(4,'barangay_treasurer','Manages financial transactions and collections',80,1,'2026-05-25 14:48:13','2026-05-25 14:48:13'),(5,'barangay_kagawad','Barangay council member with limited admin access',70,1,'2026-05-25 14:48:13','2026-05-25 14:48:13'),(6,'health_worker','Manages health-related services and appointments',70,1,'2026-05-25 14:48:13','2026-05-25 14:48:13'),(7,'tanod','Security and peacekeeping role',60,1,'2026-05-25 14:48:13','2026-05-25 14:48:13'),(8,'admin_staff','General administrative staff',65,1,'2026-05-25 14:48:13','2026-05-25 14:48:13'),(9,'resident','Regular barangay resident',10,1,'2026-05-25 14:48:13','2026-05-25 14:48:13'),(10,'senior_citizen','Senior resident with special privileges',15,0,'2026-05-25 14:48:13','2026-05-25 14:48:13'),(11,'pwd','Person with disability',15,0,'2026-05-25 14:48:13','2026-05-25 14:48:13'),(12,'business_owner','Business permit holder in the barangay',12,0,'2026-05-25 14:48:13','2026-05-25 14:48:13');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscriptions`
--

DROP TABLE IF EXISTS `subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscriptions` (
  `subscription_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `subscription_type` enum('monthly','quarterly','annual') DEFAULT 'monthly',
  `amount` decimal(12,2) NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('pending','paid','overdue','cancelled') DEFAULT 'pending',
  `payment_method` enum('cash','gcash','bank_transfer') DEFAULT NULL,
  `payment_reference` varchar(100) DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `invoice_number` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`subscription_id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `idx_due_date` (`due_date`),
  KEY `idx_status` (`status`),
  KEY `idx_subscriptions_user_status` (`user_id`,`status`),
  CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscriptions`
--

LOCK TABLES `subscriptions` WRITE;
/*!40000 ALTER TABLE `subscriptions` DISABLE KEYS */;
INSERT INTO `subscriptions` VALUES (1,5,'monthly',10.00,'2026-03-31','pending',NULL,NULL,NULL,'INV-2026-001','2026-05-25 14:48:13');
/*!40000 ALTER TABLE `subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `transaction_type` enum('subscription','document_fee','clearance_fee','other') NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_method` enum('cash','gcash','bank_transfer') NOT NULL,
  `payment_reference` varchar(100) DEFAULT NULL,
  `or_number` varchar(50) DEFAULT NULL,
  `status` enum('pending','completed','failed','refunded') DEFAULT 'completed',
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `collected_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`transaction_id`),
  UNIQUE KEY `or_number` (`or_number`),
  KEY `user_id` (`user_id`),
  KEY `collected_by` (`collected_by`),
  KEY `idx_transaction_date` (`transaction_date`),
  KEY `idx_status` (`status`),
  KEY `idx_payment_method` (`payment_method`),
  KEY `idx_transactions_date_status` (`transaction_date`,`status`),
  CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`collected_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES (1,5,'subscription',NULL,1290.00,'cash','REF123','OR-2026-001','completed','2026-05-25 14:48:13',4,NULL);
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `user_permissions_view`
--

DROP TABLE IF EXISTS `user_permissions_view`;
/*!50001 DROP VIEW IF EXISTS `user_permissions_view`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `user_permissions_view` AS SELECT
 1 AS `user_id`,
  1 AS `username`,
  1 AS `email`,
  1 AS `role_name`,
  1 AS `permission_key`,
  1 AS `permission_name`,
  1 AS `module` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `user_role_assignments`
--

DROP TABLE IF EXISTS `user_role_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_role_assignments` (
  `assignment_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `expires_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`assignment_id`),
  UNIQUE KEY `unique_user_role_active` (`user_id`,`role_id`,`is_active`),
  KEY `assigned_by` (`assigned_by`),
  KEY `idx_user` (`user_id`),
  KEY `idx_role` (`role_id`),
  KEY `idx_active` (`is_active`),
  KEY `idx_user_role_assignments_active` (`user_id`,`is_active`),
  CONSTRAINT `user_role_assignments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `user_role_assignments_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE,
  CONSTRAINT `user_role_assignments_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_role_assignments`
--

LOCK TABLES `user_role_assignments` WRITE;
/*!40000 ALTER TABLE `user_role_assignments` DISABLE KEYS */;
INSERT INTO `user_role_assignments` VALUES (1,1,1,1,'2026-05-25 14:48:13',1,NULL),(2,2,2,1,'2026-05-25 14:48:13',1,NULL),(3,3,3,1,'2026-05-25 14:48:13',1,NULL),(4,4,4,1,'2026-05-25 14:48:13',1,NULL),(5,5,9,1,'2026-05-25 14:48:13',1,NULL),(6,6,9,1,'2026-05-25 14:48:13',1,NULL),(7,6,10,1,'2026-05-25 14:48:13',1,NULL);
/*!40000 ALTER TABLE `user_role_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`),
  KEY `idx_email` (`email`),
  KEY `idx_username` (`username`),
  KEY `idx_is_active` (`is_active`),
  KEY `primary_role_id` (`primary_role_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`primary_role_id`) REFERENCES `roles` (`role_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,1,'superadmin','superadmin@mybalai.com','$2y$10$rsUcxCkZu/EFEWKxtksnLuY/Jx.3JfLVL4tLHU8SfoCjBweWIBc2O','System','Administrator',NULL,NULL,NULL,NULL,NULL,1,0,1,1,0,0,NULL,NULL,NULL,NULL,'2026-05-25 23:25:00',NULL,0,'2026-05-25 14:48:13','2026-05-25 23:25:00',NULL,NULL),(2,2,'captain.juan','captain@mybalai.com','$2y$10$rsUcxCkZu/EFEWKxtksnLuY/Jx.3JfLVL4tLHU8SfoCjBweWIBc2O','Juan','Dela Cruz',NULL,NULL,'09123456789',NULL,NULL,1,0,1,1,0,0,NULL,NULL,NULL,NULL,'2026-05-25 23:25:23',NULL,0,'2026-05-25 14:48:13','2026-05-25 23:25:23',NULL,NULL),(3,3,'secretary.maria','secretary@mybalai.com','$2y$10$rsUcxCkZu/EFEWKxtksnLuY/Jx.3JfLVL4tLHU8SfoCjBweWIBc2O','Maria','Santos',NULL,NULL,'09123456780',NULL,NULL,1,0,1,1,0,0,NULL,NULL,NULL,NULL,NULL,NULL,0,'2026-05-25 14:48:13','2026-05-25 23:10:45',NULL,NULL),(4,4,'treasurer.ana','treasurer@mybalai.com','$2y$10$rsUcxCkZu/EFEWKxtksnLuY/Jx.3JfLVL4tLHU8SfoCjBweWIBc2O','Ana','Reyes',NULL,NULL,'09123456781',NULL,NULL,1,0,1,1,0,0,NULL,NULL,NULL,NULL,NULL,NULL,0,'2026-05-25 14:48:13','2026-05-25 23:10:45',NULL,NULL),(5,9,'pedro.reyes','pedro.reyes@email.com','$2y$10$rsUcxCkZu/EFEWKxtksnLuY/Jx.3JfLVL4tLHU8SfoCjBweWIBc2O','Pedro','Reyes',NULL,NULL,'09234567890',NULL,NULL,1,0,1,1,0,0,NULL,NULL,NULL,NULL,'2026-05-25 23:12:49',NULL,0,'2026-05-25 14:48:13','2026-05-25 23:12:49',NULL,NULL),(6,10,'lola.maria','maria.santos@email.com','$2y$10$rsUcxCkZu/EFEWKxtksnLuY/Jx.3JfLVL4tLHU8SfoCjBweWIBc2O','Maria','Santos',NULL,NULL,'09345678901',NULL,NULL,1,0,1,1,0,0,NULL,NULL,NULL,NULL,NULL,NULL,0,'2026-05-25 14:48:13','2026-05-25 23:10:45',NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Final view structure for view `active_users_with_roles`
--

/*!50001 DROP VIEW IF EXISTS `active_users_with_roles`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `active_users_with_roles` AS select `u`.`user_id` AS `user_id`,`u`.`username` AS `username`,`u`.`email` AS `email`,`u`.`first_name` AS `first_name`,`u`.`last_name` AS `last_name`,`u`.`phone_number` AS `phone_number`,`u`.`is_active` AS `is_active`,`u`.`last_login` AS `last_login`,`r`.`role_name` AS `primary_role`,group_concat(distinct `r2`.`role_name` separator ', ') AS `all_roles` from (((`users` `u` left join `roles` `r` on(`u`.`primary_role_id` = `r`.`role_id`)) left join `user_role_assignments` `ura` on(`u`.`user_id` = `ura`.`user_id` and `ura`.`is_active` = 1)) left join `roles` `r2` on(`ura`.`role_id` = `r2`.`role_id`)) where `u`.`deleted_at` is null group by `u`.`user_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `user_permissions_view`
--

/*!50001 DROP VIEW IF EXISTS `user_permissions_view`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `user_permissions_view` AS select distinct `u`.`user_id` AS `user_id`,`u`.`username` AS `username`,`u`.`email` AS `email`,`r`.`role_name` AS `role_name`,`p`.`permission_key` AS `permission_key`,`p`.`permission_name` AS `permission_name`,`p`.`module` AS `module` from ((((`users` `u` join `user_role_assignments` `ura` on(`u`.`user_id` = `ura`.`user_id` and `ura`.`is_active` = 1)) join `roles` `r` on(`ura`.`role_id` = `r`.`role_id`)) join `role_permissions` `rp` on(`r`.`role_id` = `rp`.`role_id`)) join `permissions` `p` on(`rp`.`permission_id` = `p`.`permission_id`)) where `u`.`is_active` = 1 and `u`.`deleted_at` is null */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-26 12:41:43
