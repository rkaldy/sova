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
		$this->assertFalse($this->cipher->checkSomePreviousCipherSolved($cipher));
		$this->progressRepo->create(1, 13);
		$this->assertTrue($this->cipher->checkSomePreviousCipherSolved($cipher));
	}

	function testCheckSomePreviousCiphersSolvedMulti() {
		$cipher = $this->cipherRepo->get(13);
		$this->progressRepo->create(1, 11);
		$this->assertTrue($this->cipher->checkSomePreviousCipherSolved($cipher));
	}

	function testCheckAllPreviousCiphersSolved() {
		$cipher = $this->cipherRepo->get(14);
		$this->assertFalse($this->cipher->checkAllPreviousCiphersSolved($cipher));
		$this->progressRepo->create(1, 13);
		$this->assertTrue($this->cipher->checkAllPreviousCiphersSolved($cipher));
	}

	function testCheckAllPreviousCiphersSolvedMulti() {
		$cipher = $this->cipherRepo->get(13);
		$this->progressRepo->create(1, 11);
		$this->assertFalse($this->cipher->checkAllPreviousCiphersSolved($cipher));
		$this->progressRepo->create(1, 12);
		$this->assertTrue($this->cipher->checkAllPreviousCiphersSolved($cipher));
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
