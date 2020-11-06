-- phpMyAdmin SQL Dump
-- version 4.9.5deb2~bpo10+1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 28, 2020 at 07:03 PM
-- Server version: 10.3.23-MariaDB-0+deb10u1
-- PHP Version: 7.3.19-1~deb10u1

SET foreign_key_checks = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

INSERT INTO game VALUES (1, 2, 'Testovací hra', '2020-01-01', '2020-01-02');
INSERT INTO text (game_id, code, text) SELECT 1, code, text FROM text WHERE game_id IS NULL;

INSERT INTO team (team_id, game_id, name) VALUE (1, 1, 'Parta Nic');
INSERT INTO team (team_id, game_id, name) VALUE (2, 1, 'Redwool');
INSERT INTO code (team_id, game_id, code) VALUES (1, 1, 'MUZIKANT');
INSERT INTO code (team_id, game_id, code) VALUES (2, 1, 'BANDITA');

INSERT INTO point (point_id, game_id, name) VALUES (1, 1, 'Start');
INSERT INTO point (point_id, game_id, name) VALUES (2, 1, '1a');
INSERT INTO point (point_id, game_id, name) VALUES (3, 1, '1b');
INSERT INTO point (point_id, game_id, name) VALUES (4, 1, 'Turniket');
INSERT INTO point (point_id, game_id, name) VALUES (5, 1, 'Cíl');
INSERT INTO loc (point_id, description) VALUES (1, '');
INSERT INTO loc (point_id, description) VALUES (2, 'Vrchol Bílé hory');
INSERT INTO loc (point_id, description) VALUES (3, 'Vrchol Černé hory');
INSERT INTO loc (point_id, description) VALUES (4, 'Pardubické boudy, hledej orga');
INSERT INTO loc (point_id, description) VALUES (5, 'Kóta 1019 nad Pražskou boudou');

INSERT INTO code (game_id, point_id, code) VALUES (1, 1, 'PRALINKA');
INSERT INTO code (game_id, point_id, code) VALUES (1, 2, 'KYBL');
INSERT INTO code (game_id, point_id, code) VALUES (1, 3, 'PODNOS');
INSERT INTO code (game_id, point_id, code) VALUES (1, 4, 'MEDVED');
INSERT INTO code (game_id, point_id, code) VALUES (1, 5, 'SALVEJ');

INSERT INTO point (point_id, game_id, name) VALUES (11, 1, 'S1a');
INSERT INTO point (point_id, game_id, name) VALUES (12, 1, 'S1b');
INSERT INTO point (point_id, game_id, name) VALUES (13, 1, 'S2');
INSERT INTO point (point_id, game_id, name) VALUES (14, 1, 'S3a');
INSERT INTO point (point_id, game_id, name) VALUES (15, 1, 'S3b');
INSERT INTO cipher (point_id, name_int, hint, hint_timeout, solution_timeout) VALUES (11, 'Morseovka', 'Čárka tečka čárka, tak začíná Klárka', 30, 60);
INSERT INTO cipher (point_id, name_int, hint, hint_timeout, solution_timeout) VALUES (12, 'Braille', 'Zkus ji luštit poslepu', 40, NULL);
INSERT INTO cipher (point_id, name_int, hint, hint_timeout, solution_timeout) VALUES (13, 'Polský kříž', 'Krzyz', 50, NULL);
INSERT INTO cipher (point_id, name_int, hint, hint_timeout, solution_timeout) VALUES (14, 'Semafor', 'Křižovatka, železnice, Suchý', 30, 60);
INSERT INTO cipher (point_id, name_int, hint, hint_timeout, solution_timeout) VALUES (15, 'Binárka', 'Jedničky a nuly', 40, NULL);
INSERT INTO code (game_id, point_id, code) VALUES (1, 11, 'ABERACE');
INSERT INTO code (game_id, point_id, code) VALUES (1, 12, 'ZABRADLI');
INSERT INTO code (game_id, point_id, code) VALUES (1, 13, 'KOBLIHA');
INSERT INTO code (game_id, point_id, code) VALUES (1, 14, 'KALENDAR');
INSERT INTO code (game_id, point_id, code) VALUES (1, 15, 'SKLUZAVKA');

INSERT INTO unihint (unihint_id, game_id) VALUES (1, 1);
INSERT INTO unihint (unihint_id, game_id) VALUES (2, 1);
INSERT INTO code (unihint_id, game_id, code) VALUES (1, 1, 'BUBEN');
INSERT INTO code (unihint_id, game_id, code) VALUES (2, 1, 'DIVIZNA');

INSERT INTO step (from_point_id, to_point_id) VALUES (1, 11), (1, 12), (11, 2), (12, 3), (2, 13), (3, 13), (13, 4), (4, 14), (14, 5), (4, 15), (15, 5);

SET foreign_key_checks = 1;
