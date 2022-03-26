<?php
namespace Sova\Model;

use Sova\GameTestBase;
use Sova\Repo\HintRepo;
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
		$this->hint->addCCode(2);
		$this->assertEquals(1, $this->hint->unusedHintCount());
	}

	function testAdd() {
		$resp = $this->hint->addCCode(2);
		$this->assertEquals(new Text("hint.add.success", 1), $resp);
		$_SESSION["team_id"] = 2;
		$resp = $this->hint->addCCode(2);
		$this->assertEquals(new Text("hint.add.success", 1), $resp);
	}

	function testAddAlready() {
		$resp = $this->hint->addCCode(1);
		$resp = $this->hint->addCCode(1);
		$this->assertEquals(new Text("hint.add.already"), $resp);
	}

	function testApply() {
		$this->db->execute("INSERT INTO hint (team_id, ccode_id, cipher_id) VALUES (1, 2, NULL)");
		$resp = $this->hint->apply("S1");
		$this->assertEquals(new Text("hint.apply.success", "S1", "Čárka tečka čárka, tak začíná Klárka"), $resp);
	}

	function testApplyAlready() {
		$this->db->execute("INSERT INTO hint (team_id, ccode_id, cipher_id, time, type) VALUES (1, 1, 11, NOW(), 1)");
		$this->db->execute("INSERT INTO hint (team_id, ccode_id, cipher_id) VALUES (1, 2, NULL)");
		$resp = $this->hint->apply("S1");
		$this->assertEquals(new Text("hint.apply.already", "S1"), $resp);
	}

	function testApplyNoPrevious() {
		$this->db->execute("INSERT INTO hint (team_id, ccode_id, cipher_id) VALUES (1, 2, NULL)");
		$resp = $this->hint->apply("S4a");
		$this->assertEquals(new Text("cipher.no-previous-cipher", "S4a"), $resp);
	}

	function testApplyNoPreviousLoc() {
		$this->db->execute("INSERT INTO hint (team_id, ccode_id, cipher_id) VALUES (1, 2, NULL)");
		Settings::set("locVisitMandatory", 1);
		$resp = $this->hint->apply("S2");
		$this->assertEquals(new Text("cipher.no-previous-loc", "S2"), $resp);
	}

	function testApplyUnknownCipher() {
		$this->db->execute("INSERT INTO hint (team_id, ccode_id, cipher_id) VALUES (1, 2, NULL)");
		$resp = $this->hint->apply("S5");
		$this->assertEquals(new Text("cipher.unknown", "S5"), $resp);
	}

	function testApplyNoHint() {
		$resp = $this->hint->apply("A1");
		$this->assertEquals(new Text("hint.no-hint"), $resp);
	}
	
	function testApplyNoCCode() {
		$resp = $this->hint->apply("S1");
		$this->assertEquals(new Text("hint.apply.no-ccode"), $resp);
	}

	function testApplyAlreadySolved() {
		$this->db->execute("INSERT INTO hint (team_id, ccode_id, cipher_id) VALUES (1, 2, NULL)");
		$this->progressRepo->create(1, 11);
		$resp = $this->hint->apply("S1");
		$this->assertEquals(new Text("hint.apply.solved", "S1"), $resp);
	}

	function testApplyImunity() {
		$this->hint->addCCode(1);
		$this->hint->addCCode(2);
		$resp = $this->hint->applyImunity();
		$this->assertEquals(new Text("hint.imunity.not-enough-ccodes"), $resp);
		$this->hint->addCCode(3);
		$resp = $this->hint->applyImunity();
		$this->assertEquals(new Text("hint.imunity.success"), $resp);
		$resp = $this->hint->applyImunity();
		$this->assertEquals(new Text("hint.imunity.already"), $resp);
	}
}
