<?php
namespace Sova\CRUD;

use Sova\DBException;

class CcodeCrudTest extends CrudTestBase {

	function setUp(): void {
		parent::setUp();
		$this->db->execute("INSERT INTO ccode (game_id, ccode_id) VALUES (1, 1)");
		$this->db->execute("INSERT INTO code (game_id, ccode_id, code) VALUES (1, 1, 'BUBEN')");
	}

	function testCreate() {
		$ccode = $this->create(array("code" => "divizna"));
		$this->assertEquals(array(
			array("ccode_id" => 1, "code" => "BUBEN"),
			array("ccode_id" => $ccode["ccode_id"], "code" => "DIVIZNA")
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
			array("ccode_id" => 1, "code" => "BUBEN")
		), $this->list());
	}

	function testCreateGeneratedCode() {
		$ccode = $this->create(array("code" => ""));
		$this->assertEquals(1, preg_match("/[A-Z]+/", $ccode["code"]));
	}

	function testUpdate() {
		$ccode = $this->list()[0];
		$ccode["code"] = "divizna ";
		$this->update($ccode);
		$this->assertEquals(array(
			array("ccode_id" => 1, "code" => "DIVIZNA")
		), $this->list());
	}

	function testDelete() {
		$this->delete($this->list()[0]);
		$this->assertCount(0, $this->list());
	}
}
