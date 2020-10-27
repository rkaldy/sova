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

	function tearDown(): void {
		$this->db->execute("DELETE FROM progress");
		parent::tearDown();
	}

	function testCheckPreviousCipher() {
		$cipher = $this->cipherRepo->get(14);
		$this->assertFalse($this->cipher->checkPreviousCiphersSolved($cipher));
		$this->progressRepo->create(1, 13, 1);
		$this->assertTrue($this->cipher->checkPreviousCiphersSolved($cipher));
	}

	function testCheckPreviousMultipleCiphers() {
		$cipher = $this->cipherRepo->get(13);
		$this->progressRepo->create(1, 11, 1);
		$this->assertFalse($this->cipher->checkPreviousCiphersSolved($cipher));
		$this->progressRepo->create(1, 12, 1);
		$this->assertTrue($this->cipher->checkPreviousCiphersSolved($cipher));
	}
}
