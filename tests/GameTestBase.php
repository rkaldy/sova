<?php
namespace Sova;

use Sova\Repo\ProgressRepo;
use Sova\Model\Progress;

class GameTestBase extends TestBase {

	protected $progressRepo;

/* 
 * (Start)--> S1a -> (1a) --> S2 -> (Turniket) -> (3) -> S3 -> (4) --> S4a --> (Cíl)
 *        \-> S1b -> (1b) -/                                       \-> S4b -/
 */

	function setUp(): void {
		parent::setUp();

		$this->db->execute("INSERT INTO team (team_id, game_id, name) VALUE (1, 1, 'Parta Nic')");
		$this->db->execute("INSERT INTO team (team_id, game_id, name) VALUE (2, 1, 'Redwool')");

		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (1, 1, 'Start')");
		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (2, 1, '1a')");
		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (3, 1, '1b')");
		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (4, 1, 'Turniket')");
		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (5, 1, '3')");
		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (6, 1, '4')");
		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (7, 1, 'Cíl')");
		$this->db->execute("INSERT INTO loc (point_id, description) VALUES (1, '')");
		$this->db->execute("INSERT INTO loc (point_id, description) VALUES (2, 'na vrcholu Bílé hory')");
		$this->db->execute("INSERT INTO loc (point_id, description) VALUES (3, 'na vrcholu Černé hory')");
		$this->db->execute("INSERT INTO loc (point_id, description) VALUES (4, 'na Pardubických boudách, hledej orga')");
		$this->db->execute("INSERT INTO loc (point_id, description) VALUES (5, 'na Kolínské boudě')");
		$this->db->execute("INSERT INTO loc (point_id, description) VALUES (6, 'na Pražské boudě')");
		$this->db->execute("INSERT INTO loc (point_id, description) VALUES (7, 'na kótě 1019 nad Pražskou boudou')");
		
		$this->db->execute("INSERT INTO code (game_id, point_id, code) VALUES (1, 1, 'PRALINKA')");
		$this->db->execute("INSERT INTO code (game_id, point_id, code) VALUES (1, 2, 'KYBL')");
		$this->db->execute("INSERT INTO code (game_id, point_id, code) VALUES (1, 3, 'PODNOS')");
		$this->db->execute("INSERT INTO code (game_id, point_id, code) VALUES (1, 4, 'MEDVED')");
		$this->db->execute("INSERT INTO code (game_id, point_id, code) VALUES (1, 5, 'ZIDLE')");
		$this->db->execute("INSERT INTO code (game_id, point_id, code) VALUES (1, 6, 'TABULKA')");
		$this->db->execute("INSERT INTO code (game_id, point_id, code) VALUES (1, 7, 'SALVEJ')");

		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (11, 1, 'S1a')");
		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (12, 1, 'S1b')");
		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (13, 1, 'S2')");
		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (14, 1, 'S3')");
		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (15, 1, 'S4a')");
		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (16, 1, 'S4b')");
		$this->db->execute("INSERT INTO cipher (point_id, name_int, hint, hint_timeout, solution_timeout) VALUES (11, 'Morseovka', 'Čárka tečka čárka, tak začíná Klárka', 30, 60)");
		$this->db->execute("INSERT INTO cipher (point_id, name_int, hint, hint_timeout, solution_timeout) VALUES (12, 'Braille', 'Zkus ji luštit poslepu', 40, NULL)");
		$this->db->execute("INSERT INTO cipher (point_id, name_int, hint, hint_timeout, solution_timeout) VALUES (13, 'Polský kříž', 'Krzyz', 50, NULL)");
		$this->db->execute("INSERT INTO cipher (point_id, name_int, hint, hint_timeout, solution_timeout) VALUES (14, 'Vlajková abeceda', NULL, 30, 60)");
		$this->db->execute("INSERT INTO cipher (point_id, name_int, hint, hint_timeout, solution_timeout) VALUES (15, 'Semafor', 'Křižovatka, železnice, Suchý', 30, 60)");
		$this->db->execute("INSERT INTO cipher (point_id, name_int, hint, hint_timeout, solution_timeout) VALUES (16, 'Binárka', 'Jedničky a nuly', 40, NULL)");
		$this->db->execute("INSERT INTO code (game_id, point_id, code) VALUES (1, 11, 'ABERACE')");
		$this->db->execute("INSERT INTO code (game_id, point_id, code) VALUES (1, 12, 'ZABRADLI')");
		$this->db->execute("INSERT INTO code (game_id, point_id, code) VALUES (1, 13, 'KOBLIHA')");
		$this->db->execute("INSERT INTO code (game_id, point_id, code) VALUES (1, 14, 'PRIBOJ')");
		$this->db->execute("INSERT INTO code (game_id, point_id, code) VALUES (1, 15, 'KALENDAR')");
		$this->db->execute("INSERT INTO code (game_id, point_id, code) VALUES (1, 16, 'SKLUZAVKA')");

		$this->db->execute("INSERT INTO ccode (ccode_id, game_id) VALUES (1, 1)");
		$this->db->execute("INSERT INTO ccode (ccode_id, game_id) VALUES (2, 1)");
		$this->db->execute("INSERT INTO ccode (ccode_id, game_id) VALUES (3, 1)");
		$this->db->execute("INSERT INTO code (ccode_id, game_id, code) VALUES (1, 1, 'BUBEN')");
		$this->db->execute("INSERT INTO code (ccode_id, game_id, code) VALUES (2, 1, 'DIVIZNA')");
		$this->db->execute("INSERT INTO code (ccode_id, game_id, code) VALUES (3, 1, 'VCELA')");

		$this->db->execute("INSERT INTO step (from_point_id, to_point_id) VALUES (1, 11), (1, 12), (11, 2), (12, 3), (2, 13), (3, 13), (13, 4), (4, 5), (5, 14), (14, 6), (6, 15), (6, 16), (15, 7), (16, 7)");

		$this->progressRepo = new ProgressRepo();
		Progress::resetFakeTime();
		$_SESSION["team_id"] = 1;
	}
}
