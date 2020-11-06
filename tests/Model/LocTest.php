<?php
namespace Sova\Model;

use Sova\GameTestBase;
use Sova\Repo\LocRepo;
use Sova\Repo\ProgressRepo;

class LocTest extends GameTestBase {

	protected $loc;
	protected $locRepo;
	protected $progressRepo;

	function setUp(): void {
		parent::setUp();
		$this->loc = new Loc();
		$this->locRepo = new LocRepo();
		$this->progressRepo = new ProgressRepo();
	}

	function testCheckPreviousCipher() {
		$this->assertFalse($this->loc->checkPreviousCiphersSolved(2));
		$this->progressRepo->create(1, 11);
		$this->assertTrue($this->loc->checkPreviousCiphersSolved(2));
	}
		
	function testCheckPreviousNoCipher() {
		$this->assertTrue($this->loc->checkPreviousCiphersSolved(1));
	}
	
	function testCheckPreviousMultipleCiphers() {
		$this->assertFalse($this->loc->checkPreviousCiphersSolved(5));
		$this->progressRepo->create(1, 14);
		$this->assertTrue($this->loc->checkPreviousCiphersSolved(5));
	}

	
	function testVisitNotReachable() {
		$loc = $this->locRepo->get(2);
		$this->assertEquals(new Text("code.unknown", "KYBL"), $this->loc->visit($loc, "KYBL"));
	}

	function testVisitAlready() {
		$loc = $this->locRepo->get(1);
		$this->progressRepo->create(1, 1);
		$this->assertEquals(new Text("loc.already"), $this->loc->visit($loc, "KYBL"));
	}

	function testVisit() {
		$loc = $this->locRepo->get(1);
		$this->assertEquals(new Text("loc.visited", "Start", 1, "Parta Nic", $this->dbNow()), $this->loc->visit($loc, "KYBL"));
		$messages = $this->db->aquery("SELECT direction, text FROM message WHERE team_id = 1 ORDER BY time");
	}
}
