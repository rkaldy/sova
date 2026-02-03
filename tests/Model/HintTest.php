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
		Settings::set("hintCCodes", 1);
		Settings::set("howtoCCodes", 2);
		Settings::set("solutionCCodes", 0);
		Settings::set("hintPoints", 10);
		Settings::set("howtoPoints", 20);
		Settings::set("solutionPoints", 30);
		Settings::set("imunityCCodes", 2);
	}

	function testAddCCode() {
		$resp = $this->hint->addCCode(2);
		$this->assertEquals(new Text("ccode.add.success", 1), $resp);
		$_SESSION["team_id"] = 2;
		$resp = $this->hint->addCCode(2);
		$this->assertEquals(new Text("ccode.add.success", 1), $resp);
	}

	function testAddCCodeAlready() {
		$resp = $this->hint->addCCode(1);
		$resp = $this->hint->addCCode(1);
		$this->assertEquals(new Text("ccode.add.already"), $resp);
	}

	function testUnusedCCodeCount() {
		$this->assertEquals(0, $this->hint->unusedCCodeCount());
		$this->hint->addCCode(2);
		$this->assertEquals(1, $this->hint->unusedCCodeCount());
	}

	function testCheckUnknownCipher() {
		$ret = $this->hint->check("BAD");
		$this->assertEquals([false, [new Text("cipher.unknown", "BAD")]], $ret);
	}

	function testCheckActivity() {
		$ret = $this->hint->check("A1");
		$this->assertEquals([false, [new Text("hint.apply.activity")]], $ret);
	}

	function testCheckSolved() {
		$this->progressRepo->create(1, 11);
		$ret = $this->hint->check("S1");
		$this->assertEquals([false, [new Text("hint.apply.solved", "S1")]], $ret);
	}

	function testCheckNoPrevious() {
		$ret = $this->hint->check("S4a");
		$this->assertEquals([false, [new Text("cipher.no-previous-cipher", "S4a")]], $ret);
	}

	function testCheckNoPreviousLoc() {
		Settings::set("locVisitMandatory", 1);
		$ret = $this->hint->check("S2");
		$this->assertEquals([false, [new Text("cipher.no-previous-loc", "S2")]], $ret);
	}

	function testCheckNoHint() {
		$this->progressRepo->create(1, 5);
		$ret = $this->hint->check("S3");
		$this->assertEquals([false, [new Text("hint.apply.no-hint")]], $ret);
	}

	function testCheckHint() {
		$ret = $this->hint->check("S1");
		$this->assertEquals([true, [new Text("hint.apply.no-history", "S1"), new Text("hint.apply.points.no-ccode", "nápovědu", 10)]], $ret);
		
		$this->hint->addCCode(1);
		$ret = $this->hint->check("S1");
		$this->assertEquals([true, [new Text("hint.apply.no-history", "S1"), new Text("hint.apply.ccodes", "nápovědu", 1)]], $ret);
	}

	function testCheckHowto() {
		$this->db->execute("INSERT INTO hint (team_id, cipher_id, time, type) VALUES (1, 11, NOW(), 1)");
		
		$this->hint->addCCode(1);
		$ret = $this->hint->check("S1");
		$this->assertEquals([true, [new Text("hint.apply.history", "S1", "nápovědu"), new Text("hint.apply.points.no-ccode", "postup", 20)]], $ret);
		
		$this->hint->addCCode(2);
		$ret = $this->hint->check("S1");
		$this->assertEquals([true, [new Text("hint.apply.history", "S1", "nápovědu"), new Text("hint.apply.ccodes", "postup", 2)]], $ret);
	}

	function testCheckSolution() {
		$this->db->execute("INSERT INTO hint (team_id, cipher_id, time, type) VALUES (1, 11, NOW(), 2)");
		
		$this->hint->addCCode(1);
		$this->hint->addCCode(2);
		$this->hint->addCCode(3);
		$ret = $this->hint->check("S1");
		$this->assertEquals([true, [new Text("hint.apply.history", "S1", "postup"), new Text("hint.apply.points", "řešení", 30)]], $ret);
	}

	function testCheckDisabledHowto() {
		$this->progressRepo->create(1, 11);
		$this->db->execute("INSERT INTO hint (team_id, cipher_id, time, type) VALUES (1, 13, NOW(), 1)");
		
		$this->hint->addCCode(1);
		$this->hint->addCCode(2);
		$this->hint->addCCode(3);
		$ret = $this->hint->check("S2");
		$this->assertEquals([true, [new Text("hint.apply.history", "S2", "nápovědu"), new Text("hint.apply.points", "řešení", 30)]], $ret);
	}

	function testCheckAlready() {
		$this->db->execute("INSERT INTO hint (team_id, cipher_id, time, type) VALUES (1, 11, NOW(), 3)");
		$ret = $this->hint->check("S1");
		$this->assertEquals([false, [new Text("hint.apply.already", "S1")]], $ret);
	}

	function testApplyPoints() {
		$team = new Team();

		$resp = $this->hint->apply("S1");
		$this->assertEquals(new Text("hint.text.hint", "S1", "Čárka tečka čárka, tak začíná Klárka"), $resp);
		$this->assertEquals(-10, $team->points());

		$resp = $this->hint->apply("S1");
		$this->assertEquals(new Text("hint.text.howto", "S1", "Použij morseovku"), $resp);
		$this->assertEquals(-30, $team->points());

		$resp = $this->hint->apply("S1");
		$this->assertEquals(new Text("hint.text.solution", "S1", "ABERACE"), $resp);
		$this->assertEquals(-60, $team->points());
		$this->assertEquals(0, $this->hint->unusedCCodeCount());
		
		$resp = $this->hint->apply("S1");
		$this->assertEquals(new Text("hint.apply.already", "S1"), $resp);
	}

	function testApplyCCodes() {
		$team = new Team();
		$this->hint->addCCode(1);
		$this->hint->addCCode(2);

		$resp = $this->hint->apply("S1");
		$this->assertEquals(new Text("hint.text.hint", "S1", "Čárka tečka čárka, tak začíná Klárka"), $resp);
		$this->assertEquals(1, $this->hint->unusedCCodeCount());
		$this->assertEquals(0, $team->points());

		$resp = $this->hint->apply("S1");
		$this->assertEquals(new Text("hint.text.howto", "S1", "Použij morseovku"), $resp);
		$this->assertEquals(1, $this->hint->unusedCCodeCount());
		$this->assertEquals(-20, $team->points());

		$this->hint->addCCode(3);
		$resp = $this->hint->apply("S1");
		$this->assertEquals(new Text("hint.text.solution", "S1", "ABERACE"), $resp);
		$this->assertEquals(2, $this->hint->unusedCCodeCount());
		$this->assertEquals(-50, $team->points());
		
		$resp = $this->hint->apply("S1");
		$this->assertEquals(new Text("hint.apply.already", "S1"), $resp);
	}

	function testApplyPointsDisabledHowto() {
		$team = new Team();
		$this->progressRepo->create(1, 11);

		$resp = $this->hint->apply("S2");
		$this->assertEquals(new Text("hint.text.hint", "S2", "Krzyz"), $resp);
		$this->assertEquals(-10, $team->points());
		
		$resp = $this->hint->apply("S2");
		$this->assertEquals(new Text("hint.text.solution", "S2", "KOBLIHA"), $resp);
		$this->assertEquals(-40, $team->points());
		
		$resp = $this->hint->apply("S2");
		$this->assertEquals(new Text("hint.apply.already", "S2"), $resp);
	}

	function testImunityStatus() {
		$this->assertEquals([false, new Text("imunity.insufficient")], $this->hint->imunityStatus(0));
		$this->assertEquals([true, new Text("imunity.available", 2)], $this->hint->imunityStatus(5));
	}

	function testImunity() {
		$this->assertEquals(new Text("imunity.insufficient"), $this->hint->applyImunity());
		$this->hint->addCCode(1);
		$this->hint->addCCode(2);
		$this->hint->addCCode(3);
		$this->assertEquals(new Text("imunity.success"), $this->hint->applyImunity());
		$this->assertEquals(1, $this->hint->unusedCCodeCount());
		$this->assertEquals(new Text("imunity.already"), $this->hint->applyImunity());
		$this->assertEquals([false, new Text("imunity.already")], $this->hint->imunityStatus(5));
	}

}
