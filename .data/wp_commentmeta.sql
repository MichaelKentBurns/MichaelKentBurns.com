/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.5.26-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: hynxgrbyjv
-- ------------------------------------------------------
-- Server version	10.5.26-MariaDB-deb11-log

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
) ENGINE=InnoDB AUTO_INCREMENT=411 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_commentmeta`
--

LOCK TABLES `wp_commentmeta` WRITE;
/*!40000 ALTER TABLE `wp_commentmeta` DISABLE KEYS */;
INSERT INTO `wp_commentmeta` VALUES (319,226,'_wp_trash_meta_status','0'),(320,226,'_wp_trash_meta_time','1725483644'),(321,225,'_wp_trash_meta_status','0'),(322,225,'_wp_trash_meta_time','1725483644'),(323,224,'_wp_trash_meta_status','0'),(324,224,'_wp_trash_meta_time','1725483644'),(325,223,'_wp_trash_meta_status','0'),(326,223,'_wp_trash_meta_time','1725483644'),(327,222,'_wp_trash_meta_status','0'),(328,222,'_wp_trash_meta_time','1725483644'),(329,229,'_wp_trash_meta_status','0'),(330,229,'_wp_trash_meta_time','1725888960'),(331,228,'_wp_trash_meta_status','0'),(332,228,'_wp_trash_meta_time','1725888960'),(333,227,'_wp_trash_meta_status','0'),(334,227,'_wp_trash_meta_time','1725888960'),(335,245,'_wp_trash_meta_status','0'),(336,245,'_wp_trash_meta_time','1726935350'),(337,244,'_wp_trash_meta_status','0'),(338,244,'_wp_trash_meta_time','1726935350'),(339,243,'_wp_trash_meta_status','0'),(340,243,'_wp_trash_meta_time','1726935350'),(341,242,'_wp_trash_meta_status','0'),(342,242,'_wp_trash_meta_time','1726935350'),(343,241,'_wp_trash_meta_status','0'),(344,241,'_wp_trash_meta_time','1726935350'),(345,240,'_wp_trash_meta_status','0'),(346,240,'_wp_trash_meta_time','1726935350'),(347,239,'_wp_trash_meta_status','0'),(348,239,'_wp_trash_meta_time','1726935350'),(349,238,'_wp_trash_meta_status','0'),(350,238,'_wp_trash_meta_time','1726935350'),(351,237,'_wp_trash_meta_status','0'),(352,237,'_wp_trash_meta_time','1726935350'),(353,236,'_wp_trash_meta_status','0'),(354,236,'_wp_trash_meta_time','1726935350'),(355,235,'_wp_trash_meta_status','0'),(356,235,'_wp_trash_meta_time','1726935350'),(357,234,'_wp_trash_meta_status','0'),(358,234,'_wp_trash_meta_time','1726935350'),(359,233,'_wp_trash_meta_status','0'),(360,233,'_wp_trash_meta_time','1726935350'),(361,232,'_wp_trash_meta_status','0'),(362,232,'_wp_trash_meta_time','1726935350'),(363,231,'_wp_trash_meta_status','0'),(364,231,'_wp_trash_meta_time','1726935350'),(365,230,'_wp_trash_meta_status','0'),(366,230,'_wp_trash_meta_time','1726935350'),(367,265,'_wp_trash_meta_status','0'),(368,265,'_wp_trash_meta_time','1727823991'),(369,264,'_wp_trash_meta_status','0'),(370,264,'_wp_trash_meta_time','1727823991'),(371,263,'_wp_trash_meta_status','0'),(372,263,'_wp_trash_meta_time','1727823991'),(373,262,'_wp_trash_meta_status','0'),(374,262,'_wp_trash_meta_time','1727823992'),(375,261,'_wp_trash_meta_status','0'),(376,261,'_wp_trash_meta_time','1727823992'),(377,260,'_wp_trash_meta_status','0'),(378,260,'_wp_trash_meta_time','1727823992'),(379,259,'_wp_trash_meta_status','0'),(380,259,'_wp_trash_meta_time','1727823992'),(381,258,'_wp_trash_meta_status','0'),(382,258,'_wp_trash_meta_time','1727823992'),(383,257,'_wp_trash_meta_status','0'),(384,257,'_wp_trash_meta_time','1727823992'),(385,256,'_wp_trash_meta_status','0'),(386,256,'_wp_trash_meta_time','1727823992'),(387,255,'_wp_trash_meta_status','0'),(388,255,'_wp_trash_meta_time','1727823992'),(389,254,'_wp_trash_meta_status','0'),(390,254,'_wp_trash_meta_time','1727823992'),(391,253,'_wp_trash_meta_status','0'),(392,253,'_wp_trash_meta_time','1727823992'),(393,252,'_wp_trash_meta_status','0'),(394,252,'_wp_trash_meta_time','1727823992'),(395,251,'_wp_trash_meta_status','0'),(396,251,'_wp_trash_meta_time','1727823992'),(397,250,'_wp_trash_meta_status','0'),(398,250,'_wp_trash_meta_time','1727823992'),(399,249,'_wp_trash_meta_status','0'),(400,249,'_wp_trash_meta_time','1727823992'),(401,248,'_wp_trash_meta_status','0'),(402,248,'_wp_trash_meta_time','1727823992'),(403,247,'_wp_trash_meta_status','0'),(404,247,'_wp_trash_meta_time','1727823992'),(405,246,'_wp_trash_meta_status','0'),(406,246,'_wp_trash_meta_time','1727823992'),(407,267,'_wp_trash_meta_status','0'),(408,267,'_wp_trash_meta_time','1727974606'),(409,266,'_wp_trash_meta_status','0'),(410,266,'_wp_trash_meta_time','1727974610');
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

-- Dump completed on 2024-10-03 17:10:33
