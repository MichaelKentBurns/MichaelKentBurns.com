-- MariaDB dump 10.19  Distrib 10.4.34-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: hynxgrbyjv
-- ------------------------------------------------------
-- Server version	10.4.34-MariaDB-1:10.4.34+maria~deb10-log

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
-- Table structure for table `wp_commentmeta`
--

DROP TABLE IF EXISTS `wp_commentmeta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_commentmeta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `comment_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `meta_key` varchar(255) DEFAULT NULL,
  `meta_value` longtext DEFAULT NULL,
  PRIMARY KEY (`meta_id`),
  KEY `comment_id` (`comment_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB AUTO_INCREMENT=131 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_commentmeta`
--

LOCK TABLES `wp_commentmeta` WRITE;
/*!40000 ALTER TABLE `wp_commentmeta` DISABLE KEYS */;
INSERT INTO `wp_commentmeta` VALUES (39,35,'_wp_trash_meta_status','0'),(40,35,'_wp_trash_meta_time','1717249937'),(41,34,'_wp_trash_meta_status','0'),(42,34,'_wp_trash_meta_time','1717249976'),(43,33,'_wp_trash_meta_status','0'),(44,33,'_wp_trash_meta_time','1717249976'),(45,32,'_wp_trash_meta_status','0'),(46,32,'_wp_trash_meta_time','1717249976'),(47,8,'_wp_trash_meta_status','1'),(48,8,'_wp_trash_meta_time','1717250004'),(49,7,'_wp_trash_meta_status','1'),(50,7,'_wp_trash_meta_time','1717250006'),(51,37,'_wp_trash_meta_status','0'),(52,37,'_wp_trash_meta_time','1717418108'),(53,36,'_wp_trash_meta_status','0'),(54,36,'_wp_trash_meta_time','1717418112'),(55,42,'_wp_trash_meta_status','0'),(56,42,'_wp_trash_meta_time','1717688634'),(57,41,'_wp_trash_meta_status','0'),(58,41,'_wp_trash_meta_time','1717688703'),(59,44,'_wp_trash_meta_status','0'),(60,44,'_wp_trash_meta_time','1717849004'),(61,47,'_wp_trash_meta_status','0'),(62,47,'_wp_trash_meta_time','1717945904'),(63,46,'_wp_trash_meta_status','0'),(64,46,'_wp_trash_meta_time','1717945904'),(65,45,'_wp_trash_meta_status','0'),(66,45,'_wp_trash_meta_time','1717945904'),(67,50,'_wp_trash_meta_status','0'),(68,50,'_wp_trash_meta_time','1718291082'),(69,51,'_wp_trash_meta_status','0'),(70,51,'_wp_trash_meta_time','1718291100'),(71,49,'_wp_trash_meta_status','0'),(72,49,'_wp_trash_meta_time','1718291100'),(73,48,'_wp_trash_meta_status','0'),(74,48,'_wp_trash_meta_time','1718291100'),(75,55,'_wp_trash_meta_status','0'),(76,55,'_wp_trash_meta_time','1718478739'),(77,54,'_wp_trash_meta_status','0'),(78,54,'_wp_trash_meta_time','1718478739'),(79,53,'_wp_trash_meta_status','0'),(80,53,'_wp_trash_meta_time','1718478739'),(81,52,'_wp_trash_meta_status','0'),(82,52,'_wp_trash_meta_time','1718478739'),(83,57,'_wp_trash_meta_status','0'),(84,57,'_wp_trash_meta_time','1718625924'),(85,56,'_wp_trash_meta_status','0'),(86,56,'_wp_trash_meta_time','1718625938'),(87,58,'_wp_trash_meta_status','0'),(88,58,'_wp_trash_meta_time','1718683048'),(89,60,'_wp_trash_meta_status','0'),(90,60,'_wp_trash_meta_time','1718722878'),(91,59,'_wp_trash_meta_status','0'),(92,59,'_wp_trash_meta_time','1718722888'),(93,65,'_wp_trash_meta_status','0'),(94,65,'_wp_trash_meta_time','1718984510'),(95,64,'_wp_trash_meta_status','0'),(96,64,'_wp_trash_meta_time','1718984539'),(97,63,'_wp_trash_meta_status','0'),(98,63,'_wp_trash_meta_time','1718984539'),(99,62,'_wp_trash_meta_status','0'),(100,62,'_wp_trash_meta_time','1718984539'),(101,61,'_wp_trash_meta_status','0'),(102,61,'_wp_trash_meta_time','1718984539'),(103,66,'_wp_trash_meta_status','0'),(104,66,'_wp_trash_meta_time','1719060691'),(105,69,'_wp_trash_meta_status','0'),(106,69,'_wp_trash_meta_time','1719318711'),(107,68,'_wp_trash_meta_status','0'),(108,68,'_wp_trash_meta_time','1719318727'),(109,67,'_wp_trash_meta_status','0'),(110,67,'_wp_trash_meta_time','1719318727'),(111,72,'_wp_trash_meta_status','0'),(112,72,'_wp_trash_meta_time','1719412668'),(113,71,'_wp_trash_meta_status','0'),(114,71,'_wp_trash_meta_time','1719412668'),(115,70,'_wp_trash_meta_status','0'),(116,70,'_wp_trash_meta_time','1719412668'),(117,74,'_wp_trash_meta_status','0'),(118,74,'_wp_trash_meta_time','1719586974'),(119,73,'_wp_trash_meta_status','0'),(120,73,'_wp_trash_meta_time','1719586974'),(121,75,'_wp_trash_meta_status','0'),(122,75,'_wp_trash_meta_time','1719609944'),(123,77,'_wp_trash_meta_status','0'),(124,77,'_wp_trash_meta_time','1719673794'),(125,78,'_wp_trash_meta_status','0'),(126,78,'_wp_trash_meta_time','1719673807'),(127,76,'_wp_trash_meta_status','0'),(128,76,'_wp_trash_meta_time','1719673807'),(129,80,'_wp_trash_meta_status','0'),(130,80,'_wp_trash_meta_time','1719774196');
/*!40000 ALTER TABLE `wp_commentmeta` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-07-01 21:32:00
