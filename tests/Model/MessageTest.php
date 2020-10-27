<?php
namespace Sova\Model;

use DateTime;
use DateTimeZone;
use Sova\TestBase;
use Sova\Repo\MessageRepo;
use Sova\Controller\RestController;

class MessageTest extends TestBase {

	protected $message;
	protected $repo;

	function setUp(): void {
		parent::setUp();
		$this->db->execute("INSERT INTO team (team_id, game_id, name) VALUES (1, 1, 'Parta Nic')");
		$this->db->execute("INSERT INTO team (team_id, game_id, name) VALUES (2, 1, 'Redwool')");
		$this->message = new Message();
		$this->repo = new MessageRepo();
		$_SESSION['team_id'] = 1;
	}

	function tearDown(): void {
		$this->db->execute("DELETE FROM message");
		$this->db->execute("DELETE FROM team");
		parent::tearDown();
	}

	function list() {
		return $this->repo->list(Game::current(), 0, 100);
	}

	static function stripTimes($messages) {
		foreach ($messages as &$msg) {
			unset($msg["time"]);
		}
		return $messages;
	}


	function testSendToSova() {
		$this->message->sendToSova("Pomoc!");
		$messages = $this->list();
		$this->assertEquals(array(
			array("name" => "Parta Nic", "direction" => MessageRepo::FROM_TEAM, "text" => "Pomoc!")
		), self::stripTimes($messages));
		$now = new DateTime();
		$time = new DateTime($messages[0]["time"], new DateTimeZone("Europe/Prague"));
		$this->assertLessThanOrEqual($now, $time);

	}

	function testSendToTeamAmended() {
		$this->message->sendToTeam("Nápověda", null, 30);
		$this->assertEquals(array(), $this->list());
	}

	function testBroadcast() {
		$this->repo->broadcast(array(1, 2), "Konec hry");
		$this->assertEquals(array(
			array("name" => "Parta Nic", "direction" => MessageRepo::TO_TEAM, "text" => "Konec hry"),
			array("name" => "Redwool", "direction" => MessageRepo::TO_TEAM, "text" => "Konec hry")
		), self::stripTimes($this->list()));
	}

	function testREST() {
		$this->message->sendToSova("Ahoj");
		$this->message->sendToTeam("Nazdar");
		$resp = (new RestController())->messages(array("page" => 1, "pageSize" => 20));
		$this->assertEquals(2, $resp["itemsCount"]);
		$this->assertEquals(array(
			array("name" => "Parta Nic ←", "direction" => MessageRepo::TO_TEAM, "text" => "Nazdar"),
			array("name" => "Parta Nic →", "direction" => MessageRepo::FROM_TEAM, "text" => "Ahoj")
		), self::stripTimes($resp["data"]));
	}
}
