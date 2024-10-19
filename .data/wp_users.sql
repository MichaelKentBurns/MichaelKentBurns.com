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
-- Table structure for table `wp_users`
--

DROP TABLE IF EXISTS `wp_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_users` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_login` varchar(60) NOT NULL DEFAULT '',
  `user_pass` varchar(255) NOT NULL DEFAULT '',
  `user_nicename` varchar(50) NOT NULL DEFAULT '',
  `user_email` varchar(100) NOT NULL DEFAULT '',
  `user_url` varchar(100) NOT NULL DEFAULT '',
  `user_registered` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_activation_key` varchar(255) NOT NULL DEFAULT '',
  `user_status` int(11) NOT NULL DEFAULT 0,
  `display_name` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `user_login_key` (`user_login`),
  KEY `user_nicename` (`user_nicename`),
  KEY `user_email` (`user_email`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_users`
--

LOCK TABLES `wp_users` WRITE;
/*!40000 ALTER TABLE `wp_users` DISABLE KEYS */;
INSERT INTO `wp_users` VALUES (1,'michaelkentburns@gmail.com','$P$BYU5QjN1.rTMUuyFqotafBeTNUoJRr0','michael','michaelkentburns@gmail.com','https://michaelkentburns.com','2023-03-28 22:47:23','',0,'Michael'),(2,'Michael','$P$B/27ElBvfVdnDyLg9xlfphS.IiZIxN1','michael-2','MichaelBurns@mac.com','http://MichaelKentBurns.com','2023-04-01 23:43:21','',0,'Michael Burns'),(4,'Mike','$P$BDQ0yDRwMrFDa9HdCQSkUpqB0fZ6Qu.','mike','Mike@michaelkentburns.com','','2024-01-20 23:51:53','1705794713:$P$BB7FXpt7dr3lGgqUPI61Avg5xmaC1r0',0,'Mike Burns'),(5,'Bahati Philemon','$P$BASTWB9GDJHE6JjNMN3e7QqMut9uOU1','bahati-philemon','bahatiphilmon8@gmail.com','https://bahatiphilemon.netlify.app','2024-04-01 20:11:32','',0,'Philemon Bahati'),(6,'Efatha','$P$Bx2AvMw7XZfc6Kx7P3ZHCgJfUjKLX90','efatha','efathabyamungu4@gmail.com','','2024-04-01 20:12:38','1712007595:$P$BAIcUj7U3/gx364bpXpYrx0YH8L.dy1',0,'Efatha Byamungu'),(7,'ashuza','$P$BTuyeZ38dzEIxcNAPvEPpUlWFVMMcx0','ashuza','ashuzamaheshe4@gmail.com','','2024-04-16 21:45:10','',0,'Destin Ashuza'),(8,'janelljeffery','$P$BoTWMs1nX8j7754.HSecBRB9OwxPjw1','janelljeffery','tracieerlinda@makekaos.com','','2024-05-24 21:20:45','',0,'janelljeffery'),(9,'mikegiffen9','$P$BwcEVBk39NJ3YO29goyRj1.IIU848g.','mikegiffen9','jkbkxdsr@maillv.com','','2024-06-04 08:21:35','',0,'mikegiffen9'),(10,'alda10p2621','$P$BkBPJZVwdZvx07kwGf9O6U6e9CTZ0A/','alda10p2621','canbusecfast1973@gopon-tr.store','','2024-07-10 15:44:22','',0,'alda10p2621'),(11,'jeffrymcclendon','$P$BLqSKhA1SvYuJAduJwAmmXoJeMt2MO.','jeffrymcclendon','trinadeandre@andindoc.com','','2024-08-17 23:16:55','1723936615:$P$BuTg7XiN0Ox.U8KewXXpNHcTaUEXlf/',0,'jeffrymcclendon'),(12,'hilariobigelow8','$P$BQW92AMGhvh6v.nsHzUMgEjb8q/T/X.','hilariobigelow8','phillisperkin5910@hardseo.store','','2024-10-09 19:18:33','1728501513:$P$BOnaplh0L2aOLrqYzQw1O8Z1xVicPk.',0,'hilariobigelow8');
/*!40000 ALTER TABLE `wp_users` ENABLE KEYS */;
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
