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
		$this->assertFalse($this->loc->checkPreviousPointsVisited(2));
		$this->progressRepo->create(1, 11);
		$this->assertTrue($this->loc->checkPreviousPointsVisited(2));
	}
		
	function testCheckPreviousNoCipher() {
		$this->assertTrue($this->loc->checkPreviousPointsVisited(1));
	}
	
	function testCheckPreviousMultipleCiphers() {
		$this->assertFalse($this->loc->checkPreviousPointsVisited(7));
		$this->progressRepo->create(1, 15);
		$this->assertTrue($this->loc->checkPreviousPointsVisited(7));
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
		$this->assertEquals([new Text("loc.visited", "Start", 1, "Parta Nic", $this->dbNow())], $this->loc->visit($loc, "KYBL"));
		$messages = $this->db->aquery("SELECT direction, text FROM message WHERE team_id = 1 ORDER BY time");
	}

	function testNextLoc() {
		$this->progressRepo->create(1, 13);
		$loc = $this->locRepo->get(4);
		$this->assertEquals([
			new Text("loc.visited", "Turniket", 1, "Parta Nic", $this->dbNow()),
			new Text("loc.next", "3", "na Kolínské boudě")
		], $this->loc->visit($loc, "MEDVED")
		);
	}

	function testGetDescription() {
		$loc = ["description" => "na severním pólu"];
		$this->assertEquals("na severním pólu", Loc::getDescription($loc));
		$loc["coord_lat"] = "90";
	    $loc["coord_lon"] = "0";
		$this->assertEquals("na severním pólu, 90N 0E", Loc::getDescription($loc));
		$_SESSION["settings"]["linkMapyCz"] = "zimni";
		$this->assertEquals('na severním pólu, <a href="https://mapy.cz/zimni?x=0&y=90&z=15">90N 0E</a>', Loc::getDescription($loc));
	}
}
