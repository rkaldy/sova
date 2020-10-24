<?php
namespace Sova\Model;

use Sova\TestBase;
use Sova\DBException;

class LocRepoTest extends TestBase {

	protected $repo;

	function setUp(): void {
		parent::setUp();
		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (1, 1, 'Bílá hora')");
		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (2, 1, 'Černá hora')");
		$this->db->execute("INSERT INTO loc (point_id, min_ciphers_solved) VALUES (1, 10)");
		$this->db->execute("INSERT INTO loc (point_id, min_ciphers_solved) VALUES (2, 20)");
		$this->db->execute("INSERT INTO code (game_id, point_id, code) VALUES (1, 1, 'PRALINKA')");
		$this->db->execute("INSERT INTO code (game_id, point_id, code) VALUES (1, 2, 'KYBL')");
		$this->repo = new LocRepo();
	}

	function tearDown(): void {
		$this->db->exec("DELETE FROM point");
		parent::tearDown();
	}

	function testCreate() {
		$newLoc = $this->repo->create(array("name" => "Dvoračky", "min_ciphers_solved" => 30, "code" => "ovce"));
		$this->assertEquals(array(
			array("point_id" => 1, "game_id" => 1, "sort_id" => null, "name" => "Bílá hora", "description" => null, "end_time" => null, "min_ciphers_solved" => 10, "code" => "PRALINKA"),
			array("point_id" => 2, "game_id" => 1, "sort_id" => null, "name" => "Černá hora", "description" => null, "end_time" => null, "min_ciphers_solved" => 20, "code" => "KYBL"),
			array("point_id" => $newLoc["point_id"], "game_id" => 1, "sort_id" => null, "name" => "Dvoračky", "description" => null, "end_time" => null, "min_ciphers_solved" => 30, "code" => "OVCE")
		), $this->repo->list());
		$this->assertEquals(3, $this->db->equery("SELECT COUNT(*) FROM point"));
		$this->assertEquals(3, $this->db->equery("SELECT COUNT(*) FROM loc"));
		$this->assertEquals(3, $this->db->equery("SELECT COUNT(*) FROM code"));
	}

	function testCreateDuplicateCode() {
		try {
			$newLoc = $this->repo->create(array("name" => "Dvoračky", "min_ciphers_solved" => 30, "code" => "kybl"));
			$this->fail("Should throw DBException");
		} catch (DBException $ex) {
			$this->assertEquals(1062, $ex->getCode());
		}
		$this->assertEquals(array(
			array("point_id" => 1, "game_id" => 1, "sort_id" => null, "name" => "Bílá hora", "description" => null, "end_time" => null, "min_ciphers_solved" => 10, "code" => "PRALINKA"),
			array("point_id" => 2, "game_id" => 1, "sort_id" => null, "name" => "Černá hora", "description" => null, "end_time" => null, "min_ciphers_solved" => 20, "code" => "KYBL"),
		), $this->repo->list());
		$this->assertEquals(2, $this->db->equery("SELECT COUNT(*) FROM point"));
		$this->assertEquals(2, $this->db->equery("SELECT COUNT(*) FROM loc"));
		$this->assertEquals(2, $this->db->equery("SELECT COUNT(*) FROM code"));
	}

	function testUpdate() {
		$this->repo->update(array("point_id" => 2, "name" => "Dvoračky", "min_ciphers_solved" => 30, "code" => "VEDRO"));
		$this->assertEquals(array(
			array("point_id" => 1, "game_id" => 1, "sort_id" => null, "name" => "Bílá hora", "description" => null, "end_time" => null, "min_ciphers_solved" => 10, "code" => "PRALINKA"),
			array("point_id" => 2, "game_id" => 1, "sort_id" => null, "name" => "Dvoračky", "description" => null, "end_time" => null, "min_ciphers_solved" => 30, "code" => "VEDRO")
		), $this->repo->list());
	}
	
	function testDelete() {
		$this->repo->delete(array("point_id" => 2));
		$this->assertEquals(array(
			array("point_id" => 1, "game_id" => 1, "sort_id" => null, "name" => "Bílá hora", "description" => null, "end_time" => null, "min_ciphers_solved" => 10, "code" => "PRALINKA")
		), $this->repo->list());
		$this->assertEquals(1, $this->db->equery("SELECT COUNT(*) FROM point"));
		$this->assertEquals(1, $this->db->equery("SELECT COUNT(*) FROM loc"));
		$this->assertEquals(1, $this->db->equery("SELECT COUNT(*) FROM code"));
	}
}
