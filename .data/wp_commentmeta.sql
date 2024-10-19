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
) ENGINE=InnoDB AUTO_INCREMENT=479 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_commentmeta`
--

LOCK TABLES `wp_commentmeta` WRITE;
/*!40000 ALTER TABLE `wp_commentmeta` DISABLE KEYS */;
INSERT INTO `wp_commentmeta` VALUES (335,245,'_wp_trash_meta_status','0'),(336,245,'_wp_trash_meta_time','1726935350'),(337,244,'_wp_trash_meta_status','0'),(338,244,'_wp_trash_meta_time','1726935350'),(339,243,'_wp_trash_meta_status','0'),(340,243,'_wp_trash_meta_time','1726935350'),(341,242,'_wp_trash_meta_status','0'),(342,242,'_wp_trash_meta_time','1726935350'),(343,241,'_wp_trash_meta_status','0'),(344,241,'_wp_trash_meta_time','1726935350'),(345,240,'_wp_trash_meta_status','0'),(346,240,'_wp_trash_meta_time','1726935350'),(347,239,'_wp_trash_meta_status','0'),(348,239,'_wp_trash_meta_time','1726935350'),(349,238,'_wp_trash_meta_status','0'),(350,238,'_wp_trash_meta_time','1726935350'),(351,237,'_wp_trash_meta_status','0'),(352,237,'_wp_trash_meta_time','1726935350'),(353,236,'_wp_trash_meta_status','0'),(354,236,'_wp_trash_meta_time','1726935350'),(355,235,'_wp_trash_meta_status','0'),(356,235,'_wp_trash_meta_time','1726935350'),(357,234,'_wp_trash_meta_status','0'),(358,234,'_wp_trash_meta_time','1726935350'),(359,233,'_wp_trash_meta_status','0'),(360,233,'_wp_trash_meta_time','1726935350'),(361,232,'_wp_trash_meta_status','0'),(362,232,'_wp_trash_meta_time','1726935350'),(363,231,'_wp_trash_meta_status','0'),(364,231,'_wp_trash_meta_time','1726935350'),(365,230,'_wp_trash_meta_status','0'),(366,230,'_wp_trash_meta_time','1726935350'),(367,265,'_wp_trash_meta_status','0'),(368,265,'_wp_trash_meta_time','1727823991'),(369,264,'_wp_trash_meta_status','0'),(370,264,'_wp_trash_meta_time','1727823991'),(371,263,'_wp_trash_meta_status','0'),(372,263,'_wp_trash_meta_time','1727823991'),(373,262,'_wp_trash_meta_status','0'),(374,262,'_wp_trash_meta_time','1727823992'),(375,261,'_wp_trash_meta_status','0'),(376,261,'_wp_trash_meta_time','1727823992'),(377,260,'_wp_trash_meta_status','0'),(378,260,'_wp_trash_meta_time','1727823992'),(379,259,'_wp_trash_meta_status','0'),(380,259,'_wp_trash_meta_time','1727823992'),(381,258,'_wp_trash_meta_status','0'),(382,258,'_wp_trash_meta_time','1727823992'),(383,257,'_wp_trash_meta_status','0'),(384,257,'_wp_trash_meta_time','1727823992'),(385,256,'_wp_trash_meta_status','0'),(386,256,'_wp_trash_meta_time','1727823992'),(387,255,'_wp_trash_meta_status','0'),(388,255,'_wp_trash_meta_time','1727823992'),(389,254,'_wp_trash_meta_status','0'),(390,254,'_wp_trash_meta_time','1727823992'),(391,253,'_wp_trash_meta_status','0'),(392,253,'_wp_trash_meta_time','1727823992'),(393,252,'_wp_trash_meta_status','0'),(394,252,'_wp_trash_meta_time','1727823992'),(395,251,'_wp_trash_meta_status','0'),(396,251,'_wp_trash_meta_time','1727823992'),(397,250,'_wp_trash_meta_status','0'),(398,250,'_wp_trash_meta_time','1727823992'),(399,249,'_wp_trash_meta_status','0'),(400,249,'_wp_trash_meta_time','1727823992'),(401,248,'_wp_trash_meta_status','0'),(402,248,'_wp_trash_meta_time','1727823992'),(403,247,'_wp_trash_meta_status','0'),(404,247,'_wp_trash_meta_time','1727823992'),(405,246,'_wp_trash_meta_status','0'),(406,246,'_wp_trash_meta_time','1727823992'),(407,267,'_wp_trash_meta_status','0'),(408,267,'_wp_trash_meta_time','1727974606'),(409,266,'_wp_trash_meta_status','0'),(410,266,'_wp_trash_meta_time','1727974610'),(411,277,'_wp_trash_meta_status','0'),(412,277,'_wp_trash_meta_time','1728741218'),(413,276,'_wp_trash_meta_status','0'),(414,276,'_wp_trash_meta_time','1728741218'),(415,275,'_wp_trash_meta_status','0'),(416,275,'_wp_trash_meta_time','1728741218'),(417,274,'_wp_trash_meta_status','0'),(418,274,'_wp_trash_meta_time','1728741218'),(419,273,'_wp_trash_meta_status','0'),(420,273,'_wp_trash_meta_time','1728741218'),(421,272,'_wp_trash_meta_status','0'),(422,272,'_wp_trash_meta_time','1728741218'),(423,271,'_wp_trash_meta_status','0'),(424,271,'_wp_trash_meta_time','1728741218'),(425,270,'_wp_trash_meta_status','0'),(426,270,'_wp_trash_meta_time','1728741218'),(427,269,'_wp_trash_meta_status','0'),(428,269,'_wp_trash_meta_time','1728741218'),(429,268,'_wp_trash_meta_status','0'),(430,268,'_wp_trash_meta_time','1728741218'),(431,282,'_wp_trash_meta_status','0'),(432,282,'_wp_trash_meta_time','1728964130'),(433,280,'_wp_trash_meta_status','0'),(434,280,'_wp_trash_meta_time','1728964149'),(435,279,'_wp_trash_meta_status','0'),(436,279,'_wp_trash_meta_time','1728964160'),(437,278,'_wp_trash_meta_status','0'),(438,278,'_wp_trash_meta_time','1728964173'),(439,301,'_wp_trash_meta_status','0'),(440,301,'_wp_trash_meta_time','1729341691'),(441,300,'_wp_trash_meta_status','0'),(442,300,'_wp_trash_meta_time','1729341691'),(443,299,'_wp_trash_meta_status','0'),(444,299,'_wp_trash_meta_time','1729341691'),(445,298,'_wp_trash_meta_status','0'),(446,298,'_wp_trash_meta_time','1729341691'),(447,297,'_wp_trash_meta_status','0'),(448,297,'_wp_trash_meta_time','1729341691'),(449,296,'_wp_trash_meta_status','0'),(450,296,'_wp_trash_meta_time','1729341691'),(451,295,'_wp_trash_meta_status','0'),(452,295,'_wp_trash_meta_time','1729341691'),(453,294,'_wp_trash_meta_status','0'),(454,294,'_wp_trash_meta_time','1729341691'),(455,293,'_wp_trash_meta_status','0'),(456,293,'_wp_trash_meta_time','1729341691'),(457,292,'_wp_trash_meta_status','0'),(458,292,'_wp_trash_meta_time','1729341691'),(459,291,'_wp_trash_meta_status','0'),(460,291,'_wp_trash_meta_time','1729341691'),(461,290,'_wp_trash_meta_status','0'),(462,290,'_wp_trash_meta_time','1729341691'),(463,289,'_wp_trash_meta_status','0'),(464,289,'_wp_trash_meta_time','1729341691'),(465,288,'_wp_trash_meta_status','0'),(466,288,'_wp_trash_meta_time','1729341691'),(467,287,'_wp_trash_meta_status','0'),(468,287,'_wp_trash_meta_time','1729341691'),(469,286,'_wp_trash_meta_status','0'),(470,286,'_wp_trash_meta_time','1729341691'),(471,285,'_wp_trash_meta_status','0'),(472,285,'_wp_trash_meta_time','1729341691'),(473,284,'_wp_trash_meta_status','0'),(474,284,'_wp_trash_meta_time','1729341691'),(475,283,'_wp_trash_meta_status','0'),(476,283,'_wp_trash_meta_time','1729341691'),(477,281,'_wp_trash_meta_status','0'),(478,281,'_wp_trash_meta_time','1729341691');
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

-- Dump completed on 2024-10-19 12:46:20
