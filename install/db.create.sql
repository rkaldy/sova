-- MySQL dump 10.17  Distrib 10.3.23-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: sova
-- ------------------------------------------------------
-- Server version	10.3.23-MariaDB-0+deb10u1

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
-- Table structure for table `cipher`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cipher` (
  point_id int(11) NOT NULL,
  name_int varchar(50) COLLATE utf8_czech_ci DEFAULT NULL,
  hint varchar(500) COLLATE utf8_czech_ci DEFAULT NULL,
  hint_timeout int(11) DEFAULT NULL,
  solution_timeout int(11) DEFAULT NULL,
  PRIMARY KEY (point_id),
  CONSTRAINT parent_entity_cipher FOREIGN KEY (point_id) REFERENCES point (point_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `code`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `code` (
  game_id int(11) NOT NULL,
  `code` varchar(20) CHARACTER SET ascii NOT NULL,
  point_id int(11) DEFAULT NULL,
  hint_id int(11) DEFAULT NULL,
  team_id int(11) DEFAULT NULL,
  PRIMARY KEY (game_id,`code`) USING BTREE,
  KEY point_id (point_id),
  KEY team_id (team_id),
  KEY hint_id (hint_id) USING BTREE,
  CONSTRAINT code_ibfk_1 FOREIGN KEY (point_id) REFERENCES point (point_id) ON DELETE CASCADE,
  CONSTRAINT code_ibfk_2 FOREIGN KEY (hint_id) REFERENCES hint (hint_id) ON DELETE CASCADE,
  CONSTRAINT code_ibfk_3 FOREIGN KEY (team_id) REFERENCES team (team_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `game`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE game (
  game_id int(11) NOT NULL AUTO_INCREMENT,
  owner_id int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  start_time datetime NOT NULL,
  end_time datetime NOT NULL,
  PRIMARY KEY (game_id),
  KEY owner_id (owner_id),
  CONSTRAINT game_ibfk_1 FOREIGN KEY (owner_id) REFERENCES `user` (user_id)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hint`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE hint (
  hint_id int(11) NOT NULL AUTO_INCREMENT,
  game_id int(11) NOT NULL,
  PRIMARY KEY (hint_id),
  KEY game_id (game_id),
  CONSTRAINT hint_ibfk_1 FOREIGN KEY (game_id) REFERENCES game (game_id)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `loc`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE loc (
  point_id int(11) NOT NULL,
  description varchar(500) COLLATE utf8_czech_ci DEFAULT NULL,
  end_time datetime DEFAULT NULL,
  min_ciphers_solved int(11) DEFAULT NULL,
  PRIMARY KEY (point_id),
  CONSTRAINT parent_entity_loc FOREIGN KEY (point_id) REFERENCES point (point_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `message`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE message (
  message_id int(11) NOT NULL AUTO_INCREMENT,
  team_id int(11) NOT NULL,
  cipher_id int(11) DEFAULT NULL,
  direction tinyint(1) NOT NULL,
  `time` datetime NOT NULL DEFAULT current_timestamp(),
  `text` varchar(500) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (message_id),
  KEY cipher_id (cipher_id),
  KEY sort_idx (team_id,`time`,direction) USING BTREE,
  CONSTRAINT message_ibfk_1 FOREIGN KEY (team_id) REFERENCES team (team_id),
  CONSTRAINT message_ibfk_2 FOREIGN KEY (cipher_id) REFERENCES cipher (point_id)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `point`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `point` (
  point_id int(11) NOT NULL AUTO_INCREMENT,
  game_id int(11) NOT NULL,
  sort_id int(11) DEFAULT NULL,
  `name` varchar(40) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (point_id),
  UNIQUE KEY `name` (game_id,`name`),
  KEY sort_idx (game_id,sort_id)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `progress`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE progress (
  team_id int(11) NOT NULL,
  point_id int(11) NOT NULL,
  `time` datetime NOT NULL DEFAULT current_timestamp(),
  `type` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (team_id,point_id),
  KEY point_id (point_id),
  CONSTRAINT progress_ibfk_1 FOREIGN KEY (team_id) REFERENCES team (team_id),
  CONSTRAINT progress_ibfk_2 FOREIGN KEY (point_id) REFERENCES point (point_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `step`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE step (
  from_point_id int(11) NOT NULL,
  to_point_id int(11) NOT NULL,
  PRIMARY KEY (from_point_id,to_point_id) USING BTREE,
  KEY to_point_id (to_point_id) USING BTREE,
  CONSTRAINT step_ibfk_1 FOREIGN KEY (from_point_id) REFERENCES point (point_id) ON DELETE CASCADE,
  CONSTRAINT step_ibfk_2 FOREIGN KEY (to_point_id) REFERENCES point (point_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `team`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE team (
  team_id int(11) NOT NULL AUTO_INCREMENT,
  game_id int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  phone varchar(20) COLLATE utf8_czech_ci DEFAULT NULL,
  email varchar(50) COLLATE utf8_czech_ci DEFAULT NULL,
  PRIMARY KEY (team_id),
  UNIQUE KEY game_id (game_id,`name`),
  CONSTRAINT team_ibfk_1 FOREIGN KEY (game_id) REFERENCES game (game_id)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `team_hint`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE team_hint (
  team_id int(11) NOT NULL,
  hint_id int(11) NOT NULL,
  cipher_id int(11) DEFAULT NULL,
  PRIMARY KEY (team_id,hint_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  user_id int(11) NOT NULL AUTO_INCREMENT,
  login varchar(30) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  pswd varchar(60) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  PRIMARY KEY (user_id)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `wordlist`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE wordlist (
  word varchar(20) CHARACTER SET ascii NOT NULL,
  PRIMARY KEY (word)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2020-10-27 16:44:05
