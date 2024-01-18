-- Adminer 4.8.1 MySQL 5.5.5-10.4.20-MariaDB-1:10.4.20+maria~buster-log dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

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

INSERT INTO `wp_terms` (`term_id`, `name`, `slug`, `term_group`) VALUES
(1,	'Uncategorized',	'uncategorized',	0),
(2,	'twentytwentythree',	'twentytwentythree',	0),
(3,	'twentytwentytwo',	'twentytwentytwo',	0),
(4,	'header',	'header',	0),
(5,	'How to',	'how-to',	0),
(6,	'Quick tips',	'quick-tips',	0),
(7,	'In depth',	'in-depth',	0),
(8,	'Background info',	'background-info',	0),
(9,	'Personal stories',	'personal-stories',	0),
(10,	'Industry news',	'industry-news',	0),
(11,	'Home menu',	'home-menu',	0);

DROP TABLE IF EXISTS `wp_term_relationships`;
CREATE TABLE `wp_term_relationships` (
  `object_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `term_taxonomy_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `term_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`object_id`,`term_taxonomy_id`),
  KEY `term_taxonomy_id` (`term_taxonomy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

INSERT INTO `wp_term_relationships` (`object_id`, `term_taxonomy_id`, `term_order`) VALUES
(7,	2,	0),
(8,	3,	0),
(10,	3,	0),
(10,	4,	0),
(11,	3,	0),
(11,	4,	0),
(21,	11,	0),
(25,	11,	0),
(27,	11,	0),
(33,	8,	0),
(50,	11,	0),
(53,	11,	0),
(56,	11,	0),
(60,	11,	0),
(77,	11,	0),
(80,	11,	0),
(109,	11,	0),
(112,	11,	0);

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

INSERT INTO `wp_term_taxonomy` (`term_taxonomy_id`, `term_id`, `taxonomy`, `description`, `parent`, `count`) VALUES
(1,	1,	'category',	'',	0,	0),
(2,	2,	'wp_theme',	'',	0,	1),
(3,	3,	'wp_theme',	'',	0,	3),
(4,	4,	'wp_template_part_area',	'',	0,	2),
(5,	5,	'category',	'Detailed instructions on common tasks.',	0,	0),
(6,	6,	'category',	'Bite sized advice.',	0,	0),
(7,	7,	'category',	'Detailed explanations.',	0,	0),
(8,	8,	'category',	'Information that explains how things got to be the way they are.',	0,	1),
(9,	9,	'category',	'Stories about persons in our community that inspire us.',	0,	0),
(10,	10,	'category',	'Things happening in the freelance software industry.',	0,	0),
(11,	11,	'nav_menu',	'',	0,	11);

-- 2024-01-18 23:18:44
