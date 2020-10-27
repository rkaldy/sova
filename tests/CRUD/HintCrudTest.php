<?php
namespace Sova\CRUD;

use Sova\DBException;

class HintCrudTest extends CrudTestBase {

	function setUp(): void {
		parent::setUp();
		$this->db->execute("INSERT INTO hint (game_id, hint_id) VALUES (1, 1)");
		$this->db->execute("INSERT INTO code (game_id, hint_id, code) VALUES (1, 1, 'BUBEN')");
	}

	function tearDown(): void {
		$this->db->execute("DELETE FROM hint");
		parent::tearDown();
	}

	function testCreate() {
		$hint = $this->create(array("code" => "divizna"));
		$this->assertEquals(array(
			array("hint_id" => 1, "code" => "BUBEN"),
			array("hint_id" => $hint["hint_id"], "code" => "DIVIZNA")
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
			array("hint_id" => 1, "code" => "BUBEN")
		), $this->list());
	}

	function testCreateGeneratedCode() {
		$hint = $this->create(array("code" => ""));
		$this->assertEquals(1, preg_match("/[A-Z]+/", $hint["code"]));
	}

	function testUpdate() {
		$hint = $this->list()[0];
		$hint["code"] = "divizna ";
		$this->update($hint);
		$this->assertEquals(array(
			array("hint_id" => 1, "code" => "DIVIZNA")
		), $this->list());
	}

	function testDelete() {
		$this->delete($this->list()[0]);
		$this->assertCount(0, $this->list());
	}
}
