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
-- Table structure for table `wp_comments`
--

DROP TABLE IF EXISTS `wp_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_comments` (
  `comment_ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `comment_post_ID` bigint(20) unsigned NOT NULL DEFAULT 0,
  `comment_author` tinytext NOT NULL,
  `comment_author_email` varchar(100) NOT NULL DEFAULT '',
  `comment_author_url` varchar(200) NOT NULL DEFAULT '',
  `comment_author_IP` varchar(100) NOT NULL DEFAULT '',
  `comment_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment_content` text NOT NULL,
  `comment_karma` int(11) NOT NULL DEFAULT 0,
  `comment_approved` varchar(20) NOT NULL DEFAULT '1',
  `comment_agent` varchar(255) NOT NULL DEFAULT '',
  `comment_type` varchar(20) NOT NULL DEFAULT 'comment',
  `comment_parent` bigint(20) unsigned NOT NULL DEFAULT 0,
  `user_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`comment_ID`),
  KEY `comment_post_ID` (`comment_post_ID`),
  KEY `comment_approved_date_gmt` (`comment_approved`,`comment_date_gmt`),
  KEY `comment_date_gmt` (`comment_date_gmt`),
  KEY `comment_parent` (`comment_parent`),
  KEY `comment_author_email` (`comment_author_email`(10))
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_comments`
--

LOCK TABLES `wp_comments` WRITE;
/*!40000 ALTER TABLE `wp_comments` DISABLE KEYS */;
INSERT INTO `wp_comments` VALUES (4,229,'Efatha Rutakaza','efathabyamungu4@gmail.com','','45.221.4.20','2024-03-14 03:57:50','2024-03-14 03:57:50','This is a very good heads up. I\'m very thrilled that I\'m going to be a part of a great time which has somehow interesting initiatives.',0,'1','Mozilla/5.0 (Linux; Android 6.0.1; vivo Y66) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/106.0.0.0 Mobile Safari/537.36','comment',0,0),(5,229,'Efatha Rutakaza','efathabyamungu4@gmail.com','https://github.com/efatha/packetcodeofficial.github.io','45.221.4.23','2024-03-15 11:11:44','2024-03-15 11:11:44','This is remarkably good. nonetheless, I would suggest that  on the About tab, to add a little more details about your personal life in code path so that the developers who will read, that should be more motivated and lead them to a critical decision in their commitment. Then in the new link contact tab. I would suggest to manage the very big empty space at the bottom by putting links or \r\na bottom footer element. Eventually, I would like to suggest a form login that leads to all the social media whether someone submit.',0,'1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36','comment',0,0),(6,229,'Efatha Rutakaza','efathabyamungu4@gmail.com','','45.221.4.23','2024-03-15 11:18:57','2024-03-15 11:18:57','This website helps me very much, I as a web developer to think more on the Best websites, I\'ve been browsing lately! \r\nI\'m convinced that this is a very smart one.',0,'1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36','comment',0,0),(7,229,'Candice Aguilar','fiercejamal@aol.com','https://kamaoimino.com','178.159.37.56','2024-03-16 21:20:05','2024-03-16 21:20:05','I will immediately grab your rss as I can not to find your email \r\nsubscription link or e-newsletter service. Do you\'ve any?\r\nKindly let me recognize in order that I may subscribe.\r\nThanks.',0,'1','Mozilla/5.0 (Windows NT 10.0; rv:91.0) Gecko/20100101 Firefox/91.0','comment',0,0),(8,229,'Edward Googe','froreloupe@msn.com','https://sveltcolza.com','178.159.37.56','2024-03-19 02:38:54','2024-03-19 02:38:54','Howdy! This blog post couldn\'t be written much better! Looking at this post reminds me of my previous roommate!\r\nHe constantly kept preaching about this. I most certainly will send \r\nthis post to him. Fairly certain he\'s going to have a good read.\r\nThanks for sharing!',0,'1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:100.0) Gecko/20100101 Firefox/100.0','comment',0,0),(11,310,'GvrPpYNkSOJxgysM','luzrivardxl@outlook.com','http://yipPtonWRIOVLkJ','117.121.202.90','2024-04-18 13:35:52','2024-04-18 13:35:52','SFRZEDrA',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','comment',0,0),(12,310,'GvrPpYNkSOJxgysM','luzrivardxl@outlook.com','http://yipPtonWRIOVLkJ','117.121.202.90','2024-04-18 13:36:08','2024-04-18 13:36:08','GKTANCxSRuoep',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','comment',0,0),(13,310,'sMqHBvjTO','arey7zll@outlook.com','http://XodlucQaNkWOJIjx','185.18.212.190','2024-04-21 04:16:46','2024-04-21 04:16:46','imdLkMPZre',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','comment',0,0),(14,310,'RJLpNxHrBYDzAlm','irmak3_wells4v@outlook.com','http://JUlTQoIaW','54.37.155.13','2024-04-24 08:03:47','2024-04-24 08:03:47','YkDpolOXfCgmvnJL',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','comment',0,0),(15,310,'ElNrnZLvaJBDxU','burgesfernanda01@outlook.com','http://QwRWLnOxl','131.72.68.222','2024-04-27 21:56:11','2024-04-27 21:56:11','YlOjoZdmx',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','comment',0,0),(16,310,'eQBPaTbZ','suttongrir1994@gmail.com','http://jfWOIkEB','116.111.147.8','2024-05-04 08:06:05','2024-05-04 08:06:05','frsdLwDB',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','comment',0,0),(17,310,'eQBPaTbZ','suttongrir1994@gmail.com','http://jfWOIkEB','116.111.147.8','2024-05-04 08:06:26','2024-05-04 08:06:26','walkXDMcsAfHIo',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','comment',0,0),(18,310,'mcACYRtyWaPUSX','jordansd_hillvq@outlook.com','http://VHezpuaDUwG','124.248.191.43','2024-05-07 10:38:20','2024-05-07 10:38:20','kDjghMfvVpwPR',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','comment',0,0),(19,310,'mcACYRtyWaPUSX','jordansd_hillvq@outlook.com','http://VHezpuaDUwG','124.248.191.43','2024-05-07 10:38:37','2024-05-07 10:38:37','KzIQcaxmPSDMdXZ',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','comment',0,0),(20,430,'Bahati Philemon','bahatiphilmon8@gmail.com','','169.239.159.113','2024-05-08 14:13:08','2024-05-08 14:13:08','Thank you very much Michael, we\'re really grateful to you for all the training you\'re giving us.',0,'1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36','comment',0,0),(21,310,'PsmzHjdUZaeK','brandy_coleman1982@yahoo.com','http://eozPJjTcxDwhA','188.173.163.40','2024-05-14 05:15:09','2024-05-14 05:15:09','oaicpjqS',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','comment',0,0),(22,436,'Bahati Philemon','bahatiphilmon8@gmail.com','','206.42.84.117','2024-05-17 18:43:05','2024-05-17 18:43:05','We are working hard to complete this project',0,'1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36','comment',0,0),(23,310,'KlfFyQZNs','georgegp_savoycc@outlook.com','http://JfkoRYcOrEgX','200.142.107.237','2024-05-17 20:58:50','2024-05-17 20:58:50','uyWERjpn',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','comment',0,0),(24,444,'Bahati Philemon','bahatiphilmon8@gmail.com','','169.239.159.178','2024-05-21 19:40:35','2024-05-21 19:40:35','Great post on using GitHub issues and labels to improve communication and collaboration within a team!\r\n\r\nThe use of clear labels like \"Stuck!!\" and \"High Priority!!\" seems like a very effective way to flag potential roadblocks and ensure everyone is on the same page.\r\n\r\nI particularly like the emphasis on daily reviewing the issues list. This helps keep everyone informed and fosters a sense of shared responsibility for the project\'s progress.',0,'1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36','comment',0,0),(25,430,'temp mail','liliana_maxime@yahoo.com','https://maillog.org/','103.253.19.178','2024-05-22 11:37:53','2024-05-22 11:37:53','Hello, good post. I have observed that your website appears to be having issues with Internet Explorer. Because of this problem, many people will miss your fantastic work because IE is still the most widely used browser.',0,'trash','','comment',0,0),(26,444,'Michael','michaelkentburns@gmail.com','https://michaelkentburns.com','75.212.251.79','2024-05-24 12:08:56','2024-05-24 12:08:56','I’m interested in following this blog. So I clicked the checkbox ✅ below.',0,'1','Mozilla/5.0 (iPad; CPU OS 17_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Mobile/15E148 Safari/604.1','comment',0,1),(27,430,'Efatha Rutakaza','efathabyamungu4@gmail.com','https://efatha.github.io/my-portofolio/','169.239.159.35','2024-05-24 17:52:40','2024-05-24 17:52:40','Let\'s celebrate all together this amazing work provided collectively by the team members with potentials skills and efforts made to achieve this goal.',0,'1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36','comment',0,0),(28,400,'QBdiMLPeO','yvonne24yazziekql@outlook.com','http://smoQHMjDYunFiheP','201.20.104.54','2024-05-24 21:33:07','2024-05-24 21:33:07','XfaTPcbKSkmrE',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','comment',0,0);
/*!40000 ALTER TABLE `wp_comments` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-05-25 13:58:35
