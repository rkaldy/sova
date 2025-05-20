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
		$cipher1 = $this->create(["name" => "S3", "name_int" => "semafor", "activity" => false, "code" => "javor", "points_by_rank" => 0, "prev" => [2], "next" => [4, 3]]);
		$cipher2 = $this->create(["name" => "S4", "name_int" => "nahradni", "activity" => false, "code" => "soliter", "points_by_rank" => 0, "prev" => [], "points" => 1]);
		$this->assertEquals([
			["point_id" => 5, "name" => "S1", "name_int" => "Morseovka", "activity" => false, "hint" => null, "howto" => null, "code" => "ZABRADLI", "points_by_rank" => 0, "prev" => [1], "next" => [2], "points" => 0, "points_by_rank" => 0],
			["point_id" => 6, "name" => "S2", "name_int" => "Braille", "activity" => false, "hint" => null, "howto" => null, "code" => "KOBLIHA", "points_by_rank" => 0, "prev" => [1], "next" => [3], "points" => 0, "points_by_rank" => 0],
			["point_id" => $cipher1["point_id"], "name" => "S3", "name_int" => "semafor", "activity" => false, "hint" => null, "howto" => null, "code" => "JAVOR", "points_by_rank" => 0, "prev" => [2], "next" => [3, 4], "points" => 0, "points_by_rank" => 0],
			["point_id" => $cipher2["point_id"], "name" => "S4", "name_int" => "nahradni", "activity" => false, "hint" => null, "howto" => null, "code" => "SOLITER", "points_by_rank" => 0, "prev" => [], "next" => [], "points" => 1, "points_by_rank" => 0]
		], $this->list());
		$this->assertEquals(8, $this->db->equery("SELECT COUNT(*) FROM point"));
		$this->assertEquals(4, $this->db->equery("SELECT COUNT(*) FROM cipher"));
		$this->assertEquals(4, $this->db->equery("SELECT COUNT(*) FROM code"));
		$this->assertEquals(7, $this->db->equery("SELECT COUNT(*) FROM step"));
	}

	function testCreateDuplicateCode() {
		try {
			$this->create(["name" => "S3", "code" => "zabradli", "activity" => false, "points_by_rank" => 0]);
			$this->fail("Should throw DBException");
		} catch (DBException $ex) {
			$this->assertEquals(1062, $ex->getCode());
		}
		$this->assertEquals([
			["point_id" => 5, "name" => "S1", "name_int" => "Morseovka", "activity" => false, "hint" => null, "howto" => null, "code" => "ZABRADLI", "points_by_rank" => 0, "prev" => [1], "next" => [2], "points" => 0, "points_by_rank" => 0],
			["point_id" => 6, "name" => "S2", "name_int" => "Braille", "activity" => false, "hint" => null, "howto" => null, "code" => "KOBLIHA", "points_by_rank" => 0, "prev" => [1], "next" => [3], "points" => 0, "points_by_rank" => 0]
		], $this->list());
		$this->assertEquals(6, $this->db->equery("SELECT COUNT(*) FROM point"));
		$this->assertEquals(2, $this->db->equery("SELECT COUNT(*) FROM cipher"));
		$this->assertEquals(2, $this->db->equery("SELECT COUNT(*) FROM code"));
		$this->assertEquals(4, $this->db->equery("SELECT COUNT(*) FROM step"));
	}

	function testUpdate() {
		$this->update(["point_id" => 6, "name" => "S3", "name_int" => "Caesar", "activity" => false, "code" => "RUBIKON", "points_by_rank" => 0, "prev" => [1, 2], "next" => [3, 4], "points" => 2]);
		$this->assertEquals([
			["point_id" => 5, "name" => "S1", "name_int" => "Morseovka", "activity" => false, "hint" => null, "howto" => null, "code" => "ZABRADLI", "points_by_rank" => 0, "prev" => [1], "next" => [2], "points" => 0, "points_by_rank" => 0],
			["point_id" => 6, "name" => "S3", "name_int" => "Caesar", "activity" => false, "hint" => null, "howto" => null, "code" => "RUBIKON", "points_by_rank" => 0, "prev" => [1, 2], "next" => [3, 4], "points" => 2, "points_by_rank" => 0]
		], $this->list());
		$this->assertEquals(6, $this->db->equery("SELECT COUNT(*) FROM point"));
		$this->assertEquals(2, $this->db->equery("SELECT COUNT(*) FROM cipher"));
		$this->assertEquals(2, $this->db->equery("SELECT COUNT(*) FROM code"));
		$this->assertEquals(6, $this->db->equery("SELECT COUNT(*) FROM step"));
	}
	
	function testDelete() {
		$this->delete(["point_id" => 6]);
		$this->assertEquals([
			["point_id" => 5, "name" => "S1", "name_int" => "Morseovka", "activity" => false, "hint" => null, "howto" => null, "code" => "ZABRADLI", "points_by_rank" => 0, "prev" => [1], "next" => [2], "points" => 0, "points_by_rank" => 0]
		], $this->list());
		$this->assertEquals(5, $this->db->equery("SELECT COUNT(*) FROM point"));
		$this->assertEquals(1, $this->db->equery("SELECT COUNT(*) FROM cipher"));
		$this->assertEquals(1, $this->db->equery("SELECT COUNT(*) FROM code"));
		$this->assertEquals(2, $this->db->equery("SELECT COUNT(*) FROM step"));
	}
}
