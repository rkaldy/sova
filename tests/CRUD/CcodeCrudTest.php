<?php
namespace Sova\CRUD;

use Sova\DBException;

class CcodeCrudTest extends CrudTestBase {

	function setUp(): void {
		parent::setUp();
		$this->db->execute("INSERT INTO ccode (game_id, ccode_id) VALUES (1, 1)");
		$this->db->execute("INSERT INTO code (game_id, ccode_id, code) VALUES (1, 1, 'BUBEN')");
		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (1, 1, '1a')");
		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (2, 1, '1b')");
		$this->db->execute("INSERT INTO loc (point_id) VALUES (1), (2)");
	}

	function testCreate() {
		$ccode = $this->create(array("code" => "divizna", "cond_loc_id" => 2));
		$this->assertEquals(array(
			array("ccode_id" => 1, "code" => "BUBEN", "cond_loc_id" => null),
			array("ccode_id" => $ccode["ccode_id"], "code" => "DIVIZNA", "cond_loc_id" => 2)
		), $this->list());
	}

	function testcreateDuplicateCode() {
		try {
			$this->create(array("code" => "buben"));
			$this->fail("Should throw DBException");
		} catch (DBException $ex) {
			$this->assertEquals(1062, $ex->getCode());
		}
		$this->assertEquals(array(
			array("ccode_id" => 1, "code" => "BUBEN", "cond_loc_id" => null)
		), $this->list());
	}

	function testCreateGeneratedCode() {
		$ccode = $this->create(array("code" => ""));
		$this->assertEquals(1, preg_match("/[A-Z]+/", $ccode["code"]));
	}

	function testUpdate() {
		$ccode = $this->list()[0];
		$ccode["code"] = "divizna ";
		$ccode["cond_loc_id"] = 2;
		$this->update($ccode);
		$this->assertEquals(array(
			array("ccode_id" => 1, "code" => "DIVIZNA", "cond_loc_id" => 2)
		), $this->list());
	}

	function testDelete() {
		$this->delete($this->list()[0]);
		$this->assertCount(0, $this->list());
	}
}
