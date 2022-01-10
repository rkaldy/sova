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
		$loc = $this->create(array("name" => "Turniket", "code" => "ovce"));
		$this->assertEquals(array(
			array("point_id" => 1, "name" => "1a", "description" => null, "solved_cipher_count" => null, "end_time" => null, "code" => "PRALINKA", "prev" => [], "next" => []),
			array("point_id" => 2, "name" => "1b", "description" => null, "solved_cipher_count" => null, "end_time" => null, "code" => "KYBL", "prev" => [], "next" => []),
			array("point_id" => $loc["point_id"], "name" => "Turniket", "description" => null, "solved_cipher_count" => null, "end_time" => null, "code" => "OVCE", "prev" => [], "next" => [])
		), $this->list());
		$this->assertEquals(3, $this->db->equery("SELECT COUNT(*) FROM point"));
		$this->assertEquals(3, $this->db->equery("SELECT COUNT(*) FROM loc"));
		$this->assertEquals(3, $this->db->equery("SELECT COUNT(*) FROM code"));
	}

	function testCreateDuplicateCode() {
		try {
			$this->create(array("name" => "Turniket", "code" => "kybl"));
			$this->fail("Should throw DBException");
		} catch (DBException $ex) {
			$this->assertEquals(1062, $ex->getCode());
		}
		$this->assertEquals(array(
			array("point_id" => 1, "name" => "1a", "description" => null, "solved_cipher_count" => null, "end_time" => null, "code" => "PRALINKA", "prev" => [], "next" => []),
			array("point_id" => 2, "name" => "1b", "description" => null, "solved_cipher_count" => null, "end_time" => null, "code" => "KYBL", "prev" => [], "next" => []),
		), $this->list());
		$this->assertEquals(2, $this->db->equery("SELECT COUNT(*) FROM point"));
		$this->assertEquals(2, $this->db->equery("SELECT COUNT(*) FROM loc"));
		$this->assertEquals(2, $this->db->equery("SELECT COUNT(*) FROM code"));
	}

	function testUpdate() {
		$this->update(array("point_id" => 2, "name" => "Turniket", "code" => "VEDRO"));
		$this->assertEquals(array(
			array("point_id" => 1, "name" => "1a", "description" => null, "solved_cipher_count" => null, "end_time" => null, "code" => "PRALINKA", "prev" => [], "next" => []),
			array("point_id" => 2, "name" => "Turniket", "description" => null, "solved_cipher_count" => null, "end_time" => null, "code" => "VEDRO", "prev" => [], "next" => [])
		), $this->list());
	}
	
	function testDelete() {
		$this->delete(array("point_id" => 2));
		$this->assertEquals(array(
			array("point_id" => 1, "name" => "1a", "description" => null, "solved_cipher_count" => null, "end_time" => null, "code" => "PRALINKA", "prev" => [], "next" => [])
		), $this->list());
		$this->assertEquals(1, $this->db->equery("SELECT COUNT(*) FROM point"));
		$this->assertEquals(1, $this->db->equery("SELECT COUNT(*) FROM loc"));
		$this->assertEquals(1, $this->db->equery("SELECT COUNT(*) FROM code"));
	}
}
