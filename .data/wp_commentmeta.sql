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
) ENGINE=InnoDB AUTO_INCREMENT=849 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_commentmeta`
--

LOCK TABLES `wp_commentmeta` WRITE;
/*!40000 ALTER TABLE `wp_commentmeta` DISABLE KEYS */;
INSERT INTO `wp_commentmeta` VALUES (667,429,'_wp_trash_meta_status','0'),(668,429,'_wp_trash_meta_time','1733335870'),(669,428,'_wp_trash_meta_status','0'),(670,428,'_wp_trash_meta_time','1733335870'),(671,427,'_wp_trash_meta_status','0'),(672,427,'_wp_trash_meta_time','1733335870'),(673,426,'_wp_trash_meta_status','0'),(674,426,'_wp_trash_meta_time','1733335870'),(675,425,'_wp_trash_meta_status','0'),(676,425,'_wp_trash_meta_time','1733335870'),(677,424,'_wp_trash_meta_status','0'),(678,424,'_wp_trash_meta_time','1733335870'),(679,423,'_wp_trash_meta_status','0'),(680,423,'_wp_trash_meta_time','1733335870'),(681,422,'_wp_trash_meta_status','0'),(682,422,'_wp_trash_meta_time','1733335870'),(683,421,'_wp_trash_meta_status','0'),(684,421,'_wp_trash_meta_time','1733335870'),(685,420,'_wp_trash_meta_status','0'),(686,420,'_wp_trash_meta_time','1733335870'),(687,419,'_wp_trash_meta_status','0'),(688,419,'_wp_trash_meta_time','1733335870'),(689,418,'_wp_trash_meta_status','0'),(690,418,'_wp_trash_meta_time','1733335870'),(691,417,'_wp_trash_meta_status','0'),(692,417,'_wp_trash_meta_time','1733335870'),(693,416,'_wp_trash_meta_status','0'),(694,416,'_wp_trash_meta_time','1733335870'),(695,415,'_wp_trash_meta_status','0'),(696,415,'_wp_trash_meta_time','1733335870'),(697,414,'_wp_trash_meta_status','0'),(698,414,'_wp_trash_meta_time','1733335870'),(699,413,'_wp_trash_meta_status','0'),(700,413,'_wp_trash_meta_time','1733335870'),(701,412,'_wp_trash_meta_status','0'),(702,412,'_wp_trash_meta_time','1733335870'),(703,411,'_wp_trash_meta_status','0'),(704,411,'_wp_trash_meta_time','1733335870'),(705,410,'_wp_trash_meta_status','0'),(706,410,'_wp_trash_meta_time','1733335870'),(707,409,'_wp_trash_meta_status','0'),(708,409,'_wp_trash_meta_time','1733335928'),(709,408,'_wp_trash_meta_status','0'),(710,408,'_wp_trash_meta_time','1733335928'),(711,407,'_wp_trash_meta_status','0'),(712,407,'_wp_trash_meta_time','1733335928'),(713,406,'_wp_trash_meta_status','0'),(714,406,'_wp_trash_meta_time','1733335928'),(715,405,'_wp_trash_meta_status','0'),(716,405,'_wp_trash_meta_time','1733335928'),(717,404,'_wp_trash_meta_status','0'),(718,404,'_wp_trash_meta_time','1733335928'),(719,403,'_wp_trash_meta_status','0'),(720,403,'_wp_trash_meta_time','1733335928'),(721,402,'_wp_trash_meta_status','0'),(722,402,'_wp_trash_meta_time','1733335928'),(723,401,'_wp_trash_meta_status','0'),(724,401,'_wp_trash_meta_time','1733335928'),(725,400,'_wp_trash_meta_status','0'),(726,400,'_wp_trash_meta_time','1733335928'),(727,446,'_wp_trash_meta_status','0'),(728,446,'_wp_trash_meta_time','1733871240'),(729,445,'_wp_trash_meta_status','0'),(730,445,'_wp_trash_meta_time','1733871240'),(731,444,'_wp_trash_meta_status','0'),(732,444,'_wp_trash_meta_time','1733871240'),(733,443,'_wp_trash_meta_status','0'),(734,443,'_wp_trash_meta_time','1733871240'),(735,442,'_wp_trash_meta_status','0'),(736,442,'_wp_trash_meta_time','1733871240'),(737,441,'_wp_trash_meta_status','0'),(738,441,'_wp_trash_meta_time','1733871240'),(739,440,'_wp_trash_meta_status','0'),(740,440,'_wp_trash_meta_time','1733871240'),(741,439,'_wp_trash_meta_status','0'),(742,439,'_wp_trash_meta_time','1733871240'),(743,438,'_wp_trash_meta_status','0'),(744,438,'_wp_trash_meta_time','1733871240'),(745,436,'_wp_trash_meta_status','0'),(746,436,'_wp_trash_meta_time','1733871240'),(747,435,'_wp_trash_meta_status','0'),(748,435,'_wp_trash_meta_time','1733871240'),(749,434,'_wp_trash_meta_status','0'),(750,434,'_wp_trash_meta_time','1733871240'),(751,433,'_wp_trash_meta_status','0'),(752,433,'_wp_trash_meta_time','1733871240'),(753,432,'_wp_trash_meta_status','0'),(754,432,'_wp_trash_meta_time','1733871240'),(755,431,'_wp_trash_meta_status','0'),(756,431,'_wp_trash_meta_time','1733871240'),(757,430,'_wp_trash_meta_status','0'),(758,430,'_wp_trash_meta_time','1733871240'),(759,437,'_wp_trash_meta_status','0'),(760,437,'_wp_trash_meta_time','1733871247'),(761,469,'_wp_trash_meta_status','0'),(762,469,'_wp_trash_meta_time','1734567800'),(763,468,'_wp_trash_meta_status','0'),(764,468,'_wp_trash_meta_time','1734567800'),(765,467,'_wp_trash_meta_status','0'),(766,467,'_wp_trash_meta_time','1734567800'),(767,466,'_wp_trash_meta_status','0'),(768,466,'_wp_trash_meta_time','1734567800'),(769,465,'_wp_trash_meta_status','0'),(770,465,'_wp_trash_meta_time','1734567800'),(771,464,'_wp_trash_meta_status','0'),(772,464,'_wp_trash_meta_time','1734567800'),(773,463,'_wp_trash_meta_status','0'),(774,463,'_wp_trash_meta_time','1734567800'),(775,462,'_wp_trash_meta_status','0'),(776,462,'_wp_trash_meta_time','1734567800'),(777,461,'_wp_trash_meta_status','0'),(778,461,'_wp_trash_meta_time','1734567800'),(779,460,'_wp_trash_meta_status','0'),(780,460,'_wp_trash_meta_time','1734567800'),(781,459,'_wp_trash_meta_status','0'),(782,459,'_wp_trash_meta_time','1734567800'),(783,458,'_wp_trash_meta_status','0'),(784,458,'_wp_trash_meta_time','1734567800'),(785,457,'_wp_trash_meta_status','0'),(786,457,'_wp_trash_meta_time','1734567800'),(787,456,'_wp_trash_meta_status','0'),(788,456,'_wp_trash_meta_time','1734567800'),(789,455,'_wp_trash_meta_status','0'),(790,455,'_wp_trash_meta_time','1734567800'),(791,454,'_wp_trash_meta_status','0'),(792,454,'_wp_trash_meta_time','1734567800'),(793,453,'_wp_trash_meta_status','0'),(794,453,'_wp_trash_meta_time','1734567800'),(795,452,'_wp_trash_meta_status','0'),(796,452,'_wp_trash_meta_time','1734567800'),(797,451,'_wp_trash_meta_status','0'),(798,451,'_wp_trash_meta_time','1734567800'),(799,450,'_wp_trash_meta_status','0'),(800,450,'_wp_trash_meta_time','1734567800'),(801,447,'ct_marked_as_spam','1'),(802,448,'ct_marked_as_spam','1'),(803,449,'ct_marked_as_spam','1'),(804,470,'ct_marked_as_spam','1'),(805,471,'ct_marked_as_spam','1'),(806,472,'ct_marked_as_spam','1'),(807,473,'ct_marked_as_spam','1'),(808,474,'ct_marked_as_spam','1'),(809,475,'ct_marked_as_spam','1'),(810,476,'ct_marked_as_spam','1'),(811,477,'ct_marked_as_spam','1'),(812,478,'ct_marked_as_spam','1'),(813,479,'ct_marked_as_spam','1'),(814,480,'ct_marked_as_spam','1'),(815,481,'ct_marked_as_spam','1'),(816,482,'ct_marked_as_spam','1'),(817,483,'ct_marked_as_spam','1'),(818,484,'ct_marked_as_spam','1'),(819,485,'ct_marked_as_spam','1'),(820,486,'ct_marked_as_spam','1'),(821,487,'ct_marked_as_spam','1'),(822,488,'ct_marked_as_spam','1'),(823,489,'ct_marked_as_spam','1'),(824,490,'ct_marked_as_spam','1'),(825,491,'ct_marked_as_spam','1'),(826,492,'ct_marked_as_spam','1'),(827,493,'ct_marked_as_spam','1'),(828,494,'ct_marked_as_spam','1'),(829,495,'ct_marked_as_spam','1'),(830,496,'ct_marked_as_spam','1'),(831,497,'ct_marked_as_spam','1'),(832,498,'ct_marked_as_spam','1'),(833,499,'ct_marked_as_spam','1'),(834,500,'ct_marked_as_spam','1'),(835,501,'ct_marked_as_spam','1'),(836,502,'ct_marked_as_spam','1'),(837,503,'ct_marked_as_spam','1'),(838,504,'ct_marked_as_spam','1'),(839,505,'ct_marked_as_spam','1'),(840,506,'ct_marked_as_spam','1'),(841,507,'ct_marked_as_spam','1'),(842,508,'ct_marked_as_spam','1'),(843,509,'ct_marked_as_spam','1'),(844,510,'ct_marked_as_spam','1'),(845,511,'ct_marked_as_spam','1'),(846,512,'ct_marked_as_spam','1'),(847,513,'ct_marked_as_spam','1'),(848,514,'ct_marked_as_spam','1');
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

-- Dump completed on 2024-12-31 21:42:50
