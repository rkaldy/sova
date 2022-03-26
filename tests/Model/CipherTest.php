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

		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (20, 1, 'Váza')");
		$this->db->execute("INSERT INTO loc (point_id, description, solved_cipher_count) VALUES (20, 'na vrcholu Sněžky', 2)");
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

	function testSolvedNextLocAlreadyVisited() {
		$this->progressRepo->create(1, 2);
		$cipher = $this->cipherRepo->get(11);
		$this->assertEquals([
			new Text("cipher.solved", "S1", 30) 
		], $this->cipher->solve($cipher, "ABERACE"));
	}
}
