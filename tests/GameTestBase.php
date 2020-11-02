<?php
namespace Sova;

use Sova\Repo\ProgressRepo;

class GameTestBase extends TestBase {

	protected $progressRepo;

	function setUp(): void {
		parent::setUp();

		$this->db->execute("INSERT INTO team (team_id, game_id, name) VALUE (1, 1, 'Parta Nic')");
		$this->db->execute("INSERT INTO team (team_id, game_id, name) VALUE (2, 1, 'Redwool')");

		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (1, 1, 'Start')");
		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (2, 1, 'Bílá hora')");
		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (3, 1, 'Černá hora')");
		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (4, 1, 'Turniket')");
		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (5, 1, 'Cíl')");
		$this->db->execute("INSERT INTO loc (point_id) VALUES (1), (2), (3), (4), (5)");
		$this->db->execute("INSERT INTO code (game_id, point_id, code) VALUES (1, 1, 'PRALINKA')");
		$this->db->execute("INSERT INTO code (game_id, point_id, code) VALUES (1, 2, 'KYBL')");
		$this->db->execute("INSERT INTO code (game_id, point_id, code) VALUES (1, 3, 'PODNOS')");
		$this->db->execute("INSERT INTO code (game_id, point_id, code) VALUES (1, 2, 'MEDVED')");
		$this->db->execute("INSERT INTO code (game_id, point_id, code) VALUES (1, 3, 'SALVEJ')");

		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (11, 1, 'S1a')");
		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (12, 1, 'S1b')");
		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (13, 1, 'S2')");
		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (14, 1, 'S3a')");
		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (15, 1, 'S3b')");
		$this->db->execute("INSERT INTO cipher (point_id, name_int, hint, hint_timeout, solution_timeout) VALUES (11, 'Morseovka', 'Čárka tečka čárka, tak začíná Klárka', 30, 60)");
		$this->db->execute("INSERT INTO cipher (point_id, name_int, hint, hint_timeout, solution_timeout) VALUES (12, 'Braille', 'Zkus ji luštit poslepu', 40, NULL)");
		$this->db->execute("INSERT INTO cipher (point_id, name_int, hint) VALUES (13, 'Polský kříž', 'Krzyz')");
		$this->db->execute("INSERT INTO cipher (point_id, name_int, hint) VALUES (14, 'Semafor', 'Křižovatka, železnice, Suchý')");
		$this->db->execute("INSERT INTO cipher (point_id, name_int, hint) VALUES (15, 'Binárka', 'Jedničky a nuly')");
		$this->db->execute("INSERT INTO code (game_id, point_id, code) VALUES (1, 11, 'ABERACE')");
		$this->db->execute("INSERT INTO code (game_id, point_id, code) VALUES (1, 12, 'ZABRADLI')");
		$this->db->execute("INSERT INTO code (game_id, point_id, code) VALUES (1, 13, 'KOBLIHA')");
		$this->db->execute("INSERT INTO code (game_id, point_id, code) VALUES (1, 14, 'KALENDAR')");
		$this->db->execute("INSERT INTO code (game_id, point_id, code) VALUES (1, 14, 'SKLUZAVKA')");

		$this->db->execute("INSERT INTO hint (hint_id, game_id) VALUES (1, 1)");
		$this->db->execute("INSERT INTO hint (hint_id, game_id) VALUES (2, 1)");
		$this->db->execute("INSERT INTO code (hint_id, game_id, code) VALUES (1, 1, 'BUBEN')");
		$this->db->execute("INSERT INTO code (hint_id, game_id, code) VALUES (2, 1, 'DIVIZNA')");

		$this->db->execute("INSERT INTO step (from_point_id, to_point_id) VALUES (1, 11), (1, 12), (11, 2), (12, 3), (2, 13), (3, 13), (13, 4), (4, 14), (14, 5), (4, 15), (15, 5)");

		$this->progressRepo = new ProgressRepo();
		$_SESSION["team_id"] = 1;
	}

	function tearDown(): void {
		$this->db->execute("DELETE FROM progress");
		$this->db->execute("DELETE FROM message");
		$this->db->execute("DELETE FROM team_hint");
		$this->db->execute("DELETE FROM point");
		$this->db->execute("DELETE FROM hint");
		$this->db->execute("DELETE FROM team");
		parent::tearDown();
	}
}
