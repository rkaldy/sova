<?php
namespace Sova\CRUD;

use Sova\DBException;

class CipherCrudTest extends CrudTestBase {

	function setUp(): void {
		parent::setUp();
		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (1, 1, '1a')");
		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (2, 1, '1b')");
		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (3, 1, 'Turniket')");
		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (4, 1, 'Cíl')");
		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (5, 1, 'S1')");
		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (6, 1, 'S2')");
		$this->db->execute("INSERT INTO cipher (point_id, name_int) VALUES (5, 'Morseovka')");
		$this->db->execute("INSERT INTO cipher (point_id, name_int) VALUES (6, 'Braille')");
		$this->db->execute("INSERT INTO code (game_id, point_id, code) VALUES (1, 5, 'ZABRADLI')");
		$this->db->execute("INSERT INTO code (game_id, point_id, code) VALUES (1, 6, 'KOBLIHA')");
		$this->db->execute("INSERT INTO step (from_point_id, to_point_id) VALUES (1, 5), (1, 6), (5, 2), (6, 3)");
	}

	function testCreate() {
		$cipher1 = $this->create(["name" => "S3", "name_int" => "semafor", "code" => "javor", "prev" => [2], "next" => [4, 3]]);
		$cipher2 = $this->create(["name" => "S4", "name_int" => "nahradni", "code" => "soliter", "prev" => [], "points" => 1]);
		$this->assertEquals([
			["point_id" => 5, "name" => "S1", "name_int" => "Morseovka", "solution_timeout" => null, "hint" => null, "hint_timeout" => null, "code" => "ZABRADLI", "prev" => [1], "next" => [2], "points" => 0],
			["point_id" => 6, "name" => "S2", "name_int" => "Braille", "solution_timeout" => null, "hint" => null, "hint_timeout" => null, "code" => "KOBLIHA", "prev" => [1], "next" => [3], "points" => 0],
			["point_id" => $cipher1["point_id"], "name" => "S3", "name_int" => "semafor", "solution_timeout" => null, "hint" => null, "hint_timeout" => null, "code" => "JAVOR", "prev" => [2], "next" => [3, 4], "points" => 0],
			["point_id" => $cipher2["point_id"], "name" => "S4", "name_int" => "nahradni", "solution_timeout" => null, "hint" => null, "hint_timeout" => null, "code" => "SOLITER", "prev" => [], "next" => [], "points" => 1]
		], $this->list());
		$this->assertEquals(8, $this->db->equery("SELECT COUNT(*) FROM point"));
		$this->assertEquals(4, $this->db->equery("SELECT COUNT(*) FROM cipher"));
		$this->assertEquals(4, $this->db->equery("SELECT COUNT(*) FROM code"));
		$this->assertEquals(7, $this->db->equery("SELECT COUNT(*) FROM step"));
	}

	function testCreateDuplicateCode() {
		try {
			$this->create(["name" => "S3", "code" => "zabradli"]);
			$this->fail("Should throw DBException");
		} catch (DBException $ex) {
			$this->assertEquals(1062, $ex->getCode());
		}
		$this->assertEquals([
			["point_id" => 5, "name" => "S1", "name_int" => "Morseovka", "solution_timeout" => null, "hint" => null, "hint_timeout" => null, "code" => "ZABRADLI", "prev" => [1], "next" => [2], "points" => 0],
			["point_id" => 6, "name" => "S2", "name_int" => "Braille", "solution_timeout" => null, "hint" => null, "hint_timeout" => null, "code" => "KOBLIHA", "prev" => [1], "next" => [3], "points" => 0]
		], $this->list());
		$this->assertEquals(6, $this->db->equery("SELECT COUNT(*) FROM point"));
		$this->assertEquals(2, $this->db->equery("SELECT COUNT(*) FROM cipher"));
		$this->assertEquals(2, $this->db->equery("SELECT COUNT(*) FROM code"));
		$this->assertEquals(4, $this->db->equery("SELECT COUNT(*) FROM step"));
	}

	function testUpdate() {
		$this->update(["point_id" => 6, "name" => "S3", "name_int" => "Caesar", "code" => "RUBIKON", "prev" => [1, 2], "next" => [3, 4], "points" => 2]);
		$this->assertEquals([
			["point_id" => 5, "name" => "S1", "name_int" => "Morseovka", "solution_timeout" => null, "hint" => null, "hint_timeout" => null, "code" => "ZABRADLI", "prev" => [1], "next" => [2], "points" => 0],
			["point_id" => 6, "name" => "S3", "name_int" => "Caesar", "solution_timeout" => null, "hint" => null, "hint_timeout" => null, "code" => "RUBIKON", "prev" => [1, 2], "next" => [3, 4], "points" => 2]
		], $this->list());
		$this->assertEquals(6, $this->db->equery("SELECT COUNT(*) FROM point"));
		$this->assertEquals(2, $this->db->equery("SELECT COUNT(*) FROM cipher"));
		$this->assertEquals(2, $this->db->equery("SELECT COUNT(*) FROM code"));
		$this->assertEquals(6, $this->db->equery("SELECT COUNT(*) FROM step"));
	}
	
	function testDelete() {
		$this->delete(["point_id" => 6]);
		$this->assertEquals([
			["point_id" => 5, "name" => "S1", "name_int" => "Morseovka", "solution_timeout" => null, "hint" => null, "hint_timeout" => null, "code" => "ZABRADLI", "prev" => [1], "next" => [2], "points" => 0]
		], $this->list());
		$this->assertEquals(5, $this->db->equery("SELECT COUNT(*) FROM point"));
		$this->assertEquals(1, $this->db->equery("SELECT COUNT(*) FROM cipher"));
		$this->assertEquals(1, $this->db->equery("SELECT COUNT(*) FROM code"));
		$this->assertEquals(2, $this->db->equery("SELECT COUNT(*) FROM step"));
	}
}
