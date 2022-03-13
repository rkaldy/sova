<?php
namespace Sova\Model;

use Sova\GameTestBase;
use Sova\Repo\CipherRepo;

class HintTest extends GameTestBase {

	protected $hint;

	function setUp(): void {
		parent::setUp();
		$this->progressRepo->create(1, 1);
		$this->hint = new Hint();
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
		$resp = $this->hint->add(1);
		$this->assertEquals(new Text("hint.add.already"), $resp);
	}

	function testApply() {
		$this->db->execute("INSERT INTO hint (team_id, unihint_id, cipher_id) VALUES (1, 2, NULL)");
		$resp = $this->hint->apply("S1b");
		$this->assertEquals(new Text("hint.apply.success", "S1b", "Zkus ji luštit poslepu"), $resp);
	}

	function testApplyAlready() {
		$this->db->execute("INSERT INTO hint (team_id, unihint_id, cipher_id, time, type) VALUES (1, 1, 11, NOW(), 1)");
		$this->db->execute("INSERT INTO hint (team_id, unihint_id, cipher_id) VALUES (1, 2, NULL)");
		$resp = $this->hint->apply("S1a");
		$this->assertEquals(new Text("hint.apply.already", "S1a"), $resp);
	}

	function testApplyNoPrevious() {
		$this->db->execute("INSERT INTO hint (team_id, unihint_id, cipher_id) VALUES (1, 2, NULL)");
		$resp = $this->hint->apply("S4a");
		$this->assertEquals(new Text("cipher.no-previous-cipher", "S4a"), $resp);
	}

	function testApplyNoPreviousLoc() {
		$this->db->execute("INSERT INTO hint (team_id, unihint_id, cipher_id) VALUES (1, 2, NULL)");
		$_SESSION["settings"]["locVisitMandatory"] = 1;
		$resp = $this->hint->apply("S2");
		$this->assertEquals(new Text("cipher.no-previous-loc", "S2"), $resp);
	}

	function testApplyUnknownCipher() {
		$this->db->execute("INSERT INTO hint (team_id, unihint_id, cipher_id) VALUES (1, 2, NULL)");
		$resp = $this->hint->apply("S5");
		$this->assertEquals(new Text("cipher.unknown", "S5"), $resp);
	}

	function testApplyNoHint() {
		$resp = $this->hint->apply("S3");
		$this->assertEquals(new Text("hint.no-hint"), $resp);
	}
	
	function testApplyNoUnihint() {
		$resp = $this->hint->apply("S1b");
		$this->assertEquals(new Text("hint.apply.no-unihint"), $resp);
	}

	function testApplyAlreadySolved() {
		$this->db->execute("INSERT INTO hint (team_id, unihint_id, cipher_id) VALUES (1, 2, NULL)");
		$this->progressRepo->create(1, 11);
		$resp = $this->hint->apply("S1a");
		$this->assertEquals(new Text("hint.apply.solved", "S1a"), $resp);
	}


	function testApplyImunity() {
		$this->hint->add(1);
		$this->hint->add(2);
		$resp = $this->hint->applyImunity();
		$this->assertEquals(new Text("hint.imunity.not-enough-unihints"), $resp);
		$this->hint->add(3);
		$resp = $this->hint->applyImunity();
		$this->assertEquals(new Text("hint.imunity.success"), $resp);
		$resp = $this->hint->applyImunity();
		$this->assertEquals(new Text("hint.imunity.already"), $resp);
	}


	function testAmend() {
		$cipher = (new CipherRepo())->get(11);
		$resp = $this->hint->amend($cipher);
		
		$hints = $this->db->aquery("SELECT team_id, unihint_id, cipher_id, type FROM hint ORDER BY time");
		$this->assertEquals([
			["team_id" => 1, "unihint_id" => null, "cipher_id" => 11, "type" => Hint::NORMAL],
			["team_id" => 1, "unihint_id" => null, "cipher_id" => 11, "type" => Hint::ABSOLUTE]
		], $hints);
		$messages = $this->db->aquery("SELECT team_id, direction, text FROM message ORDER BY time");
		$this->assertEquals([
			["team_id" => 1, "direction" => Message::TO_TEAM, "text" => (new Text("cipher.hint", "S1a", "Čárka tečka čárka, tak začíná Klárka"))->format()],
			["team_id" => 1, "direction" => Message::TO_TEAM, "text" => (new Text("cipher.solution", "S1a", "ABERACE"))->format()]
		], $messages);
	}
	

	function testAmendWithoutDead() {
		$cipher = (new CipherRepo())->get(12);
		$resp = $this->hint->amend($cipher);
		
		$hints = $this->db->aquery("SELECT team_id, unihint_id, cipher_id, type FROM hint ORDER BY time");
		$this->assertEquals([
			["team_id" => 1, "unihint_id" => null, "cipher_id" => 12, "type" => Hint::NORMAL]
		], $hints);
		$messages = $this->db->aquery("SELECT team_id, direction, text FROM message ORDER BY time");
		$this->assertEquals([
			["team_id" => 1, "direction" => Message::TO_TEAM, "text" => (new Text("cipher.hint", "S1b", "Zkus ji luštit poslepu"))->format()],
		], $messages);
	}


	function testUniHintAfterAmend() {
		$cipher = (new CipherRepo())->get(11);
		$resp = $this->hint->amend($cipher);
		
		$this->db->execute("INSERT INTO hint (team_id, unihint_id, cipher_id) VALUES (1, 2, NULL)");
		$resp = $this->hint->apply("S1a");
		$this->assertEquals(new Text("hint.apply.success", "S1a", "Čárka tečka čárka, tak začíná Klárka"), $resp);

		$hints = $this->db->aquery("SELECT team_id, unihint_id, cipher_id, type FROM hint ORDER BY time");
		$this->assertEquals([
			["team_id" => 1, "unihint_id" => 2, "cipher_id" => 11, "type" => Hint::NORMAL],
			["team_id" => 1, "unihint_id" => null, "cipher_id" => 11, "type" => Hint::ABSOLUTE]
		], $hints);
		$messages = $this->db->aquery("SELECT team_id, direction, text FROM message ORDER BY time");
		$this->assertEquals([
			["team_id" => 1, "direction" => Message::TO_TEAM, "text" => (new Text("cipher.solution", "S1a", "ABERACE"))->format()]
		], $messages);
	}
}
