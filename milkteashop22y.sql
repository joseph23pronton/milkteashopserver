-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: localhost    Database: milkteashop2
-- ------------------------------------------------------
-- Server version	8.0.43

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `attendance`
--

DROP TABLE IF EXISTS `attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendance` (
  `id` int NOT NULL AUTO_INCREMENT,
  `employee_id` int NOT NULL,
  `attendance_date` date NOT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `status` enum('present','late','absent','on_leave') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'absent',
  `late_minutes` int DEFAULT '0',
  `scheduled_shift` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_attendance` (`employee_id`,`attendance_date`)
) ENGINE=InnoDB AUTO_INCREMENT=149 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance`
--

LOCK TABLES `attendance` WRITE;
/*!40000 ALTER TABLE `attendance` DISABLE KEYS */;
INSERT INTO `attendance` VALUES (1,15633,'2025-10-22','19:31:55','00:00:00','late',691,NULL,'','2025-10-22 16:22:01'),(3,20002,'2025-10-22','19:32:35','19:31:41','late',692,NULL,NULL,'2025-10-22 16:43:19'),(8,20002,'2025-10-23','22:40:40','22:57:03','late',880,NULL,NULL,'2025-10-22 17:00:26'),(14,15633,'2025-10-23','22:33:16','22:40:21','late',873,NULL,NULL,'2025-10-22 17:37:50'),(16,38502,'2025-10-23','03:30:47','03:31:02','present',0,NULL,NULL,'2025-10-22 19:30:47'),(17,20002,'2025-10-24','00:11:29','00:25:26','present',0,NULL,NULL,'2025-10-23 16:03:46'),(18,31980,'2025-10-24','00:06:29','00:07:34','present',0,NULL,NULL,'2025-10-23 16:06:29'),(19,26770,'2025-10-24','00:09:26','00:10:04','present',0,NULL,NULL,'2025-10-23 16:09:26'),(21,12194,'2025-10-24','04:33:06',NULL,'present',0,NULL,NULL,'2025-10-23 20:27:33'),(22,99999,'2025-10-24','16:46:33',NULL,'late',526,NULL,NULL,'2025-10-23 20:36:09'),(23,16114,'2025-10-24','17:39:18',NULL,'late',579,NULL,NULL,'2025-10-23 21:12:58'),(24,18044,'2025-10-24','05:33:29','05:33:35','present',0,NULL,NULL,'2025-10-23 21:33:29'),(25,18044,'2025-10-25','00:42:36','00:50:51','present',0,NULL,NULL,'2025-10-24 16:42:11'),(26,99999,'2025-10-25','14:22:02',NULL,'late',382,NULL,NULL,'2025-10-24 17:25:18'),(27,16114,'2025-10-25','15:38:00','22:00:00','late',98,'2:00 PM - 10:00 PM','','2025-10-25 07:14:46'),(84,15633,'2025-11-01','08:00:00','16:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(85,15633,'2025-11-03','08:00:00','16:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(86,15633,'2025-11-04','08:00:00','16:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(87,15633,'2025-11-05','08:00:00','16:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(88,15633,'2025-11-06','08:00:00','16:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(89,15633,'2025-11-07','08:00:00','16:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(90,15633,'2025-11-08','08:00:00','16:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(91,15633,'2025-11-10','08:00:00','16:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(92,15633,'2025-11-11','08:00:00','16:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(93,15633,'2025-11-12','08:00:00','16:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(94,16114,'2025-11-01','08:00:00','19:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(95,16114,'2025-11-03','08:00:00','19:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(96,16114,'2025-11-04','08:00:00','19:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(97,16114,'2025-11-05','08:00:00','19:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(98,16114,'2025-11-06','08:00:00','19:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(99,16114,'2025-11-07','23:59:06',NULL,'late',959,'8:00 AM - 4:00 PM',NULL,'2025-11-07 08:55:27'),(100,16114,'2025-11-08','06:00:00','14:00:00','present',0,'6:00 AM - 2:00 PM','','2025-11-07 08:55:27'),(101,16114,'2025-11-10','08:00:00','19:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(102,16114,'2025-11-11','08:00:00','19:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(103,16114,'2025-11-12','08:00:00','19:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(104,16114,'2025-11-13','08:00:00','18:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(105,16114,'2025-11-14','08:00:00','18:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(106,16114,'2025-11-15','08:00:00','18:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(107,16114,'2025-11-02','08:00:00','18:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(108,16114,'2025-11-09','08:00:00','18:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(109,18044,'2025-11-01','08:00:00','19:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(110,18044,'2025-11-03','08:00:00','19:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(111,18044,'2025-11-04','08:00:00','19:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(112,18044,'2025-11-05','08:00:00','19:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(113,18044,'2025-11-06','08:00:00','19:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(114,18044,'2025-11-07','21:01:45','22:47:20','late',421,'2:00 PM - 10:00 PM',NULL,'2025-11-07 08:55:27'),(115,18044,'2025-11-08','00:39:32',NULL,'present',0,'6:00 AM - 2:00 PM',NULL,'2025-11-07 08:55:27'),(116,18044,'2025-11-10','08:00:00','19:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(117,18044,'2025-11-11','08:00:00','19:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(118,18044,'2025-11-12','08:00:00','19:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(119,18044,'2025-11-13','08:00:00','18:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(120,18044,'2025-11-14','08:00:00','18:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(121,18044,'2025-11-15','08:00:00','18:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(122,18044,'2025-11-02','08:00:00','18:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(123,18044,'2025-11-09','08:00:00','18:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(124,16044,'2025-11-01','08:30:00','17:00:00','absent',30,NULL,NULL,'2025-11-07 08:55:27'),(125,16044,'2025-11-03','08:15:00','17:00:00','absent',15,NULL,NULL,'2025-11-07 08:55:27'),(126,16044,'2025-11-04','08:00:00','17:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(127,16044,'2025-11-05','08:45:00','17:00:00','absent',45,NULL,NULL,'2025-11-07 08:55:27'),(128,16044,'2025-11-06','08:00:00','17:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(129,16044,'2025-11-07','08:20:00','17:00:00','absent',20,NULL,NULL,'2025-11-07 08:55:27'),(130,16044,'2025-11-08','08:00:00','17:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(131,16044,'2025-11-10','08:10:00','17:00:00','absent',10,NULL,NULL,'2025-11-07 08:55:27'),(132,16044,'2025-11-11','08:00:00','17:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(133,16044,'2025-11-12','08:00:00','17:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(134,16044,'2025-11-13','08:00:00','17:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(135,16044,'2025-11-14','08:00:00','17:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(136,16044,'2025-11-15','08:00:00','17:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(137,16044,'2025-11-02','08:00:00','16:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(138,16044,'2025-11-09','08:00:00','16:00:00','absent',0,NULL,NULL,'2025-11-07 08:55:27'),(139,12194,'2025-11-07','22:47:32','23:42:11','late',527,'2:00 PM - 10:00 PM',NULL,'2025-11-07 14:47:32'),(140,99999,'2025-11-07','23:58:49','23:58:52','late',598,'2:00 PM - 10:00 PM',NULL,'2025-11-07 15:43:32'),(142,12194,'2025-11-08',NULL,'00:39:27','present',0,'6:00 AM - 2:00 PM',NULL,'2025-11-07 16:03:32'),(144,17642,'2025-11-08',NULL,NULL,'present',0,'6:00 AM - 2:00 PM',NULL,'2025-11-07 16:03:32'),(146,99999,'2025-11-08',NULL,NULL,'present',0,'6:00 AM - 2:00 PM',NULL,'2025-11-07 16:03:32'),(147,17192,'2025-11-08',NULL,NULL,'present',0,'6:00 AM - 2:00 PM',NULL,'2025-11-07 16:03:32');
/*!40000 ALTER TABLE `attendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `branches`
--

DROP TABLE IF EXISTS `branches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `branches` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `branches`
--

LOCK TABLES `branches` WRITE;
/*!40000 ALTER TABLE `branches` DISABLE KEYS */;
INSERT INTO `branches` VALUES (245,'Llano','Caloocan'),(8954,'Congressional','Caloocan');
/*!40000 ALTER TABLE `branches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `budgets`
--

DROP TABLE IF EXISTS `budgets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `budgets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `allocated_amount` decimal(10,2) NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `branch_id` int DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_period` (`period_start`,`period_end`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budgets`
--

LOCK TABLES `budgets` WRITE;
/*!40000 ALTER TABLE `budgets` DISABLE KEYS */;
/*!40000 ALTER TABLE `budgets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `departments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES (1,'SALES','Handles customer transactions and orders','2025-10-25 06:37:01'),(2,'PRODUCTION','Manages production operations','2025-10-25 06:37:01'),(3,'INVENTORY','Manages stock and supplies','2025-10-25 06:37:01'),(4,'HR','Human resources management','2025-10-25 06:37:01'),(5,'FINANCE','Financial management and accounting','2025-10-25 06:37:01');
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expenses`
--

DROP TABLE IF EXISTS `expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expenses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `amount` decimal(10,2) NOT NULL,
  `branch_id` int DEFAULT NULL,
  `expense_date` date NOT NULL,
  `payment_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Cash',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_archived` tinyint(1) DEFAULT '0',
  `archived_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_expense_date` (`expense_date`),
  KEY `idx_branch` (`branch_id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expenses`
--

LOCK TABLES `expenses` WRITE;
/*!40000 ALTER TABLE `expenses` DISABLE KEYS */;
INSERT INTO `expenses` VALUES (1,'Electricity','MERALCO BILL',2000.00,245,'2025-10-24','Cash','2025-10-24 07:52:19',0,NULL),(2,'Water','MAYNILAD BILL',570.00,245,'2025-11-24','Cash','2025-10-24 07:56:43',0,NULL),(3,'Raw Materials','Restock of Caramel Sugar Flavor (Invoice: INV-20251024-0021) - 10 units',3.10,245,'2025-10-24','Bank Transfer','2025-10-24 08:05:22',0,NULL),(4,'Raw Materials','Restock of Chocolate Flavor (Invoice: INV-20251024-0002) - 50 units',20.50,245,'2025-10-24','Bank Transfer','2025-10-24 08:21:41',0,NULL),(5,'Raw Materials','Restock of Cookies & Cream Flavor (Invoice: INV-20251024-0003) - 50 units',20.50,245,'2025-10-24','Bank Transfer','2025-10-24 08:21:41',0,NULL),(6,'Raw Materials','Restock of Crushed Cookies (Invoice: INV-20251024-0004) - 50 units',2.50,245,'2025-10-24','Bank Transfer','2025-10-24 08:21:41',0,NULL),(7,'Raw Materials','Restock of Dark Chocolate Flavor (Invoice: INV-20251024-0005) - 50 units',13.00,245,'2025-10-24','Bank Transfer','2025-10-24 08:21:41',0,NULL),(8,'Raw Materials','Restock of Fructose Corn Syrup (Invoice: INV-20251024-0006) - 50 units',5.50,245,'2025-10-24','Bank Transfer','2025-10-24 08:21:41',0,NULL),(9,'Raw Materials','Restock of Hazelnut Flavor (Invoice: INV-20251024-0007) - 50 units',12.50,245,'2025-10-24','Bank Transfer','2025-10-24 08:21:41',0,NULL),(10,'Raw Materials','Restock of Fructose Corn Syrup (Invoice: INV-20251024-0008) - 50 units',5.50,245,'2025-10-24','Bank Transfer','2025-10-24 08:21:41',0,NULL),(11,'Raw Materials','Restock of Hokkaido Flavor (Invoice: INV-20251024-0009) - 50 units',12.50,245,'2025-10-24','Bank Transfer','2025-10-24 08:21:41',0,NULL),(12,'Raw Materials','Restock of Loose Tea (Invoice: INV-20251024-0010) - 50 units',3.00,245,'2025-10-24','Bank Transfer','2025-10-24 08:21:41',0,NULL),(13,'Raw Materials','Restock of Ice (Invoice: INV-20251024-0011) - 150 units',28.50,245,'2025-10-24','Bank Transfer','2025-10-24 08:21:41',0,NULL),(14,'Raw Materials','Restock of Popping Boba (Invoice: INV-20251024-0012) - 50 units',4.50,245,'2025-10-24','Bank Transfer','2025-10-24 08:21:41',0,NULL),(15,'Raw Materials','Restock of Caramel Sugar Flavor (Invoice: INV-20251024-0013) - 50 units',15.50,245,'2025-10-24','Bank Transfer','2025-10-24 08:21:41',0,NULL),(16,'Raw Materials','Restock of Chocolate Flavor (Invoice: INV-20251024-0014) - 1000 units',410.00,245,'2025-10-24','Bank Transfer','2025-10-24 08:21:41',0,NULL),(17,'Raw Materials','Restock of Crushed Cookies (Invoice: INV-20251024-0015) - 200 units',10.00,245,'2025-10-24','Bank Transfer','2025-10-24 08:21:41',0,NULL),(18,'Raw Materials','Restock of Loose Tea (Invoice: INV-20251024-0016) - 500 units',30.00,245,'2025-10-24','Bank Transfer','2025-10-24 08:21:41',0,NULL),(19,'Raw Materials','Restock of Loose Tea (Invoice: INV-20251024-0017) - 500 units',30.00,245,'2025-10-24','Bank Transfer','2025-10-24 08:21:41',0,NULL),(20,'Raw Materials','Restock of Caramel Sugar Flavor (Invoice: INV-20251024-0019) - 1000 units',310.00,245,'2025-10-24','Bank Transfer','2025-10-24 08:21:41',0,NULL),(21,'Raw Materials','Restock of Loose Tea (Invoice: INV-20251024-0025) - 500 units',30.00,245,'2025-10-24','Bank Transfer','2025-10-24 17:25:25',0,NULL),(22,'Raw Materials','Restock of Fructose Corn Syrup (Invoice: INV-20251024-0029) - 1000 units',110.00,245,'2025-10-24','Bank Transfer','2025-10-24 17:42:55',0,NULL),(23,'Raw Materials','Restock of Tapioca Pearls (Invoice: INV-20251024-0028) - 1000 units',200.00,245,'2025-10-24','Bank Transfer','2025-10-24 17:42:58',0,NULL),(24,'Raw Materials','Restock of Loose Tea (Invoice: INV-20251024-0027) - 1000 units',60.00,245,'2025-10-24','Bank Transfer','2025-10-24 17:43:04',0,NULL),(25,'Raw Materials','Restock of Ice (Invoice: INV-20251024-0026) - 500 units',95.00,245,'2025-10-24','Bank Transfer','2025-10-24 17:43:09',0,NULL);
/*!40000 ALTER TABLE `expenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ingredients`
--

DROP TABLE IF EXISTS `ingredients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ingredients` (
  `id` int NOT NULL,
  `ingredientsID` int DEFAULT NULL,
  `ingredientsName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `branchesID` int DEFAULT NULL,
  `currentStock` int DEFAULT NULL,
  `lastRestock` date DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ingredients`
--

LOCK TABLES `ingredients` WRITE;
/*!40000 ALTER TABLE `ingredients` DISABLE KEYS */;
INSERT INTO `ingredients` VALUES (1095,13,NULL,245,990,'2024-12-21','2024-12-20 16:00:00'),(1130,8,NULL,964,980,'2024-12-21','2024-12-20 16:00:00'),(1303,20,NULL,964,490,'2024-12-21','2024-12-20 16:00:00'),(1337,9,NULL,245,2010,'2025-10-24','2025-10-24 08:05:22'),(1515,3,NULL,964,1090,'2024-12-21','2024-12-20 16:00:00'),(1689,14,NULL,964,1000,'2024-12-21','2024-12-20 16:00:00'),(2013,11,NULL,964,2020,'2024-12-21','2024-12-20 18:33:11'),(2030,18,NULL,245,500,'2024-12-21','2024-12-20 16:00:00'),(2107,16,NULL,245,800,'2025-10-25','2025-10-24 17:42:58'),(2156,21,NULL,964,53,'2024-12-21','2024-12-20 16:00:00'),(2225,23,NULL,964,63,'2024-12-21','2024-12-20 16:00:00'),(2256,15,NULL,245,1000,'2024-12-21','2024-12-20 16:00:00'),(2289,10,NULL,245,1000,'2024-12-21','2024-12-20 16:00:00'),(2568,7,NULL,964,950,'2024-12-21','2024-12-20 16:00:00'),(2599,24,NULL,245,455,'2024-12-21','2024-12-20 16:00:00'),(2777,22,NULL,964,50,'2024-12-21','2024-12-20 16:00:00'),(2936,9,NULL,964,1220,'2025-10-20','2025-10-19 16:00:00'),(2989,25,NULL,245,75,'2024-12-21','2024-12-20 16:00:00'),(3381,12,NULL,245,1000,'2024-12-21','2024-12-20 16:00:00'),(4108,17,NULL,964,500,'2024-12-21','2024-12-20 16:00:00'),(4216,4,NULL,964,1655,'2025-10-20','2025-10-19 16:00:00'),(4594,21,NULL,245,455,'2024-12-21','2024-12-20 16:00:00'),(4938,7,NULL,245,720,'2024-12-21','2024-12-20 16:00:00'),(5032,17,NULL,245,500,'2024-12-21','2024-12-20 16:00:00'),(5173,15,NULL,964,1000,'2024-12-21','2024-12-20 16:00:00'),(5177,12,NULL,964,550,'2024-12-21','2024-12-20 16:00:00'),(5411,24,NULL,964,63,'2024-12-21','2024-12-20 16:00:00'),(5743,19,NULL,245,500,'2024-12-21','2024-12-20 19:27:09'),(5944,23,NULL,245,455,'2024-12-21','2024-12-20 16:00:00'),(6143,10,NULL,964,1050,'2024-12-21','2024-12-20 16:00:00'),(6291,13,NULL,964,1050,'2024-12-21','2024-12-20 16:00:00'),(6422,8,NULL,245,970,'2024-12-21','2024-12-20 16:00:00'),(6843,14,NULL,245,1000,'2024-12-21','2024-12-20 16:00:00'),(6952,16,NULL,964,20,'2024-12-21','2024-12-20 16:00:00'),(7037,22,NULL,245,500,'2024-12-21','2024-12-20 16:00:00'),(7249,20,NULL,245,350,'2025-10-25','2025-10-24 17:43:09'),(7430,6,NULL,245,920,'2024-12-21','2024-12-20 16:00:00'),(7450,18,NULL,964,50,'2024-12-20','2024-12-20 13:17:13'),(8104,19,NULL,964,550,'2024-12-21','2024-12-20 16:00:00'),(8156,2,NULL,245,570,'2024-12-21','2024-12-20 16:00:00'),(8262,5,NULL,964,1000,'2024-12-21','2024-12-20 16:00:00'),(8320,11,NULL,245,950,'2024-12-21','2024-12-20 16:00:00'),(8543,5,NULL,245,1000,'2024-12-21','2024-12-20 16:00:00'),(8572,6,NULL,964,1000,'2024-12-21','2024-12-20 16:00:00'),(8972,2,NULL,964,950,'2024-12-21','2024-12-20 16:00:00'),(9503,3,NULL,245,850,'2025-10-25','2025-10-24 17:42:55'),(9855,25,NULL,964,15,'2024-12-21','2024-12-20 16:00:00'),(9951,4,NULL,245,475,'2025-10-25','2025-10-24 17:43:04'),(100000,13,NULL,8954,990,'2024-12-21','2025-10-23 15:38:38'),(100001,9,NULL,8954,1000,'2024-12-21','2025-10-23 15:38:38'),(100002,18,NULL,8954,500,'2024-12-21','2025-10-23 15:38:38'),(100003,16,NULL,8954,680,'2024-12-21','2025-10-23 15:38:38'),(100004,15,NULL,8954,1000,'2024-12-21','2025-10-23 15:38:38'),(100005,10,NULL,8954,1000,'2024-12-21','2025-10-23 15:38:38'),(100006,24,NULL,8954,477,'2024-12-21','2025-10-23 15:38:38'),(100007,25,NULL,8954,185,'2024-12-21','2025-10-23 15:38:38'),(100008,12,NULL,8954,1000,'2024-12-21','2025-10-23 15:38:38'),(100009,21,NULL,8954,477,'2024-12-21','2025-10-23 15:38:38'),(100010,7,NULL,8954,770,'2024-12-21','2025-10-23 15:38:38'),(100011,17,NULL,8954,500,'2024-12-21','2025-10-23 15:38:38'),(100012,19,NULL,8954,500,'2024-12-21','2025-10-23 15:38:38'),(100013,23,NULL,8954,477,'2024-12-21','2025-10-23 15:38:38'),(100014,8,NULL,8954,980,'2024-12-21','2025-10-23 15:38:38'),(100015,14,NULL,8954,1000,'2024-12-21','2025-10-23 15:38:38'),(100016,22,NULL,8954,500,'2024-12-21','2025-10-23 15:38:38'),(100017,20,NULL,8954,510,'2024-12-21','2025-10-23 15:38:38'),(100018,6,NULL,8954,970,'2024-12-21','2025-10-23 15:38:38'),(100019,2,NULL,8954,1040,'2024-12-21','2025-10-23 15:38:38'),(100020,11,NULL,8954,1000,'2024-12-21','2025-10-23 15:38:38'),(100021,5,NULL,8954,1000,'2024-12-21','2025-10-23 15:38:38'),(100022,3,NULL,8954,510,'2024-12-21','2025-10-23 15:38:38'),(100023,4,NULL,8954,845,'2025-10-20','2025-10-23 15:38:38');
/*!40000 ALTER TABLE `ingredients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ingredientsheader`
--

DROP TABLE IF EXISTS `ingredientsheader`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ingredientsheader` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ingredients_limit` int NOT NULL DEFAULT '300',
  `unit` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'g',
  `is_archived` tinyint(1) DEFAULT '0',
  `price_per_unit` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ingredientsheader`
--

LOCK TABLES `ingredientsheader` WRITE;
/*!40000 ALTER TABLE `ingredientsheader` DISABLE KEYS */;
INSERT INTO `ingredientsheader` VALUES (2,'Malaysian Non-Dairy Creamer',300,'g',0,0.19),(3,'Fructose Corn Syrup',300,'g',0,0.11),(4,'Loose Tea',300,'g',0,0.06),(5,'Wintermelon Flavor',300,'g',0,1.25),(6,'Okinawa Flavor',300,'g',0,0.74),(7,'Hokkaido Flavor',300,'g',0,0.25),(8,'Matcha Flavor',300,'g',0,0.40),(9,'Caramel Sugar Flavor',300,'g',0,0.31),(10,'Hazelnut Flavor',300,'g',0,0.25),(11,'Chocolate Flavor',300,'g',0,0.41),(12,'Dark Chocolate Flavor',300,'g',0,0.26),(13,'Cookies & Cream Flavor',300,'g',0,0.41),(14,'Red Velvet Flavor',300,'g',0,0.20),(15,'Mango Cheesecake Flavor',300,'g',0,0.32),(16,'Tapioca Pearls',300,'g',0,0.20),(17,'Nata',300,'g',0,1.00),(18,'Popping Boba',300,'g',0,0.09),(19,'Crushed Cookies',300,'g',0,0.05),(20,'Ice',300,'g',0,0.19),(21,'Plastic Cups - M',100,'pcs',0,0.34),(22,'Plastic Cups - L',100,'pcs',0,1.26),(23,'Plastic Straw',100,'pcs',0,0.50),(24,'Plastic Lids',100,'pcs',0,1.00),(25,'Tissue',100,'pcs',0,40.00);
/*!40000 ALTER TABLE `ingredientsheader` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payroll`
--

DROP TABLE IF EXISTS `payroll`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payroll` (
  `id` int NOT NULL AUTO_INCREMENT,
  `employee_id` int NOT NULL,
  `pay_period_start` date NOT NULL,
  `pay_period_end` date NOT NULL,
  `total_hours` decimal(10,2) DEFAULT '0.00',
  `gross_pay` decimal(10,2) DEFAULT '0.00',
  `late_deductions` decimal(10,2) DEFAULT '0.00',
  `sss_contribution` decimal(10,2) DEFAULT '0.00',
  `pagibig_contribution` decimal(10,2) DEFAULT '0.00',
  `philhealth_contribution` decimal(10,2) DEFAULT '0.00',
  `net_pay` decimal(10,2) DEFAULT '0.00',
  `status` enum('pending','approved','paid') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_archived` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payroll`
--

LOCK TABLES `payroll` WRITE;
/*!40000 ALTER TABLE `payroll` DISABLE KEYS */;
INSERT INTO `payroll` VALUES (2,20002,'2025-10-23','2025-10-23',0.10,0.00,0.00,0.00,0.00,0.00,0.00,'pending','2025-10-22 17:06:28',1),(3,15633,'2025-10-23','2025-10-23',0.26,15.95,0.00,0.00,0.00,0.00,14.36,'paid','2025-10-22 17:59:21',1),(9,16114,'2025-11-07','2025-12-07',0.07,0.00,0.00,0.00,0.00,0.00,0.00,'pending','2025-11-07 07:33:32',1),(10,18044,'2025-11-07','2025-12-07',0.02,0.00,0.00,0.00,0.00,0.00,0.00,'pending','2025-11-07 08:06:05',1),(11,16114,'2025-11-07','2025-12-08',8.00,800.00,801.67,0.00,0.00,0.00,-81.67,'pending','2025-11-07 08:10:54',1),(12,18044,'2025-11-07','2025-12-08',0.02,1.53,158.67,0.00,0.00,0.00,-157.29,'pending','2025-11-07 08:11:35',1),(13,16114,'2025-11-01','2025-11-30',8.00,800.00,801.67,36.00,10.00,20.00,-67.67,'pending','2025-11-07 08:36:22',1),(14,18044,'2025-11-01','2025-11-30',0.02,1.53,158.67,0.07,0.02,0.04,-157.27,'pending','2025-11-07 08:36:22',1),(15,16114,'2025-11-01','2025-11-15',160.00,16000.00,0.00,720.00,200.00,400.00,14680.00,'pending','2025-11-07 08:56:34',0),(16,18044,'2025-11-01','2025-11-15',160.00,12800.00,0.00,585.00,200.00,320.00,11695.00,'paid','2025-11-07 08:56:34',0),(17,16114,'2025-11-06','2025-11-09',37.00,3700.00,1598.33,166.50,46.25,92.50,1796.42,'approved','2025-11-07 16:19:19',0),(18,12194,'2025-11-07','2025-11-08',0.91,68.31,658.75,3.07,1.14,1.71,-596.36,'approved','2025-11-07 16:19:46',0);
/*!40000 ALTER TABLE `payroll` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_returns`
--

DROP TABLE IF EXISTS `product_returns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_returns` (
  `id` int NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `purchase_order_id` int DEFAULT NULL,
  `ingredient_id` int NOT NULL,
  `quantity_returned` int NOT NULL,
  `reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `return_date` date NOT NULL,
  `refund_amount` decimal(10,2) NOT NULL,
  `supplier_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `branch_id` int DEFAULT NULL,
  `status` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `idx_return_date` (`return_date`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_returns`
--

LOCK TABLES `product_returns` WRITE;
/*!40000 ALTER TABLE `product_returns` DISABLE KEYS */;
INSERT INTO `product_returns` VALUES (1,'RET-20251024-9827',2,11,50,'Damaged','2025-10-24',20.50,'N/A',245,'pending','2025-10-24 06:39:52'),(2,'RET-20251024-1060',21,9,10,'Damaged','2025-10-24',3.10,'N/A',245,'approved','2025-10-24 08:48:00'),(3,'RET-20251024-0737',4,19,50,'Damaged','2025-10-24',2.50,'N/A',245,'approved','2025-10-24 08:48:38'),(4,'RET-20251024-3235',21,9,10,'Damaged','2025-10-24',3.10,'N/A',245,'approved','2025-10-24 08:49:44'),(5,'RET-20251024-7131',4,19,50,'Damaged','2025-10-24',2.50,'N/A',245,'approved','2025-10-24 09:17:26');
/*!40000 ALTER TABLE `product_returns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `size` enum('Medium','Large') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Medium',
  `price` decimal(10,0) DEFAULT NULL,
  `initial_price` decimal(10,0) NOT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_archived` tinyint(1) DEFAULT '0',
  `price_cushion` decimal(10,2) DEFAULT '5.00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (25,'Classic Milktea','Large',80,53,1,'/uploads/product_images/product_675a8a109de268.18871704.png',NULL,NULL,0,5.00),(26,'Classic Milktea','Medium',70,45,1,'/uploads/product_images/product_675a8e82ab8bc6.91799638.png',NULL,NULL,1,5.00),(29,'Wintermelon','Large',80,54,1,'/uploads/product_images/product_675a5a089e0a03.08280236.png',NULL,NULL,0,5.00),(30,'Wintermelon','Medium',70,46,1,'/uploads/product_images/product_675a5a089e0a03.08280236.png',NULL,NULL,0,5.00),(31,'Okinawa','Large',80,54,1,'/uploads/product_images/product_675a8a69b5d476.46089103.png',NULL,NULL,0,5.00),(32,'Okinawa','Medium',70,46,1,'/uploads/product_images/product_675a8a970505d6.18791711.png',NULL,NULL,0,5.00),(33,'Hokkaido','Large',80,55,1,'/uploads/product_images/product_675a601ef29378.59103931.PNG',NULL,NULL,0,5.00),(34,'Hokkaido','Medium',70,47,1,'/uploads/product_images/product_675a8aaf557662.60160168.png',NULL,NULL,0,5.00),(35,'Matcha','Large',80,55,1,'/uploads/product_images/product_675a63fb8615c4.18567909.png',NULL,NULL,0,5.00),(36,'Matcha','Medium',70,47,1,'/uploads/product_images/product_675a63fb8615c4.18567909.png',NULL,NULL,0,5.00),(39,'Hazelnut','Large',80,56,1,'/uploads/product_images/product_675a6729e88d23.59938506.png',NULL,NULL,0,5.00),(40,'Hazelnut','Medium',70,48,1,'/uploads/product_images/product_675a6729e88d23.59938506.png',NULL,NULL,0,5.00),(42,'Chocolate','Medium',70,46,1,'/uploads/product_images/product_675a798f8da0a4.94163823.png',NULL,NULL,1,5.00),(43,'Cookies and Cream','Large',80,54,1,'/uploads/product_images/product_675a79d4edc045.26841628.',NULL,NULL,0,5.00),(44,'Cookies and Cream','Medium',70,46,1,'/uploads/product_images/product_675a79d4edc045.26841628.',NULL,NULL,0,5.00),(46,'Chocolate','Large',80,55,1,'/uploads/product_images/product_675a7a15081e75.24538850.png',NULL,NULL,0,5.00);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products_ingredient`
--

DROP TABLE IF EXISTS `products_ingredient`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products_ingredient` (
  `id` int NOT NULL AUTO_INCREMENT,
  `productID` int DEFAULT NULL,
  `ingredientsID` int DEFAULT NULL,
  `quantityRequired` decimal(10,0) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=787 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products_ingredient`
--

LOCK TABLES `products_ingredient` WRITE;
/*!40000 ALTER TABLE `products_ingredient` DISABLE KEYS */;
INSERT INTO `products_ingredient` VALUES (1,11,2,499),(2,12,3,500),(3,12,2,250),(4,14,2,344),(5,15,2,299),(6,15,2,312),(7,17,2,567),(8,17,3,345),(9,20,2,123),(10,20,3,562),(11,22,2,123),(12,22,3,423),(16,23,2,231),(17,24,2,123),(18,24,3,534),(28,28,2,40),(29,28,3,40),(30,28,4,100),(31,28,16,50),(32,28,22,1),(33,28,24,1),(34,28,23,1),(35,28,25,5),(36,28,20,30),(479,47,2,30),(480,47,3,40),(481,47,4,100),(482,47,14,10),(483,47,16,50),(484,47,20,40),(485,47,22,1),(486,47,23,1),(487,47,24,1),(488,47,25,5),(509,48,2,20),(510,48,3,30),(511,48,4,85),(512,48,14,10),(513,48,16,40),(514,48,20,30),(515,48,21,1),(516,48,23,1),(517,48,24,1),(518,48,25,5),(576,38,2,20),(577,38,3,30),(578,38,4,85),(579,38,9,10),(580,38,16,40),(581,38,20,30),(582,38,21,1),(583,38,23,1),(584,38,24,1),(585,38,25,1),(586,37,2,30),(587,37,3,40),(588,37,4,100),(589,37,16,50),(590,37,22,1),(591,37,23,1),(592,37,24,1),(593,37,20,40),(594,37,9,10),(595,37,25,5),(614,50,2,1231231),(615,56,2,1231231),(625,25,2,40),(626,25,3,40),(627,25,4,40),(628,25,16,50),(629,25,20,40),(630,25,22,1),(631,25,24,1),(632,25,23,1),(633,25,25,5),(634,26,2,30),(635,26,3,30),(636,26,4,85),(637,26,16,40),(638,26,21,1),(639,26,23,1),(640,26,24,1),(641,26,20,30),(642,26,25,5),(643,29,2,30),(644,29,3,40),(645,29,4,100),(646,29,16,50),(647,29,20,40),(648,29,22,1),(649,29,24,1),(650,29,23,1),(651,29,25,5),(652,30,2,20),(653,30,3,30),(654,30,4,85),(655,30,16,40),(656,30,20,30),(657,30,21,1),(658,30,24,1),(659,30,23,1),(660,30,25,5),(661,31,2,30),(662,31,3,40),(663,31,4,100),(664,31,6,10),(665,31,16,50),(666,31,20,40),(667,31,22,1),(668,31,24,1),(669,31,23,1),(670,31,25,5),(671,32,2,20),(672,32,3,30),(673,32,4,85),(674,32,6,10),(675,32,16,40),(676,32,20,30),(677,32,21,1),(678,32,24,1),(679,32,23,1),(680,32,25,5),(681,33,2,30),(682,33,3,40),(683,33,4,100),(684,33,7,10),(685,33,16,40),(686,33,22,1),(687,33,20,30),(688,33,23,1),(689,33,24,1),(690,33,25,5),(691,34,2,20),(692,34,3,30),(693,34,4,85),(694,34,7,10),(695,34,16,40),(696,34,20,30),(697,34,21,1),(698,34,24,1),(699,34,23,1),(700,34,25,5),(701,35,2,30),(702,35,3,40),(703,35,4,100),(704,35,8,10),(705,35,16,50),(706,35,20,40),(707,35,22,1),(708,35,23,1),(709,35,24,1),(710,35,25,5),(711,36,2,20),(712,36,3,30),(713,36,4,85),(714,36,8,10),(715,36,16,40),(716,36,20,30),(717,36,21,1),(718,36,23,1),(719,36,24,1),(720,36,25,5),(721,39,2,30),(722,39,3,40),(723,39,4,100),(724,39,22,1),(725,39,23,1),(726,39,24,1),(727,39,16,50),(728,39,20,40),(729,39,10,10),(730,39,25,5),(731,40,2,20),(732,40,3,30),(733,40,4,85),(734,40,16,40),(735,40,21,1),(736,40,23,1),(737,40,24,1),(738,40,20,30),(739,40,25,5),(740,40,10,10),(741,42,2,20),(742,42,3,30),(743,42,4,85),(744,42,16,40),(745,42,11,10),(746,42,21,1),(747,42,23,1),(748,42,24,1),(749,42,25,5),(750,42,20,30),(751,43,2,30),(752,43,3,40),(753,43,4,100),(754,43,13,10),(755,43,16,50),(756,43,20,40),(757,43,22,1),(758,43,23,1),(759,43,24,1),(760,43,25,5),(761,44,2,20),(762,44,3,30),(763,44,4,85),(764,44,13,10),(765,44,16,40),(766,44,20,30),(767,44,21,1),(768,44,23,1),(769,44,24,1),(770,44,25,5),(771,46,2,30),(772,46,3,40),(773,46,4,100),(774,46,11,10),(775,46,16,50),(776,46,20,40),(777,46,22,1),(778,46,23,1),(779,46,24,1),(780,46,25,5),(781,58,2,123),(782,58,22,1),(783,60,2,30),(784,60,3,30),(785,59,2,40),(786,59,3,40);
/*!40000 ALTER TABLE `products_ingredient` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `restockorder`
--

DROP TABLE IF EXISTS `restockorder`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `restockorder` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ingredientsName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `branchID` int NOT NULL,
  `ingredientsID` int NOT NULL,
  `requested_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `restock_amount` int NOT NULL,
  `is_accepted` tinyint(1) DEFAULT '0',
  `is_confirmed` tinyint(1) DEFAULT '0',
  `invoice_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `confirmed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_invoice_number` (`invoice_number`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `restockorder`
--

LOCK TABLES `restockorder` WRITE;
/*!40000 ALTER TABLE `restockorder` DISABLE KEYS */;
INSERT INTO `restockorder` VALUES (2,'encoder almar','Chocolate Flavor',245,11,'encoder almar',50,1,1,'INV-20251024-0002','2025-10-24 02:09:22',NULL),(3,'encoder almar','Cookies & Cream Flavor',245,13,'encoder almar',50,1,1,'INV-20251024-0003','2025-10-24 02:09:22',NULL),(4,'encoder almar','Crushed Cookies',245,19,'encoder almar',50,1,1,'INV-20251024-0004','2025-10-24 02:09:22',NULL),(5,'encoder almar','Dark Chocolate Flavor',245,12,'encoder almar',50,1,1,'INV-20251024-0005','2025-10-24 02:09:22',NULL),(6,'encoder almar','Fructose Corn Syrup',245,3,'encoder almar',50,1,1,'INV-20251024-0006','2025-10-24 02:09:22',NULL),(7,'encoder almar','Hazelnut Flavor',245,10,'encoder almar',50,1,1,'INV-20251024-0007','2025-10-24 02:09:22',NULL),(8,'encoder almar','Fructose Corn Syrup',245,3,'encoder almar',50,1,1,'INV-20251024-0008','2025-10-24 02:09:22',NULL),(9,'encoder almar','Hokkaido Flavor',245,7,'17509',50,1,1,'INV-20251024-0009','2025-10-24 02:09:22',NULL),(10,'encoder almar','Loose Tea',245,4,'17509',50,1,1,'INV-20251024-0010','2025-10-24 02:09:22',NULL),(11,'encoder almar','Ice',245,20,'17509',150,1,1,'INV-20251024-0011','2025-10-24 02:09:22',NULL),(12,'encoder almar','Popping Boba',245,18,'17509',50,1,1,'INV-20251024-0012','2025-10-24 02:09:22',NULL),(13,'encoder almar','Caramel Sugar Flavor',245,9,'17509',50,1,1,'INV-20251024-0013','2025-10-24 02:09:22',NULL),(14,'Yheena Mangabat','Chocolate Flavor',245,11,'10895',1000,1,1,'INV-20251024-0014','2025-10-24 02:09:22',NULL),(15,'Aubrey Daraido','Crushed Cookies',245,19,'12664',200,1,1,'INV-20251024-0015','2025-10-24 02:09:22',NULL),(16,'Aubrey Daraido','Loose Tea',245,4,'12664',500,1,1,'INV-20251024-0016','2025-10-24 02:09:22',NULL),(17,'Aubrey Daraido','Loose Tea',245,4,'12664',500,1,1,'INV-20251024-0017','2025-10-24 02:09:22',NULL),(19,'Stock management','Caramel Sugar Flavor',245,9,'14939',1000,1,1,'INV-20251024-0019','2025-10-24 02:09:22','2025-10-24 06:14:57'),(20,'Stock management','Caramel Sugar Flavor',245,9,'14939',1000,0,0,'INV-20251024-0020','2025-10-24 02:09:22',NULL),(21,'Stock management','Caramel Sugar Flavor',245,9,'14939',10,1,1,'INV-20251024-0021','2025-10-24 02:09:22','2025-10-24 16:05:22'),(22,'Stock management','Chocolate Flavor',245,11,'14939',20,0,0,'INV-20251024-0022','2025-10-24 02:09:22',NULL),(23,'noe orano','Caramel Sugar Flavor',8954,9,'31980',10,0,0,'INV-20251024-0023','2025-10-24 02:09:22',NULL),(24,'Vaughn Maco','Cookies & Cream Flavor',245,13,'12194',10,0,0,NULL,'2025-10-24 15:17:46',NULL),(25,'Roselyn Mongado','Loose Tea',245,4,'18044',500,1,1,'INV-20251024-0025','2025-10-25 01:24:47','2025-10-25 01:25:25'),(26,'Roselyn Mongado','Ice',245,20,'18044',500,1,1,'INV-20251024-0026','2025-10-25 01:41:54','2025-10-25 01:43:09'),(27,'Roselyn Mongado','Loose Tea',245,4,'18044',1000,1,1,'INV-20251024-0027','2025-10-25 01:42:02','2025-10-25 01:43:04'),(28,'Roselyn Mongado','Tapioca Pearls',245,16,'18044',1000,1,1,'INV-20251024-0028','2025-10-25 01:42:15','2025-10-25 01:42:58'),(29,'Roselyn Mongado','Fructose Corn Syrup',245,3,'18044',1000,1,1,'INV-20251024-0029','2025-10-25 01:42:39','2025-10-25 01:42:55');
/*!40000 ALTER TABLE `restockorder` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales`
--

DROP TABLE IF EXISTS `sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sales` (
  `id` int NOT NULL AUTO_INCREMENT,
  `branchID` int DEFAULT NULL,
  `receiptID` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `productName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `price` int DEFAULT NULL,
  `initial_price` int DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `totalPrice` decimal(10,2) DEFAULT NULL,
  `sales_date` timestamp NULL DEFAULT NULL,
  `customerName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cashierID` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=228 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales`
--

LOCK TABLES `sales` WRITE;
/*!40000 ALTER TABLE `sales` DISABLE KEYS */;
INSERT INTO `sales` VALUES (46,964,'REC1734408571','Classic Milktea',70,0,1,70.00,'2024-12-16 20:09:31','Walk-in',1055),(47,964,'REC1734408626','Classic Milktea',70,0,1,70.00,'2024-12-16 20:10:26','Walk-in',1055),(48,964,'REC1734408677','Classic Milktea',70,0,1,70.00,'2024-12-16 20:11:17','Walk-in',1055),(50,964,'REC1734408782','Classic Milktea',70,40,6,420.00,'2024-12-16 20:13:02','Walk-in',1055),(51,964,'REC1734414627','Chocolate',70,70,1,70.00,'2024-12-16 21:50:27','Walk-in',1055),(52,964,'REC1734414655','Cookies and Cream',70,70,1,70.00,'2024-12-16 21:50:55','Walk-in',1055),(53,964,'REC1734414673','Cookies and Cream',70,70,1,70.00,'2024-12-16 21:51:13','Walk-in',1055),(54,964,'REC1734414882','Cookies and Cream',70,70,1,70.00,'2024-12-16 21:54:42','Walk-in',1055),(55,964,'REC1734415001','Cookies and Cream',70,70,1,70.00,'2024-12-16 21:56:41','Walk-in',1055),(56,964,'REC1734415038','Chocolate',70,70,1,70.00,'2024-12-16 21:57:18','Walk-in',1055),(57,964,'REC1734415054','Cookies and Cream',70,70,1,70.00,'2024-12-16 21:57:34','Walk-in',1055),(58,964,'REC1734415127','Cookies and Cream',70,70,1,70.00,'2024-12-16 21:58:47','Walk-in',1055),(59,964,'REC1734415171','Cookies and Cream',70,70,1,70.00,'2024-12-16 21:59:31','Walk-in',1055),(60,964,'REC1734417572','Chocolate',70,35,1,70.00,'2024-12-16 22:39:32','Walk-in',1055),(61,964,'REC1734418347','Classic Milktea',70,70,1,70.00,'2024-12-16 22:52:27','Walk-in',16519),(62,964,'REC1734418347','Cookies and Cream',80,80,1,80.00,'2024-12-16 22:52:27','Walk-in',16519),(63,964,'REC1734418469','Okinawa',70,70,1,70.00,'2024-12-16 22:54:29','Walk-in',1055),(64,964,'REC1734420933','Hazelnut',70,70,1,70.00,'2024-12-16 23:35:33','Walk-in',1055),(65,964,'REC1734421017','Cookies and Cream',70,70,1,70.00,'2024-12-16 23:36:57','Walk-in',1055),(66,964,'REC1734421102','Cookies and Cream',70,70,1,70.00,'2024-12-16 23:38:22','Walk-in',1055),(67,964,'REC1734422348','Hokkaido',70,70,1,70.00,'2024-12-16 23:59:08','Walk-in',1055),(68,964,'REC1734422423','Hokkaido',70,70,1,70.00,'2024-12-17 00:00:23','Walk-in',1055),(69,964,'REC1734422533','Wintermelon',70,70,1,70.00,'2024-12-17 00:02:13','Walk-in',1055),(70,964,'REC1734422655','Classic Milktea',70,70,1,70.00,'2024-12-17 00:04:15','Walk-in',1055),(71,964,'REC1734424280','Hokkaido',70,70,1,70.00,'2024-12-17 00:31:20','Walk-in',16519),(72,964,'REC1734424693','Okinawa',70,70,1,70.00,'2024-12-17 00:38:13','Walk-in',16519),(73,8954,'REC1734424835','Wintermelon',70,70,1,70.00,'2024-12-17 00:40:35','Walk-in',16519),(74,964,'REC1734424912','Wintermelon',70,70,1,70.00,'2024-12-17 00:41:52','Walk-in',16519),(75,964,'REC1734425145','Classic Milktea',70,70,1,70.00,'2024-12-17 00:45:45','Walk-in',16519),(76,964,'REC1734426550','Okinawa',80,80,1,80.00,'2024-12-17 01:09:10','Walk-in',16519),(77,964,'REC1734426550','Okinawa',80,80,1,80.00,'2024-12-17 01:09:10','Walk-in',16519),(78,964,'REC1734426564','Okinawa',80,80,1,80.00,'2024-12-17 01:09:24','Walk-in',16519),(79,964,'REC1734426587','Hokkaido',70,70,1,70.00,'2024-12-17 01:09:47','Walk-in',1055),(80,964,'REC1734426587','Cookies and Cream',70,70,1,70.00,'2024-12-17 01:09:47','Walk-in',1055),(81,964,'REC1734426686','Chocolate',90,90,1,90.00,'2024-12-17 01:11:26','Walk-in',16519),(82,964,'REC1734426686','Cookies and Cream',90,90,1,90.00,'2024-12-17 01:11:26','Walk-in',16519),(83,964,'REC1734426686','Hokkaido',70,70,1,70.00,'2024-12-17 01:11:26','Walk-in',16519),(84,964,'REC1734426686','Hokkaido',90,90,1,90.00,'2024-12-17 01:11:26','Walk-in',16519),(85,964,'REC1734426686','Okinawa',90,4,1,90.00,'2024-12-17 01:11:26','Walk-in',16519),(86,964,'REC1734426686','Wintermelon',100,5,1,100.00,'2024-12-17 01:11:26','Walk-in',16519),(87,964,'REC1734426686','Chocolate',100,6,1,100.00,'2024-12-17 01:11:26','Walk-in',16519),(88,964,'REC1734426686','Hazelnut',100,5,1,100.00,'2024-12-17 01:11:26','Walk-in',16519),(89,964,'REC1734426686','Hazelnut',80,4,1,80.00,'2024-12-17 01:11:26','Walk-in',16519),(90,964,'REC1734426686','Matcha',100,5,1,100.00,'2024-12-17 01:11:26','Walk-in',16519),(91,964,'REC1734426686','Hokkaido',100,6,1,100.00,'2024-12-17 01:11:26','Walk-in',16519),(92,964,'REC1734426686','Chocolate',100,100,1,100.00,'2024-12-17 01:11:26','Walk-in',16519),(93,964,'REC1734426686','Classic Milktea',100,6,1,100.00,'2024-12-17 01:11:26','Walk-in',16519),(94,964,'REC1734426686','Classic Milktea',80,8,1,80.00,'2024-12-17 01:11:26','Walk-in',16519),(95,964,'REC1734427469','Hokkaido',90,10,1,90.00,'2024-12-17 01:24:29','Walk-in',16519),(96,964,'REC1734427491','Okinawa',90,13,1,90.00,'2024-12-17 01:24:51','Walk-in',16519),(97,964,'REC1734427531','Cookies and Cream',100,14,1,100.00,'2024-12-17 01:25:31','Walk-in',16519),(98,964,'REC1734427538','Cookies and Cream',100,14,1,100.00,'2024-12-17 01:25:38','Walk-in',16519),(99,964,'REC1734427571','Cookies and Cream',100,14,1,100.00,'2024-12-17 01:26:11','Walk-in',16519),(100,964,'REC1734427851','Cookies and Cream',70,70,1,70.00,'2024-12-17 01:30:51','Walk-in',1055),(101,964,'REC1734427851','Cookies and Cream',80,10,1,80.00,'2024-12-17 01:30:51','Walk-in',1055),(102,964,'REC1734427901','Cookies and Cream',80,13,1,80.00,'2024-12-17 01:31:41','Walk-in',1055),(103,964,'REC1734428011','Chocolate',80,11,1,80.00,'2024-12-17 01:33:31','Walk-in',1055),(104,964,'REC1734428038','Hokkaido',70,18,1,70.00,'2024-12-17 01:33:58','Walk-in',1055),(105,964,'REC1734428103','Hokkaido',70,18,4,280.00,'2024-12-17 01:35:03','Walk-in',1055),(106,964,'REC1734431759','Matcha',80,80,1,80.00,'2024-12-17 02:35:59','Walk-in',16519),(107,964,'REC1734613231','Okinawa',70,70,1,70.00,'2024-12-19 05:00:31','Walk-in',1055),(108,964,'REC1734613326','Hokkaido',70,70,1,70.00,'2024-12-19 05:02:06','Walk-in',1055),(109,964,'REC1734613373','Hokkaido',70,70,1,70.00,'2024-12-19 05:02:53','Walk-in',1055),(110,964,'REC1734620058','Hokkaido',70,70,1,70.00,'2024-12-19 06:54:18','Walk-in',1055),(111,964,'REC1734620121','Hokkaido',70,23,3,210.00,'2024-12-19 06:55:21','Walk-in',1055),(112,964,'REC1734620150','Hokkaido',70,18,4,280.00,'2024-12-19 06:55:50','Walk-in',1055),(113,964,'REC1734620150','Cookies and Cream',70,12,6,420.00,'2024-12-19 06:55:50','Walk-in',1055),(114,964,'REC1734622067','Hokkaido',70,70,1,70.00,'2024-12-19 07:27:47','Walk-in',1055),(115,964,'REC1734622112','Chocolate',70,70,1,70.00,'2024-12-19 07:28:32','Walk-in',1055),(116,964,'REC1734622112','Wintermelon',70,18,4,280.00,'2024-12-19 07:28:32','Walk-in',1055),(117,964,'REC1734622144','Wintermelon',70,70,1,70.00,'2024-12-19 07:29:04','Walk-in',1055),(121,964,'REC1734623351','Wintermelon',70,70,1,70.00,'2024-12-19 07:49:11','Walk-in',1055),(122,964,'REC1734714739','Wintermelon',70,70,1,70.00,'2024-12-20 09:12:19','Walk-in',1055),(123,964,'REC1734714776','Wintermelon',70,70,1,70.00,'2024-12-20 09:12:56','Walk-in',1055),(124,964,'REC1734714831','Wintermelon',80,80,1,80.00,'2024-12-20 09:13:51','Walk-in',1055),(128,964,'REC20241005','Wintermelon',70,70,2,140.00,'2024-10-05 02:30:00','Walk-In',1055),(129,964,'REC20241012','Wintermelon',70,70,2,140.00,'2024-10-12 06:00:00','Walk-In',1055),(130,964,'REC20241020','Wintermelon',70,70,2,140.00,'2024-10-20 03:15:00','Walk-In',1055),(131,964,'REC20241103','Wintermelon',70,70,2,140.00,'2024-11-03 04:45:00','Walk-In',1055),(132,964,'REC20241115','Wintermelon',70,70,2,140.00,'2024-11-15 08:10:00','Walk-In',1055),(133,964,'REC20241125','Wintermelon',70,70,2,140.00,'2024-11-25 01:30:00','Walk-In',1055),(134,964,'REC20241201','Wintermelon',70,70,2,140.00,'2024-12-01 00:00:00','Walk-In',1055),(135,964,'REC20241210','Wintermelon',70,70,2,140.00,'2024-12-10 06:20:00','Walk-In',1055),(136,964,'REC20241222','Wintermelon',70,70,2,140.00,'2024-12-22 09:50:00','Walk-In',1055),(149,964,'REC1734745179','Chocolate',70,70,1,70.00,'2024-12-20 17:39:39','Walk-in',1055),(153,964,'REC1734747556','Chocolate',70,70,1,70.00,'2024-12-20 18:19:16','Walk-in',10895),(154,964,'REC1734748345','Chocolate',70,70,1,70.00,'2024-12-20 18:32:25','Walk-in',16519),(156,245,'REC1734750145','Wintermelon',90,90,1,90.00,'2024-12-20 19:02:25','Walk-in',13395),(157,245,'REC1734751419','Wintermelon',70,35,2,140.00,'2024-12-20 19:23:39','Walk-in',13395),(158,245,'REC1734751419','Hokkaido',90,30,3,270.00,'2024-12-20 19:23:39','Walk-in',13395),(159,245,'REC1734751467','Hokkaido',70,70,1,70.00,'2024-12-20 19:24:27','Walk-in',13395),(160,245,'REC1734751827','Hokkaido',90,18,5,450.00,'2024-12-20 19:30:27','Walk-in',13395),(161,245,'REC1734754813','Cookies and Cream',100,100,1,100.00,'2024-12-20 20:20:13','Walk-in',13395),(162,245,'REC1734754813','Matcha',70,35,2,140.00,'2024-12-20 20:20:13','Walk-in',13395),(163,245,'REC1734754813','Hokkaido',70,70,1,70.00,'2024-12-20 20:20:13','Walk-in',13395),(165,964,'REC1758732503','Classic Milktea',70,70,1,70.00,'2025-09-24 10:48:23','Walk-in',15633),(166,964,'INV20250925173629','Wintermelon',70,70,1,70.00,'2025-09-25 09:36:29','Walk-in',15633),(167,964,'INV20250925173653','Matcha',70,70,1,70.00,'2025-09-25 09:36:53','Walk-in',15633),(168,964,'INV20250925173720','Matcha',70,70,1,70.00,'2025-09-25 09:37:20','Walk-in',15633),(172,245,'INV20250925174207','Okinawa',70,70,1,70.00,'2025-09-25 09:42:07','Walk-in',15633),(173,245,'INV20250925174417','Okinawa',70,70,1,70.00,'2025-09-25 09:44:17','Walk-in',15633),(174,245,'INV20250925174723','Wintermelon',70,70,1,70.00,'2025-09-25 09:47:23','Walk-in',15633),(175,245,'INV20250925174723','Okinawa',70,70,1,70.00,'2025-09-25 09:47:23','Walk-in',15633),(176,245,'INV20251020200024','Hokkaido',90,90,1,90.00,'2025-10-20 12:00:24','Walk-in',15633),(189,245,'INV20251020201558','Hokkaido',70,70,1,70.00,'2025-10-20 12:15:58','Walk-in',15633),(190,245,'INV20251020205646','Hokkaido',80,80,1,80.00,'2025-10-20 12:56:46','Walk-in',15633),(191,NULL,'RET-20251024-1060',NULL,NULL,NULL,NULL,3.00,'2025-10-23 16:00:00',NULL,NULL),(192,NULL,'RET-20251024-0737',NULL,NULL,NULL,NULL,2.00,'2025-10-23 16:00:00',NULL,NULL),(193,NULL,'RET-20251024-3235',NULL,NULL,NULL,NULL,3.00,'2025-10-23 16:00:00',NULL,NULL),(194,NULL,'RET-20251024-7131',NULL,2,NULL,-1,-2.50,'2025-10-23 16:00:00',NULL,NULL),(195,245,'INV20251024185927','Hokkaido',90,90,1,90.00,'2025-10-24 10:59:27','Walk-in',18044),(196,245,'INV20251024190034','Wintermelon',80,80,1,80.00,'2025-10-24 11:00:34','Walk-in',18044),(197,245,'INV20251024190034','Chocolate',80,80,1,80.00,'2025-10-24 11:00:34','Walk-in',18044),(198,245,'INV20251024190201','Wintermelon',70,70,1,70.00,'2025-10-24 11:02:01','Walk-in',18044),(199,245,'INV20251024190201','Chocolate',70,70,1,70.00,'2025-10-24 11:02:01','Walk-in',18044),(200,245,'INV20251024190201','Matcha',70,70,1,70.00,'2025-10-24 11:02:01','Walk-in',18044),(209,245,'INV20251024190353','Chocolate',70,70,1,70.00,'2025-10-24 11:03:53','Walk-in',18044),(210,245,'INV20251024190534','Hokkaido',70,70,1,70.00,'2025-10-24 11:05:34','Walk-in',18044),(211,245,'INV20251025010926','Okinawa',70,70,1,70.00,'2025-10-24 17:09:26','Walk-in',18044),(215,245,'INV20251025012600','Hokkaido',70,70,1,70.00,'2025-10-24 17:26:00','Walk-in',18044),(216,245,'INV20251025012616','Okinawa',70,70,1,70.00,'2025-10-24 17:26:16','Walk-in',18044),(217,245,'INV20251025012642','Okinawa',70,70,1,70.00,'2025-10-24 17:26:42','Walk-in',18044),(218,245,'INV20251025012657','Chocolate',70,70,1,70.00,'2025-10-24 17:26:57','Walk-in',18044),(219,245,'INV20251104155619','Chocolate',80,80,1,80.00,'2025-11-04 07:56:19','Walk-in',18044),(220,245,'INV20251104160803','Wintermelon',90,90,1,90.00,'2025-11-04 08:08:03','Walk-in',18044),(221,245,'INV20251104164107','Okinawa',90,90,1,90.00,'2025-11-04 08:41:07','Walk-in',18044),(222,245,'INV20251107212316','Hokkaido',70,70,1,70.00,'2025-11-07 13:23:16','Walk-in',18044),(223,245,'INV20251107214253','Okinawa',80,80,1,80.00,'2025-11-07 13:42:53','Walk-in',18044),(224,245,'INV20251107221250','Hokkaido',90,90,1,90.00,'2025-11-07 14:12:50','Walk-in',18044),(225,245,'INV20251107223911','Classic Milktea',90,70,1,90.00,'2025-11-07 14:39:11','Walk-in',18044),(226,245,'INV20251108004103','Classic Milktea',90,80,1,90.00,'2025-11-07 16:41:03','Walk-in',18044),(227,245,'INV20251108004137','Classic Milktea',80,80,1,80.00,'2025-11-07 16:41:37','Walk-in',18044);
/*!40000 ALTER TABLE `sales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedules`
--

DROP TABLE IF EXISTS `schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `schedules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `employee_id` int NOT NULL,
  `week_start` date NOT NULL,
  `monday_shift` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'OFF',
  `tuesday_shift` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'OFF',
  `wednesday_shift` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'OFF',
  `thursday_shift` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'OFF',
  `friday_shift` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'OFF',
  `saturday_shift` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'OFF',
  `sunday_shift` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'OFF',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_schedule` (`employee_id`,`week_start`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedules`
--

LOCK TABLES `schedules` WRITE;
/*!40000 ALTER TABLE `schedules` DISABLE KEYS */;
INSERT INTO `schedules` VALUES (1,15633,'2025-10-20','6:00 AM - 2:00 PM','7:00 AM - 3:00 PM','8:00 AM - 4:00 PM','9:00 AM - 5:00 PM','10:00 AM - 6:00 PM','11:00 AM - 7:00 PM','1:00 PM - 9:00 PM','2025-10-22 16:31:35'),(2,38502,'2025-10-20','OFF','OFF','OFF','6:00 AM - 2:00 PM','OFF','OFF','OFF','2025-10-23 14:52:29'),(3,16114,'2025-10-20','2:00 PM - 10:00 PM','OFF','OFF','OFF','OFF','2:00 PM - 10:00 PM','OFF','2025-10-25 07:37:00'),(4,16114,'2025-11-03','OFF','OFF','OFF','OFF','8:00 AM - 4:00 PM','6:00 AM - 2:00 PM','OFF','2025-11-07 07:37:57'),(5,18044,'2025-11-03','OFF','OFF','OFF','OFF','2:00 PM - 10:00 PM','6:00 AM - 2:00 PM','OFF','2025-11-07 07:45:24'),(6,12194,'2025-11-03','OFF','OFF','OFF','OFF','2:00 PM - 10:00 PM','OFF','OFF','2025-11-07 07:45:29'),(7,99999,'2025-11-03','OFF','OFF','OFF','OFF','2:00 PM - 10:00 PM','6:00 AM - 2:00 PM','OFF','2025-11-07 07:45:32'),(8,17192,'2025-11-03','OFF','OFF','OFF','OFF','2:00 PM - 10:00 PM','6:00 AM - 2:00 PM','OFF','2025-11-07 07:45:37'),(9,17642,'2025-11-03','OFF','OFF','OFF','OFF','2:00 PM - 10:00 PM','6:00 AM - 2:00 PM','OFF','2025-11-07 07:45:39');
/*!40000 ALTER TABLE `schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transactions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `branchID` int DEFAULT NULL,
  `cashierID` int DEFAULT NULL,
  `cashierName` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `receiptID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `transactionNumber` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `invoiceNumber` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `customerName` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `totalAmount` decimal(10,2) DEFAULT NULL,
  `salesDate` datetime DEFAULT NULL,
  `orderItems` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transactionNumber` (`transactionNumber`),
  UNIQUE KEY `invoiceNumber` (`invoiceNumber`),
  CONSTRAINT `transactions_chk_1` CHECK (json_valid(`orderItems`))
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES (1,964,15633,NULL,'REC1758814589','TXN1758814589841','INV20250925173629','Walk-in',NULL,'2025-09-25 17:36:29',NULL),(2,964,15633,NULL,'REC1758814613','TXN1758814613358','INV20250925173653','Walk-in',NULL,'2025-09-25 17:36:53',NULL),(3,964,15633,NULL,'REC1758814640','TXN1758814640423','INV20250925173720','Walk-in',NULL,'2025-09-25 17:37:20',NULL),(4,245,15633,NULL,'REC1758814927','TXN1758814927860','INV20250925174207','Walk-in',NULL,'2025-09-25 17:42:07',NULL),(5,245,15633,NULL,'REC1758815057','TXN1758815057279','INV20250925174417','Walk-in',NULL,'2025-09-25 17:44:17',NULL),(6,245,15633,NULL,'REC1758815243','TXN1758815243258','INV20250925174723','Walk-in',NULL,'2025-09-25 17:47:23',NULL),(7,245,15633,NULL,'REC1760983224','TXN1760983224322','INV20251020200024','Walk-in',NULL,'2025-10-20 20:00:24',NULL),(8,245,15633,NULL,'REC1760984158','TXN1760984158124','INV20251020201558','Walk-in',NULL,'2025-10-20 20:15:58',NULL),(9,245,15633,NULL,'REC1760986606','TXN1760986606664','INV20251020205646','Walk-in',NULL,'2025-10-20 20:56:46',NULL),(10,245,18044,NULL,'REC1761325167','TXN1761325167995','INV20251024185927','Walk-in',NULL,'2025-10-24 18:59:27',NULL),(11,245,18044,NULL,'REC1761325234','TXN1761325234407','INV20251024190034','Walk-in',NULL,'2025-10-24 19:00:34',NULL),(12,245,18044,NULL,'REC1761325321','TXN1761325321490','INV20251024190201','Walk-in',NULL,'2025-10-24 19:02:01',NULL),(13,245,18044,NULL,'REC1761325433','TXN1761325433296','INV20251024190353','Walk-in',NULL,'2025-10-24 19:03:53',NULL),(14,245,18044,NULL,'REC1761325534','TXN1761325534701','INV20251024190534','Walk-in',NULL,'2025-10-24 19:05:34',NULL),(15,245,18044,NULL,'REC1761325766','TXN1761325766737','INV20251025010926','Walk-in',NULL,'2025-10-25 01:09:26',NULL),(16,245,18044,NULL,'REC1761326760','TXN1761326760887','INV20251025012600','Walk-in',NULL,'2025-10-25 01:26:00',NULL),(17,245,18044,NULL,'REC1761326776','TXN1761326776600','INV20251025012616','Walk-in',NULL,'2025-10-25 01:26:16',NULL),(18,245,18044,NULL,'REC1761326802','TXN1761326802673','INV20251025012642','Walk-in',NULL,'2025-10-25 01:26:42',NULL),(19,245,18044,NULL,'REC1761326817','TXN1761326817935','INV20251025012657','Walk-in',NULL,'2025-10-25 01:26:57',NULL),(20,245,18044,NULL,'REC1762242979','TXN1762242979696','INV20251104155619','Walk-in',NULL,'2025-11-04 15:56:19',NULL),(21,245,18044,NULL,'REC1762243683','TXN1762243683270','INV20251104160803','Walk-in',NULL,'2025-11-04 16:08:03',NULL),(22,245,18044,NULL,'REC1762245667','TXN1762245667937','INV20251104164107','Walk-in',90.00,'2025-11-04 16:41:07','[{\"productID\":32,\"product\":\"Okinawa\",\"size\":\"medium\",\"price\":90,\"initial_price\":90,\"quantity\":1}]'),(23,245,18044,NULL,'REC1762521796','TXN1762521796746','INV20251107212316','Walk-in',NULL,'2025-11-07 21:23:16',NULL),(24,245,18044,NULL,'REC1762522973','TXN1762522973711','INV20251107214253','Walk-in',NULL,'2025-11-07 21:42:53',NULL),(25,245,18044,NULL,'REC1762524770','TXN1762524770937','INV20251107221250','Walk-in',NULL,'2025-11-07 22:12:50',NULL),(26,245,18044,NULL,'REC1762526351','TXN1762526351487','INV20251107223911','Walk-in',90.00,'2025-11-07 22:39:11','[{\"product\":\"Classic Milktea\",\"size\":\"medium\",\"price\":90,\"quantity\":1}]'),(27,245,18044,NULL,'REC1762533663','TXN1762533663605','INV20251108004103','Walk-in',90.00,'2025-11-08 00:41:03','[{\"product\":\"Classic Milktea\",\"size\":\"large\",\"price\":90,\"quantity\":1}]'),(28,245,18044,NULL,'REC1762533697','TXN1762533697191','INV20251108004137','Walk-in',80.00,'2025-11-08 00:41:37','[{\"product\":\"Classic Milktea\",\"size\":\"large\",\"price\":80,\"quantity\":1}]');
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL,
  `fname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `lname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` enum('admin','production','sales','inventory','hr','finance') COLLATE utf8mb4_general_ci NOT NULL,
  `branch_assignment` int DEFAULT NULL,
  `password_changed` tinyint(1) DEFAULT '0',
  `is_archived` tinyint(1) DEFAULT '0',
  `department_id` int DEFAULT NULL,
  `hourly_rate` decimal(10,2) DEFAULT '0.00',
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `hire_date` date DEFAULT NULL,
  `employee_status` enum('active','training','for_interview','applying','terminated') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (12194,'Vaughn','Maco','maco@lovetea.com','$2y$10$A4meJwL3LCc3FfgzxkAjKuMw9NV8HNGEcJaw20ZbyZnlKsDEzbe4G','inventory',245,1,0,3,75.00,NULL,NULL,'2025-11-07','active'),(14158,'Admin','Yes','admin@lovetea.com','$2y$10$FLDG8KjJyUYsUxarBwe6qeR11CQJr2Y78ykGXXJIupBX3dKv1vNzO','admin',245,1,0,NULL,0.00,NULL,NULL,NULL,'active'),(16114,'Kyle','Estacio','estacio@lovetea.com','$2y$10$EC5WGMW4kOzWg9Kras437OqlMzmUFKblhhJf8vcpiFFiqM6mg/IUm','hr',245,1,0,4,100.00,NULL,NULL,'2025-11-07','active'),(17642,'Arturo','Calma','calma@lovetea.com','$2y$10$HJ/Ztrpx3C5e50jW8v/rU.rlvFdff1Ory.BuP7h9E1jzZDu0IyrzO','finance',245,0,0,5,90.00,NULL,NULL,'2025-11-07','active'),(18044,'Roselyn','Mongado','mongado@lovetea.com','$2y$10$x7X8.XKbsmOYqXiSuyOfVOFJE61m3oTPoCWJs/qmeaU0MLf/6SlsC','sales',245,1,0,1,80.00,NULL,NULL,'2025-11-07','active'),(20002,'HR','Manager','hr@lovetea.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin',245,1,0,3,0.00,'','','2025-10-23','active'),(99999,'Finance','Manager','finance@lovetea.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','finance',245,1,0,5,90.00,NULL,NULL,'2025-11-07','active'),(17192,'Darrem','Garrate','darrem@lovetea.com','$2y$10$cNE93nYGDZFblxEDyEoKmOrxmCpbAAqBujdRVRayKvaE9RCMbwXSS','production',245,1,0,2,70.00,NULL,NULL,'2025-11-07','active');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'milkteashop2'
--

--
-- Dumping routines for database 'milkteashop2'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-08  0:56:26
