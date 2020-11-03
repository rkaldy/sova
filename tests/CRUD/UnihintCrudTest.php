<?php
namespace Sova\CRUD;

use Sova\DBException;

class UnihintCrudTest extends CrudTestBase {

	function setUp(): void {
		parent::setUp();
		$this->db->execute("INSERT INTO unihint (game_id, unihint_id) VALUES (1, 1)");
		$this->db->execute("INSERT INTO code (game_id, unihint_id, code) VALUES (1, 1, 'BUBEN')");
	}

	function testCreate() {
		$unihint = $this->create(array("code" => "divizna"));
		$this->assertEquals(array(
			array("unihint_id" => 1, "code" => "BUBEN"),
			array("unihint_id" => $unihint["unihint_id"], "code" => "DIVIZNA")
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
			array("unihint_id" => 1, "code" => "BUBEN")
		), $this->list());
	}

	function testCreateGeneratedCode() {
		$unihint = $this->create(array("code" => ""));
		$this->assertEquals(1, preg_match("/[A-Z]+/", $unihint["code"]));
	}

	function testUpdate() {
		$unihint = $this->list()[0];
		$unihint["code"] = "divizna ";
		$this->update($unihint);
		$this->assertEquals(array(
			array("unihint_id" => 1, "code" => "DIVIZNA")
		), $this->list());
	}

	function testDelete() {
		$this->delete($this->list()[0]);
		$this->assertCount(0, $this->list());
	}
}
