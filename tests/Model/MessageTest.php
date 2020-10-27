<?php
namespace Sova\Model;

use DateTime;
use DateTimeZone;
use Sova\TestBase;
use Sova\Controller\RestController;

class MessageTest extends TestBase {

	protected $message;

	function setUp(): void {
		parent::setUp();
		$this->db->execute("INSERT INTO team (team_id, game_id, name) VALUES (1, 1, 'Parta Nic')");
		$this->db->execute("INSERT INTO team (team_id, game_id, name) VALUES (2, 1, 'Redwool')");
		$this->message = new Message();
		$_SESSION['team_id'] = 1;
	}

	function tearDown(): void {
		$this->db->execute("DELETE FROM message");
		$this->db->execute("DELETE FROM team");
		parent::tearDown();
	}

	static function stripTimes($messages) {
		foreach ($messages as &$msg) {
			unset($msg["time"]);
		}
		return $messages;
	}


	function testSendToSova() {
		$this->message->sendToSova("Pomoc!");
		$messages = $this->message->list(1, 100);
		$this->assertEquals(array(
			array("name" => "Parta Nic →", "direction" => Message::FROM_TEAM, "text" => "Pomoc!")
		), self::stripTimes($messages));
		$now = new DateTime();
		$time = new DateTime($messages[0]["time"], new DateTimeZone("Europe/Prague"));
		$this->assertLessThanOrEqual($now, $time);
	}

	function testSendToTeamAmended() {
		$this->message->sendToTeam("Nápověda", null, 30);
		$this->assertEquals(array(), $this->message->list(1, 100));
	}

	function testBroadcast() {
		$this->message->broadcast(array(1, 2), "Konec hry");
		$this->assertEquals(array(
			array("name" => "Parta Nic ←", "direction" => Message::TO_TEAM, "text" => "Konec hry"),
			array("name" => "Redwool ←", "direction" => Message::TO_TEAM, "text" => "Konec hry")
		), self::stripTimes($this->message->list(1, 100)));
	}

	function testListForTeam() {
		$this->message->broadcast(array(1, 2), "Konec hry");
		$this->assertEquals(array(
			array("direction" => Message::TO_TEAM, "text" => "Konec hry")
		), self::stripTimes($this->message->listForTeam()));
	}

	function testREST() {
		$this->message->sendToSova("Ahoj");
		$this->message->sendToTeam("Nazdar");
		$resp = (new RestController())->messages(array("page" => 1, "pageSize" => 20));
		$this->assertEquals(2, $resp["itemsCount"]);
		$this->assertEquals(array(
			array("name" => "Parta Nic ←", "direction" => Message::TO_TEAM, "text" => "Nazdar"),
			array("name" => "Parta Nic →", "direction" => Message::FROM_TEAM, "text" => "Ahoj")
		), self::stripTimes($resp["data"]));
	}
}
