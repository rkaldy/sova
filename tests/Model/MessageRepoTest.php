<?php
namespace Sova\Model;

use DateTime;
use DateTimeZone;
use Sova\TestBase;

class MessageRepoTest extends TestBase {

	protected $repo;

	function setUp(): void {
		parent::setUp();
		$this->db->execute("INSERT INTO team (team_id, game_id, name) VALUES (1, 1, 'Parta Nic')");
		$this->db->execute("INSERT INTO team (team_id, game_id, name) VALUES (2, 1, 'Redwool')");
		$this->repo = new MessageRepo();
		$_SESSION['team_id'] = 1;
	}

	function tearDown(): void {
		$this->db->execute("DELETE FROM message");
		$this->db->execute("DELETE FROM team");
		parent::tearDown();
	}

	static function stripTimes(array $messages) {
		foreach ($messages as &$msg) {
			unset($msg["time"]);
		}
		return $messages;
	}


	function testSendToSova() {
		$this->repo->sendToSova("Pomoc!");
		$msgs = $this->repo->list(0, 100);
		$this->assertEquals(array(
			array("name" => "Parta Nic", "direction" => MessageRepo::FROM_TEAM, "text" => "Pomoc!")
		), self::stripTimes($msgs));
		$now = new DateTime();
		$time = new DateTime($msgs[0]["time"], new DateTimeZone("Europe/Prague"));
		$this->assertLessThanOrEqual($now, $time);

	}

	function testSendToTeamAmended() {
		$this->repo->sendToTeam("Nápověda", null, 30);
		$msgs = $this->repo->list(0, 100);
		$this->assertEquals(array(
			array("name" => "Parta Nic", "direction" => MessageRepo::TO_TEAM, "text" => "Nápověda")
		), self::stripTimes($msgs));
		$now = new DateTime();
		$time = new DateTime($msgs[0]["time"], new DateTimeZone("Europe/Prague"));
		$this->assertGreaterThan($now, $time);
	}

	function testBroadcast() {
		$this->repo->broadcast(array(1, 2), "Konec hry");
		$this->assertEquals(array(
			array("name" => "Parta Nic", "direction" => MessageRepo::TO_TEAM, "text" => "Konec hry"),
			array("name" => "Redwool", "direction" => MessageRepo::TO_TEAM, "text" => "Konec hry")
		), self::stripTimes($this->repo->list(0, 100)));
	}
}
