<?php
namespace Sova\Model;

use Sova\GameTestBase;
use Sova\Repo\CipherRepo;
use Sova\Repo\ProgressRepo;

class CipherTest extends GameTestBase {

	protected $cipher;
	protected $cipherRepo;
	protected $progressRepo;

	function setUp(): void {
		parent::setUp();
		$this->cipher = new Cipher();
		$this->cipherRepo = new CipherRepo();
		$this->progressRepo = new ProgressRepo();
	}

	function tearDown(): void {
		$this->db->execute("DELETE FROM progress");
		parent::tearDown();
	}

	function testCheckPreviousLoc() {
		$t1 = $this->cipherRepo->get(14);
		$this->assertFalse($this->cipher->checkPreviousCiphersSolved($t1));
		$this->progressRepo->create(1, 3, 1);
		$this->assertTrue($this->cipher->checkPreviousCiphersSolved($t1));
	}

	function testCheckPreviousCiphers() {
		$t1 = $this->cipherRepo->get(14);
		$this->progressRepo->create(1, 12, 1);
		$this->assertFalse($this->cipher->checkPreviousCiphersSolved($t1));
		$this->progressRepo->create(1, 13, 1);
		$this->assertTrue($this->cipher->checkPreviousCiphersSolved($t1));
	}
}
