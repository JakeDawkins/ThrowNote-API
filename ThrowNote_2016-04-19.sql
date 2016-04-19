# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: localhost (MySQL 5.5.42)
# Database: ThrowNote
# Generation Time: 2016-04-19 09:16:11 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table attachments
# ------------------------------------------------------------

DROP TABLE IF EXISTS `attachments`;

CREATE TABLE `attachments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(128) NOT NULL DEFAULT '',
  `filetype_id` int(11) unsigned NOT NULL,
  `note_id` int(11) unsigned NOT NULL,
  `path` varchar(256) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `filetype_attach` (`filetype_id`),
  KEY `note_attach` (`note_id`),
  CONSTRAINT `filetype_attach` FOREIGN KEY (`filetype_id`) REFERENCES `filetypes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `note_attach` FOREIGN KEY (`note_id`) REFERENCES `notes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `attachments` WRITE;
/*!40000 ALTER TABLE `attachments` DISABLE KEYS */;

INSERT INTO `attachments` (`id`, `filename`, `filetype_id`, `note_id`, `path`)
VALUES
	(1,'test.png',1,1,'/Users/jake/Sites/ThrowNote/public_html/uploads/1/test.png');

/*!40000 ALTER TABLE `attachments` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table filetypes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `filetypes`;

CREATE TABLE `filetypes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `filetype` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `filetypes` WRITE;
/*!40000 ALTER TABLE `filetypes` DISABLE KEYS */;

INSERT INTO `filetypes` (`id`, `filetype`)
VALUES
	(1,'png');

/*!40000 ALTER TABLE `filetypes` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table notes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `notes`;

CREATE TABLE `notes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `text` varchar(256) DEFAULT '',
  `created` datetime NOT NULL,
  `updated` datetime DEFAULT NULL,
  `owner` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `note_owner` (`owner`),
  CONSTRAINT `note_owner` FOREIGN KEY (`owner`) REFERENCES `uc_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `notes` WRITE;
/*!40000 ALTER TABLE `notes` DISABLE KEYS */;

INSERT INTO `notes` (`id`, `text`, `created`, `updated`, `owner`)
VALUES
	(1,'This is a sample note','2016-01-10 17:10:07','2016-03-07 17:43:08',1),
	(68,'this is a #test','2016-03-07 22:15:13',NULL,1),
	(69,'#waddup','2016-03-07 22:19:27',NULL,1),
	(70,'my owner is #jake #dawkins','2016-01-01 00:00:01',NULL,1),
	(71,'my owner is #jake #dawkins #peoples','2016-01-01 00:00:01',NULL,1);

/*!40000 ALTER TABLE `notes` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table tags
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tags`;

CREATE TABLE `tags` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `tags` WRITE;
/*!40000 ALTER TABLE `tags` DISABLE KEYS */;

INSERT INTO `tags` (`id`, `name`)
VALUES
	(1,'tag1'),
	(2,'tag2'),
	(17,'web'),
	(18,'clip'),
	(19,'tag3'),
	(20,'#hello'),
	(21,'#world'),
	(22,'#mate'),
	(23,'#wow'),
	(24,'#test'),
	(26,'#wowzers'),
	(28,'#aaaa'),
	(29,' #bbbb'),
	(30,' #cccc'),
	(31,' #dddd'),
	(32,'#waddup'),
	(33,'#jake'),
	(34,'#dawkins'),
	(35,'#peoples');

/*!40000 ALTER TABLE `tags` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table tags_notes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tags_notes`;

CREATE TABLE `tags_notes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `note` int(11) unsigned NOT NULL,
  `tag` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `test_note` (`note`),
  KEY `test_tag` (`tag`),
  CONSTRAINT `test_note` FOREIGN KEY (`note`) REFERENCES `notes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `test_tag` FOREIGN KEY (`tag`) REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `tags_notes` WRITE;
/*!40000 ALTER TABLE `tags_notes` DISABLE KEYS */;

INSERT INTO `tags_notes` (`id`, `note`, `tag`)
VALUES
	(113,1,1),
	(114,1,19),
	(115,68,24),
	(116,69,32),
	(117,70,33),
	(118,70,34),
	(119,71,33),
	(120,71,34),
	(121,71,35);

/*!40000 ALTER TABLE `tags_notes` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table uc_configuration
# ------------------------------------------------------------

DROP TABLE IF EXISTS `uc_configuration`;

CREATE TABLE `uc_configuration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `value` varchar(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `uc_configuration` WRITE;
/*!40000 ALTER TABLE `uc_configuration` DISABLE KEYS */;

INSERT INTO `uc_configuration` (`id`, `name`, `value`)
VALUES
	(1,'website_name','ThrowNote'),
	(2,'website_url','localhost/'),
	(3,'email','noreply@thrownote.com'),
	(4,'activation','false'),
	(5,'resend_activation_threshold','0'),
	(6,'language','models/languages/en.php');

/*!40000 ALTER TABLE `uc_configuration` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table uc_pages
# ------------------------------------------------------------

DROP TABLE IF EXISTS `uc_pages`;

CREATE TABLE `uc_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page` varchar(150) NOT NULL,
  `private` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `uc_pages` WRITE;
/*!40000 ALTER TABLE `uc_pages` DISABLE KEYS */;

INSERT INTO `uc_pages` (`id`, `page`, `private`)
VALUES
	(1,'account.php',1),
	(2,'activate-account.php',0),
	(3,'admin_configuration.php',1),
	(4,'admin_page.php',1),
	(5,'admin_pages.php',1),
	(6,'admin_permission.php',1),
	(7,'admin_permissions.php',1),
	(8,'admin_user.php',1),
	(9,'admin_users.php',1),
	(10,'forgot-password.php',0),
	(11,'index.php',0),
	(12,'left-nav.php',0),
	(13,'login.php',0),
	(14,'logout.php',1),
	(15,'register.php',0),
	(16,'resend-activation.php',0),
	(17,'user_settings.php',1),
	(18,'index-notes.php',0),
	(19,'test.php',0);

/*!40000 ALTER TABLE `uc_pages` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table uc_permission_page_matches
# ------------------------------------------------------------

DROP TABLE IF EXISTS `uc_permission_page_matches`;

CREATE TABLE `uc_permission_page_matches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `permission_id` int(11) NOT NULL,
  `page_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `uc_permission_page_matches` WRITE;
/*!40000 ALTER TABLE `uc_permission_page_matches` DISABLE KEYS */;

INSERT INTO `uc_permission_page_matches` (`id`, `permission_id`, `page_id`)
VALUES
	(1,1,1),
	(2,1,14),
	(3,1,17),
	(4,2,1),
	(5,2,3),
	(6,2,4),
	(7,2,5),
	(8,2,6),
	(9,2,7),
	(10,2,8),
	(11,2,9),
	(12,2,14),
	(13,2,17);

/*!40000 ALTER TABLE `uc_permission_page_matches` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table uc_permissions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `uc_permissions`;

CREATE TABLE `uc_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `uc_permissions` WRITE;
/*!40000 ALTER TABLE `uc_permissions` DISABLE KEYS */;

INSERT INTO `uc_permissions` (`id`, `name`)
VALUES
	(1,'New Member'),
	(2,'Administrator');

/*!40000 ALTER TABLE `uc_permissions` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table uc_user_permission_matches
# ------------------------------------------------------------

DROP TABLE IF EXISTS `uc_user_permission_matches`;

CREATE TABLE `uc_user_permission_matches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `uc_user_permission_matches` WRITE;
/*!40000 ALTER TABLE `uc_user_permission_matches` DISABLE KEYS */;

INSERT INTO `uc_user_permission_matches` (`id`, `user_id`, `permission_id`)
VALUES
	(1,1,2),
	(2,1,1),
	(3,2,1),
	(4,3,1),
	(5,4,1),
	(6,5,1),
	(7,6,1),
	(8,7,1);

/*!40000 ALTER TABLE `uc_user_permission_matches` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table uc_users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `uc_users`;

CREATE TABLE `uc_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(50) NOT NULL,
  `display_name` varchar(50) NOT NULL,
  `password` varchar(225) NOT NULL,
  `email` varchar(150) NOT NULL,
  `activation_token` varchar(225) NOT NULL,
  `last_activation_request` int(11) NOT NULL,
  `lost_password_request` tinyint(1) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `title` varchar(150) NOT NULL,
  `sign_up_stamp` int(11) NOT NULL,
  `last_sign_in_stamp` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `uc_users` WRITE;
/*!40000 ALTER TABLE `uc_users` DISABLE KEYS */;

INSERT INTO `uc_users` (`id`, `user_name`, `display_name`, `password`, `email`, `activation_token`, `last_activation_request`, `lost_password_request`, `active`, `title`, `sign_up_stamp`, `last_sign_in_stamp`)
VALUES
	(1,'jakedawkins','jakedawkins','be7569dd198acae68c0703e96610e926d89e3dc04596d9c037129c8c9b66c12b0','dawkinsjh@gmail.com','25f6ec76abd9d29f0172bd5c8b75059f',1455057360,0,1,'Master',1455057360,1457799746),
	(2,'tester','tester','0891e7a6b60111268ffcc24872b6f659a3ed007ce3c3f9ded1af0c8a647a93d76','jacksod@clemson.edu','5c68d53d296bf468e2edd554b6cf4e87',1455396127,0,0,'New Member',1455396127,0),
	(3,'newuser','newUser','3353e559ff2536c3897af143491224039a56bd4407791aeafd5533790a284b450','newuser','9552d1f44871ced29255b6dbcbc3b785',1460859089,0,1,'New Member',1460859089,1460859250),
	(4,'newuser2','newUser2','f8051e7330ef6ff6141f88dd1277a67cd3258d0c525c5ce6033e5343b0c961278','newuser2','70192f81977c03bf2bb5338e25cdf967',1460859275,0,1,'New Member',1460859275,1460859275),
	(5,'newuser23','newUser23','008f10e073decae095a5b3faab35f190f1695425d7bce7524a4cdf64b9011d318','newuser23','8b1a8d6c1fa4b48f5fda08de2f8d2191',1460903901,0,1,'New Member',1460903901,1460903901),
	(6,'newuser234','newUser234','b9f2c4e58480941e87462cc4355c57f592d9e1b60fc49d68e857ce4685ef36289','newuser234','b21e8349147202ceb6eb3d5883dcc3c7',1460903932,0,1,'New Member',1460903932,1460904360),
	(7,'newuser2345','newUser2345','b3923cb1c2631fa4886b3709486cb865901ca6e5d1351ded7a54aad25afbecb47','newuser2345','cbbd7dd955f882265f8a6bd0466c0699',1460904388,0,1,'New Member',1460904388,1460909446);

/*!40000 ALTER TABLE `uc_users` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(25) NOT NULL DEFAULT '',
  `password` varchar(128) NOT NULL DEFAULT '',
  `email` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;

INSERT INTO `users` (`id`, `username`, `password`, `email`)
VALUES
	(1,'jake','123456','dawkinsjh@gmail.com'),
	(2,'tester','password','jake.dawkins@newspring.cc');

/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
