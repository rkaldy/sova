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
	}

	function testCheckSomePreviousCipherSolved() {
		$cipher = $this->cipherRepo->get(14);
		$this->assertFalse($this->cipher->isReachable($cipher));
		$this->progressRepo->create(1, 13);
		$this->assertTrue($this->cipher->isReachable($cipher));
	}

	function testCheckSomePreviousCiphersSolvedMulti() {
		$cipher = $this->cipherRepo->get(13);
		$this->progressRepo->create(1, 11);
		$this->assertTrue($this->cipher->isReachable($cipher));
	}

	function testCheckPreviouslLocsVisited() {
		$_SESSION["settings"]["locVisitMandatory"] = 1;
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
			new Text("cipher.solved", "S1a", 1, "Parta Nic", $this->dbNow()), 
			new Text("loc.next", "Vrchol Bílé hory")
		], $this->cipher->solve($cipher, "ABERACE"));
	}

}
