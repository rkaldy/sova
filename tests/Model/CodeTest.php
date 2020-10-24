<?php
namespace Sova\Model;

use Sova\TestBase;

class CodeTest extends TestBase {

	function setUp(): void {
		parent::setUp();
		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (1, 1, 'Bílá hora')");
		$this->db->execute("INSERT INTO loc (point_id, min_ciphers_solved) VALUES (1, 10)");
		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (2, 1, 'S1')");
		$this->db->execute("INSERT INTO cipher (point_id, name_int) VALUES (2, 'Morseovka')");
		$this->db->execute("INSERT INTO hint (hint_id, game_id) VALUES (1, 1)");
		$this->db->execute("INSERT INTO team (team_id, game_id, name) VALUES (1, 1, 'Parta Nic')");
		$this->db->execute("INSERT INTO code (game_id, point_id, code) VALUES (1, 1, 'ZABRADLI')");
		$this->db->execute("INSERT INTO code (game_id, point_id, code) VALUES (1, 2, 'KOBLIHA')");
		$this->db->execute("INSERT INTO code (game_id, hint_id, code) VALUES (1, 1, 'BUBEN')");
		$this->db->execute("INSERT INTO code (game_id, team_id, code) VALUES (1, 1, 'PRAK')");
	}

	function tearDown(): void {
		$this->db->execute("DELETE FROM point");
		$this->db->execute("DELETE FROM hint");
		$this->db->execute("DELETE FROM team");
		parent::tearDown();
	}

	function testPrepare() {
		$code = " ahoj";
		Code::prepare($code);
		$this->assertEquals("AHOJ", $code);
	}

	function testGenerate() {
		$code = "";
		Code::prepare($code);
		$this->assertEquals(1, preg_match("/[A-Z]+/", $code));
		$this->assertNotEquals("ZABRADLI", $code);
		$this->assertNotEquals("KOBLIHA", $code);
		$this->assertNotEquals("BUBEN", $code);
		$this->assertNotEquals("PRAK", $code);
	}

	function testGetLoc() {
		$this->assertEquals(array("point_id" => 1, "game_id" => 1, "sort_id" => null, "name" => "Bílá hora", "description" => null, "end_time" => null, "min_ciphers_solved" => 10), Code::getForCode("zabradli"));
		$this->assertEquals(array("point_id" => 2, "game_id" => 1, "sort_id" => null, "name" => "S1", "name_int" => "Morseovka", "solution_timeout" => null, "hint" => null, "hint_timeout" => null), Code::getForCode("kobliha"));
		$this->assertEquals(array("hint_id" => 1, "game_id" => 1), Code::getForCode("buben"));
		$this->assertNull(Code::getForCode("prak"));
	}
}
