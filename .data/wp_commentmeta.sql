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
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_commentmeta`
--

LOCK TABLES `wp_commentmeta` WRITE;
/*!40000 ALTER TABLE `wp_commentmeta` DISABLE KEYS */;
INSERT INTO `wp_commentmeta` VALUES (15,17,'_wp_trash_meta_status','0'),(16,17,'_wp_trash_meta_time','1714822838'),(17,16,'_wp_trash_meta_status','0'),(18,16,'_wp_trash_meta_time','1714822839'),(19,15,'_wp_trash_meta_status','0'),(20,15,'_wp_trash_meta_time','1714822841'),(21,18,'_wp_trash_meta_status','0'),(22,18,'_wp_trash_meta_time','1715079274'),(23,19,'_wp_trash_meta_status','0'),(24,19,'_wp_trash_meta_time','1715079330'),(25,21,'_wp_trash_meta_status','0'),(26,21,'_wp_trash_meta_time','1715942180'),(27,23,'_wp_trash_meta_status','0'),(28,23,'_wp_trash_meta_time','1715987085'),(29,25,'_wp_trash_meta_status','1'),(30,25,'_wp_trash_meta_time','1716385774'),(31,28,'_wp_trash_meta_status','0'),(32,28,'_wp_trash_meta_time','1716590456'),(33,29,'_wp_trash_meta_status','0'),(34,29,'_wp_trash_meta_time','1716675017'),(35,30,'_wp_trash_meta_status','0'),(36,30,'_wp_trash_meta_time','1716809077'),(37,31,'_wp_trash_meta_status','0'),(38,31,'_wp_trash_meta_time','1716941750'),(39,35,'_wp_trash_meta_status','0'),(40,35,'_wp_trash_meta_time','1717249937'),(41,34,'_wp_trash_meta_status','0'),(42,34,'_wp_trash_meta_time','1717249976'),(43,33,'_wp_trash_meta_status','0'),(44,33,'_wp_trash_meta_time','1717249976'),(45,32,'_wp_trash_meta_status','0'),(46,32,'_wp_trash_meta_time','1717249976'),(47,8,'_wp_trash_meta_status','1'),(48,8,'_wp_trash_meta_time','1717250004'),(49,7,'_wp_trash_meta_status','1'),(50,7,'_wp_trash_meta_time','1717250006');
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

-- Dump completed on 2024-06-02 21:50:47
