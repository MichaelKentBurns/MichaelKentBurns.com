/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.5.29-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: hynxgrbyjv
-- ------------------------------------------------------
-- Server version	10.5.29-MariaDB-deb11-log

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
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `wp_commentmeta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `comment_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `meta_key` varchar(255) DEFAULT NULL,
  `meta_value` longtext DEFAULT NULL,
  PRIMARY KEY (`meta_id`),
  KEY `comment_id` (`comment_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB AUTO_INCREMENT=1122 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_commentmeta`
--

LOCK TABLES `wp_commentmeta` WRITE;
/*!40000 ALTER TABLE `wp_commentmeta` DISABLE KEYS */;
INSERT INTO `wp_commentmeta` VALUES (964,534,'ct_hash','7a541e8b10a302c0873334f39fe93f14'),(965,535,'ct_hash','0d6d2b5cc4a6bcc8b041dc9d4b1e4453'),(967,537,'ct_hash','302c0f6700c472a8a62d85e6f27324f3'),(970,540,'ct_real_user_badge_hash','fe7e155de22adf69e524e89c5fbbd9bb'),(971,541,'ct_hash','f82a671d05c17866981b2335b63f4a03'),(972,542,'ct_real_user_badge_hash','e4959249ab1755ca9b8db1e3057e577d'),(973,543,'ct_real_user_badge_hash','a4a744c77beade183deaddbb3b78c5ef'),(974,544,'ct_hash','83ded8b7a8b0fca3715150825f5b86e1'),(975,545,'ct_hash','bfb116d3e42e580c1d295811293e6fb0'),(976,546,'ct_real_user_badge_hash','87cbacff15e0e1fad85982eadf3aab6b'),(977,547,'ct_hash','648b0aed4d23c4c43ab1c33930564947'),(978,548,'ct_hash','9e534821fcf28e274f351b7aaeab0b04'),(979,549,'ct_hash','68317158f04bc7006480b203a416e1ef'),(980,550,'ct_hash','bd443e470cbe99a95fe3a78effb79d1d'),(981,551,'ct_real_user_badge_hash','2f3d3d55500de0136c25a4a3e54ca2cd'),(982,552,'ct_real_user_badge_hash','ef65981b86c76087c17177aa2272b988'),(983,553,'ct_real_user_badge_hash','068fc6c74b6f6d5891c1e721a1113ce8'),(984,554,'ct_real_user_badge_hash','2e9940b94bd12796997904ec91948cab'),(985,555,'ct_real_user_badge_hash','d0eadba171683dd16a1a92b459a1b181'),(986,556,'ct_hash','a7dc675c9de00f4e149ab9e7a04dddd0'),(987,557,'ct_hash','15f383529809f5f429af1dc520302ca0'),(988,558,'ct_hash','a0c33f63975626cca26a81633c08b0e8'),(989,559,'ct_real_user_badge_hash','8bdde62cc6140303f68d371de4456637'),(990,560,'ct_real_user_badge_hash','e2e860b18518c1754c6790aff93bdd82'),(991,561,'ct_hash','c78e50930baa1aba47a8e266860fc239'),(992,563,'ct_real_user_badge_hash','8b6c5d575bf0f7c9bca10df2e872e93b'),(993,569,'ct_hash','1147836291fa1a23960b4b348ccda814'),(994,576,'ct_hash','d36b34855facf612853bede8f07e7c76'),(995,578,'ct_hash','75ddfb756bc46cfdac6cd040a0c20a2e'),(996,580,'ct_hash','ee8b1ffe778ea228df72dc147ea1646a'),(999,581,'ct_real_user_badge_hash','3a49a221b9735279bcdc447f95768381'),(1000,582,'ct_real_user_badge_hash','c8be028d8304f52e0dacc8545ed4a107'),(1001,583,'ct_hash','7189fa6a3dc78ecaaa1ce453c1d73ab8'),(1002,584,'ct_real_user_badge_hash','d037cd1699c79f10b88da55b0c66db32'),(1003,585,'ct_real_user_badge_hash','f2936466d7e7d5036615eb7af23507e3'),(1004,586,'ct_hash','26174fad9a6b545cd94d25f3977e3157'),(1005,587,'ct_hash','3053be36fcbed678e2c017c54f1fece7'),(1006,588,'ct_hash','4278d03fd268392c02086699857b9217'),(1007,589,'ct_hash','107da82168eaeddbd2ab6fd71c4197de'),(1008,590,'ct_hash','8b4b0be2451580e6a1d5a06ab9fc3704'),(1009,591,'ct_hash','431a66166b54fa763c17b0e8d084e734'),(1010,592,'ct_hash','136c5b5fa1ce67a60caa3bb1b97f3ad7'),(1011,593,'ct_hash','1249cbe4cbe06cb25687eb2fb5ada306'),(1012,594,'ct_hash','1238d75e7c15ce39ab90db01c53d3718'),(1013,595,'ct_hash','4594adadc292274d9ca632568a47c3b4'),(1014,596,'ct_hash','dddb58bf0c612747ec2c47a630007d4c'),(1015,597,'ct_hash','310d9055e34de17262d446d011598bd7'),(1016,598,'ct_hash','8f399d435b412bb9a32d48173543e209'),(1017,599,'ct_hash','f8ee5eccbd88d1573a8658f8d2d2d582'),(1018,600,'ct_hash','d685a692d1abb1fc8c19ee14e4b58011'),(1019,601,'ct_hash','afb9c51d1278d84e6f08285a49d8c5ee'),(1020,602,'ct_hash','39096fd8688b56e9ab53119d8058fbc2'),(1021,603,'ct_hash','8e14dd36af53f042f03a4fee4689b475'),(1022,604,'ct_hash','7c903d4795f68f6311d6ae5b54c5f230'),(1023,605,'ct_hash','43f9c87dea451b97446136119481342d'),(1024,606,'ct_hash','f8088b4d1a64a3187dc3e5b318316208'),(1025,607,'ct_hash','9bd0232b41e581ff09ec6623bdc57e5a'),(1026,608,'ct_hash','628debd6e5a182a507bc4bc9b5194853'),(1027,609,'ct_hash','1e95f52a2db85bfca564b90511cb715e'),(1028,610,'ct_hash','6d9470c0da67d0f02166527e25a55056'),(1029,611,'ct_hash','65a61e31485709bc183a91a6b4294f97'),(1030,612,'ct_hash','e98f05b372ef107ea8fa88dd93fe6ed6'),(1031,613,'ct_hash','f91d081ea7fc0f2fb4a358523bc6a434'),(1032,614,'ct_hash','8930792989bbe6ff2200c8a28ffad739'),(1033,615,'ct_hash','8d3ba8aaaaf5fd3726c8de7aade8af8b'),(1034,616,'ct_hash','cb0e3d6057f1d2ede55beb357427709c'),(1035,617,'ct_hash','107bb26af5a5bd02acf2e007838aa44e'),(1036,618,'ct_hash','d1cc9cbfb50f758a74f61e3b4dee4073'),(1037,619,'ct_hash','9275021fa76105675ea3e9f64980db4f'),(1038,620,'ct_hash','4e2cf0433f7bc132ba5f0c2541f964a9'),(1039,621,'ct_hash','7c8cd72dbf8227844defc4ed1333bee2'),(1040,622,'ct_hash','017e3604dccfc7281ba67286357c9cac'),(1041,623,'ct_hash','ee865a9c410b296435fb08794795f05b'),(1042,624,'ct_hash','83df5b63dd87d95c34649709204e9bfa'),(1043,625,'ct_hash','300d472e1399c0845e9d5769db01a714'),(1044,626,'ct_hash','9c7232968bfbc555b3dfab90c984749c'),(1045,627,'ct_hash','d4f6444a57979a506d44690555a28688'),(1046,628,'ct_hash','69128e5780a99128075494a5269ca11e'),(1047,629,'ct_hash','41c52b6c0cdcb884fb783a95ba90415f'),(1048,630,'ct_hash','7c00d8c21ef1a4a5241c0222710cb73d'),(1049,631,'ct_hash','3651b362925df72dc0dbe927e65e4353'),(1050,632,'ct_hash','924b01340e8210e7fa4c98e4990f18c3'),(1051,633,'ct_hash','0c97d187bff406915a17072dc6a7b4c1'),(1052,634,'ct_hash','64c512d8b24b411141588f3441c86faf'),(1053,635,'ct_hash','fb88f64021d272a224c8fade5568948d'),(1054,636,'ct_hash','82eb9a43964b99efcd19e197c976cb38'),(1055,637,'ct_hash','3686967802f45d1caaedb2f42f3547b0'),(1056,638,'ct_hash','c7cfca64f63723c14b056952aa64cbd1'),(1057,639,'ct_hash','354b8916b432b1f87c34d717e71a1e50'),(1058,640,'ct_hash','a7bc6a6276ceaba1ecc75b7101e16012'),(1059,641,'ct_hash','841fc22cac1f8e170cf7aa713e1261d7'),(1060,642,'ct_hash','ee751ec23b3d9135c591cccb6f1c5e13'),(1061,643,'ct_hash','0e4232f469ebaaa8da0c0e6749cbe5bf'),(1062,644,'ct_hash','691327bfdcb3caa5ad5b73871fde822a'),(1063,645,'ct_hash','c6392ebcb4239ee932db369920aa987e'),(1064,646,'ct_hash','ebfdbec173ef9a156b228854c05aea34'),(1065,647,'ct_hash','6a6bdb9ce2f3806ffb4164cec2dbe6bf'),(1066,648,'ct_hash','3980b89727e0e871bb97cef65b432b83'),(1067,649,'ct_hash','b1840ef1bc6fc16362289d978aaa5025'),(1068,650,'ct_hash','ba02b6b50499da7408c434aee407d0ed'),(1069,651,'ct_hash','757d87dcf79b28c109343b7d3344a160'),(1070,652,'ct_hash','6f90c033ac9ffbbbd9014180ced67393'),(1071,653,'ct_hash','ce62f6f426dfd3ee043e7c54e18514e4'),(1072,654,'ct_hash','56102f0eb0e6d69565421d45bf82c4b6'),(1073,655,'ct_hash','759b82e1b5133eed28c99699b9277189'),(1074,656,'ct_hash','07c58900e345ca954cd48f597a62dbe8'),(1075,657,'ct_real_user_badge_hash','62bd50a2c74d84cc4369ab2f74c1ba5a'),(1076,658,'ct_hash','bac1252c675e03bf34ac6380cccd9b27'),(1077,659,'ct_hash','9b891671c96e0bb0e16cab2f94f69424'),(1078,660,'ct_hash','cb42021fa9d3dd4684af3531d0acf758'),(1079,661,'ct_hash','08a889a1ed7c297fec0dd26717cdfef6'),(1080,662,'ct_hash','c0665303a6f63322c1b114181bb4878e'),(1081,663,'ct_hash','ffe323aedfb5dda771eab3acfd082731'),(1082,664,'ct_hash','8e13f9ba1a6c20bfd7013f59efba17c5'),(1083,665,'ct_hash','527d8e195fe0fbf5a28ab1cecd4f9386'),(1084,666,'ct_hash','a046492cb75be9ff38aa72233c16e49a'),(1085,667,'ct_hash','1d912cb286bbf069d55dc145b2f633bd'),(1086,668,'ct_hash','61a77f6df7954287694a00e7c578d66b'),(1087,669,'ct_hash','c0172d72268f7f7b88c9d67717840162'),(1088,670,'ct_hash','09ae6cf8cbb7de5ae901f57322c74caf'),(1089,671,'ct_hash','0608a7eeba5870016dc7cc0ecc9c9265'),(1090,672,'ct_hash','a4a6f3027076b8c9425f31f9af680434'),(1091,673,'ct_hash','cd3bb2b8525926fa548a713b813391fc'),(1092,674,'ct_hash','d7f8a8effcbd6655fe21b98947af2376'),(1093,675,'ct_hash','eaf8d8863ff96972bf0509aefbecde78'),(1094,676,'ct_hash','4030bb30ecbf641273940a5abbfbe90f'),(1095,677,'ct_hash','1ccb34582e5edafb336225ed6b9e4196'),(1096,678,'ct_hash','710409e382fc1aaf8e71733cb2a19381'),(1097,679,'ct_hash','ada3741bcafb6dca185b02d962638f80'),(1098,680,'ct_hash','771dff6ac2f3406d453242892747b52d'),(1099,681,'ct_hash','08d670c2c9fd411e7e2081d1cc078f73'),(1100,682,'ct_hash','59e57f5b9c0c56d290224962ebc7ae88'),(1101,683,'ct_hash','745e9f5bad0177a6e3a58f85b7538f37'),(1102,684,'ct_hash','0f0ef40f50b30a9ccf02257eed41eacb'),(1103,685,'ct_hash','70805eb932886feac598172f67769d2d'),(1104,686,'ct_hash','1dc0c5119a6e677394ce11a07eeb5c3e'),(1105,687,'ct_hash','f22ce54f9652a52410dc485322e2332f'),(1106,688,'ct_hash','3d1fbc474e8715a02c099e2f3b15eddf'),(1107,689,'ct_hash','9a6ce497f2767324510ecd562fd88c2d'),(1108,690,'ct_hash','0cb830eb38050196479bc0e4066a5986'),(1109,691,'ct_hash','162af41e2c5012ca31aa92be9c94da51'),(1110,692,'ct_hash','31f6ba476c9581a77dab823a6836b374'),(1111,693,'ct_hash','12b7d1a762fcc31c9f806a7ceafd872e'),(1112,694,'ct_hash','02a15b99549d9c4fe6e278c19d40809d'),(1113,695,'ct_hash','e8559faafca959541f2c123fbbeb9548'),(1114,696,'ct_hash','bf9214e0656a17b01f22c9be14731be4'),(1115,697,'ct_hash','37367f3937f0373d584a003018b45052'),(1116,698,'ct_hash','483bddc792d7059f4e89659eea489c7f'),(1117,699,'ct_hash','4cb3408db22116387ab101e499ee7233'),(1118,700,'ct_hash','13d6989e8e301380c0d8b2764390cbe9'),(1119,701,'ct_hash','de3b974d6390336da78de993531f93f5'),(1120,702,'ct_hash','eedf0e8090a929c62a08449423b58422'),(1121,703,'ct_hash','bfb7fab367c76d187c51081e6fb81562');
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

-- Dump completed on 2025-07-29 20:50:33
