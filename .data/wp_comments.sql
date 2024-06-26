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
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_comments`
--

LOCK TABLES `wp_comments` WRITE;
/*!40000 ALTER TABLE `wp_comments` DISABLE KEYS */;
INSERT INTO `wp_comments` VALUES (4,229,'Efatha Rutakaza','efathabyamungu4@gmail.com','','45.221.4.20','2024-03-14 03:57:50','2024-03-14 03:57:50','This is a very good heads up. I\'m very thrilled that I\'m going to be a part of a great time which has somehow interesting initiatives.',0,'1','Mozilla/5.0 (Linux; Android 6.0.1; vivo Y66) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/106.0.0.0 Mobile Safari/537.36','comment',0,0),(5,229,'Efatha Rutakaza','efathabyamungu4@gmail.com','https://github.com/efatha/packetcodeofficial.github.io','45.221.4.23','2024-03-15 11:11:44','2024-03-15 11:11:44','This is remarkably good. nonetheless, I would suggest that  on the About tab, to add a little more details about your personal life in code path so that the developers who will read, that should be more motivated and lead them to a critical decision in their commitment. Then in the new link contact tab. I would suggest to manage the very big empty space at the bottom by putting links or \r\na bottom footer element. Eventually, I would like to suggest a form login that leads to all the social media whether someone submit.',0,'1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36','comment',0,0),(6,229,'Efatha Rutakaza','efathabyamungu4@gmail.com','','45.221.4.23','2024-03-15 11:18:57','2024-03-15 11:18:57','This website helps me very much, I as a web developer to think more on the Best websites, I\'ve been browsing lately! \r\nI\'m convinced that this is a very smart one.',0,'1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36','comment',0,0),(7,229,'Candice Aguilar','fiercejamal@aol.com','https://kamaoimino.com','178.159.37.56','2024-03-16 21:20:05','2024-03-16 21:20:05','I will immediately grab your rss as I can not to find your email \r\nsubscription link or e-newsletter service. Do you\'ve any?\r\nKindly let me recognize in order that I may subscribe.\r\nThanks.',0,'spam','Mozilla/5.0 (Windows NT 10.0; rv:91.0) Gecko/20100101 Firefox/91.0','comment',0,0),(8,229,'Edward Googe','froreloupe@msn.com','https://sveltcolza.com','178.159.37.56','2024-03-19 02:38:54','2024-03-19 02:38:54','Howdy! This blog post couldn\'t be written much better! Looking at this post reminds me of my previous roommate!\r\nHe constantly kept preaching about this. I most certainly will send \r\nthis post to him. Fairly certain he\'s going to have a good read.\r\nThanks for sharing!',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:100.0) Gecko/20100101 Firefox/100.0','comment',0,0),(11,310,'GvrPpYNkSOJxgysM','luzrivardxl@outlook.com','http://yipPtonWRIOVLkJ','117.121.202.90','2024-04-18 13:35:52','2024-04-18 13:35:52','SFRZEDrA',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','comment',0,0),(12,310,'GvrPpYNkSOJxgysM','luzrivardxl@outlook.com','http://yipPtonWRIOVLkJ','117.121.202.90','2024-04-18 13:36:08','2024-04-18 13:36:08','GKTANCxSRuoep',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','comment',0,0),(13,310,'sMqHBvjTO','arey7zll@outlook.com','http://XodlucQaNkWOJIjx','185.18.212.190','2024-04-21 04:16:46','2024-04-21 04:16:46','imdLkMPZre',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','comment',0,0),(14,310,'RJLpNxHrBYDzAlm','irmak3_wells4v@outlook.com','http://JUlTQoIaW','54.37.155.13','2024-04-24 08:03:47','2024-04-24 08:03:47','YkDpolOXfCgmvnJL',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','comment',0,0),(15,310,'ElNrnZLvaJBDxU','burgesfernanda01@outlook.com','http://QwRWLnOxl','131.72.68.222','2024-04-27 21:56:11','2024-04-27 21:56:11','YlOjoZdmx',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','comment',0,0),(16,310,'eQBPaTbZ','suttongrir1994@gmail.com','http://jfWOIkEB','116.111.147.8','2024-05-04 08:06:05','2024-05-04 08:06:05','frsdLwDB',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','comment',0,0),(17,310,'eQBPaTbZ','suttongrir1994@gmail.com','http://jfWOIkEB','116.111.147.8','2024-05-04 08:06:26','2024-05-04 08:06:26','walkXDMcsAfHIo',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','comment',0,0),(18,310,'mcACYRtyWaPUSX','jordansd_hillvq@outlook.com','http://VHezpuaDUwG','124.248.191.43','2024-05-07 10:38:20','2024-05-07 10:38:20','kDjghMfvVpwPR',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','comment',0,0),(19,310,'mcACYRtyWaPUSX','jordansd_hillvq@outlook.com','http://VHezpuaDUwG','124.248.191.43','2024-05-07 10:38:37','2024-05-07 10:38:37','KzIQcaxmPSDMdXZ',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','comment',0,0),(20,430,'Bahati Philemon','bahatiphilmon8@gmail.com','','169.239.159.113','2024-05-08 14:13:08','2024-05-08 14:13:08','Thank you very much Michael, we\'re really grateful to you for all the training you\'re giving us.',0,'1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36','comment',0,0),(21,310,'PsmzHjdUZaeK','brandy_coleman1982@yahoo.com','http://eozPJjTcxDwhA','188.173.163.40','2024-05-14 05:15:09','2024-05-14 05:15:09','oaicpjqS',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','comment',0,0),(22,436,'Bahati Philemon','bahatiphilmon8@gmail.com','','206.42.84.117','2024-05-17 18:43:05','2024-05-17 18:43:05','We are working hard to complete this project',0,'1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36','comment',0,0),(23,310,'KlfFyQZNs','georgegp_savoycc@outlook.com','http://JfkoRYcOrEgX','200.142.107.237','2024-05-17 20:58:50','2024-05-17 20:58:50','uyWERjpn',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','comment',0,0),(24,444,'Bahati Philemon','bahatiphilmon8@gmail.com','','169.239.159.178','2024-05-21 19:40:35','2024-05-21 19:40:35','Great post on using GitHub issues and labels to improve communication and collaboration within a team!\r\n\r\nThe use of clear labels like \"Stuck!!\" and \"High Priority!!\" seems like a very effective way to flag potential roadblocks and ensure everyone is on the same page.\r\n\r\nI particularly like the emphasis on daily reviewing the issues list. This helps keep everyone informed and fosters a sense of shared responsibility for the project\'s progress.',0,'1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36','comment',0,0),(26,444,'Michael','michaelkentburns@gmail.com','https://michaelkentburns.com','75.212.251.79','2024-05-24 12:08:56','2024-05-24 12:08:56','I’m interested in following this blog. So I clicked the checkbox ✅ below.',0,'1','Mozilla/5.0 (iPad; CPU OS 17_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Mobile/15E148 Safari/604.1','comment',0,1),(27,430,'Efatha Rutakaza','efathabyamungu4@gmail.com','https://efatha.github.io/my-portofolio/','169.239.159.35','2024-05-24 17:52:40','2024-05-24 17:52:40','Let\'s celebrate all together this amazing work provided collectively by the team members with potentials skills and efforts made to achieve this goal.',0,'1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36','comment',0,0),(28,400,'QBdiMLPeO','yvonne24yazziekql@outlook.com','http://smoQHMjDYunFiheP','201.20.104.54','2024-05-24 21:33:07','2024-05-24 21:33:07','XfaTPcbKSkmrE',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','comment',0,0),(29,444,'I Fashion Styles','Pouliot50522@hotmail.com','https://www.ifashionstyles.com','167.160.172.22','2024-05-25 21:54:48','2024-05-25 21:54:48','I have witnessed that service fees for internet degree specialists tend to be a terrific value. For example a full 4-year college Degree in Communication in the University of Phoenix Online consists of Sixty credits at $515/credit or $30,900. Also American Intercontinental University Online provides a Bachelors of Business Administration with a whole education course element of 180 units and a tariff of $30,560. Online degree learning has made getting your higher education degree been so cool because you can earn your current degree through the comfort of your dwelling place and when you finish working. Thanks for all the other tips I\'ve learned through the website.',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36','comment',0,0),(30,444,'Hottest Hairstyles','Buenger66177@hotmail.com','https://www.hairstylesvip.com','192.158.226.17','2024-05-26 21:06:54','2024-05-26 21:06:54','Greetings from Carolina! I\'m bored to death at work so I decided to check out your site on my iphone during lunch break. I love the information you provide here and can\'t wait to take a look when I get home. I\'m shocked at how fast your blog loaded on my phone .. I\'m not even using WIFI, just 3G .. Anyhow, superb site!',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36','comment',0,0),(31,444,'Hairstyles Length','Keifer76584@hotmail.com','https://www.hairstylesvip.com/length','198.12.64.38','2024-05-28 22:55:45','2024-05-28 22:55:45','Hi! I\'m at work browsing your blog from my new iphone 4! Just wanted to say I love reading through your blog and look forward to all your posts! Carry on the excellent work!',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36','comment',0,0),(32,444,'Hairstyles','Harkins88324@hotmail.com','https://www.hairstylesvip.com','198.12.64.44','2024-05-30 10:56:39','2024-05-30 10:56:39','Superb blog! Do you have any hints for aspiring writers? I\'m hoping to start my own site soon but I\'m a little lost on everything. Would you advise starting with a free platform like Wordpress or go for a paid option? There are so many options out there that I\'m totally overwhelmed .. Any suggestions? Thanks!',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36','comment',0,0),(33,444,'Food','Manion25072@hotmail.com','https://www.ifashionstyles.com/Food','183.30.187.175','2024-05-31 10:14:04','2024-05-31 10:14:04','Youre so cool! I dont suppose Ive read something like this before. So nice to search out any person with some original ideas on this subject. realy thank you for starting this up. this web site is something that is wanted on the internet, someone with a bit of originality. helpful job for bringing one thing new to the internet!',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36','comment',0,0),(34,444,'LifeStyle','Pfeifle81343@yahoo.com','https://www.ifashionstyles.com/LifeStyle','172.241.214.246','2024-05-31 21:56:22','2024-05-31 21:56:22','Nice read, I just passed this onto a friend who was doing some research on that. And he just bought me lunch because I found it for him smile So let me rephrase that: Thanks for lunch!',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36','comment',0,0),(35,444,'Food','Digeorgio7708@hotmail.com','https://www.ifashionstyles.com/Food','149.88.108.7','2024-06-01 12:04:48','2024-06-01 12:04:48','Thank you for sharing superb informations. Your web-site is very cool. I\'m impressed by the details that you have on this blog. It reveals how nicely you perceive this subject. Bookmarked this web page, will come back for extra articles. You, my pal, ROCK! I found simply the info I already searched all over the place and simply could not come across. What an ideal web-site.',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36','comment',0,0),(36,400,'sLbWtTqmI','vkelley8002@gmail.com','http://CrnmMVEZ','51.68.229.7','2024-06-02 07:06:28','2024-06-02 07:06:28','EXfLQoWPaIRU',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','comment',0,0),(37,444,'Hair Type','Eugenio74670@hotmail.com','https://www.hairstylesvip.com/hair-type','198.23.242.154','2024-06-02 23:37:27','2024-06-02 23:37:27','Hey There. I found your weblog the use of msn. That is a very smartly written article. I will make sure to bookmark it and return to learn more of your useful info. Thanks for the post. I抣l definitely comeback.',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36','comment',0,0),(38,462,'Bahati Philemon','bahatiphilmon8@gmail.com','','169.239.159.58','2024-06-03 09:31:14','2024-06-03 09:31:14','Thank you for sharing this wonderful story and we\'re delighted to have put your recommendations into practice.',0,'1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36','comment',0,0),(39,436,'Efatha Rutakaza','efathabyamungu4@gmail.com','https://efatha.github.io/pastry.nas/','169.239.159.108','2024-06-04 08:28:16','2024-06-04 08:28:16','Good results should be implemented in the project thanks to our determination and commitment on this team work project. It helps us to work as a team with tremendous and potential tools and strategies worth to know in our learning process. I\'m happy for doing the HTML and CSS work part and happy working in a team with Michael as the project owner and coworkers, Philemon and Ash. We all work diligently to reach to the expectation of this project.',0,'1','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Mobile Safari/537.36','comment',0,0),(40,444,'Efatha Rutakaza','efathabyamungu4@gmail.com','https://efatha.github.io/pastry.nas/','169.239.159.48','2024-06-04 19:05:45','2024-06-04 19:05:45','This post helped us to work as a professional team in collaborating by creating issues and suitable labels for them, in order to alert the whole team, so that they stand and focus on what has to be resolved foremost.  For instance: The issues with the label like \"high priority\", \"bug\" or \"stuck\" when this one needs help from other team members, to keep on working. This is a professional way that we communicate effectively on our project. \r\n\r\nIt has changed the my perspective on working as a part of professional team on web development. At the beginning, I had a blot on the landscape, but now I have the deep understanding on how it works.',0,'1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36','comment',0,0),(41,2,'Kak_pcKi','fmnjwqgitKi@xruma.com','https://plastica.onclinic.ru','93.170.204.95','2024-06-05 04:40:15','2024-06-05 04:40:15','Самые действенные меры по борьбе с бородавками. \r\nОксолиновая мазь от бородавок <a href=\"https://plastica.onclinic.ru/\" / rel=\"nofollow ugc\">Оксолиновая мазь от бородавок</a> .',0,'spam','Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36','comment',0,0),(42,2,'Davidkex','algebraically.pawlo@gmail.com','','62.122.184.194','2024-06-06 03:24:56','2024-06-06 03:24:56','Hej, jeg ønskede at kende din pris.',0,'trash','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Iron Safari/537.36','comment',0,0),(43,462,'Efatha Rutakaza','efathabyamungu4@gmail.com','https://efatha.github.io/my-portofolio/','169.239.159.34','2024-06-06 19:49:56','2024-06-06 19:49:56','I encourage the collaboration effort between us and the timely communication in maintaining productivity. Because each member played a crucial role-identifying the problem, providing a solution, and verifying the fix. This division of responsibilities and quick feedback loop underscores the strength of collaborative problem-solving in a team setting  \r\nThanks to Michael Kent Burns for the project owner for providing in this project these work-tools, experience and qualities as professional web developer. I, Philemon and Ash have learned a couple of things in this field.\r\n For the best practice in software development, the team use GitHub for issue tracking and communication. We are happy for this progress.',0,'1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36','comment',0,0),(44,2,'Amandatroupe1','amandaZEVAWAPYc@gmail.com','','37.99.3.9','2024-06-08 05:28:01','2024-06-08 05:28:01','Hey darling, want to hang out? -  https://rb.gy/trhl05?Weaddy',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.5060.134 Safari/537.36','comment',0,0),(45,2,'Hairstyles VIP','Longshore16100@yahoo.com','https://www.hairstylesvip.com','64.120.2.184','2024-06-08 21:47:36','2024-06-08 21:47:36','I am really loving the theme/design of your web site. Do you ever run into any internet browser compatibility problems? A small number of my blog readers have complained about my site not working correctly in Explorer but looks great in Safari. Do you have any recommendations to help fix this issue?',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36','comment',0,0),(46,2,'FtVcmjavS','anthony20williamsjho@outlook.com','http://OBQCilrbFpPjNc','92.87.142.135','2024-06-09 07:19:54','2024-06-09 07:19:54','LDUMTdCAKpV',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','comment',0,0),(47,462,'JerryHam','luxemoda2024@hotmail.com','https://luxe-moda.ru/','85.239.57.136','2024-06-09 09:22:22','2024-06-09 09:22:22','Несомненно стильные новости индустрии. \r\nАктуальные события мировых подуимов. \r\nМодные дома, торговые марки, высокая мода. \r\nСамое лучшее место для стильныех людей. \r\nhttps://luxe-moda.ru/',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.5042.0 Safari/537.36','comment',0,0),(48,2,'VhogduaODFeTW','artemnlolvo@outlook.com','http://YyZbdiRNUn','103.190.114.202','2024-06-12 12:01:05','2024-06-12 12:01:05','nZXwvzCpj',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','comment',0,0),(49,462,'VhogduaODFeTW','artemnlolvo@outlook.com','http://YyZbdiRNUn','103.190.114.202','2024-06-12 12:01:40','2024-06-12 12:01:40','uphUGtxC',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','comment',0,0),(50,462,'DonaldNup','fashion5main@hotmail.com','https://fashion5.ru/','91.242.228.200','2024-06-13 04:08:53','2024-06-13 04:08:53','Очень стильные новости модного мира. \r\nАктуальные эвенты известнейших подуимов. \r\nМодные дома, торговые марки, высокая мода. \r\nЛучшее место для трендовых хайпбистов. \r\nhttps://fashion5.ru/',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36 Herring/95.1.8810.11','comment',0,0),(51,462,'Zofianom','stylecross@hotmail.com','https://stylecross.ru/','45.148.235.205','2024-06-13 11:26:39','2024-06-13 11:26:39','Полностью свежие новости моды. \r\nИсчерпывающие мероприятия известнейших подуимов. \r\nМодные дома, торговые марки, гедонизм. \r\nЛучшее место для модных людей. \r\nhttps://stylecross.ru/',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36 Herring/95.1.8810.11','comment',0,0),(52,2,'Robertkex','cautioningsehomogen@gmail.com','','176.111.174.153','2024-06-14 09:10:32','2024-06-14 09:10:32','Ciao, volevo sapere il tuo prezzo.',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Iron Safari/537.36','comment',0,0),(53,52,'dramago.live','kay_sed@hotmail.com','https://dramago.live/','111.88.52.191','2024-06-15 07:32:38','2024-06-15 07:32:38','Hi,Check out [www.dramago.live] for all your favorite Korean series. They have a huge variety of the latest and popular dramas you can watch anytime on any device.Thanks!',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36','comment',0,0),(54,2,'MKfaEymNrZ','acowanwj30@gmail.com','http://czYsdiCQrF','136.228.158.127','2024-06-15 19:09:44','2024-06-15 19:09:44','ZTrEtdxL',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','comment',0,0),(55,310,'MKfaEymNrZ','acowanwj30@gmail.com','http://czYsdiCQrF','136.228.158.127','2024-06-15 19:10:00','2024-06-15 19:10:00','tTGmajsKSnC',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','comment',0,0),(56,2,'Masonkex','yjdisantoyjdissemin@gmail.com','','85.209.11.117','2024-06-15 23:49:36','2024-06-15 23:49:36','Hi, kam dashur të di çmimin tuaj',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.6099.71 Safari/537.36','comment',0,0),(57,22,'mikeenquili','p.r.o.sp.e.r.i.tyt.h.e.mo.neyuspen@gmail.com','','146.70.52.118','2024-06-17 11:20:21','2024-06-17 11:20:21','A financial renaissance awaits you! Will you have an income of up to $ 500 a day in a month? Find out how you can become part of the new era of financial success!\r\n - https://rb.gy/9fznxm?Woms-dex',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36','comment',0,0),(58,462,'Zofianom','stylecross@hotmail.com','https://whitesneaker.ru/','185.68.247.157','2024-06-18 03:10:37','2024-06-18 03:10:37','Наиболее актуальные события подиума. \r\nАбсолютно все эвенты всемирных подуимов. \r\nМодные дома, лейблы, высокая мода. \r\nСвежее место для модных людей. \r\nhttps://whitesneaker.ru/',0,'spam','Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.5060.114 Safari/537.36','comment',0,0),(59,462,'Darrellpramb','rfsneakers2024@hotmail.com','https://rfsneakers.ru','193.233.141.98','2024-06-18 06:29:51','2024-06-18 06:29:51','Несомненно трендовые новости индустрии. \r\nВсе мероприятия лучших подуимов. \r\nМодные дома, лейблы, высокая мода. \r\nИнтересное место для трендовых хайпбистов. \r\nhttps://rfsneakers.ru',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.5060.66 Safari/537.36','comment',0,0),(60,404,'cantrellmechanicfcy5k2+3jq6iv0dagrr@gmail.com','cantrellmechanicfcy5k2+3jq6iv0dagrr@gmail.com','http://callumthompson.co','45.15.72.89','2024-06-18 13:59:36','2024-06-18 13:59:36','ratione dolor aut eveniet delectus ab nisi quos animi quod delectus aut accusamus. blanditiis cupiditate necessitatibus aut dolorem ducimus nemo quo quia vel ipsa quo inventore a cupiditate voluptatem et nobis deleniti. nisi asperiores qui consequatur optio ipsam quidem. exercitationem hic beatae atque porro unde quas laboriosam officiis ut rerum autem dicta ea accusantium.',0,'spam','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4.1 Safari/605.1.15','comment',0,0),(61,2,'tMCbQgZsknOXJR','eflinty40@gmail.com','http://ltQJCVSknisb','104.28.225.223','2024-06-18 22:59:22','2024-06-18 22:59:22','sELXGbeHSu',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','comment',0,0),(62,2,'X22Finly','xrumer23Finly@gmail.com','','93.170.204.202','2024-06-19 16:09:50','2024-06-19 16:09:50','Hey people!!!!! \r\nGood mood and good luck to everyone!!!!!',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Iron Safari/537.36','comment',0,0),(63,462,'OrvilleTup','modavmode@hotmail.com','https://modavmode.ru','185.77.221.173','2024-06-20 03:52:20','2024-06-20 03:52:20','Несомненно свежие новости мировых подиумов. \r\nВажные эвенты лучших подуимов. \r\nМодные дома, лейблы, высокая мода. \r\nСамое лучшее место для модных людей. \r\nhttps://modavmode.ru',0,'spam','Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36','comment',0,0),(64,2,'Diagnostik_uySi','tkorsedmlSi@xruma.store','https://diagnost-avtokondicioner.ru/','93.170.204.225','2024-06-21 10:42:16','2024-06-21 10:42:16','Где найти квалифицированных специалистов по диагностике автокондиционеров в Москве. \r\nЗаправка и диагностика автокондиционера <a href=\"http://diagnost-avtokondicioner.ru/\" / rel=\"nofollow ugc\">http://diagnost-avtokondicioner.ru/</a> .',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Iron Safari/537.36','comment',0,0),(65,462,'RichardAxock','miramoda2024@hotmail.com','https://miramoda.ru','91.242.228.200','2024-06-21 13:07:05','2024-06-21 13:07:05','Абсолютно актуальные события индустрии. \r\nИсчерпывающие новости всемирных подуимов. \r\nМодные дома, лейблы, высокая мода. \r\nЛучшее место для стильныех людей. \r\nhttps://miramoda.ru',0,'spam','Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36','comment',0,0),(66,462,'Davidcat','urbanmoda2024@hotmail.com','https://urban-moda.ru/','185.152.95.53','2024-06-22 12:42:01','2024-06-22 12:42:01','Абсолютно стильные события моды. \r\nАктуальные мероприятия лучших подуимов. \r\nМодные дома, торговые марки, высокая мода. \r\nЛучшее место для трендовых людей. \r\nhttps://urban-moda.ru/',0,'spam','Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.5060.114 Safari/537.36','comment',0,0),(67,2,'Proverka_vnMa','prwhkuoowMa@xruma.com','https://zapravka-avtokonditsioner.ru/','93.170.204.138','2024-06-22 17:33:36','2024-06-22 17:33:36','Полезные советы по обслуживанию кондиционеров авто в Москве, плюсы и минусы. \r\nЗаправка автокондиционера по манометру гарантирует правильное количество хладагента. <a href=\"http://www.zapravka-avtokonditsioner.ru\" rel=\"nofollow ugc\">http://www.zapravka-avtokonditsioner.ru</a> .',0,'spam','Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36','comment',0,0),(68,462,'PabloNeogs','sofiamoda2024@hotmail.com','https://sofiamoda.ru','185.94.35.249','2024-06-23 02:18:46','2024-06-23 02:18:46','Очень актуальные новости подиума. \r\nВсе эвенты лучших подуимов. \r\nМодные дома, торговые марки, гедонизм. \r\nИнтересное место для трендовых людей. \r\nhttps://sofiamoda.ru',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.5060.134 Safari/537.36','comment',0,0),(69,462,'WillieMic','fashionsecret2024@hotmail.com','https://fashionsecret.ru','193.233.138.113','2024-06-25 11:55:10','2024-06-25 11:55:10','Самые стильные новости модного мира. \r\nАбсолютно все эвенты лучших подуимов. \r\nМодные дома, бренды, высокая мода. \r\nИнтересное место для модных хайпбистов. \r\nhttps://fashionsecret.ru',0,'spam','Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.5060.53 Safari/537.36','comment',0,0),(70,2,'VDOUcERtNFiJnXu','sarah.brady1980@yahoo.com','http://OuJbTdRWAmD','176.126.224.95','2024-06-26 06:29:54','2024-06-26 06:29:54','ShVlsUIunrY',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','comment',0,0),(71,462,'DebraCer','worldsfashion2024@hotmail.com','https://worldsfashion.ru/','212.119.45.32','2024-06-26 07:33:10','2024-06-26 07:33:10','Абсолютно актуальные новости мировых подиумов. \r\nАбсолютно все эвенты всемирных подуимов. \r\nМодные дома, лейблы, haute couture. \r\nИнтересное место для стильныех людей. \r\nhttps://worldsfashion.ru/',0,'spam','Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.5060.114 Safari/537.36','comment',0,0),(72,462,'Danielsautt','fashionablelook@hotmail.com','https://fashionablelook.ru','91.242.228.157','2024-06-26 13:19:04','2024-06-26 13:19:04','Точно свежие новинки моды. \r\nВсе мероприятия всемирных подуимов. \r\nМодные дома, бренды, высокая мода. \r\nСвежее место для модных хайпбистов. \r\nhttps://fashionablelook.ru',0,'spam','Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.5060.114 Safari/537.36','comment',0,0),(73,2,'1win_huka','qsrnmoevuka@sltcartself.com','https://t.me/s/zerkalo_1win_rabochee_nasegodnya','89.110.72.212','2024-06-26 15:41:50','2024-06-26 15:41:50','1win казино — популярная платформа для онлайн азартных игр. Оно предлагает широкий выбор слотов, настольных игр и ставок на спорт в удобном интерфейсе. <a href=\"https://t.me/s/zerkalo_1win_rabochee_nasegodnya\" rel=\"nofollow ugc\">1win зеркало рабочее на сегодня</a> поможет вам обойти блокировки и получить доступ к сайту без ограничений. Бонусы для новых игроков и регулярные акции делают игру выгодной и увлекательной. Стабильная работа сайта и быстрые выплаты делают 1win привлекательным выбором для любителей азарта.',0,'spam','Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36','comment',0,0),(74,462,'ClydeFrofs','modavgorode@hotmail.com','https://modavgorode.ru','193.233.82.64','2024-06-28 03:29:01','2024-06-28 03:29:01','Точно важные события мира fashion. \r\nАктуальные эвенты мировых подуимов. \r\nМодные дома, лейблы, высокая мода. \r\nСамое приятное место для модных людей. \r\nhttps://modavgorode.ru',0,'spam','Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.5060.114 Safari/537.36','comment',0,0),(75,2,'Jokex','kayleighbpsteamship@gmail.com','','176.111.174.153','2024-06-28 19:22:02','2024-06-28 19:22:02','Hi, roeddwn i eisiau gwybod eich pris.',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36','comment',0,0),(76,404,'medranostarckuzz8n0+3jq6j016bqp5@gmail.com','medranostarckuzz8n0+3jq6j016bqp5@gmail.com','http://williamhowe.co','188.130.187.94','2024-06-29 05:05:25','2024-06-29 05:05:25','debitis quod minima et eum id voluptatem voluptatibus incidunt delectus qui recusandae inventore. qui laboriosam suscipit architecto non atque voluptatibus incidunt repudiandae non cum dolore et tenetur saepe.',0,'spam','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4.1 Safari/605.1.15','comment',0,0),(77,2,'kHYlpKFraI','lopezjimjum1982@yahoo.com','http://TCNmGxyuv','103.146.185.82','2024-06-29 14:27:36','2024-06-29 14:27:36','oJUGzEhMwKd',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','comment',0,0),(78,400,'kHYlpKFraI','lopezjimjum1982@yahoo.com','http://TCNmGxyuv','103.146.185.82','2024-06-29 14:28:20','2024-06-29 14:28:20','snXfpVdPieolRh',0,'spam','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','comment',0,0),(79,55,'Suleman Manzoor','ranasuleman014@gmail.com','','206.84.190.95','2024-06-29 21:27:02','2024-06-29 21:27:02','I\'m suleman Manzoor. My skills are HTML CSS Bootstrap5 JavaScript PHP and MySQL.',0,'1','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Mobile Safari/537.36','comment',0,0),(80,506,'Leroyblunc','modaizkomoda@hotmail.com','https://modaizkomoda.ru','85.239.58.251','2024-06-30 01:46:14','2024-06-30 01:46:14','Точно актуальные события моды. \r\nИсчерпывающие события лучших подуимов. \r\nМодные дома, торговые марки, гедонизм. \r\nСамое лучшее место для модных хайпбистов. \r\nhttps://modaizkomoda.ru',0,'spam','Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.5060.114 Safari/537.36','comment',0,0),(81,400,'Destin ASHUZA','ashuzamaheshe4@gmail.com','','135.129.124.207','2024-06-30 11:19:11','2024-06-30 11:19:11','It was truly a pleasure to contribute to these pages of such an enriching site, which helped me understand many things while learning at the same time.',0,'1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36','comment',0,0),(82,444,'Destin ASHUZA','ashuzamaheshe4@gmail.com','','135.129.124.207','2024-06-30 12:23:01','2024-06-30 12:23:01','This post is very interesting, as it accurately describes the process used to facilitate communication on this project, as well as on team projects in general.\r\n\r\nThe concept of communication through issues with labels is a relevant method that I have experienced with others on this project.',0,'1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36','comment',0,0),(83,506,'Destin ASHUZA','ashuzamaheshe4@gmail.com','','135.129.124.207','2024-06-30 12:55:21','2024-06-30 12:55:21','It has been a genuine pleasure to be part of Cohort 1. It will also be a pleasure to assist and guide the new members of Cohort 2.',0,'1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36','comment',0,0),(84,506,'Bahati Philemon','bahatiphilmon8@gmail.com','','169.239.159.33','2024-06-30 18:46:03','2024-06-30 18:46:03','It was a great pleasure to take part in the first cohort, we learnt a lot about teamwork as developers and we\'ll be more than happy to help the second cohort realise the new project and succeed.',0,'1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36','comment',0,0),(85,55,'Michael','michaelkentburns@gmail.com','https://michaelkentburns.com','97.139.80.131','2024-06-30 21:06:35','2024-06-30 21:06:35','I think you may not understand this job post.\n\nYou obviously are an experienced developer. \nThe job I just posted it titled:  Beginner web developer make yourself familiar with the MichaelKentBurns.com training site.\nThis is a limited cohort of beginners. \nI’m not currently accepting people of your experience for mentoring. \nYou are more than welcome to visit my site and learn what you can.  I even welcome thoughtful comments on many pages and posts. \n\nBest of luck, and God bless your learning.',0,'1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Safari/605.1.15','comment',79,1),(86,506,'Joannpaymn','myfashionacademy@hotmail.com','https://myfashionacademy.ru/','178.20.30.98','2024-07-01 06:14:11','2024-07-01 06:14:11','Абсолютно важные новинки индустрии. \r\nИсчерпывающие новости мировых подуимов. \r\nМодные дома, бренды, haute couture. \r\nСамое приятное место для стильныех людей. \r\nhttps://myfashionacademy.ru/',0,'0','Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36','comment',0,0),(87,506,'Roberthussy','metamoda2@hotmail.com','https://metamoda.ru/moda/599-doja-cat-vyzvala-bezumie-v-tope-i-yubke-iz-pishchevoy-plenki-s-rezhisserom-vetements-guram-gvasalia/','193.233.82.64','2024-07-01 18:41:59','2024-07-01 18:41:59','Полностью свежие новости подиума. \r\nИсчерпывающие новости самых влиятельных подуимов. \r\nМодные дома, бренды, haute couture. \r\nПриятное место для стильных хайпбистов. \r\nhttps://metamoda.ru/moda/599-doja-cat-vyzvala-bezumie-v-tope-i-yubke-iz-pishchevoy-plenki-s-rezhisserom-vetements-guram-gvasalia/',0,'0','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36 OpenWave/97.4.2043.44','comment',0,0);
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

-- Dump completed on 2024-07-01 21:32:00
