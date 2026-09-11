<?php
namespace Sova\Model;

use Sova\GameTestBase;
use Sova\Repo\CipherRepo;
use Sova\Repo\ProgressRepo;

class CipherTest extends GameTestBase {

	protected $cipher;
	protected $cipherRepo;

	function setUp(): void {
		parent::setUp();
		$this->cipher = new Cipher();
		$this->cipherRepo = new CipherRepo();

		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (20, 1, 'S1')");
		$this->db->execute("INSERT INTO loc (point_id, description, solved_cipher_count) VALUES (20, 'na vrcholu Sněžky', 2)");
		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (21, 1, 'Váza')");
		$this->db->execute("INSERT INTO loc (point_id, description) VALUES (21, 'na uvedených souřadnicích')");
        Settings::set("locFinish", 21);
        Settings::set("finishPointThreshold", 40);
	}

	function testCheckSomePreviousCipherSolved() {
		$cipher = $this->cipherRepo->get(15);
		$this->assertFalse($this->cipher->isReachable($cipher));
		$this->progressRepo->create(1, 14);
		$this->assertTrue($this->cipher->isReachable($cipher));
	}

	function testCheckSomePreviousCiphersSolvedMulti() {
		$cipher = $this->cipherRepo->get(13);
		$this->progressRepo->create(1, 11);
		$this->assertTrue($this->cipher->isReachable($cipher));
	}

	function testCheckPreviouslLocsVisited() {
		Settings::set("locVisitMandatory", 1);
		$cipher = $this->cipherRepo->get(13);
		$this->assertFalse($this->cipher->isReachable($cipher));
		$this->progressRepo->create(1, 2);
		$this->assertTrue($this->cipher->isReachable($cipher));
		$this->progressRepo->create(1, 3);
		$this->assertTrue($this->cipher->isReachable($cipher));
		
	}

	function testCheckPreviouslLocsVisitedAllLocsMandatory() {
		Settings::set("locVisitMandatory", 1);
		$cipher = $this->cipherRepo->get(13);
		$cipher["all_locs_mandatory"] = true;
		$this->assertFalse($this->cipher->isReachable($cipher));
		$this->progressRepo->create(1, 2);
		$this->assertFalse($this->cipher->isReachable($cipher));
		$this->progressRepo->create(1, 3);
		$this->assertTrue($this->cipher->isReachable($cipher));
		
	}

	function testSolveNotReachable() {
		$cipher = $this->cipherRepo->get(13);
		$this->assertEquals(new Text("code.unknown", "KOBLIHA"), $this->cipher->solve($cipher, "KOBLIHA"));
	}

	function testSolveAlready() {
		$this->progressRepo->create(1, 11);
		$cipher = $this->cipherRepo->get(11);
		$this->assertEquals(new Text("cipher.already"), $this->cipher->solve($cipher, "KOBLIHA"));
	}
	
	function testSolve() {
		$cipher = $this->cipherRepo->get(11);
		$this->assertEquals([
			new Text("cipher.solved", "S1", 30), 
			new Text("loc.next", "1a", "na vrcholu Bílé hory")
		], $this->cipher->solve($cipher, "ABERACE"));
		$this->assertEquals(30, $this->db->equery("SELECT points FROM progress WHERE team_id = 1 AND point_id = 11"));
		$this->assertEquals(0, $this->db->equery("SELECT points FROM progress WHERE team_id = 1 AND point_id = 1"));
	}

	function testSolveRank() {
		Settings::set("showRank", true);
		$cipher = $this->cipherRepo->get(11);
		$this->assertEquals([
			new Text("cipher.solved", "S1", 30), 
			new Text("cipher.rank", 1, "Parta Nic", $this->dbNow()), 
			new Text("loc.next", "1a", "na vrcholu Bílé hory")
		], $this->cipher->solve($cipher, "ABERACE"));
	}

	function testSolveActivityRank() {
		Settings::set("showRank", true);
		$cipher = $this->cipherRepo->get(12);
		$this->assertEquals([
			new Text("activity.solved", "A1", 20), 
			new Text("activity.rank", 1, "Parta Nic", $this->dbNow()), 
			new Text("loc.next", "1b", "na vrcholu Černé hory")
		], $this->cipher->solve($cipher, "ABERACE"));
	}

	function testSolveForce() {
		$this->db->execute("UPDATE point SET points_by_rank = 5 WHERE point_id = 11");

		$this->assertEquals($this->cipher->solveForce(1, 11), new Text("cipher.solved", "S1", 30));
		$this->assertTrue($this->progressRepo->isDone(1, 11));
		$this->assertEquals(30, (new Team())->points(1));
		$this->assertEquals(30, $this->db->equery("SELECT points FROM progress WHERE team_id = 1 AND point_id = 11"));
		$this->assertEquals(0, (new Team())->points(2));
		
        $this->assertEquals($this->cipher->solveForce(2, 11), new Text("cipher.solved", "S1", 25));
		$this->assertTrue($this->progressRepo->isDone(2, 11));
		$this->assertEquals(30, (new Team())->points(1));
		$this->assertEquals(25, (new Team())->points(2));
		$this->assertEquals(25, $this->db->equery("SELECT points FROM progress WHERE team_id = 2 AND point_id = 11"));
		$this->assertEquals(
			"Úspěšně jste vyluštili šifru S1. Máte 30 bodů.",
			$this->db->equery("SELECT text FROM message WHERE team_id = 1 AND direction = ?", Message::TO_TEAM)
		);
		$this->assertEquals(
			"Úspěšně jste vyluštili šifru S1. Máte 25 bodů.",
			$this->db->equery("SELECT text FROM message WHERE team_id = 2 AND direction = ?", Message::TO_TEAM)
		);
	}

	function testSolvedNextLoc() {
		$this->progressRepo->create(1, 2);
		$cipher = $this->cipherRepo->get(11);
		$this->assertEquals([
			new Text("cipher.solved", "S1", 30),
			new Text("loc.next", "1a", "na vrcholu Bílé hory") 
		], $this->cipher->solve($cipher, "ABERACE"));
	}

	function testPointsByRank() {
		$cipher = $this->cipherRepo->get(11);
		$cipher["points_by_rank"] = 5;
		$this->assertEquals([
			new Text("cipher.solved", "S1", 30), 
			new Text("loc.next", "1a", "na vrcholu Bílé hory")
		], $this->cipher->solve($cipher, "ABERACE"));
		$_SESSION["team_id"] = 2;
		$this->assertEquals([
			new Text("cipher.solved", "S1", 25), 
			new Text("loc.next", "1a", "na vrcholu Bílé hory")
		], $this->cipher->solve($cipher, "ABERACE"));
		$this->assertEquals(30, $this->db->equery("SELECT points FROM progress WHERE team_id = 1 AND point_id = 11"));
		$this->assertEquals(25, $this->db->equery("SELECT points FROM progress WHERE team_id = 2 AND point_id = 11"));
	}

    function testFinishPointThreshold() {
		$cipher = $this->cipherRepo->get(11);
        $team = new Team();
        $team->addPoints(10);
		$this->assertEquals([
			new Text("cipher.solved", "S1", 40), 
			new Text("loc.next", "1a", "na vrcholu Bílé hory"),
			new Text("loc.finish", "na uvedených souřadnicích")
		], $this->cipher->solve($cipher, "ABERACE"));
    }
}
