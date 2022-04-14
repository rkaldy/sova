<?php
namespace Sova\Model;

use DateTime;
use DateTimeZone;
use Sova\TestBase;
use Sova\Controller\RestHandler;

class MessageTest extends TestBase {

	protected $message;

	function setUp(): void {
		parent::setUp();
		$this->db->execute("INSERT INTO team (team_id, game_id, name) VALUES (1, 1, 'Parta Nic')");
		$this->db->execute("INSERT INTO team (team_id, game_id, name) VALUES (2, 1, 'Redwool')");
		$this->message = new Message();
		$_SESSION['team_id'] = 1;
	}

	static function stripTimes($messages) {
		foreach ($messages as &$msg) {
			unset($msg["time"]);
		}
		return $messages;
	}


	function testSendToSova() {
		$this->message->sendToSova("Pomoc!");
		$messages = $this->message->list();
		$this->assertEquals([
			["name" => "Parta Nic", "direction" => Message::FROM_TEAM, "direction_str" => "in", "text" => "Pomoc!"]
		], self::stripTimes($messages));
		$now = new DateTime();
		$time = new DateTime($messages[0]["time"], new DateTimeZone("Europe/Prague"));
		$this->assertLessThanOrEqual($now, $time);
	}

	function testBroadcast() {
		$this->message->broadcast([1, 2], "Konec hry");
		$messages = $this->message->list();
		$this->assertEquals([
			["name" => "Parta Nic", "direction" => Message::TO_TEAM, "direction_str" => "out", "text" => "Konec hry"],
			["name" => "Redwool", "direction" => Message::TO_TEAM, "direction_str" => "out", "text" => "Konec hry"]
		], self::stripTimes($messages));
	}

	function testListForTeam() {
		$this->message->broadcast([1, 2], "Konec hry");
		list($messages, $count) = $this->message->listForTeam();
		$this->assertEquals(1, $count);
		$this->assertEquals([
			["direction" => Message::TO_TEAM, "direction_str" => "out", "text" => "Konec hry"]
		], self::stripTimes($messages));
	}

	function testRecent() {
		$this->assertEquals([], $this->message->recent("2000-01-01 00:00:00"));
		$this->message->broadcast([1, 2], "Ahoj");
		$this->assertEquals(["Ahoj"], $this->message->recent("2000-01-01 00:00:00"));
	}

	function testREST() {
		$this->message->sendToSova("Ahoj");
		$this->message->sendToTeam("Nazdar");
		$resp = (new RestHandler())->crud("message", "GET", [], ["page" => 1, "pageSize" => 20]);
		$this->assertEquals(2, $resp["itemsCount"]);
		$this->assertEquals([
			["name" => "Parta Nic", "direction" => "2", "direction_str" => "out", "text" => "Nazdar"],
			["name" => "Parta Nic", "direction" => "1", "direction_str" => "in", "text" => "Ahoj"]
		], self::stripTimes($resp["data"]));
	}
}
