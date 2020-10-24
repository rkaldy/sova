<?php
namespace Sova\Model;

use Sova\TestBase;
use Sova\DBException;

class HintRepoTest extends TestBase {

	protected $repo;

	function setUp(): void {
		parent::setUp();
		$this->db->execute("INSERT INTO hint (game_id, hint_id) VALUES (1, 1)");
		$this->db->execute("INSERT INTO code (game_id, hint_id, code) VALUES (1, 1, 'BUBEN')");
		$this->repo = new HintRepo();
	}

	function tearDown(): void {
		$this->db->execute("DELETE FROM hint");
		parent::tearDown();
	}

	function testCreate() {
		$hint = $this->repo->create(array("code" => "divizna"));
		$this->assertEquals(array(
			array("game_id" => 1, "hint_id" => 1, "code" => "BUBEN"),
			array("game_id" => 1, "hint_id" => $hint["hint_id"], "code" => "DIVIZNA")
		), $this->repo->list());
	}

	function testcreateDuplicateCode() {
		try {
			$hint = $this->repo->create(array("code" => "buben"));
			$this->fail("Should throw DBException");
		} catch (DBException $ex) {
			$this->assertEquals(1062, $ex->getCode());
		}
		$this->assertEquals(array(
			array("game_id" => 1, "hint_id" => 1, "code" => "BUBEN")
		), $this->repo->list());
	}

	function testCreateGeneratedCode() {
		$hint = $this->repo->create(array());
		$this->assertEquals(1, preg_match("/[A-Z]+/", $hint["code"]));
	}

	function testUpdate() {
		$hint = $this->repo->list()[0];
		$hint["code"] = "divizna ";
		$this->repo->update($hint);
		$this->assertEquals(array(
			array("game_id" => 1, "hint_id" => 1, "code" => "DIVIZNA")
		), $this->repo->list());
	}

	function testDelete() {
		$this->repo->delete($this->repo->list()[0]);
		$this->assertCount(0, $this->repo->list());
	}
}
