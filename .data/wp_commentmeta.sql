/*!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.5.25-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: hynxgrbyjv
-- ------------------------------------------------------
-- Server version	10.5.25-MariaDB-deb11-log

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
) ENGINE=InnoDB AUTO_INCREMENT=319 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_commentmeta`
--

LOCK TABLES `wp_commentmeta` WRITE;
/*!40000 ALTER TABLE `wp_commentmeta` DISABLE KEYS */;
INSERT INTO `wp_commentmeta` VALUES (205,169,'_wp_trash_meta_status','0'),(206,169,'_wp_trash_meta_time','1722913390'),(207,168,'_wp_trash_meta_status','0'),(208,168,'_wp_trash_meta_time','1722913391'),(209,167,'_wp_trash_meta_status','0'),(210,167,'_wp_trash_meta_time','1722913391'),(211,166,'_wp_trash_meta_status','0'),(212,166,'_wp_trash_meta_time','1722913391'),(213,165,'_wp_trash_meta_status','0'),(214,165,'_wp_trash_meta_time','1722913391'),(215,164,'_wp_trash_meta_status','0'),(216,164,'_wp_trash_meta_time','1722913391'),(217,163,'_wp_trash_meta_status','0'),(218,163,'_wp_trash_meta_time','1722913391'),(219,171,'_wp_trash_meta_status','0'),(220,171,'_wp_trash_meta_time','1723032090'),(221,170,'_wp_trash_meta_status','0'),(222,170,'_wp_trash_meta_time','1723032103'),(223,174,'_wp_trash_meta_status','0'),(224,174,'_wp_trash_meta_time','1723423787'),(225,173,'_wp_trash_meta_status','0'),(226,173,'_wp_trash_meta_time','1723423788'),(227,172,'_wp_trash_meta_status','0'),(228,172,'_wp_trash_meta_time','1723423790'),(229,177,'_wp_trash_meta_status','0'),(230,177,'_wp_trash_meta_time','1723650693'),(231,176,'_wp_trash_meta_status','0'),(232,176,'_wp_trash_meta_time','1723650693'),(233,175,'_wp_trash_meta_status','0'),(234,175,'_wp_trash_meta_time','1723650693'),(235,186,'_wp_trash_meta_status','0'),(236,186,'_wp_trash_meta_time','1723940754'),(237,185,'_wp_trash_meta_status','0'),(238,185,'_wp_trash_meta_time','1723940754'),(239,184,'_wp_trash_meta_status','0'),(240,184,'_wp_trash_meta_time','1723940754'),(241,183,'_wp_trash_meta_status','0'),(242,183,'_wp_trash_meta_time','1723940754'),(243,182,'_wp_trash_meta_status','0'),(244,182,'_wp_trash_meta_time','1723940754'),(245,181,'_wp_trash_meta_status','0'),(246,181,'_wp_trash_meta_time','1723940754'),(247,180,'_wp_trash_meta_status','0'),(248,180,'_wp_trash_meta_time','1723940754'),(249,179,'_wp_trash_meta_status','0'),(250,179,'_wp_trash_meta_time','1723940754'),(251,178,'_wp_trash_meta_status','0'),(252,178,'_wp_trash_meta_time','1723940754'),(253,196,'_wp_trash_meta_status','0'),(254,196,'_wp_trash_meta_time','1724361554'),(255,195,'_wp_trash_meta_status','0'),(256,195,'_wp_trash_meta_time','1724361554'),(257,194,'_wp_trash_meta_status','0'),(258,194,'_wp_trash_meta_time','1724361554'),(259,192,'_wp_trash_meta_status','0'),(260,192,'_wp_trash_meta_time','1724361554'),(261,191,'_wp_trash_meta_status','0'),(262,191,'_wp_trash_meta_time','1724361554'),(263,190,'_wp_trash_meta_status','0'),(264,190,'_wp_trash_meta_time','1724361554'),(265,189,'_wp_trash_meta_status','0'),(266,189,'_wp_trash_meta_time','1724361554'),(267,188,'_wp_trash_meta_status','0'),(268,188,'_wp_trash_meta_time','1724361554'),(269,187,'_wp_trash_meta_status','0'),(270,187,'_wp_trash_meta_time','1724361554'),(271,204,'_wp_trash_meta_status','0'),(272,204,'_wp_trash_meta_time','1724506916'),(273,203,'_wp_trash_meta_status','0'),(274,203,'_wp_trash_meta_time','1724506916'),(275,202,'_wp_trash_meta_status','0'),(276,202,'_wp_trash_meta_time','1724506916'),(277,201,'_wp_trash_meta_status','0'),(278,201,'_wp_trash_meta_time','1724506916'),(279,200,'_wp_trash_meta_status','0'),(280,200,'_wp_trash_meta_time','1724506916'),(281,199,'_wp_trash_meta_status','0'),(282,199,'_wp_trash_meta_time','1724506916'),(283,198,'_wp_trash_meta_status','0'),(284,198,'_wp_trash_meta_time','1724506916'),(285,197,'_wp_trash_meta_status','0'),(286,197,'_wp_trash_meta_time','1724506916'),(287,205,'_wp_trash_meta_status','0'),(288,205,'_wp_trash_meta_time','1724677792'),(289,208,'_wp_trash_meta_status','0'),(290,208,'_wp_trash_meta_time','1724708983'),(291,207,'_wp_trash_meta_status','0'),(292,207,'_wp_trash_meta_time','1724708986'),(293,221,'_wp_trash_meta_status','0'),(294,221,'_wp_trash_meta_time','1725131864'),(295,220,'_wp_trash_meta_status','0'),(296,220,'_wp_trash_meta_time','1725131864'),(297,219,'_wp_trash_meta_status','0'),(298,219,'_wp_trash_meta_time','1725131864'),(299,218,'_wp_trash_meta_status','0'),(300,218,'_wp_trash_meta_time','1725131864'),(301,217,'_wp_trash_meta_status','0'),(302,217,'_wp_trash_meta_time','1725131864'),(303,216,'_wp_trash_meta_status','0'),(304,216,'_wp_trash_meta_time','1725131864'),(305,215,'_wp_trash_meta_status','0'),(306,215,'_wp_trash_meta_time','1725131864'),(307,214,'_wp_trash_meta_status','0'),(308,214,'_wp_trash_meta_time','1725131864'),(309,213,'_wp_trash_meta_status','0'),(310,213,'_wp_trash_meta_time','1725131864'),(311,212,'_wp_trash_meta_status','0'),(312,212,'_wp_trash_meta_time','1725131864'),(313,211,'_wp_trash_meta_status','0'),(314,211,'_wp_trash_meta_time','1725131864'),(315,210,'_wp_trash_meta_status','0'),(316,210,'_wp_trash_meta_time','1725131864'),(317,209,'_wp_trash_meta_status','0'),(318,209,'_wp_trash_meta_time','1725131864');
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

-- Dump completed on 2024-08-31 19:20:43
