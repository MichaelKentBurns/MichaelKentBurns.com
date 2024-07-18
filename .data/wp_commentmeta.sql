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
) ENGINE=InnoDB AUTO_INCREMENT=173 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_commentmeta`
--

LOCK TABLES `wp_commentmeta` WRITE;
/*!40000 ALTER TABLE `wp_commentmeta` DISABLE KEYS */;
INSERT INTO `wp_commentmeta` VALUES (87,58,'_wp_trash_meta_status','0'),(88,58,'_wp_trash_meta_time','1718683048'),(89,60,'_wp_trash_meta_status','0'),(90,60,'_wp_trash_meta_time','1718722878'),(91,59,'_wp_trash_meta_status','0'),(92,59,'_wp_trash_meta_time','1718722888'),(93,65,'_wp_trash_meta_status','0'),(94,65,'_wp_trash_meta_time','1718984510'),(95,64,'_wp_trash_meta_status','0'),(96,64,'_wp_trash_meta_time','1718984539'),(97,63,'_wp_trash_meta_status','0'),(98,63,'_wp_trash_meta_time','1718984539'),(99,62,'_wp_trash_meta_status','0'),(100,62,'_wp_trash_meta_time','1718984539'),(101,61,'_wp_trash_meta_status','0'),(102,61,'_wp_trash_meta_time','1718984539'),(103,66,'_wp_trash_meta_status','0'),(104,66,'_wp_trash_meta_time','1719060691'),(105,69,'_wp_trash_meta_status','0'),(106,69,'_wp_trash_meta_time','1719318711'),(107,68,'_wp_trash_meta_status','0'),(108,68,'_wp_trash_meta_time','1719318727'),(109,67,'_wp_trash_meta_status','0'),(110,67,'_wp_trash_meta_time','1719318727'),(111,72,'_wp_trash_meta_status','0'),(112,72,'_wp_trash_meta_time','1719412668'),(113,71,'_wp_trash_meta_status','0'),(114,71,'_wp_trash_meta_time','1719412668'),(115,70,'_wp_trash_meta_status','0'),(116,70,'_wp_trash_meta_time','1719412668'),(117,74,'_wp_trash_meta_status','0'),(118,74,'_wp_trash_meta_time','1719586974'),(119,73,'_wp_trash_meta_status','0'),(120,73,'_wp_trash_meta_time','1719586974'),(121,75,'_wp_trash_meta_status','0'),(122,75,'_wp_trash_meta_time','1719609944'),(123,77,'_wp_trash_meta_status','0'),(124,77,'_wp_trash_meta_time','1719673794'),(125,78,'_wp_trash_meta_status','0'),(126,78,'_wp_trash_meta_time','1719673807'),(127,76,'_wp_trash_meta_status','0'),(128,76,'_wp_trash_meta_time','1719673807'),(129,80,'_wp_trash_meta_status','0'),(130,80,'_wp_trash_meta_time','1719774196'),(131,87,'_wp_trash_meta_status','0'),(132,87,'_wp_trash_meta_time','1719872545'),(133,86,'_wp_trash_meta_status','0'),(134,86,'_wp_trash_meta_time','1719872559'),(135,88,'_wp_trash_meta_status','0'),(136,88,'_wp_trash_meta_time','1720005674'),(137,91,'_wp_trash_meta_status','0'),(138,91,'_wp_trash_meta_time','1720034492'),(139,101,'_wp_trash_meta_status','0'),(140,101,'_wp_trash_meta_time','1720112715'),(141,102,'_wp_trash_meta_status','0'),(142,102,'_wp_trash_meta_time','1720112723'),(143,107,'_wp_trash_meta_status','0'),(144,107,'_wp_trash_meta_time','1720268122'),(145,110,'_wp_trash_meta_status','0'),(146,110,'_wp_trash_meta_time','1720458698'),(147,109,'_wp_trash_meta_status','0'),(148,109,'_wp_trash_meta_time','1720458749'),(149,119,'_wp_trash_meta_status','0'),(150,119,'_wp_trash_meta_time','1720532472'),(151,118,'_wp_trash_meta_status','0'),(152,118,'_wp_trash_meta_time','1720532475'),(153,117,'_wp_trash_meta_status','0'),(154,117,'_wp_trash_meta_time','1720532478'),(155,116,'_wp_trash_meta_status','0'),(156,116,'_wp_trash_meta_time','1720532502'),(157,120,'_wp_trash_meta_status','0'),(158,120,'_wp_trash_meta_time','1720568958'),(159,122,'_wp_trash_meta_status','0'),(160,122,'_wp_trash_meta_time','1720724897'),(161,133,'_wp_trash_meta_status','0'),(162,133,'_wp_trash_meta_time','1720984380'),(163,132,'_wp_trash_meta_status','0'),(164,132,'_wp_trash_meta_time','1720984380'),(165,129,'_wp_trash_meta_status','0'),(166,129,'_wp_trash_meta_time','1720984380'),(167,136,'_wp_trash_meta_status','0'),(168,136,'_wp_trash_meta_time','1721267661'),(169,140,'_wp_trash_meta_status','0'),(170,140,'_wp_trash_meta_time','1721299549'),(171,141,'_wp_trash_meta_status','0'),(172,141,'_wp_trash_meta_time','1721299551');
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

-- Dump completed on 2024-07-18 16:36:58
