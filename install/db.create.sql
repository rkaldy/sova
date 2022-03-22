SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


DROP TABLE IF EXISTS cipher;
DROP TABLE IF EXISTS code;
DROP TABLE IF EXISTS game;
DROP TABLE IF EXISTS hint;
DROP TABLE IF EXISTS loc;
DROP TABLE IF EXISTS message;
DROP TABLE IF EXISTS point;
DROP TABLE IF EXISTS progress;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS step;
DROP TABLE IF EXISTS superuser;
DROP TABLE IF EXISTS team;
DROP TABLE IF EXISTS text;
DROP TABLE IF EXISTS ccode;
DROP TABLE IF EXISTS wordlist;


CREATE TABLE `cipher` (
  point_id int(11) NOT NULL,
  name_int varchar(50) COLLATE utf8mb4_czech_ci DEFAULT NULL,
  hint varchar(500) COLLATE utf8mb4_czech_ci DEFAULT NULL,
  hint_timeout int(11) DEFAULT NULL,
  solution_timeout int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

CREATE TABLE `code` (
  game_id int(11) NOT NULL,
  code varchar(20) CHARACTER SET ascii NOT NULL,
  point_id int(11) DEFAULT NULL,
  ccode_id int(11) DEFAULT NULL,
  team_id int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

CREATE TABLE game (
  game_id int(11) NOT NULL,
  name varchar(100) COLLATE utf8mb4_czech_ci NOT NULL,
  pswd varchar(60) CHARACTER SET ascii COLLATE ascii_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

CREATE TABLE hint (
  hint_id int(11) NOT NULL,
  team_id int(11) NOT NULL,
  ccode_id int(11) DEFAULT NULL,
  cipher_id int(11) DEFAULT NULL,
  time datetime DEFAULT NULL,
  type int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

CREATE TABLE loc (
  point_id int(11) NOT NULL,
  description varchar(500) COLLATE utf8mb4_czech_ci DEFAULT NULL,
  order_id int(11) NULL,
  coord_lat decimal(10, 7) NULL,
  coord_lon decimal(10, 7) NULL,
  solved_cipher_count int(11) DEFAULT NULL,
  end_time datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

CREATE TABLE message (
  message_id int(11) NOT NULL,
  team_id int(11) NOT NULL,
  hint_id int(11) DEFAULT NULL,
  direction tinyint(1) NOT NULL,
  async tinyint(1) NOT NULL DEFAULT 0,
  time datetime NOT NULL DEFAULT current_timestamp(),
  text varchar(500) COLLATE utf8mb4_czech_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

CREATE TABLE `point` (
  point_id int(11) NOT NULL,
  game_id int(11) NOT NULL,
  name varchar(40) COLLATE utf8mb4_czech_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

CREATE TABLE progress (
  team_id int(11) NOT NULL,
  point_id int(11) NOT NULL,
  time datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

CREATE TABLE settings (
  game_id int(11) NOT NULL,
  gameStart timestamp NULL DEFAULT NULL,
  gameEnd timestamp NULL DEFAULT NULL,
  locVisitMandatory tinyint(4) NOT NULL DEFAULT 0,
  locFinish int(11) DEFAULT NULL,
  showRank tinyint(4) NOT NULL DEFAULT 1,
  linkMapyCz VARCHAR(20) NULL DEFAULT NULL,
  deleteParallelHints tinyint(4) NOT NULL DEFAULT 0,
  imunityPrice int(11) DEFAULT 3,
  gamePrice int(11) DEFAULT 0,
  accomodationPrice int(11) DEFAULT 0,
  tshirtPrice int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE step (
  from_point_id int(11) NOT NULL,
  to_point_id int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

CREATE TABLE superuser (
  pswd varchar(60) CHARACTER SET ascii COLLATE ascii_bin NOT NULL
) ENGINE=InnoDB;

CREATE TABLE team (
  team_id int(11) NOT NULL,
  game_id int(11) NOT NULL,
  name varchar(100) COLLATE utf8mb4_czech_ci NOT NULL,
  phone varchar(20) COLLATE utf8mb4_czech_ci DEFAULT NULL,
  email varchar(50) COLLATE utf8mb4_czech_ci DEFAULT NULL,
  members varchar(200) COLLATE utf8mb4_czech_ci DEFAULT NULL,
  accomodation tinyint(1) NOT NULL DEFAULT 1,
  paid tinyint(1) NOT NULL DEFAULT 0,
  tshirt int(11) NOT NULL DEFAULT 0,
  remarks varchar(500) COLLATE utf8mb4_czech_ci DEFAULT NULL,
  additional varchar(500) COLLATE utf8mb4_czech_ci DEFAULT NULL,
  points int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

CREATE TABLE `text` (
  text_id int(11) NOT NULL,
  game_id int(11) DEFAULT NULL,
  code varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci NOT NULL,
  text varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ccode (
  ccode_id int(11) NOT NULL,
  game_id int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

CREATE TABLE wordlist (
  word varchar(20) CHARACTER SET ascii NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;


ALTER TABLE `cipher`
  ADD PRIMARY KEY (point_id);

ALTER TABLE `code`
  ADD PRIMARY KEY (game_id,code) USING BTREE,
  ADD UNIQUE KEY point_id (point_id,game_id) USING BTREE,
  ADD UNIQUE KEY team_id (team_id,game_id) USING BTREE,
  ADD UNIQUE KEY ccode_id (ccode_id,game_id) USING BTREE;

ALTER TABLE game
  ADD PRIMARY KEY (game_id),
  ADD UNIQUE KEY name (name) USING BTREE;

ALTER TABLE hint
  ADD PRIMARY KEY (hint_id) USING BTREE,
  ADD UNIQUE KEY ccode_id (ccode_id,team_id) USING BTREE,
  ADD UNIQUE KEY team_id (team_id,cipher_id,type) USING BTREE,
  ADD KEY cipher_id (cipher_id);

ALTER TABLE loc
  ADD PRIMARY KEY (point_id);

ALTER TABLE message
  ADD PRIMARY KEY (message_id),
  ADD KEY sort_idx (team_id,time,direction) USING BTREE,
  ADD KEY hint_id (hint_id);

ALTER TABLE `point`
  ADD PRIMARY KEY (point_id,game_id) USING BTREE,
  ADD KEY name (game_id,name);

ALTER TABLE progress
  ADD PRIMARY KEY (team_id,point_id),
  ADD KEY point_id (point_id);

ALTER TABLE settings
  ADD PRIMARY KEY (game_id);

ALTER TABLE step
  ADD PRIMARY KEY (from_point_id,to_point_id) USING BTREE,
  ADD KEY to_point_id (to_point_id) USING BTREE;

ALTER TABLE team
  ADD PRIMARY KEY (team_id),
  ADD UNIQUE KEY game_id (game_id,name);

ALTER TABLE `text`
  ADD PRIMARY KEY (text_id),
  ADD UNIQUE KEY code (game_id,code);

ALTER TABLE ccode
  ADD PRIMARY KEY (ccode_id),
  ADD KEY game_id (game_id);

ALTER TABLE wordlist
  ADD PRIMARY KEY (word);


ALTER TABLE game
  MODIFY game_id int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE hint
  MODIFY hint_id int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE message
  MODIFY message_id int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `point`
  MODIFY point_id int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE team
  MODIFY team_id int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `text`
  MODIFY text_id int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE ccode
  MODIFY ccode_id int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `cipher`
  ADD CONSTRAINT parent_entity_cipher FOREIGN KEY (point_id) REFERENCES point (point_id) ON DELETE CASCADE;

ALTER TABLE `code`
  ADD CONSTRAINT code_ibfk_1 FOREIGN KEY (point_id) REFERENCES point (point_id) ON DELETE CASCADE,
  ADD CONSTRAINT code_ibfk_2 FOREIGN KEY (team_id) REFERENCES team (team_id) ON DELETE CASCADE,
  ADD CONSTRAINT code_ibfk_3 FOREIGN KEY (ccode_id) REFERENCES ccode (ccode_id) ON DELETE CASCADE;

ALTER TABLE hint
  ADD CONSTRAINT hint_ibfk_1 FOREIGN KEY (team_id) REFERENCES team (team_id) ON DELETE CASCADE,
  ADD CONSTRAINT hint_ibfk_2 FOREIGN KEY (ccode_id) REFERENCES ccode (ccode_id) ON DELETE CASCADE,
  ADD CONSTRAINT hint_ibfk_3 FOREIGN KEY (cipher_id) REFERENCES cipher (point_id) ON DELETE CASCADE;

ALTER TABLE loc
  ADD CONSTRAINT parent_entity_loc FOREIGN KEY (point_id) REFERENCES point (point_id) ON DELETE CASCADE;

ALTER TABLE message
  ADD CONSTRAINT message_ibfk_1 FOREIGN KEY (team_id) REFERENCES team (team_id) ON DELETE CASCADE,
  ADD CONSTRAINT message_ibfk_2 FOREIGN KEY (hint_id) REFERENCES hint (hint_id) ON DELETE CASCADE;

ALTER TABLE `point`
  ADD CONSTRAINT point_ibfk_1 FOREIGN KEY (game_id) REFERENCES game (game_id) ON DELETE CASCADE;

ALTER TABLE progress
  ADD CONSTRAINT progress_ibfk_1 FOREIGN KEY (team_id) REFERENCES team (team_id) ON DELETE CASCADE,
  ADD CONSTRAINT progress_ibfk_2 FOREIGN KEY (point_id) REFERENCES point (point_id) ON DELETE CASCADE;

ALTER TABLE settings
  ADD CONSTRAINT settings_ibfk_1 FOREIGN KEY (game_id) REFERENCES game (game_id) ON DELETE CASCADE;

ALTER TABLE step
  ADD CONSTRAINT step_ibfk_1 FOREIGN KEY (from_point_id) REFERENCES point (point_id) ON DELETE CASCADE,
  ADD CONSTRAINT step_ibfk_2 FOREIGN KEY (to_point_id) REFERENCES point (point_id) ON DELETE CASCADE;

ALTER TABLE team
  ADD CONSTRAINT team_ibfk_1 FOREIGN KEY (game_id) REFERENCES game (game_id) ON DELETE CASCADE;

ALTER TABLE `text`
  ADD CONSTRAINT text_ibfk_1 FOREIGN KEY (game_id) REFERENCES game (game_id) ON DELETE CASCADE;

ALTER TABLE ccode
  ADD CONSTRAINT ccode_ibfk_1 FOREIGN KEY (game_id) REFERENCES game (game_id) ON DELETE CASCADE;
SET FOREIGN_KEY_CHECKS=1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
