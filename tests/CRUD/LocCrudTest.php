<?php
namespace Sova\CRUD;

use Sova\DBException;

class LocCrudTest extends CrudTestBase {

	function setUp(): void {
		parent::setUp();
		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (1, 1, '1a')");
		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (2, 1, '1b')");
		$this->db->execute("INSERT INTO loc (point_id) VALUES (1), (2)");
		$this->db->execute("INSERT INTO code (game_id, point_id, code) VALUES (1, 1, 'PRALINKA')");
		$this->db->execute("INSERT INTO code (game_id, point_id, code) VALUES (1, 2, 'KYBL')");
	}

	function testCreate() {
		$loc = $this->create(["name" => "Turniket", "code" => "ovce", "points" => 1]);
		$this->assertEquals([
			["point_id" => 1, "name" => "1a", "description" => null, "order_id" => null, "solved_cipher_count" => null, "end_time" => null, "coord_lat" => null, "coord_lon" => null, "code" => "PRALINKA", "prev" => [], "next" => [], "points" => 0, "points_by_rank" => 0],
			["point_id" => 2, "name" => "1b", "description" => null, "order_id" => null, "solved_cipher_count" => null, "end_time" => null, "coord_lat" => null, "coord_lon" => null, "code" => "KYBL", "prev" => [], "next" => [], "points" => 0, "points_by_rank" => 0],
			["point_id" => $loc["point_id"], "name" => "Turniket", "description" => null, "order_id" => null, "solved_cipher_count" => null, "end_time" => null, "coord_lat" => null, "coord_lon" => null, "code" => "OVCE", "prev" => [], "next" => [], "points" => 1, "points_by_rank" => 0]
		], $this->list());
		$this->assertEquals(3, $this->db->equery("SELECT COUNT(*) FROM point"));
		$this->assertEquals(3, $this->db->equery("SELECT COUNT(*) FROM loc"));
		$this->assertEquals(3, $this->db->equery("SELECT COUNT(*) FROM code"));
	}

	function testCreateDuplicateCode() {
		try {
			$this->create(["name" => "Turniket", "code" => "kybl"]);
			$this->fail("Should throw DBException");
		} catch (DBException $ex) {
			$this->assertEquals(1062, $ex->getCode());
		}
		$this->assertEquals([
			["point_id" => 1, "name" => "1a", "description" => null, "order_id" => null, "solved_cipher_count" => null, "end_time" => null, "coord_lat" => null, "coord_lon" => null, "code" => "PRALINKA", "prev" => [], "next" => [], "points" => 0, "points_by_rank" => 0],
			["point_id" => 2, "name" => "1b", "description" => null, "order_id" => null, "solved_cipher_count" => null, "end_time" => null, "coord_lat" => null, "coord_lon" => null, "code" => "KYBL", "prev" => [], "next" => [], "points" => 0, "points_by_rank" => 0],
		], $this->list());
		$this->assertEquals(2, $this->db->equery("SELECT COUNT(*) FROM point"));
		$this->assertEquals(2, $this->db->equery("SELECT COUNT(*) FROM loc"));
		$this->assertEquals(2, $this->db->equery("SELECT COUNT(*) FROM code"));
	}

	function testUpdate() {
		$this->update(["point_id" => 2, "name" => "Turniket", "code" => "VEDRO", "points" => 2]);
		$this->assertEquals([
			["point_id" => 1, "name" => "1a", "description" => null, "order_id" => null, "solved_cipher_count" => null, "end_time" => null, "coord_lat" => null, "coord_lon" => null, "code" => "PRALINKA", "prev" => [], "next" => [], "points" => 0, "points_by_rank" => 0],
			["point_id" => 2, "name" => "Turniket", "description" => null, "order_id" => null, "solved_cipher_count" => null, "end_time" => null, "coord_lat" => null, "coord_lon" => null, "code" => "VEDRO", "prev" => [], "next" => [], "points" => 2, "points_by_rank" => 0]
		], $this->list());
	}
	
	function testDelete() {
		$this->delete(["point_id" => 2]);
		$this->assertEquals([
			["point_id" => 1, "name" => "1a", "description" => null, "order_id" => null, "solved_cipher_count" => null, "end_time" => null, "coord_lat" => null, "coord_lon" => null, "code" => "PRALINKA", "prev" => [], "next" => [], "points" => 0, "points_by_rank" => 0]
		], $this->list());
		$this->assertEquals(1, $this->db->equery("SELECT COUNT(*) FROM point"));
		$this->assertEquals(1, $this->db->equery("SELECT COUNT(*) FROM loc"));
		$this->assertEquals(1, $this->db->equery("SELECT COUNT(*) FROM code"));
	}
}
