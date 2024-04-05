-- MariaDB dump 10.19  Distrib 10.4.33-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: hynxgrbyjv
-- ------------------------------------------------------
-- Server version	10.4.33-MariaDB-1:10.4.33+maria~deb10-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `wp_bv_fw_requests`
--

DROP TABLE IF EXISTS `wp_bv_fw_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_bv_fw_requests` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ip` varchar(50) NOT NULL DEFAULT '',
  `status` int(1) NOT NULL DEFAULT 0,
  `time` bigint(20) NOT NULL DEFAULT 1388516401,
  `path` varchar(100) NOT NULL DEFAULT '',
  `host` varchar(100) NOT NULL DEFAULT '',
  `method` varchar(100) NOT NULL DEFAULT '',
  `resp_code` int(6) NOT NULL DEFAULT 0,
  `category` int(1) NOT NULL DEFAULT 4,
  `referer` varchar(200) NOT NULL DEFAULT '',
  `user_agent` varchar(200) NOT NULL DEFAULT '',
  `filenames` text DEFAULT NULL,
  `query_string` text DEFAULT NULL,
  `rules_info` text DEFAULT NULL,
  `request_id` varchar(200) DEFAULT NULL,
  `matched_rules` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=890 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-04-02 21:38:22
