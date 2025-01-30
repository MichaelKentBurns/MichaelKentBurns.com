/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.5.27-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: hynxgrbyjv
-- ------------------------------------------------------
-- Server version	10.5.27-MariaDB-deb11-log

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
) ENGINE=InnoDB AUTO_INCREMENT=964 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_commentmeta`
--

LOCK TABLES `wp_commentmeta` WRITE;
/*!40000 ALTER TABLE `wp_commentmeta` DISABLE KEYS */;
INSERT INTO `wp_commentmeta` VALUES (849,514,'_wp_trash_meta_status','0'),(850,514,'_wp_trash_meta_time','1735683823'),(851,513,'_wp_trash_meta_status','0'),(852,513,'_wp_trash_meta_time','1735683823'),(853,512,'_wp_trash_meta_status','0'),(854,512,'_wp_trash_meta_time','1735683823'),(855,511,'_wp_trash_meta_status','0'),(856,511,'_wp_trash_meta_time','1735683823'),(857,510,'_wp_trash_meta_status','0'),(858,510,'_wp_trash_meta_time','1735683823'),(859,509,'_wp_trash_meta_status','0'),(860,509,'_wp_trash_meta_time','1735683823'),(861,508,'_wp_trash_meta_status','0'),(862,508,'_wp_trash_meta_time','1735683823'),(863,507,'_wp_trash_meta_status','0'),(864,507,'_wp_trash_meta_time','1735683823'),(865,506,'_wp_trash_meta_status','0'),(866,506,'_wp_trash_meta_time','1735683823'),(867,505,'_wp_trash_meta_status','0'),(868,505,'_wp_trash_meta_time','1735683823'),(869,504,'_wp_trash_meta_status','0'),(870,504,'_wp_trash_meta_time','1735683823'),(871,503,'_wp_trash_meta_status','0'),(872,503,'_wp_trash_meta_time','1735683823'),(873,502,'_wp_trash_meta_status','0'),(874,502,'_wp_trash_meta_time','1735683823'),(875,501,'_wp_trash_meta_status','0'),(876,501,'_wp_trash_meta_time','1735683823'),(877,500,'_wp_trash_meta_status','0'),(878,500,'_wp_trash_meta_time','1735683823'),(879,499,'_wp_trash_meta_status','0'),(880,499,'_wp_trash_meta_time','1735683823'),(881,498,'_wp_trash_meta_status','0'),(882,498,'_wp_trash_meta_time','1735683823'),(883,497,'_wp_trash_meta_status','0'),(884,497,'_wp_trash_meta_time','1735683823'),(885,496,'_wp_trash_meta_status','0'),(886,496,'_wp_trash_meta_time','1735683823'),(887,495,'_wp_trash_meta_status','0'),(888,495,'_wp_trash_meta_time','1735683823'),(889,494,'_wp_trash_meta_status','0'),(890,494,'_wp_trash_meta_time','1735683823'),(891,493,'_wp_trash_meta_status','0'),(892,493,'_wp_trash_meta_time','1735683823'),(893,492,'_wp_trash_meta_status','0'),(894,492,'_wp_trash_meta_time','1735683823'),(895,491,'_wp_trash_meta_status','0'),(896,491,'_wp_trash_meta_time','1735683823'),(897,490,'_wp_trash_meta_status','0'),(898,490,'_wp_trash_meta_time','1735683823'),(899,489,'_wp_trash_meta_status','0'),(900,489,'_wp_trash_meta_time','1735683823'),(901,488,'_wp_trash_meta_status','0'),(902,488,'_wp_trash_meta_time','1735683823'),(903,487,'_wp_trash_meta_status','0'),(904,487,'_wp_trash_meta_time','1735683823'),(905,486,'_wp_trash_meta_status','0'),(906,486,'_wp_trash_meta_time','1735683823'),(907,485,'_wp_trash_meta_status','0'),(908,485,'_wp_trash_meta_time','1735683823'),(909,484,'_wp_trash_meta_status','0'),(910,484,'_wp_trash_meta_time','1735683823'),(911,483,'_wp_trash_meta_status','0'),(912,483,'_wp_trash_meta_time','1735683823'),(913,482,'_wp_trash_meta_status','0'),(914,482,'_wp_trash_meta_time','1735683823'),(915,481,'_wp_trash_meta_status','0'),(916,481,'_wp_trash_meta_time','1735683823'),(917,480,'_wp_trash_meta_status','0'),(918,480,'_wp_trash_meta_time','1735683823'),(919,479,'_wp_trash_meta_status','0'),(920,479,'_wp_trash_meta_time','1735683823'),(921,478,'_wp_trash_meta_status','0'),(922,478,'_wp_trash_meta_time','1735683823'),(923,477,'_wp_trash_meta_status','0'),(924,477,'_wp_trash_meta_time','1735683823'),(925,476,'_wp_trash_meta_status','0'),(926,476,'_wp_trash_meta_time','1735683823'),(927,475,'_wp_trash_meta_status','0'),(928,475,'_wp_trash_meta_time','1735683823'),(929,474,'_wp_trash_meta_status','0'),(930,474,'_wp_trash_meta_time','1735683823'),(931,473,'_wp_trash_meta_status','0'),(932,473,'_wp_trash_meta_time','1735683823'),(933,472,'_wp_trash_meta_status','0'),(934,472,'_wp_trash_meta_time','1735683823'),(935,471,'_wp_trash_meta_status','0'),(936,471,'_wp_trash_meta_time','1735683823'),(937,470,'_wp_trash_meta_status','0'),(938,470,'_wp_trash_meta_time','1735683823'),(939,449,'_wp_trash_meta_status','0'),(940,449,'_wp_trash_meta_time','1735683823'),(941,448,'_wp_trash_meta_status','0'),(942,448,'_wp_trash_meta_time','1735683823'),(943,447,'_wp_trash_meta_status','0'),(944,447,'_wp_trash_meta_time','1735683823');
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

-- Dump completed on 2025-01-30 17:30:01
