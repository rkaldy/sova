<?php
namespace Sova\Controller;

use Sova\GameTestBase;
use Sova\Model\Progress;
use Sova\Model\Statistics;

class StatControllerTest extends GameTestBase {

	protected $stat;

	function setUp(): void {
		parent::setUp();
		$this->db->execute("INSERT INTO team (team_id, game_id, name) VALUES (3, 1, 'abpopa')");
		$this->stat = new Statistics();
	}

	function tearDown(): void {
		parent::tearDown();
	}

	private function sendCode(int $teamId, string $point, bool $cipher) {
		if ($cipher) {
			$entity = "cipher";
		} else {
			$entity = "loc";
		}
		$code = $this->db->equery("
			SELECT code FROM code
			NATURAL JOIN point
			NATURAL JOIN $entity
			WHERE point.name = ?
		", $point);
		if ($code == NULL) {
			throw new \Exception("Code for $entity $point not found");
		}
		$_SESSION["team_id"] = $teamId;
		return CodeController::process($code);
	}

	function testCiphers() {
		$this->sendCode(1, "Start", false);
		$this->sendCode(2, "Start", false);
		$this->sendCode(3, "Start", false);
		Progress::addFakeTime(10);
		$this->sendCode(1, "S1a", true);
		Progress::addFakeTime(5);
		$this->sendCode(2, "S1b", true);
		print_r($this->stat->ciphers());
		die();
	}
}
