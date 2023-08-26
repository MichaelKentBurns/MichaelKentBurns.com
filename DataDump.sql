-- Adminer 4.7.8 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `wp_bv_fw_requests`;
CREATE TABLE `wp_bv_fw_requests` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ip` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `status` int(1) NOT NULL DEFAULT 0,
  `time` bigint(20) NOT NULL DEFAULT 1388516401,
  `path` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `host` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `method` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `resp_code` int(6) NOT NULL DEFAULT 0,
  `category` int(1) NOT NULL DEFAULT 4,
  `referer` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_agent` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `filenames` text COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `query_string` text COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `rules_info` text COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `request_id` varchar(200) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `matched_rules` text COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


DROP TABLE IF EXISTS `wp_bv_ip_store`;
CREATE TABLE `wp_bv_ip_store` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `start_ip_range` varbinary(16) NOT NULL,
  `end_ip_range` varbinary(16) NOT NULL,
  `is_fw` tinyint(1) NOT NULL,
  `is_lp` tinyint(1) NOT NULL,
  `type` int(1) NOT NULL,
  `is_v6` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `ip_range` (`start_ip_range`,`end_ip_range`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;


DROP TABLE IF EXISTS `wp_bv_lp_requests`;
CREATE TABLE `wp_bv_lp_requests` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ip` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `status` int(1) NOT NULL DEFAULT 0,
  `username` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `message` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `category` int(1) NOT NULL DEFAULT 0,
  `time` bigint(20) NOT NULL DEFAULT 1388516401,
  `request_id` varchar(200) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


DROP TABLE IF EXISTS `wp_ce4wp_contacts`;
CREATE TABLE `wp_ce4wp_contacts` (
  `contact_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `first_name` varchar(200) COLLATE utf8mb4_unicode_520_ci DEFAULT '',
  `last_name` varchar(200) COLLATE utf8mb4_unicode_520_ci DEFAULT '',
  `telephone` varchar(200) COLLATE utf8mb4_unicode_520_ci DEFAULT '',
  `consent` varchar(200) COLLATE utf8mb4_unicode_520_ci DEFAULT '',
  PRIMARY KEY (`contact_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


DROP TABLE IF EXISTS `wp_commentmeta`;
CREATE TABLE `wp_commentmeta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `comment_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`meta_id`),
  KEY `comment_id` (`comment_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


DROP TABLE IF EXISTS `wp_comments`;
CREATE TABLE `wp_comments` (
  `comment_ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `comment_post_ID` bigint(20) unsigned NOT NULL DEFAULT 0,
  `comment_author` tinytext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `comment_author_email` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_author_url` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_author_IP` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment_content` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `comment_karma` int(11) NOT NULL DEFAULT 0,
  `comment_approved` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '1',
  `comment_agent` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_type` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'comment',
  `comment_parent` bigint(20) unsigned NOT NULL DEFAULT 0,
  `user_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`comment_ID`),
  KEY `comment_post_ID` (`comment_post_ID`),
  KEY `comment_approved_date_gmt` (`comment_approved`,`comment_date_gmt`),
  KEY `comment_date_gmt` (`comment_date_gmt`),
  KEY `comment_parent` (`comment_parent`),
  KEY `comment_author_email` (`comment_author_email`(10))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


DROP TABLE IF EXISTS `wp_links`;
CREATE TABLE `wp_links` (
  `link_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `link_url` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_name` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_image` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_target` varchar(25) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_description` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_visible` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'Y',
  `link_owner` bigint(20) unsigned NOT NULL DEFAULT 1,
  `link_rating` int(11) NOT NULL DEFAULT 0,
  `link_updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `link_rel` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_notes` mediumtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `link_rss` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`link_id`),
  KEY `link_visible` (`link_visible`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


DROP TABLE IF EXISTS `wp_options`;
CREATE TABLE `wp_options` (
  `option_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `option_name` varchar(191) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `option_value` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `autoload` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`option_id`),
  UNIQUE KEY `option_name` (`option_name`),
  KEY `autoload` (`autoload`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


DROP TABLE IF EXISTS `wp_postmeta`;
CREATE TABLE `wp_postmeta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`meta_id`),
  KEY `post_id` (`post_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


DROP TABLE IF EXISTS `wp_posts`;
CREATE TABLE `wp_posts` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_author` bigint(20) unsigned NOT NULL DEFAULT 0,
  `post_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_title` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_excerpt` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_status` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'publish',
  `comment_status` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'open',
  `ping_status` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'open',
  `post_password` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `post_name` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `to_ping` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `pinged` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_modified_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content_filtered` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_parent` bigint(20) unsigned NOT NULL DEFAULT 0,
  `guid` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `menu_order` int(11) NOT NULL DEFAULT 0,
  `post_type` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'post',
  `post_mime_type` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_count` bigint(20) NOT NULL DEFAULT 0,
  PRIMARY KEY (`ID`),
  KEY `post_name` (`post_name`(191)),
  KEY `type_status_date` (`post_type`,`post_status`,`post_date`,`ID`),
  KEY `post_parent` (`post_parent`),
  KEY `post_author` (`post_author`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


DROP TABLE IF EXISTS `wp_termmeta`;
CREATE TABLE `wp_termmeta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `term_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`meta_id`),
  KEY `term_id` (`term_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


DROP TABLE IF EXISTS `wp_terms`;
CREATE TABLE `wp_terms` (
  `term_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `slug` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `term_group` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`term_id`),
  KEY `slug` (`slug`(191)),
  KEY `name` (`name`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


DROP TABLE IF EXISTS `wp_term_relationships`;
CREATE TABLE `wp_term_relationships` (
  `object_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `term_taxonomy_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `term_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`object_id`,`term_taxonomy_id`),
  KEY `term_taxonomy_id` (`term_taxonomy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


DROP TABLE IF EXISTS `wp_term_taxonomy`;
CREATE TABLE `wp_term_taxonomy` (
  `term_taxonomy_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `term_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `taxonomy` varchar(32) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `description` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `parent` bigint(20) unsigned NOT NULL DEFAULT 0,
  `count` bigint(20) NOT NULL DEFAULT 0,
  PRIMARY KEY (`term_taxonomy_id`),
  UNIQUE KEY `term_id_taxonomy` (`term_id`,`taxonomy`),
  KEY `taxonomy` (`taxonomy`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


DROP TABLE IF EXISTS `wp_usermeta`;
CREATE TABLE `wp_usermeta` (
  `umeta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`umeta_id`),
  KEY `user_id` (`user_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

INSERT INTO `wp_usermeta` (`umeta_id`, `user_id`, `meta_key`, `meta_value`) VALUES
(1,	1,	'nickname',	'michaelkentburns@gmail.com'),
(2,	1,	'first_name',	''),
(3,	1,	'last_name',	''),
(4,	1,	'description',	''),
(5,	1,	'rich_editing',	'true'),
(6,	1,	'syntax_highlighting',	'true'),
(7,	1,	'comment_shortcuts',	'false'),
(8,	1,	'admin_color',	'fresh'),
(9,	1,	'use_ssl',	'0'),
(10,	1,	'show_admin_bar_front',	'true'),
(11,	1,	'locale',	''),
(12,	1,	'wp_capabilities',	'a:1:{s:13:\"administrator\";b:1;}'),
(13,	1,	'wp_user_level',	'10'),
(14,	1,	'dismissed_wp_pointers',	'objectcache-setting-pointer'),
(15,	1,	'show_welcome_panel',	'1'),
(17,	1,	'wp_dashboard_quick_press_last_post_id',	'97'),
(18,	1,	'community-events-location',	'a:1:{s:2:\"ip\";s:11:\"97.139.76.0\";}'),
(19,	1,	'wp_persisted_preferences',	'a:5:{s:14:\"core/edit-site\";a:1:{s:12:\"welcomeGuide\";b:0;}s:9:\"_modified\";s:24:\"2023-04-01T23:46:18.011Z\";s:14:\"core/edit-post\";a:3:{s:26:\"isComplementaryAreaVisible\";b:0;s:12:\"welcomeGuide\";b:0;s:10:\"openPanels\";a:3:{i:0;s:11:\"post-status\";i:1;s:15:\"page-attributes\";i:2;s:23:\"taxonomy-panel-category\";}}s:17:\"core/edit-widgets\";a:2:{s:26:\"isComplementaryAreaVisible\";b:0;s:12:\"welcomeGuide\";b:0;}s:22:\"core/customize-widgets\";a:1:{s:12:\"welcomeGuide\";b:0;}}'),
(20,	1,	'wp_user-settings',	'libraryContent=browse'),
(21,	1,	'wp_user-settings-time',	'1680194294'),
(22,	1,	'managenav-menuscolumnshidden',	'a:5:{i:0;s:11:\"link-target\";i:1;s:11:\"css-classes\";i:2;s:3:\"xfn\";i:3;s:11:\"description\";i:4;s:15:\"title-attribute\";}'),
(23,	1,	'metaboxhidden_nav-menus',	'a:2:{i:0;s:12:\"add-post_tag\";i:1;s:15:\"add-post_format\";}'),
(24,	1,	'nav_menu_recently_edited',	'11'),
(25,	2,	'nickname',	'Michael'),
(26,	2,	'first_name',	'Michael'),
(27,	2,	'last_name',	'Burns'),
(28,	2,	'description',	'Worked for 40 years as a software engineer, the last 20 years as a principle systems developer.  33 years at SAS Institute.\r\n\r\nHe is now retired and enjoying doing software his way on his terms.'),
(29,	2,	'rich_editing',	'true'),
(30,	2,	'syntax_highlighting',	'true'),
(31,	2,	'comment_shortcuts',	'false'),
(32,	2,	'admin_color',	'fresh'),
(33,	2,	'use_ssl',	'0'),
(34,	2,	'show_admin_bar_front',	'true'),
(35,	2,	'locale',	''),
(36,	2,	'wp_capabilities',	'a:1:{s:10:\"subscriber\";b:1;}'),
(37,	2,	'wp_user_level',	'0'),
(38,	2,	'dismissed_wp_pointers',	''),
(40,	2,	'community-events-location',	'a:1:{s:2:\"ip\";s:12:\"75.226.208.0\";}'),
(41,	1,	'session_tokens',	'a:3:{s:64:\"f18840724975a17fbf5ec31c33897886972ffaab8159d0255c61567031d652b3\";a:4:{s:10:\"expiration\";i:1693512755;s:2:\"ip\";s:14:\"72.105.164.166\";s:2:\"ua\";s:119:\"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.5.2 Safari/605.1.15\";s:5:\"login\";i:1692303155;}s:64:\"05251162f5a99af40828fe01ea688978b20769a4e6edb8f2a7371a823b6e20d3\";a:4:{s:10:\"expiration\";i:1693602138;s:2:\"ip\";s:14:\"72.105.164.166\";s:2:\"ua\";s:117:\"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Safari/605.1.15\";s:5:\"login\";i:1692392538;}s:64:\"2e3be2a42c1c15911503a588314cd3be7e62648c603567a0c4917d5cc6fc3dea\";a:4:{s:10:\"expiration\";i:1693263018;s:2:\"ip\";s:13:\"97.139.76.216\";s:2:\"ua\";s:117:\"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Safari/605.1.15\";s:5:\"login\";i:1693090218;}}'),
(43,	1,	'jetpack_tracks_wpcom_id',	'234073799'),
(44,	1,	'my-jetpack-cache-date',	'1681327431'),
(45,	1,	'my-jetpack-cache',	'O:8:\"stdClass\":52:{s:15:\"jetpack_premium\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2000;s:12:\"product_name\";s:15:\"Jetpack Premium\";s:12:\"product_slug\";s:15:\"jetpack_premium\";s:11:\"description\";s:53:\"Daily Backups, Automated Restores and Spam Protection\";s:12:\"product_type\";s:6:\"bundle\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:15:\"jetpack-premium\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:6:\"$99.00\";s:21:\"combined_cost_display\";s:6:\"$99.00\";s:4:\"cost\";i:99;s:18:\"cost_smallest_unit\";i:9900;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:4:\"year\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:16:\"jetpack_business\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2001;s:12:\"product_name\";s:20:\"Jetpack Professional\";s:12:\"product_slug\";s:16:\"jetpack_business\";s:11:\"description\";s:68:\"Daily Backups, Security Scanning, Spam Protection, Polls and Surveys\";s:12:\"product_type\";s:6:\"bundle\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:16:\"jetpack-business\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:7:\"$299.00\";s:21:\"combined_cost_display\";s:7:\"$299.00\";s:4:\"cost\";i:299;s:18:\"cost_smallest_unit\";i:29900;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:4:\"year\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:12:\"jetpack_free\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2002;s:12:\"product_name\";s:12:\"Jetpack Free\";s:12:\"product_slug\";s:12:\"jetpack_free\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:12:\"jetpack-free\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:5:\"$0.00\";s:21:\"combined_cost_display\";s:5:\"$0.00\";s:4:\"cost\";i:0;s:18:\"cost_smallest_unit\";i:0;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:8:\"one time\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:23:\"jetpack_premium_monthly\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2003;s:12:\"product_name\";s:15:\"Jetpack Premium\";s:12:\"product_slug\";s:23:\"jetpack_premium_monthly\";s:11:\"description\";s:49:\"Daily Backups, Security Scanning, Spam Protection\";s:12:\"product_type\";s:6:\"bundle\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:15:\"jetpack-premium\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:5:\"$9.00\";s:21:\"combined_cost_display\";s:5:\"$9.00\";s:4:\"cost\";i:9;s:18:\"cost_smallest_unit\";i:900;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:5:\"month\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:24:\"jetpack_business_monthly\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2004;s:12:\"product_name\";s:20:\"Jetpack Professional\";s:12:\"product_slug\";s:24:\"jetpack_business_monthly\";s:11:\"description\";s:67:\"Daily Backups, Malware Scanning, Threat Resolution, Spam Protection\";s:12:\"product_type\";s:6:\"bundle\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:16:\"jetpack-business\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:6:\"$29.00\";s:21:\"combined_cost_display\";s:6:\"$29.00\";s:4:\"cost\";i:29;s:18:\"cost_smallest_unit\";i:2900;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:5:\"month\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:16:\"jetpack_personal\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2005;s:12:\"product_name\";s:16:\"Jetpack Personal\";s:12:\"product_slug\";s:16:\"jetpack_personal\";s:11:\"description\";s:83:\"Daily backups, Spam protection, plus all the features you already love from Jetpack\";s:12:\"product_type\";s:6:\"bundle\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:16:\"jetpack-personal\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:6:\"$39.00\";s:21:\"combined_cost_display\";s:6:\"$39.00\";s:4:\"cost\";i:39;s:18:\"cost_smallest_unit\";i:3900;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:4:\"year\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:24:\"jetpack_personal_monthly\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2006;s:12:\"product_name\";s:16:\"Jetpack Personal\";s:12:\"product_slug\";s:24:\"jetpack_personal_monthly\";s:11:\"description\";s:67:\"Daily Backups, Malware Scanning, Threat Resolution, Spam Protection\";s:12:\"product_type\";s:6:\"bundle\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:16:\"jetpack-personal\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:5:\"$3.50\";s:21:\"combined_cost_display\";s:5:\"$3.50\";s:4:\"cost\";d:3.5;s:18:\"cost_smallest_unit\";i:350;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:5:\"month\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:22:\"jetpack_security_daily\";O:8:\"stdClass\":19:{s:10:\"product_id\";i:2010;s:12:\"product_name\";s:22:\"Jetpack Security Daily\";s:12:\"product_slug\";s:22:\"jetpack_security_daily\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:22:\"jetpack-security-daily\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:7:\"$299.00\";s:21:\"combined_cost_display\";s:7:\"$299.00\";s:4:\"cost\";i:299;s:18:\"cost_smallest_unit\";i:29900;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:4:\"year\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";s:18:\"introductory_offer\";O:8:\"stdClass\":6:{s:13:\"interval_unit\";s:4:\"year\";s:14:\"interval_count\";i:1;s:11:\"usage_limit\";N;s:17:\"cost_per_interval\";i:149;s:30:\"transition_after_renewal_count\";i:0;s:30:\"should_prorate_when_offer_ends\";b:0;}}s:30:\"jetpack_security_daily_monthly\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2011;s:12:\"product_name\";s:22:\"Jetpack Security Daily\";s:12:\"product_slug\";s:30:\"jetpack_security_daily_monthly\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:22:\"jetpack-security-daily\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:6:\"$24.95\";s:21:\"combined_cost_display\";s:6:\"$24.95\";s:4:\"cost\";d:24.95;s:18:\"cost_smallest_unit\";i:2495;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:5:\"month\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:25:\"jetpack_security_realtime\";O:8:\"stdClass\":19:{s:10:\"product_id\";i:2012;s:12:\"product_name\";s:26:\"Jetpack Security Real-time\";s:12:\"product_slug\";s:25:\"jetpack_security_realtime\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:25:\"jetpack-security-realtime\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:7:\"$839.00\";s:21:\"combined_cost_display\";s:7:\"$839.00\";s:4:\"cost\";i:839;s:18:\"cost_smallest_unit\";i:83900;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:4:\"year\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";s:18:\"introductory_offer\";O:8:\"stdClass\":6:{s:13:\"interval_unit\";s:4:\"year\";s:14:\"interval_count\";i:1;s:11:\"usage_limit\";N;s:17:\"cost_per_interval\";i:419;s:30:\"transition_after_renewal_count\";i:0;s:30:\"should_prorate_when_offer_ends\";b:0;}}s:33:\"jetpack_security_realtime_monthly\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2013;s:12:\"product_name\";s:26:\"Jetpack Security Real-time\";s:12:\"product_slug\";s:33:\"jetpack_security_realtime_monthly\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:25:\"jetpack-security-realtime\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:6:\"$69.95\";s:21:\"combined_cost_display\";s:6:\"$69.95\";s:4:\"cost\";d:69.95;s:18:\"cost_smallest_unit\";i:6995;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:5:\"month\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:16:\"jetpack_complete\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2014;s:12:\"product_name\";s:16:\"Jetpack Complete\";s:12:\"product_slug\";s:16:\"jetpack_complete\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:16:\"jetpack-complete\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:7:\"$600.00\";s:21:\"combined_cost_display\";s:7:\"$600.00\";s:4:\"cost\";i:600;s:18:\"cost_smallest_unit\";i:60000;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:4:\"year\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:24:\"jetpack_complete_monthly\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2015;s:12:\"product_name\";s:16:\"Jetpack Complete\";s:12:\"product_slug\";s:24:\"jetpack_complete_monthly\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:16:\"jetpack-complete\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:7:\"$100.00\";s:21:\"combined_cost_display\";s:7:\"$100.00\";s:4:\"cost\";i:100;s:18:\"cost_smallest_unit\";i:10000;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:5:\"month\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:26:\"jetpack_security_t1_yearly\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2016;s:12:\"product_name\";s:23:\"Jetpack Security (10GB)\";s:12:\"product_slug\";s:26:\"jetpack_security_t1_yearly\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:6:\"bundle\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:23:\"jetpack-security-tier-1\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:7:\"$240.00\";s:21:\"combined_cost_display\";s:7:\"$240.00\";s:4:\"cost\";i:240;s:18:\"cost_smallest_unit\";i:24000;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:4:\"year\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:27:\"jetpack_security_t1_monthly\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2017;s:12:\"product_name\";s:23:\"Jetpack Security (10GB)\";s:12:\"product_slug\";s:27:\"jetpack_security_t1_monthly\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:6:\"bundle\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:23:\"jetpack-security-tier-1\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:6:\"$40.00\";s:21:\"combined_cost_display\";s:6:\"$40.00\";s:4:\"cost\";i:40;s:18:\"cost_smallest_unit\";i:4000;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:5:\"month\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:26:\"jetpack_security_t2_yearly\";O:8:\"stdClass\":19:{s:10:\"product_id\";i:2019;s:12:\"product_name\";s:22:\"Jetpack Security (1TB)\";s:12:\"product_slug\";s:26:\"jetpack_security_t2_yearly\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:6:\"bundle\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:23:\"jetpack-security-tier-2\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:7:\"$899.40\";s:21:\"combined_cost_display\";s:7:\"$899.40\";s:4:\"cost\";d:899.4;s:18:\"cost_smallest_unit\";i:89940;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:4:\"year\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";s:18:\"introductory_offer\";O:8:\"stdClass\":6:{s:13:\"interval_unit\";s:4:\"year\";s:14:\"interval_count\";i:1;s:11:\"usage_limit\";N;s:17:\"cost_per_interval\";d:359.4;s:30:\"transition_after_renewal_count\";i:0;s:30:\"should_prorate_when_offer_ends\";b:0;}}s:27:\"jetpack_security_t2_monthly\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2020;s:12:\"product_name\";s:22:\"Jetpack Security (1TB)\";s:12:\"product_slug\";s:27:\"jetpack_security_t2_monthly\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:6:\"bundle\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:23:\"jetpack-security-tier-2\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:6:\"$74.95\";s:21:\"combined_cost_display\";s:6:\"$74.95\";s:4:\"cost\";d:74.95;s:18:\"cost_smallest_unit\";i:7495;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:5:\"month\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:41:\"jetpack_backup_addon_storage_10gb_monthly\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2040;s:12:\"product_name\";s:47:\"Jetpack VaultPress Backup Add-on Storage (10GB)\";s:12:\"product_slug\";s:41:\"jetpack_backup_addon_storage_10gb_monthly\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:33:\"jetpack-backup-addon-storage-10gb\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:5:\"$6.00\";s:21:\"combined_cost_display\";s:5:\"$6.00\";s:4:\"cost\";i:6;s:18:\"cost_smallest_unit\";i:600;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:5:\"month\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:42:\"jetpack_backup_addon_storage_100gb_monthly\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2044;s:12:\"product_name\";s:48:\"Jetpack VaultPress Backup Add-on Storage (100GB)\";s:12:\"product_slug\";s:42:\"jetpack_backup_addon_storage_100gb_monthly\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:34:\"jetpack-backup-addon-storage-100gb\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:6:\"$10.00\";s:21:\"combined_cost_display\";s:6:\"$10.00\";s:4:\"cost\";i:10;s:18:\"cost_smallest_unit\";i:1000;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:5:\"month\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:40:\"jetpack_backup_addon_storage_1tb_monthly\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2048;s:12:\"product_name\";s:46:\"Jetpack VaultPress Backup Add-on Storage (1TB)\";s:12:\"product_slug\";s:40:\"jetpack_backup_addon_storage_1tb_monthly\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:32:\"jetpack-backup-addon-storage-1tb\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:6:\"$20.00\";s:21:\"combined_cost_display\";s:6:\"$20.00\";s:4:\"cost\";i:20;s:18:\"cost_smallest_unit\";i:2000;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:5:\"month\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:40:\"jetpack_backup_addon_storage_3tb_monthly\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2052;s:12:\"product_name\";s:46:\"Jetpack VaultPress Backup Add-on Storage (3TB)\";s:12:\"product_slug\";s:40:\"jetpack_backup_addon_storage_3tb_monthly\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:32:\"jetpack-backup-addon-storage-3tb\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:6:\"$20.00\";s:21:\"combined_cost_display\";s:6:\"$20.00\";s:4:\"cost\";i:20;s:18:\"cost_smallest_unit\";i:2000;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:5:\"month\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:40:\"jetpack_backup_addon_storage_5tb_monthly\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2056;s:12:\"product_name\";s:46:\"Jetpack VaultPress Backup Add-on Storage (5TB)\";s:12:\"product_slug\";s:40:\"jetpack_backup_addon_storage_5tb_monthly\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:32:\"jetpack-backup-addon-storage-5tb\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:6:\"$30.00\";s:21:\"combined_cost_display\";s:6:\"$30.00\";s:4:\"cost\";i:30;s:18:\"cost_smallest_unit\";i:3000;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:5:\"month\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:20:\"jetpack_backup_daily\";O:8:\"stdClass\":19:{s:10:\"product_id\";i:2100;s:12:\"product_name\";s:22:\"Jetpack Backup (Daily)\";s:12:\"product_slug\";s:20:\"jetpack_backup_daily\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:20:\"jetpack-backup-daily\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:7:\"$119.00\";s:21:\"combined_cost_display\";s:7:\"$119.00\";s:4:\"cost\";i:119;s:18:\"cost_smallest_unit\";i:11900;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:4:\"year\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";s:18:\"introductory_offer\";O:8:\"stdClass\":6:{s:13:\"interval_unit\";s:4:\"year\";s:14:\"interval_count\";i:1;s:11:\"usage_limit\";N;s:17:\"cost_per_interval\";i:59;s:30:\"transition_after_renewal_count\";i:0;s:30:\"should_prorate_when_offer_ends\";b:0;}}s:28:\"jetpack_backup_daily_monthly\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2101;s:12:\"product_name\";s:22:\"Jetpack Backup (Daily)\";s:12:\"product_slug\";s:28:\"jetpack_backup_daily_monthly\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:20:\"jetpack-backup-daily\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:5:\"$9.95\";s:21:\"combined_cost_display\";s:5:\"$9.95\";s:4:\"cost\";d:9.95;s:18:\"cost_smallest_unit\";i:995;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:5:\"month\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:23:\"jetpack_backup_realtime\";O:8:\"stdClass\":19:{s:10:\"product_id\";i:2102;s:12:\"product_name\";s:26:\"Jetpack Backup (Real-time)\";s:12:\"product_slug\";s:23:\"jetpack_backup_realtime\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:23:\"jetpack-backup-realtime\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:7:\"$599.00\";s:21:\"combined_cost_display\";s:7:\"$599.00\";s:4:\"cost\";i:599;s:18:\"cost_smallest_unit\";i:59900;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:4:\"year\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";s:18:\"introductory_offer\";O:8:\"stdClass\":6:{s:13:\"interval_unit\";s:4:\"year\";s:14:\"interval_count\";i:1;s:11:\"usage_limit\";N;s:17:\"cost_per_interval\";i:299;s:30:\"transition_after_renewal_count\";i:0;s:30:\"should_prorate_when_offer_ends\";b:0;}}s:31:\"jetpack_backup_realtime_monthly\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2103;s:12:\"product_name\";s:26:\"Jetpack Backup (Real-time)\";s:12:\"product_slug\";s:31:\"jetpack_backup_realtime_monthly\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:23:\"jetpack-backup-realtime\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:6:\"$49.95\";s:21:\"combined_cost_display\";s:6:\"$49.95\";s:4:\"cost\";d:49.95;s:18:\"cost_smallest_unit\";i:4995;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:5:\"month\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:14:\"jetpack_search\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2104;s:12:\"product_name\";s:14:\"Jetpack Search\";s:12:\"product_slug\";s:14:\"jetpack_search\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:6:\"search\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:14:\"jetpack-search\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:6:\"$99.00\";s:21:\"combined_cost_display\";s:6:\"$99.00\";s:4:\"cost\";i:99;s:18:\"cost_smallest_unit\";i:9900;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:1:{i:0;O:8:\"stdClass\":12:{s:13:\"minimum_units\";i:0;s:13:\"maximum_units\";N;s:13:\"minimum_price\";i:0;s:13:\"maximum_price\";i:0;s:28:\"transform_quantity_divide_by\";i:10000;s:24:\"transform_quantity_round\";s:2:\"up\";s:8:\"flat_fee\";i:0;s:12:\"per_unit_fee\";i:9900;s:21:\"minimum_price_display\";s:2:\"$0\";s:29:\"minimum_price_monthly_display\";s:2:\"$0\";s:21:\"maximum_price_display\";N;s:29:\"maximum_price_monthly_display\";N;}}s:25:\"price_tier_usage_quantity\";i:10;s:12:\"product_term\";s:4:\"year\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:22:\"jetpack_search_monthly\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2105;s:12:\"product_name\";s:14:\"Jetpack Search\";s:12:\"product_slug\";s:22:\"jetpack_search_monthly\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:6:\"search\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:14:\"jetpack-search\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:5:\"$8.25\";s:21:\"combined_cost_display\";s:5:\"$8.25\";s:4:\"cost\";d:8.25;s:18:\"cost_smallest_unit\";i:825;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:1:{i:0;O:8:\"stdClass\":12:{s:13:\"minimum_units\";i:0;s:13:\"maximum_units\";N;s:13:\"minimum_price\";i:0;s:13:\"maximum_price\";i:0;s:28:\"transform_quantity_divide_by\";i:10000;s:24:\"transform_quantity_round\";s:2:\"up\";s:8:\"flat_fee\";i:0;s:12:\"per_unit_fee\";i:825;s:21:\"minimum_price_display\";s:2:\"$0\";s:29:\"minimum_price_monthly_display\";s:2:\"$0\";s:21:\"maximum_price_display\";N;s:29:\"maximum_price_monthly_display\";N;}}s:25:\"price_tier_usage_quantity\";i:10;s:12:\"product_term\";s:5:\"month\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:12:\"jetpack_scan\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2106;s:12:\"product_name\";s:18:\"Jetpack Scan Daily\";s:12:\"product_slug\";s:12:\"jetpack_scan\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:12:\"jetpack-scan\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:7:\"$120.00\";s:21:\"combined_cost_display\";s:7:\"$120.00\";s:4:\"cost\";i:120;s:18:\"cost_smallest_unit\";i:12000;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:4:\"year\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:20:\"jetpack_scan_monthly\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2107;s:12:\"product_name\";s:18:\"Jetpack Scan Daily\";s:12:\"product_slug\";s:20:\"jetpack_scan_monthly\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:12:\"jetpack-scan\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:6:\"$20.00\";s:21:\"combined_cost_display\";s:6:\"$20.00\";s:4:\"cost\";i:20;s:18:\"cost_smallest_unit\";i:2000;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:5:\"month\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:21:\"jetpack_scan_realtime\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2108;s:12:\"product_name\";s:21:\"Jetpack Scan Realtime\";s:12:\"product_slug\";s:21:\"jetpack_scan_realtime\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:21:\"jetpack-scan-realtime\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:7:\"$500.00\";s:21:\"combined_cost_display\";s:7:\"$500.00\";s:4:\"cost\";i:500;s:18:\"cost_smallest_unit\";i:50000;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:4:\"year\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:29:\"jetpack_scan_realtime_monthly\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2109;s:12:\"product_name\";s:21:\"Jetpack Scan Realtime\";s:12:\"product_slug\";s:29:\"jetpack_scan_realtime_monthly\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:21:\"jetpack-scan-realtime\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:6:\"$50.00\";s:21:\"combined_cost_display\";s:6:\"$50.00\";s:4:\"cost\";i:50;s:18:\"cost_smallest_unit\";i:5000;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:5:\"month\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:17:\"jetpack_anti_spam\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2110;s:12:\"product_name\";s:25:\"Jetpack Akismet Anti-spam\";s:12:\"product_slug\";s:17:\"jetpack_anti_spam\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:17:\"jetpack-anti-spam\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:7:\"$120.00\";s:21:\"combined_cost_display\";s:7:\"$120.00\";s:4:\"cost\";i:120;s:18:\"cost_smallest_unit\";i:12000;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:4:\"year\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:25:\"jetpack_anti_spam_monthly\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2111;s:12:\"product_name\";s:25:\"Jetpack Akismet Anti-spam\";s:12:\"product_slug\";s:25:\"jetpack_anti_spam_monthly\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:17:\"jetpack-anti-spam\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:6:\"$20.00\";s:21:\"combined_cost_display\";s:6:\"$20.00\";s:4:\"cost\";i:20;s:18:\"cost_smallest_unit\";i:2000;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:5:\"month\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:24:\"jetpack_backup_t1_yearly\";O:8:\"stdClass\":19:{s:10:\"product_id\";i:2112;s:12:\"product_name\";s:32:\"Jetpack VaultPress Backup (10GB)\";s:12:\"product_slug\";s:24:\"jetpack_backup_t1_yearly\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:21:\"jetpack-backup-tier-1\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:7:\"$120.00\";s:21:\"combined_cost_display\";s:7:\"$120.00\";s:4:\"cost\";i:120;s:18:\"cost_smallest_unit\";i:12000;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:4:\"year\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";s:18:\"introductory_offer\";O:8:\"stdClass\":6:{s:13:\"interval_unit\";s:5:\"month\";s:14:\"interval_count\";i:1;s:11:\"usage_limit\";i:0;s:17:\"cost_per_interval\";i:12;s:30:\"transition_after_renewal_count\";i:0;s:30:\"should_prorate_when_offer_ends\";b:0;}}s:25:\"jetpack_backup_t1_monthly\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2113;s:12:\"product_name\";s:32:\"Jetpack VaultPress Backup (10GB)\";s:12:\"product_slug\";s:25:\"jetpack_backup_t1_monthly\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:21:\"jetpack-backup-tier-1\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:6:\"$20.00\";s:21:\"combined_cost_display\";s:6:\"$20.00\";s:4:\"cost\";i:20;s:18:\"cost_smallest_unit\";i:2000;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:5:\"month\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:24:\"jetpack_backup_t2_yearly\";O:8:\"stdClass\":19:{s:10:\"product_id\";i:2114;s:12:\"product_name\";s:31:\"Jetpack VaultPress Backup (1TB)\";s:12:\"product_slug\";s:24:\"jetpack_backup_t2_yearly\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:21:\"jetpack-backup-tier-2\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:7:\"$359.40\";s:21:\"combined_cost_display\";s:7:\"$359.40\";s:4:\"cost\";d:359.4;s:18:\"cost_smallest_unit\";i:35940;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:4:\"year\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";s:18:\"introductory_offer\";O:8:\"stdClass\":6:{s:13:\"interval_unit\";s:4:\"year\";s:14:\"interval_count\";i:1;s:11:\"usage_limit\";N;s:17:\"cost_per_interval\";d:143.4;s:30:\"transition_after_renewal_count\";i:0;s:30:\"should_prorate_when_offer_ends\";b:0;}}s:25:\"jetpack_backup_t2_monthly\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2115;s:12:\"product_name\";s:31:\"Jetpack VaultPress Backup (1TB)\";s:12:\"product_slug\";s:25:\"jetpack_backup_t2_monthly\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:21:\"jetpack-backup-tier-2\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:6:\"$29.95\";s:21:\"combined_cost_display\";s:6:\"$29.95\";s:4:\"cost\";d:29.95;s:18:\"cost_smallest_unit\";i:2995;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:5:\"month\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:18:\"jetpack_videopress\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2116;s:12:\"product_name\";s:18:\"Jetpack VideoPress\";s:12:\"product_slug\";s:18:\"jetpack_videopress\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:18:\"jetpack-videopress\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:7:\"$120.00\";s:21:\"combined_cost_display\";s:7:\"$120.00\";s:4:\"cost\";i:120;s:18:\"cost_smallest_unit\";i:12000;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:4:\"year\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:26:\"jetpack_videopress_monthly\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2117;s:12:\"product_name\";s:18:\"Jetpack VideoPress\";s:12:\"product_slug\";s:26:\"jetpack_videopress_monthly\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:18:\"jetpack-videopress\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:6:\"$20.00\";s:21:\"combined_cost_display\";s:6:\"$20.00\";s:4:\"cost\";i:20;s:18:\"cost_smallest_unit\";i:2000;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:5:\"month\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:24:\"jetpack_backup_t0_yearly\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2120;s:12:\"product_name\";s:31:\"Jetpack VaultPress Backup (1GB)\";s:12:\"product_slug\";s:24:\"jetpack_backup_t0_yearly\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:21:\"jetpack-backup-tier-0\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:6:\"$35.88\";s:21:\"combined_cost_display\";s:6:\"$35.88\";s:4:\"cost\";d:35.88;s:18:\"cost_smallest_unit\";i:3588;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:4:\"year\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:25:\"jetpack_backup_t0_monthly\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2121;s:12:\"product_name\";s:31:\"Jetpack VaultPress Backup (1GB)\";s:12:\"product_slug\";s:25:\"jetpack_backup_t0_monthly\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:21:\"jetpack-backup-tier-0\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:5:\"$2.99\";s:21:\"combined_cost_display\";s:5:\"$2.99\";s:4:\"cost\";d:2.99;s:18:\"cost_smallest_unit\";i:299;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:5:\"month\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:19:\"jetpack_search_free\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2130;s:12:\"product_name\";s:19:\"Jetpack Search Free\";s:12:\"product_slug\";s:19:\"jetpack_search_free\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:6:\"search\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:19:\"jetpack-search-free\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:5:\"$0.00\";s:21:\"combined_cost_display\";s:5:\"$0.00\";s:4:\"cost\";i:0;s:18:\"cost_smallest_unit\";i:0;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:8:\"one time\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:23:\"jetpack_backup_one_time\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2201;s:12:\"product_name\";s:36:\"Jetpack VaultPress Backup (One-time)\";s:12:\"product_slug\";s:23:\"jetpack_backup_one_time\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:23:\"jetpack-backup-one-time\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:5:\"$0.99\";s:21:\"combined_cost_display\";s:5:\"$0.99\";s:4:\"cost\";d:0.99;s:18:\"cost_smallest_unit\";i:99;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:8:\"one time\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:21:\"jetpack_boost_monthly\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2400;s:12:\"product_name\";s:13:\"Jetpack Boost\";s:12:\"product_slug\";s:21:\"jetpack_boost_monthly\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:13:\"jetpack-boost\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:6:\"$40.00\";s:21:\"combined_cost_display\";s:6:\"$40.00\";s:4:\"cost\";i:40;s:18:\"cost_smallest_unit\";i:4000;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:5:\"month\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:20:\"jetpack_boost_yearly\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2401;s:12:\"product_name\";s:13:\"Jetpack Boost\";s:12:\"product_slug\";s:20:\"jetpack_boost_yearly\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:13:\"jetpack-boost\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:7:\"$240.00\";s:21:\"combined_cost_display\";s:7:\"$240.00\";s:4:\"cost\";i:240;s:18:\"cost_smallest_unit\";i:24000;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:4:\"year\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:22:\"jetpack_social_monthly\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2500;s:12:\"product_name\";s:20:\"Jetpack Social Basic\";s:12:\"product_slug\";s:22:\"jetpack_social_monthly\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:14:\"jetpack-social\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:6:\"$12.00\";s:21:\"combined_cost_display\";s:6:\"$12.00\";s:4:\"cost\";i:12;s:18:\"cost_smallest_unit\";i:1200;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:5:\"month\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:27:\"jetpack_social_basic_yearly\";O:8:\"stdClass\":19:{s:10:\"product_id\";i:2503;s:12:\"product_name\";s:20:\"Jetpack Social Basic\";s:12:\"product_slug\";s:27:\"jetpack_social_basic_yearly\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:27:\"jetpack-social-basic-yearly\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:7:\"$120.00\";s:21:\"combined_cost_display\";s:7:\"$120.00\";s:4:\"cost\";i:120;s:18:\"cost_smallest_unit\";i:12000;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:4:\"year\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";s:18:\"introductory_offer\";O:8:\"stdClass\":6:{s:13:\"interval_unit\";s:5:\"month\";s:14:\"interval_count\";i:1;s:11:\"usage_limit\";i:0;s:17:\"cost_per_interval\";i:12;s:30:\"transition_after_renewal_count\";i:0;s:30:\"should_prorate_when_offer_ends\";b:0;}}s:28:\"jetpack_social_basic_monthly\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2504;s:12:\"product_name\";s:20:\"Jetpack Social Basic\";s:12:\"product_slug\";s:28:\"jetpack_social_basic_monthly\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:27:\"jetpack-social-basic-yearly\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:6:\"$12.00\";s:21:\"combined_cost_display\";s:6:\"$12.00\";s:4:\"cost\";i:12;s:18:\"cost_smallest_unit\";i:1200;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:5:\"month\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:30:\"jetpack_social_advanced_yearly\";O:8:\"stdClass\":19:{s:10:\"product_id\";i:2602;s:12:\"product_name\";s:30:\"Jetpack Social Advanced (Beta)\";s:12:\"product_slug\";s:30:\"jetpack_social_advanced_yearly\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:23:\"jetpack-social-advanced\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:7:\"$180.00\";s:21:\"combined_cost_display\";s:7:\"$180.00\";s:4:\"cost\";i:180;s:18:\"cost_smallest_unit\";i:18000;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:4:\"year\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";s:18:\"introductory_offer\";O:8:\"stdClass\":6:{s:13:\"interval_unit\";s:5:\"month\";s:14:\"interval_count\";i:1;s:11:\"usage_limit\";i:0;s:17:\"cost_per_interval\";i:12;s:30:\"transition_after_renewal_count\";i:0;s:30:\"should_prorate_when_offer_ends\";b:0;}}s:31:\"jetpack_social_advanced_monthly\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2603;s:12:\"product_name\";s:30:\"Jetpack Social Advanced (Beta)\";s:12:\"product_slug\";s:31:\"jetpack_social_advanced_monthly\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:23:\"jetpack-social-advanced\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:6:\"$20.00\";s:21:\"combined_cost_display\";s:6:\"$20.00\";s:4:\"cost\";i:20;s:18:\"cost_smallest_unit\";i:2000;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:5:\"month\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}s:29:\"jetpack_golden_token_lifetime\";O:8:\"stdClass\":18:{s:10:\"product_id\";i:2900;s:12:\"product_name\";s:20:\"Jetpack Golden Token\";s:12:\"product_slug\";s:29:\"jetpack_golden_token_lifetime\";s:11:\"description\";s:0:\"\";s:12:\"product_type\";s:7:\"jetpack\";s:9:\"available\";b:1;s:20:\"billing_product_slug\";s:20:\"jetpack-golden-token\";s:22:\"is_domain_registration\";b:0;s:12:\"cost_display\";s:5:\"$0.00\";s:21:\"combined_cost_display\";s:5:\"$0.00\";s:4:\"cost\";i:0;s:18:\"cost_smallest_unit\";i:0;s:13:\"currency_code\";s:3:\"USD\";s:15:\"price_tier_list\";a:0:{}s:25:\"price_tier_usage_quantity\";N;s:12:\"product_term\";s:8:\"one time\";s:11:\"price_tiers\";a:0:{}s:15:\"price_tier_slug\";s:0:\"\";}}'),
(46,	3,	'nickname',	'ArtAdmin'),
(47,	3,	'first_name',	'Mary'),
(48,	3,	'last_name',	'Burns'),
(49,	3,	'description',	''),
(50,	3,	'rich_editing',	'true'),
(51,	3,	'syntax_highlighting',	'true'),
(52,	3,	'comment_shortcuts',	'false'),
(53,	3,	'admin_color',	'fresh'),
(54,	3,	'use_ssl',	'0'),
(55,	3,	'show_admin_bar_front',	'true'),
(56,	3,	'locale',	''),
(57,	3,	'wp_capabilities',	'a:1:{s:13:\"administrator\";b:1;}'),
(58,	3,	'wp_user_level',	'10'),
(59,	3,	'dismissed_wp_pointers',	'');

DROP TABLE IF EXISTS `wp_users`;
CREATE TABLE `wp_users` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_login` varchar(60) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_pass` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_nicename` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_email` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_url` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_registered` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_activation_key` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_status` int(11) NOT NULL DEFAULT 0,
  `display_name` varchar(250) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `user_login_key` (`user_login`),
  KEY `user_nicename` (`user_nicename`),
  KEY `user_email` (`user_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


-- 2023-08-26 23:02:12
