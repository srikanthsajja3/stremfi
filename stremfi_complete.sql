/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-12.3.2-MariaDB, for osx10.21 (arm64)
--
-- Host: localhost    Database: stremfi
-- ------------------------------------------------------
-- Server version	12.3.2-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operator_id` int(11) DEFAULT NULL,
  `activity` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `operator_id` (`operator_id`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`operator_id`) REFERENCES `operators` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES
(1,3,'Login','Operator successfully logged in.','2026-07-15 08:33:27'),
(2,3,'Recharge','Recharged Customer ID 4 with Plan: Base Plan','2026-07-15 08:33:41'),
(3,1,'Login','Operator successfully logged in.','2026-07-15 08:34:05'),
(4,3,'Login','Operator successfully logged in.','2026-07-15 09:35:26'),
(5,3,'Recharge','Recharged Customer ID 4 with Plan: Base Plan','2026-07-15 09:36:04'),
(6,2,'Login','Operator successfully logged in.','2026-07-15 09:36:26'),
(7,3,'Login','Operator successfully logged in.','2026-07-15 09:37:31'),
(8,3,'Recharge','Recharged Customer ID 4 with Plan: Base Plan','2026-07-15 09:37:52'),
(9,2,'Login','Operator successfully logged in.','2026-07-15 09:38:11'),
(10,2,'Create Operator','Created operator sajja srikanth (operator)','2026-07-15 09:38:42'),
(11,1,'Login','Operator successfully logged in.','2026-07-15 11:39:30'),
(12,1,'Update Operator','Updated operator ID 4','2026-07-15 11:42:32');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `customer_devices`
--

DROP TABLE IF EXISTS `customer_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `device_uuid` varchar(255) DEFAULT NULL,
  `device_name` varchar(150) DEFAULT NULL,
  `mac_address` varchar(100) DEFAULT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `android_id` varchar(150) DEFAULT NULL,
  `launcher_version` varchar(30) DEFAULT NULL,
  `app_version` varchar(30) DEFAULT NULL,
  `last_seen` datetime DEFAULT NULL,
  `status` enum('ACTIVE','BLOCKED','LOST') DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `device_uuid` (`device_uuid`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `customer_devices_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_devices`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `customer_devices` WRITE;
/*!40000 ALTER TABLE `customer_devices` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_devices` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `customer_launcher_profile`
--

DROP TABLE IF EXISTS `customer_launcher_profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_launcher_profile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `default_launcher_app_id` int(11) NOT NULL,
  `boot_mode` enum('DEFAULT_APP','LAST_OPENED','HOME_SCREEN') DEFAULT 'DEFAULT_APP',
  `auto_launch` tinyint(1) DEFAULT 1,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_id` (`customer_id`),
  KEY `default_launcher_app_id` (`default_launcher_app_id`),
  KEY `updated_by` (`updated_by`),
  CONSTRAINT `customer_launcher_profile_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `customer_launcher_profile_ibfk_2` FOREIGN KEY (`default_launcher_app_id`) REFERENCES `launcher_apps` (`id`),
  CONSTRAINT `customer_launcher_profile_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `operators` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_launcher_profile`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `customer_launcher_profile` WRITE;
/*!40000 ALTER TABLE `customer_launcher_profile` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_launcher_profile` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `customer_subscriptions`
--

DROP TABLE IF EXISTS `customer_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `activated_by` int(11) NOT NULL,
  `activation_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `status` enum('ACTIVE','EXPIRED','SUSPENDED') DEFAULT 'ACTIVE',
  `auto_renew` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `plan_id` (`plan_id`),
  KEY `activated_by` (`activated_by`),
  CONSTRAINT `customer_subscriptions_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `customer_subscriptions_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`),
  CONSTRAINT `customer_subscriptions_ibfk_3` FOREIGN KEY (`activated_by`) REFERENCES `operators` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_subscriptions`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `customer_subscriptions` WRITE;
/*!40000 ALTER TABLE `customer_subscriptions` DISABLE KEYS */;
INSERT INTO `customer_subscriptions` VALUES
(6,3,1,2,'2026-07-14','2026-08-13','ACTIVE',1,'2026-07-14 06:21:42'),
(7,4,1,3,'2026-05-15','2026-06-14','EXPIRED',0,'2026-07-14 06:22:02'),
(8,4,1,3,'2026-07-15','2026-08-14','EXPIRED',0,'2026-07-15 08:33:41'),
(9,4,1,3,'2026-08-14','2026-09-13','EXPIRED',0,'2026-07-15 09:36:04'),
(10,4,1,3,'2026-09-13','2026-10-13','ACTIVE',0,'2026-07-15 09:37:52');
/*!40000 ALTER TABLE `customer_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operator_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `phone_number` varchar(20) NOT NULL,
  `password` varchar(20) NOT NULL,
  `customer_code` varchar(30) DEFAULT NULL,
  `installation_address` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `login_devices` int(11) DEFAULT 0,
  `Max_login_devices` int(11) DEFAULT 4,
  PRIMARY KEY (`id`),
  UNIQUE KEY `operator_id` (`operator_id`),
  UNIQUE KEY `customer_code` (`customer_code`),
  CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`operator_id`) REFERENCES `operators` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES
(3,2,'Ramesh','Kumar','9876543210','12345678','CUS0001','Hyderabad, Telangana','Premium Customer','2026-07-14 06:19:18',0,4),
(4,3,'Suresh','Reddy','9876543211','12345678','CUS0002','Warangal, Telangana','Subscription Expired','2026-07-14 06:19:35',0,4);
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `impersonation_sessions`
--

DROP TABLE IF EXISTS `impersonation_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `impersonation_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `impersonator_id` int(11) NOT NULL,
  `target_operator_id` int(11) NOT NULL,
  `original_operator_id` int(11) NOT NULL,
  `session_token` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ended_at` timestamp NULL DEFAULT NULL,
  `status` enum('ACTIVE','ENDED') DEFAULT 'ACTIVE',
  PRIMARY KEY (`id`),
  KEY `idx_impersonator` (`impersonator_id`),
  KEY `idx_target` (`target_operator_id`),
  KEY `idx_original` (`original_operator_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_impersonator` FOREIGN KEY (`impersonator_id`) REFERENCES `operators` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_original` FOREIGN KEY (`original_operator_id`) REFERENCES `operators` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_target` FOREIGN KEY (`target_operator_id`) REFERENCES `operators` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `impersonation_sessions`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `impersonation_sessions` WRITE;
/*!40000 ALTER TABLE `impersonation_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `impersonation_sessions` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `launcher_apps`
--

DROP TABLE IF EXISTS `launcher_apps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `launcher_apps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `app_name` varchar(100) NOT NULL,
  `package_name` varchar(255) NOT NULL,
  `activity_name` varchar(255) DEFAULT NULL,
  `app_icon` varchar(255) DEFAULT NULL,
  `app_type` enum('IPTV','OTT','SYSTEM','CUSTOM') DEFAULT 'CUSTOM',
  `is_installed_by_default` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `package_name` (`package_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `launcher_apps`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `launcher_apps` WRITE;
/*!40000 ALTER TABLE `launcher_apps` DISABLE KEYS */;
/*!40000 ALTER TABLE `launcher_apps` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `login_history`
--

DROP TABLE IF EXISTS `login_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `login_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operator_id` int(11) DEFAULT NULL,
  `login_time` datetime DEFAULT NULL,
  `logout_time` datetime DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `device` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `operator_id` (`operator_id`),
  CONSTRAINT `login_history_ibfk_1` FOREIGN KEY (`operator_id`) REFERENCES `operators` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_history`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `login_history` WRITE;
/*!40000 ALTER TABLE `login_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `login_history` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `operator_plan_prices`
--

DROP TABLE IF EXISTS `operator_plan_prices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `operator_plan_prices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operator_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `purchase_price` decimal(10,2) NOT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `operator_id` (`operator_id`,`plan_id`),
  KEY `plan_id` (`plan_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `operator_plan_prices_ibfk_1` FOREIGN KEY (`operator_id`) REFERENCES `operators` (`id`),
  CONSTRAINT `operator_plan_prices_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`),
  CONSTRAINT `operator_plan_prices_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `operators` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `operator_plan_prices`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `operator_plan_prices` WRITE;
/*!40000 ALTER TABLE `operator_plan_prices` DISABLE KEYS */;
/*!40000 ALTER TABLE `operator_plan_prices` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `operator_sessions`
--

DROP TABLE IF EXISTS `operator_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `operator_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operator_id` int(11) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `device_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expiry` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `operator_id` (`operator_id`),
  CONSTRAINT `operator_sessions_ibfk_1` FOREIGN KEY (`operator_id`) REFERENCES `operators` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `operator_sessions`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `operator_sessions` WRITE;
/*!40000 ALTER TABLE `operator_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `operator_sessions` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `operators`
--

DROP TABLE IF EXISTS `operators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `operators` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `role` enum('super_admin','admin','operator') NOT NULL,
  `name` varchar(150) NOT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `wallet_balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `company_name` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `pincode` varchar(20) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `impersonated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `mobile` (`mobile`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `operators_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `operators` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `operators`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `operators` WRITE;
/*!40000 ALTER TABLE `operators` DISABLE KEYS */;
INSERT INTO `operators` VALUES
(1,NULL,'super_admin','Super Admin','9999999999','superadmin@example.com','123456',100000.00,'Pioneer Digital TV','Head Office','Hyderabad','Telangana','500001',NULL,1,NULL,NULL,'2026-07-14 06:03:08','2026-07-14 06:03:08'),
(2,1,'admin','Main Admin','8888888888','admin@example.com','123456',50000.00,'Pioneer Digital TV','Admin Office','Hyderabad','Telangana','500001',NULL,1,1,NULL,'2026-07-14 06:03:18','2026-07-14 06:03:18'),
(3,2,'operator','Operator One','7777777777','operator@example.com','123456',9602.00,'Pioneer Digital TV','Branch Office','Warangal','Telangana','506001',NULL,1,2,NULL,'2026-07-14 06:03:46','2026-07-15 09:37:52'),
(4,2,'operator','sajja srikanth','09133095695','SRIKANTHSAJJA360@GMAIL.COM','123456',300.00,'student',NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,'2026-07-15 09:38:42','2026-07-15 11:42:32');
/*!40000 ALTER TABLE `operators` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `plan_types`
--

DROP TABLE IF EXISTS `plan_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `plan_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plan_types`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `plan_types` WRITE;
/*!40000 ALTER TABLE `plan_types` DISABLE KEYS */;
INSERT INTO `plan_types` VALUES
(1,'IPTV','Local Channels',1,'2026-07-14 06:08:16');
/*!40000 ALTER TABLE `plan_types` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `plans`
--

DROP TABLE IF EXISTS `plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `provider_id` int(11) NOT NULL,
  `plan_name` varchar(255) NOT NULL,
  `validity_days` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `mrp` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `speed` varchar(50) DEFAULT NULL,
  `data_limit` varchar(100) DEFAULT NULL,
  `plan_code` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `provider_id` (`provider_id`),
  CONSTRAINT `plans_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plans`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `plans` WRITE;
/*!40000 ALTER TABLE `plans` DISABLE KEYS */;
INSERT INTO `plans` VALUES
(1,1,'Base Plan',30,199.00,299.00,'Base OTT subscription with access to live TV and on-demand content.',NULL,'Unlimited','SFT_BASE_30',1,'2026-07-14 06:08:40');
/*!40000 ALTER TABLE `plans` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `providers`
--

DROP TABLE IF EXISTS `providers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `providers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_type_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `plan_type_id` (`plan_type_id`),
  CONSTRAINT `providers_ibfk_1` FOREIGN KEY (`plan_type_id`) REFERENCES `plan_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `providers`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `providers` WRITE;
/*!40000 ALTER TABLE `providers` DISABLE KEYS */;
INSERT INTO `providers` VALUES
(1,1,'StremFiTV','stremfitv.png','StremFiTV OTT Streaming Platform',1,'2026-07-14 06:08:28');
/*!40000 ALTER TABLE `providers` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `recharge_history`
--

DROP TABLE IF EXISTS `recharge_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `recharge_history` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `transaction_id` varchar(50) DEFAULT NULL,
  `customer_id` int(11) NOT NULL,
  `subscription_id` int(11) DEFAULT NULL,
  `plan_id` int(11) NOT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_mode` enum('WALLET','CASH','ONLINE') DEFAULT NULL,
  `recharge_type` enum('NEW','RENEWAL','UPGRADE') DEFAULT NULL,
  `recharged_by` int(11) NOT NULL,
  `recharge_date` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_id` (`transaction_id`),
  KEY `customer_id` (`customer_id`),
  KEY `subscription_id` (`subscription_id`),
  KEY `plan_id` (`plan_id`),
  KEY `recharged_by` (`recharged_by`),
  CONSTRAINT `recharge_history_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `recharge_history_ibfk_2` FOREIGN KEY (`subscription_id`) REFERENCES `customer_subscriptions` (`id`),
  CONSTRAINT `recharge_history_ibfk_3` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`),
  CONSTRAINT `recharge_history_ibfk_4` FOREIGN KEY (`recharged_by`) REFERENCES `operators` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recharge_history`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `recharge_history` WRITE;
/*!40000 ALTER TABLE `recharge_history` DISABLE KEYS */;
INSERT INTO `recharge_history` VALUES
(1,NULL,4,8,1,199.00,'CASH','NEW',3,'2026-07-15 14:03:41'),
(2,NULL,4,9,1,199.00,'WALLET','RENEWAL',3,'2026-07-15 15:06:04'),
(3,NULL,4,10,1,199.00,'WALLET','RENEWAL',3,'2026-07-15 15:07:52');
/*!40000 ALTER TABLE `recharge_history` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `wallet_transactions`
--

DROP TABLE IF EXISTS `wallet_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `wallet_transactions` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `transaction_id` varchar(50) NOT NULL,
  `operator_id` int(11) NOT NULL,
  `reference_operator_id` int(11) DEFAULT NULL,
  `transaction_type` enum('CREDIT','DEBIT','TRANSFER_IN','TRANSFER_OUT','REFUND','ADJUSTMENT') NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `balance_before` decimal(12,2) NOT NULL,
  `balance_after` decimal(12,2) NOT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_id` (`transaction_id`),
  KEY `created_by` (`created_by`),
  KEY `operator_id` (`operator_id`),
  KEY `reference_operator_id` (`reference_operator_id`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `wallet_transactions_ibfk_1` FOREIGN KEY (`operator_id`) REFERENCES `operators` (`id`),
  CONSTRAINT `wallet_transactions_ibfk_2` FOREIGN KEY (`reference_operator_id`) REFERENCES `operators` (`id`),
  CONSTRAINT `wallet_transactions_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `operators` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wallet_transactions`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `wallet_transactions` WRITE;
/*!40000 ALTER TABLE `wallet_transactions` DISABLE KEYS */;
INSERT INTO `wallet_transactions` VALUES
(1,'TXN6A575484D108B',3,NULL,'DEBIT',199.00,10000.00,9801.00,'Recharged Customer ID 4 for Plan: Base Plan',3,'2026-07-15 09:36:04'),
(2,'TXN6A5754F056E1D',3,NULL,'DEBIT',199.00,9801.00,9602.00,'Recharged Customer ID 4 for Plan: Base Plan',3,'2026-07-15 09:37:52');
/*!40000 ALTER TABLE `wallet_transactions` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
--
-- Alter operators table for banner_image
--
ALTER TABLE `operators` ADD COLUMN IF NOT EXISTS `banner_image` varchar(255) DEFAULT NULL AFTER `profile_image`;

--
-- Table structure for table `app_versions`
--

DROP TABLE IF EXISTS `app_versions`;
CREATE TABLE `app_versions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `app_name` varchar(100) NOT NULL DEFAULT 'launcher',
  `platform` varchar(50) NOT NULL DEFAULT 'android_tv',
  `version_code` int(11) NOT NULL,
  `version_name` varchar(50) NOT NULL,
  `force_update` tinyint(1) NOT NULL DEFAULT 0,
  `update_message` text DEFAULT NULL,
  `apk_url` varchar(255) DEFAULT NULL,
  `playstore_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `app_versions` (`id`, `app_name`, `platform`, `version_code`, `version_name`, `force_update`, `update_message`, `apk_url`, `playstore_url`, `created_at`) VALUES
(1, 'launcher', 'android_tv', 117, '1.1.7', 0, 'New update available. Please update to continue.', 'https://play.stremfitv.in/images/stremfitv.apk', 'https://play.stremfitv.in/images/stremfitv.apk', '2026-01-07 14:16:39');

--
-- Table structure for table `app_store`
--

DROP TABLE IF EXISTS `app_store`;
CREATE TABLE `app_store` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `package_name` varchar(255) NOT NULL,
  `play_store_id` varchar(255) DEFAULT NULL,
  `amazon_app_id` varchar(255) DEFAULT NULL,
  `apk_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `app_store` (`id`, `name`, `image_url`, `package_name`, `play_store_id`, `amazon_app_id`, `apk_url`, `is_active`, `created_at`) VALUES
(1, 'Netflix', 'https://play.stremfitv.in/images/netflix.png', 'com.netflix.ninja', 'https://play.google.com/store/apps/details?id=com.netflix.ninja', 'https://www.amazon.com/dp/B00FUZ3K5W', 'https://play.stremfitv.in/apk/netflix.apk', 1, '2026-07-26 10:00:00');

--
-- Table structure for table `actors`
--

DROP TABLE IF EXISTS `actors`;
CREATE TABLE `actors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `actor_order` int(11) NOT NULL DEFAULT 1,
  `is_category` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `actors` (`id`, `name`, `image`, `actor_order`, `is_category`, `created_at`) VALUES
(1, 'Chiranjeevi', '/images/actors/actor1.jpg', 1, 0, '2026-07-26 10:00:00'),
(5, 'Kids', '/images/categories/kids.jpg', 2, 1, '2026-07-26 10:00:00');

--
-- Table structure for table `youtube_categories`
--

DROP TABLE IF EXISTS `youtube_categories`;
CREATE TABLE `youtube_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `actor_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category_order` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `actor_id` (`actor_id`),
  CONSTRAINT `youtube_categories_ibfk_1` FOREIGN KEY (`actor_id`) REFERENCES `actors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `youtube_categories` (`id`, `actor_id`, `name`, `image`, `category_order`, `created_at`) VALUES
(1, 5, 'Rhymes', '/images/categories/rhymes.jpg', 1, '2026-07-26 10:00:00');

--
-- Table structure for table `youtube_movies`
--

DROP TABLE IF EXISTS `youtube_movies`;
CREATE TABLE `youtube_movies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `youtube_video_id` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `actor_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `role` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `youtube_video_id` (`youtube_video_id`),
  KEY `actor_id` (`actor_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `youtube_movies_ibfk_1` FOREIGN KEY (`actor_id`) REFERENCES `actors` (`id`) ON DELETE SET NULL,
  CONSTRAINT `youtube_movies_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `youtube_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `youtube_movies` (`id`, `name`, `image`, `thumbnail`, `youtube_video_id`, `description`, `actor_id`, `category_id`, `role`, `created_at`) VALUES
(1, 'Gang Leader', '/images/movies/gangleader.jpg', '/images/movies/thumb.jpg', 'ABC123', 'Movie Description', 1, NULL, 'Hero', '2026-07-26 10:00:00');

--
-- Table structure for table `tv_channels`
--

DROP TABLE IF EXISTS `tv_channels`;
CREATE TABLE `tv_channels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `imageUrl` varchar(255) DEFAULT NULL,
  `channelUrl` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT 'Entertainment',
  `language` varchar(50) DEFAULT 'Telugu',
  `player` varchar(50) DEFAULT 'internal',
  `channelNumber` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `channelNumber` (`channelNumber`),
  UNIQUE KEY `channelUrl` (`channelUrl`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tv_channels` (`id`, `name`, `imageUrl`, `channelUrl`, `category`, `language`, `player`, `channelNumber`, `created_at`) VALUES
(1, 'Maha Max', 'https://vrplay.in/images/channels/mahamax.png', 'https://d1msejlow1t3l4.cloudfront.net/fta/mahaamax/chunks.m3u8', 'Entertainment', 'Telugu', 'internal', 67, '2026-07-26 10:00:00'),
(2, 'TV20', 'https://vrplay.in/images/channels/tv20.jpg', 'http://154.206.17.250:9676/tv20/index.m3u8', 'Entertainment', 'Telugu', 'internal', 68, '2026-07-26 10:00:00'),
(3, 'Star Maa', 'https://vrplay.in/images/channels/starmaa.png', 'https://example.com/live/starmaa/index.m3u8', 'Entertainment', 'Telugu', 'internal', 101, '2026-07-26 10:00:00');

--
-- Table structure for table `music_categories`
--

DROP TABLE IF EXISTS `music_categories`;
CREATE TABLE `music_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `has_album` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `music_categories` (`id`, `name`, `image_url`, `has_album`, `is_active`, `created_at`) VALUES
(1, 'FM', 'https://vrplay.in/images/music/fm.png', 0, 1, '2026-06-23 06:48:23'),
(2, 'Hindu Devotional', 'https://vrplay.in/images/music/devotional.png', 1, 1, '2026-06-23 06:48:23'),
(3, 'Bhajans', 'https://vrplay.in/images/music/bhajans.png', 1, 1, '2026-06-23 06:48:23');

--
-- Table structure for table `music_albums`
--

DROP TABLE IF EXISTS `music_albums`;
CREATE TABLE `music_albums` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `music_albums_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `music_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `music_albums` (`id`, `category_id`, `name`, `image_url`, `is_active`, `created_at`) VALUES
(2, 2, 'Adi Parvam', 'https://vrplay.in/images/music/mahabharath.png', 1, '2026-06-23 06:48:23'),
(9, 2, 'Udyoga Parvam', 'https://vrplay.in/images/music/mahabharath.png', 1, '2026-06-23 06:48:23');

--
-- Table structure for table `music`
--

DROP TABLE IF EXISTS `music`;
CREATE TABLE `music` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `album_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `type` varchar(50) DEFAULT 'PODCAST',
  `stream_url` text NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `album_id` (`album_id`),
  CONSTRAINT `music_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `music_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `music_ibfk_2` FOREIGN KEY (`album_id`) REFERENCES `music_albums` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `music` (`id`, `category_id`, `album_id`, `name`, `image_url`, `type`, `stream_url`, `is_active`, `created_at`) VALUES
(6, 2, 2, 'Adi Parvam 01', NULL, 'PODCAST', 'http://media.srichaganti.org/mahabharath/01/ADIPARVAM01.mp3', 1, '2026-06-23 07:50:50'),
(101, 2, 2, 'Adi Parvam 21', '', 'PODCAST', 'http://media.srichaganti.org/mahabharath/01/ADIPARVAM21.mp3', 1, '2026-06-23 07:50:50'),
(102, 1, NULL, 'Radio Mirchi', 'https://upload.wikimedia.org/wikipedia/en/1/1d/Radio_Mirchi_logo.png', 'FM', 'https://stream.zeno.fm/7kbt507d3qzuv', 1, '2026-06-23 07:50:50');

--
-- Table structure for table `education_categories`
--

DROP TABLE IF EXISTS `education_categories`;
CREATE TABLE `education_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `has_subjects` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `education_categories` (`id`, `name`, `image_url`, `has_subjects`, `is_active`, `created_at`) VALUES
(4, 'Maths', 'https://vrplay.in/images/education/maths.png', 1, 1, '2026-06-27 09:42:29'),
(6, 'Algebra', 'https://vrplay.in/images/education/algebra.png', 0, 1, '2026-06-27 10:05:16'),
(7, 'Science', 'https://vrplay.in/images/education/science.png', 1, 1, '2026-06-27 10:05:16');

--
-- Table structure for table `education_subjects`
--

DROP TABLE IF EXISTS `education_subjects`;
CREATE TABLE `education_subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `education_subjects_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `education_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `education_subjects` (`id`, `category_id`, `name`, `image_url`, `is_active`, `created_at`) VALUES
(2, 4, 'Algebra', 'https://vrplay.in/images/education/algebra.png', 1, '2026-06-27 09:42:43'),
(5, 4, 'Geometry', 'https://vrplay.in/images/education/geometry.png', 1, '2026-06-27 09:42:43');

--
-- Table structure for table `education_videos`
--

DROP TABLE IF EXISTS `education_videos`;
CREATE TABLE `education_videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `video_url` text NOT NULL,
  `video_type` varchar(50) DEFAULT 'youtube',
  `duration` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `subject_id` (`subject_id`),
  CONSTRAINT `education_videos_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `education_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `education_videos_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `education_subjects` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `education_videos` (`id`, `category_id`, `subject_id`, `title`, `image_url`, `video_url`, `video_type`, `duration`, `description`, `is_active`, `created_at`) VALUES
(2, 6, NULL, 'Introduction to Algebra', 'https://i.ytimg.com/vi/MHeirBPOI6w/maxresdefault.jpg', 'https://youtu.be/MHeirBPOI6w', 'youtube', '10:20', 'Basic Algebra', 1, '2026-06-27 10:58:00'),
(10, 6, NULL, 'Linear Algebra Basics', 'https://example.com/algebra.jpg', 'https://youtu.be/abcd1234', 'youtube', '18:30', 'Introduction to Linear Algebra', 1, '2026-06-27 10:58:00'),
(11, 4, 2, 'Matrices - Part 1', 'https://example.com/matrices.jpg', 'https://youtu.be/xyz987', 'youtube', '25:40', 'Matrices for beginners', 1, '2026-06-27 10:58:00');

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-07-15 17:23:49

-- Table structure for table `ads`
DROP TABLE IF EXISTS `ads`;
CREATE TABLE `ads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `image_url` text NOT NULL,
  `link_url` text DEFAULT NULL,
  `position` varchar(50) DEFAULT 'banner',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `ads` (`id`, `title`, `image_url`, `link_url`, `position`, `is_active`) VALUES
(1, 'StremFi Launcher Special Promo Banner', 'https://images.unsplash.com/photo-1574375927938-d5a98e8ffe85?w=800&q=80', 'https://stremfitv.in/promo', 'banner', 1),
(2, 'Summer Movies Special Popup Ad', 'https://images.unsplash.com/photo-1536440136628-849c177e76a1?w=800&q=80', 'https://stremfitv.in/movies', 'popup', 1);

-- Table structure for table `ip_whitelist`
DROP TABLE IF EXISTS `ip_whitelist`;
CREATE TABLE `ip_whitelist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` enum('enabled','disabled') DEFAULT 'enabled',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `ip_whitelist` (`id`, `ip_address`, `description`, `status`) VALUES
(1, '192.168.1.100', 'Main Office Admin Gateway', 'enabled'),
(2, '103.45.67.89', 'Secondary Production Server', 'enabled'),
(3, '172.16.0.45', 'Staging Testing Server', 'disabled');
