-- MySQL dump 10.13  Distrib 5.1.41, for debian-linux-gnu (i486)
--
-- Host: localhost    Database: rest
-- ------------------------------------------------------
-- Server version	5.1.41-3ubuntu12.6

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `interest`
--

DROP TABLE IF EXISTS `interest`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `interest` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user` bigint(20) unsigned NOT NULL,
  `latitude` decimal(8,6) NOT NULL,
  `longitude` decimal(9,6) NOT NULL,
  `create_time` int(10) unsigned NOT NULL,
  `expire` int(10) unsigned NOT NULL,
  `distance` smallint(5) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `stems` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `stems_numless` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `kind` varchar(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT '_',
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `image` varchar(70) CHARACTER SET ascii NOT NULL,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `stems` (`stems`),
  FULLTEXT KEY `stems_numless` (`stems_numless`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `interest`
--

LOCK TABLES `interest` WRITE;
/*!40000 ALTER TABLE `interest` DISABLE KEYS */;
/*!40000 ALTER TABLE `interest` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `location`
--

DROP TABLE IF EXISTS `location`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `location` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user` bigint(20) unsigned NOT NULL,
  `latitude` decimal(8,6) NOT NULL,
  `longitude` decimal(9,6) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `location`
--

LOCK TABLES `location` WRITE;
/*!40000 ALTER TABLE `location` DISABLE KEYS */;
/*!40000 ALTER TABLE `location` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `match`
--

DROP TABLE IF EXISTS `match`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `match` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `interest` bigint(20) unsigned NOT NULL,
  `matched_interest` bigint(20) unsigned NOT NULL,
  `rank` float(4,3) NOT NULL,
  `unread` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `interest` (`interest`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `match`
--

LOCK TABLES `match` WRITE;
/*!40000 ALTER TABLE `match` DISABLE KEYS */;
/*!40000 ALTER TABLE `match` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message`
--

DROP TABLE IF EXISTS `message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `thread` bigint(20) unsigned NOT NULL,
  `user` bigint(20) unsigned NOT NULL,
  `create_time` int(10) unsigned NOT NULL,
  `unread` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `text` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `match` (`thread`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message`
--

LOCK TABLES `message` WRITE;
/*!40000 ALTER TABLE `message` DISABLE KEYS */;
/*!40000 ALTER TABLE `message` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `queue_matches`
--

DROP TABLE IF EXISTS `queue_matches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `queue_matches` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` bigint(20) unsigned NOT NULL,
  `last_push` int(10) unsigned NOT NULL,
  `interest_id` bigint(20) unsigned NOT NULL,
  `match_id` bigint(20) unsigned NOT NULL,
  `text` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `queue_matches`
--

LOCK TABLES `queue_matches` WRITE;
/*!40000 ALTER TABLE `queue_matches` DISABLE KEYS */;
/*!40000 ALTER TABLE `queue_matches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `queue_messages`
--

DROP TABLE IF EXISTS `queue_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `queue_messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` bigint(20) unsigned NOT NULL,
  `last_push` int(10) unsigned NOT NULL,
  `interest_id` bigint(20) unsigned NOT NULL,
  `match_id` bigint(20) unsigned NOT NULL,
  `text` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `queue_messages`
--

LOCK TABLES `queue_messages` WRITE;
/*!40000 ALTER TABLE `queue_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `queue_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `thread`
--

DROP TABLE IF EXISTS `thread`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `thread` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `interest` bigint(20) unsigned NOT NULL,
  `matched_interest` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `thread`
--

LOCK TABLES `thread` WRITE;
/*!40000 ALTER TABLE `thread` DISABLE KEYS */;
/*!40000 ALTER TABLE `thread` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `password` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `firstname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `lastname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `mobile_number` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_login` int(10) unsigned NOT NULL,
  `apple_push_token` varchar(64) CHARACTER SET ascii NOT NULL,
  PRIMARY KEY (`id`),
  KEY `password` (`password`,`mobile_number`,`email`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'7ba64906c960daa650e15d172a7cd45d','Rok','Krulec','','rkr@mmatcher.com',1285165191,'c9c46efcf4027a9bd4702adec2dfcc66e1b31b29d5687739ca6e0130347be9d3'),(2,'5828bb6f63885517f2710fe2371a9af6','Rok','Gregoric','38641357777','rgr@mmatcher.com',1285167798,'0a612e6af04de7716a635029630b6262891f0f3ed41cb48e71c53414a75b3659'),(3,'6621359e0990d3599ef1947d0079604e','Robert','Farazin','38641992549','rfa@mmatcher.com',1281798787,'5ddecddf8f6cf06248e4d09a3eb9b3e7b7e19a2b49c912412aaa6c1a2ffc57ab'),(4,'632c407f955841f59c0d9716e48ac153','Miha','Rebernik','','mre@mmatcher.com',1281949807,'e28e7d852c43c378b90f4f6eaffaadc129d29d821588ce8fa63457f5e7440179'),(5,'6dbd8f08764db032171a6217725b58a5','Uros','Derstvensek','','ude@mmatcher.com',1276265187,''),(6,'2d2c33440fe945b67f7625131bc5b161','Mac','Awais','','maw@mmatcher.com',1281544991,''),(7,'6bf9e70a1f928aba143ef1eebe2720b5','Pija','Rezek','','pre@mmatcher.com',0,'');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mem_sql_profile`
--

DROP TABLE IF EXISTS `mem_sql_profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mem_sql_profile` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `sql_text` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `line_number` int(11) NOT NULL,
  `class_name` varchar(100) NOT NULL,
  `function_name` varchar(100) NOT NULL,
  `time_seconds` float NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MEMORY DEFAULT CHARSET=latin1 COMMENT='SQL profiling information';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mem_sql_profile`
--

LOCK TABLES `mem_sql_profile` WRITE;
/*!40000 ALTER TABLE `mem_sql_profile` DISABLE KEYS */;
/*!40000 ALTER TABLE `mem_sql_profile` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2010-09-22 17:21:50
