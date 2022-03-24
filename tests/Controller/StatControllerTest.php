<?php
namespace Sova\Controller;

use Sova\Request;
use Sova\GameTestBase;
use Sova\Model\Progress;
use Sova\Model\Settings;
use Sova\Controller\StatController;


class StatControllerTest extends GameTestBase {

	protected $stat;

	function setUp(): void {
		parent::setUp();
		$this->db->execute("INSERT INTO team (team_id, game_id, name) VALUES (3, 1, 'abpopa')");
		$this->stat = new StatController();
        $_SESSION["user_id"] = 1;
        Settings::set("locVisitMandatory", 1);
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
		CodeController::process($code);
        return;
	}

    private function getStats(string $type) {
        $req = new Request("GET", null, ["type" => $type], []);
        $resp = $this->stat->process($req, []);
        $this->assertEquals(200, $resp->status);
        return $resp->data;
    }

	function testCiphers() {
		$this->sendCode(1, "Start", false);
		$this->sendCode(2, "Start", false);
		$this->sendCode(3, "Start", false);
		Progress::addFakeTime(10);
		$this->sendCode(1, "S1a", true);
		Progress::addFakeTime(5);
		$this->sendCode(2, "S1b", true);
        Progress::addFakeTime(1);
        $this->sendCode(1, "S1b", true);
        Progress::addFakeTime(1);
        $this->sendCode(3, "S1b", true);
        Progress::addFakeTime(2);
        $this->sendCode(1, "1a", false);
        Progress::addFakeTime(10);
        $this->sendCode(1, "1b", false);
        Progress::addFakeTime(4);
        $this->sendCode(1, "S2", true);

        $this->assertMatchesRegularExpression(
            "/Šifra,Tým,Doba luštění\nS1a,Parta Nic,00:10:0[01]\nS1b,Redwool,00:15:0[01]\nS1b,Parta Nic,00:16:0[01]\nS1b,abpopa,00:17:0[01]\nS2,Parta Nic,00:04:0[01]\n/",
            $this->getStats("ciphers")
        );
	}
}
