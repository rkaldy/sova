<?php
namespace Sova\Model;

use Sova\GameTestBase;
use Sova\Controller\Text;

class HintTest extends GameTestBase {

	protected $hint;

	function setUp(): void {
		parent::setUp();
		$this->db->execute("INSERT INTO team_hint (team_id, hint_id, cipher_id) VALUES (1, 1, 11)");
		$this->progressRepo->create(1, 1, 1);
		$this->hint = new Hint();
	}

	function tearDown(): void {
		$this->db->execute("DELETE FROM team_hint");
		$this->db->execute("DELETE FROM progress");
		parent::tearDown();
	}

	function testUnusedHintCount() {
		$this->assertEquals(0, $this->hint->unusedHintCount());
		$this->hint->add(2);
		$this->assertEquals(1, $this->hint->unusedHintCount());
	}

	function testAdd() {
		$resp = $this->hint->add(2);
		$this->assertEquals(new Text("hint.add.success", 1), $resp);
		$_SESSION["team_id"] = 2;
		$resp = $this->hint->add(2);
		$this->assertEquals(new Text("hint.add.success", 1), $resp);
	}

	function testAddAlready() {
		$resp = $this->hint->add(1);
		$this->assertEquals(new Text("hint.add.already"), $resp);
	}

	function testApply() {
		$this->db->execute("INSERT INTO team_hint (team_id, hint_id, cipher_id) VALUES (1, 2, NULL)");
		$resp = $this->hint->apply("S1b");
		$this->assertEquals(new Text("hint.apply.success", "S1b", "Zkus ji luštit poslepu"), $resp);
	}

	function testApplyAlready() {
		$this->db->execute("INSERT INTO team_hint (team_id, hint_id, cipher_id) VALUES (1, 2, NULL)");
		$resp = $this->hint->apply("S1a");
		$this->assertEquals(new Text("hint.apply.already", "S1a"), $resp);
	}

	function testApplyNoPrevious() {
		$this->db->execute("INSERT INTO team_hint (team_id, hint_id, cipher_id) VALUES (1, 2, NULL)");
		$resp = $this->hint->apply("S3");
		$this->assertEquals(new Text("cipher.no-previous", "S3"), $resp);
	}

	function testApplyNoPreviousMulti() {
		$this->db->execute("INSERT INTO team_hint (team_id, hint_id, cipher_id) VALUES (1, 2, NULL)");
		$resp = $this->hint->apply("S2");
		$this->assertEquals(new Text("cipher.no-previous.multi", "S2", 2), $resp);
	}

	function testApplyUnknownCipher() {
		$this->db->execute("INSERT INTO team_hint (team_id, hint_id, cipher_id) VALUES (1, 2, NULL)");
		$resp = $this->hint->apply("S4");
		$this->assertEquals(new Text("cipher.unknown", "S4"), $resp);
	}
	function testApplyNoHint() {
		$resp = $this->hint->apply("S1b");
		$this->assertEquals(new Text("hint.apply.no-hint"), $resp);
	}
}
