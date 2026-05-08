<?php
namespace Sova\Controller;

use Sova\TestBase;
use Sova\DB;
use Sova\Request;
use Sova\Response;
use Sova\Model\Message;
use Sova\Model\Settings;
use Mockery;

class RestControllerTest extends TestBase {

	function setUp(): void {
		parent::setUp();
	}

	function rest(string $method, string $path, array $in = [], array $params = []): array {
		$path = explode("/", $path);
		$req = new Request($method, "/", $params, $in);
		$resp = (new RestController())->process($req, $path);
		return [$resp->status, json_decode($resp->data, true)];
	}

	function assertMessages(array $expected): void {
		$this->assertEquals($expected, $this->db->aquery("
			SELECT team_id, direction, text
			FROM message
			ORDER BY message_id
		"));
	}

	
	function testUnauthenticated() {
		unset($_SESSION["game_id"]);
		list($status, $data) = $this->rest("GET", "loc");
		$this->assertEquals(401, $status);
	}

	function testUnauthorized() {
		list($status, $data) = $this->rest("PUT", "game");
		$this->assertEquals(403, $status);
	}

	function testUnknownResource() {
		list($status, $data) = $this->rest("GET", "bad");
		$this->assertEquals(400, $status);
		$this->assertEquals("Unknown resource: 'bad'", $data["error"]);
	}

	function testQuery() {
		list($status, $data) = $this->rest("GET", "graph");
		$this->assertEquals(200, $status);
	}

	function testGeneralError() {
		$_SESSION["superuser"] = 1;
		list($status, $data) = $this->rest("POST", "game", ["name" => "lavina", "pswd" => ""]);
		$this->assertEquals(500, $status);
		$this->assertFalse(isset($data["code"]));
	}

	function testGeneralDatabaseError() {
		list($status, $data) = $this->rest("POST", "loc", ["name" => "Start", "order_id" => "NotANumber"]);
		$this->assertEquals(422, $status);
	}

	function testBasicAuthentication() {
		unset($_SESSION["game_id"]);
		list($status, $data) = $this->rest("GET", "game", []);
		$this->assertEquals(401, $status);
        $this->assertEquals("Unauthenticated", $data["error"]);
		$this->assertFalse(isset($_SESSION["game_id"]));
		
        $_SERVER["HTTP_AUTHORIZATION"] = "Basic ".base64_encode("game1:bad");
		list($status, $data) = $this->rest("GET", "game", []);
		$this->assertEquals(401, $status);
        $this->assertEquals("Authentication failed", $data["error"]);
		$this->assertFalse(isset($_SESSION["game_id"]));

        $_SERVER["HTTP_AUTHORIZATION"] = "Basic ".base64_encode("game1:samara");
		list($status, $data) = $this->rest("GET", "game", []);
		$this->assertEquals(200, $status);
		$this->assertEquals(1, $_SESSION["game_id"]);
		$this->assertEquals([
			["game_id" => 1, "name" => "game1"],
			["game_id" => 2, "name" => "game2"]
		], $data);
	}

	function testGET() {
		list($status, $data) = $this->rest("GET", "game");
		$this->assertEquals(200, $status);
		$this->assertEquals([
			["game_id" => 1, "name" => "game1"],
			["game_id" => 2, "name" => "game2"]
		], $data);
	}

	function testPUT() {
		$_SESSION["superuser"] = 1;
		list($status, $data) = $this->rest("PUT", "game", ["game_id" => 2, "name" => "lavina"]);
		$this->assertEquals(200, $status);
		$this->assertEquals(["game_id" => 2, "name" => "lavina"], $data);
		list($status, $data) = $this->rest("GET", "game");
		$this->assertEquals([
			["game_id" => 1, "name" => "game1"],
			["game_id" => 2, "name" => "lavina"]
		], $data);
	}

	function testPOST() {
		$_SESSION["superuser"] = 1;
		list($status, $data) = $this->rest("POST", "game", ["name" => "lavina", "pswd" => "secret"]);
		$this->assertEquals(200, $status);
		$this->assertEquals("lavina", $data["name"]);
		$newGameId = $data["game_id"];
		list($status, $data) = $this->rest("GET", "game");
		$this->assertEquals([
			["game_id" => 1, "name" => "game1"],
			["game_id" => 2, "name" => "game2"],
			["game_id" => $newGameId, "name" => "lavina"]
		], $data);
	}

	function testDELETE() {
		$_SESSION["superuser"] = 1;
		list($status, $data) = $this->rest("DELETE", "game", ["game_id" => 1, "name" => "game1"]);
		$this->assertEquals(200, $status);
		$this->assertEquals(["game_id" => 1, "name" => "game1"], $data);
		list($status, $data) = $this->rest("GET", "game");
		$this->assertEquals([
			["game_id" => 2, "name" => "game2"]
		], $data);
	}

	function testPKViolation() {
		$_SESSION["game_id"] = 1;
		list($status, $data) = $this->rest("POST", "loc", ["name" => "Černá hora", "code" => "HOUBA"]);
		$this->assertEquals(200, $status);
		list($status, $data) = $this->rest("POST", "cipher", ["name" => "S2", "code" => "HOUBA", "activity" => false, "points_by_rank" => 0, "all_locs_mandatory" => false]);
		$this->assertEquals(422, $status);
		$this->assertEquals(1062, $data["code"]);
		
		$_SESSION["game_id"] = 2;
		list($status, $data) = $this->rest("POST", "loc", ["name" => "S2", "code" => "HOUBA"]);
		$this->assertEquals(200, $status);
	}

	function testJSONFields() {
		Settings::set("gamePrice", 1000);
		Settings::set("accomodationPrice", 100);
		Settings::set("tshirtPrice", 10);

        list($status, $data) = $this->rest("POST", "team", ["name" => "Parta Nic", "phone" => "123", "email" => "partanic@post.cz", "members" => ["Rumcajs", "Manka", "Cipísek"], "accomodation" => true, "paid" => false, "tshirt" => 2]);
        $this->assertEquals(200, $status);
        list($status, $data) = $this->rest("GET", "team");
        $this->assertEquals(200, $status);
        $this->assertEquals(1, count($data));
        $team = $data[0];
        $this->assertNotNull($team["team_id"]);
        $this->assertMatchesRegularExpression("/[A-Z]{4,9}/", $team["pswd"]);
        $this->assertTrue($team["accomodation"] === true);
        $this->assertTrue($team["paid"] === false);
		$this->assertEquals(["Rumcajs", "Manka", "Cipísek"], $team["members"]);
		$this->assertEquals(1320, $team["fee"]);
    }


    function setupHintFixture() {
		$this->db->execute("INSERT INTO team (team_id, game_id, name) VALUE (1, 1, 'Parta Nic')");
		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (101, 1, 'Start')");
		$this->db->execute("INSERT INTO loc (point_id, description) VALUES (101, '')");
		$this->db->execute("INSERT INTO point (point_id, game_id, name) VALUES (111, 1, 'S1')");
		$this->db->execute("INSERT INTO cipher (point_id, name_int, hint, howto) VALUES (111, 'Morseovka', 'Čárka tečka čárka, tak začíná Klárka', 'Použij morseovku')");
		$this->db->execute("INSERT INTO code (game_id, point_id, code) VALUES (1, 111, 'ABERACE')");
		$this->db->execute("INSERT INTO step (from_point_id, to_point_id) VALUES (101, 111)");
		$this->db->execute("INSERT INTO progress (team_id, point_id) VALUES (1, 101)");
		$_SESSION["team_id"] = 1;
		$_SESSION["team_name"] = "Parta Nic";
		Settings::set("hintPoints", 10);
	}

	function setupCodeFixture() {
		$this->db->execute("INSERT INTO team (team_id, game_id, name) VALUE (1, 1, 'Parta Nic')");
		$this->db->execute("INSERT INTO point (point_id, game_id, name, points) VALUES (101, 1, 'Start', 15)");
		$this->db->execute("INSERT INTO loc (point_id, description) VALUES (101, '')");
		$this->db->execute("INSERT INTO code (game_id, point_id, code) VALUES (1, 101, 'PRALINKA')");
		$_SESSION["team_id"] = 1;
		$_SESSION["team_name"] = "Parta Nic";
	}

	function testCode() {
		$this->setupCodeFixture();

		list($status, $data) = $this->rest("GET", "code", [], ["code" => "pralinka"]);

		$this->assertEquals(200, $status);
		$this->assertEquals(["Vítejte na stanovišti Start. Máte 15 bodů."], $data["response"]);
		$this->assertEquals(15, $this->db->equery("SELECT points FROM team WHERE team_id = 1"));
		$this->assertMessages([
			["team_id" => 1, "direction" => Message::FROM_TEAM, "text" => "PRALINKA"],
			["team_id" => 1, "direction" => Message::TO_TEAM, "text" => "Vítejte na stanovišti Start. Máte 15 bodů."],
		]);
	}

	function testCodeAfterGameEnd() {
		$this->setupCodeFixture();
		Settings::set("gameEndTimestamp", time() - 1);

		list($status, $data) = $this->rest("GET", "code", [], ["code" => "pralinka"]);

		$this->assertEquals(403, $status);
		$this->assertEquals("Hra již skončila", $data["error"]);
		$this->assertMessages([]);
	}

	function testHintCheck() {
		$this->setupHintFixture();

		list($status, $data) = $this->rest("GET", "hint_check", [], ["cipher" => "S1"]);

		$this->assertEquals(200, $status);
		$this->assertTrue($data["success"]);
		$this->assertEquals(["Pro šifru S1 jste ještě žádnou nápovědu nedostali.", "Nyní můžete zažádat o nápovědu za 10 bodů."], $data["response"]);
		$this->assertMessages([
			["team_id" => 1, "direction" => Message::FROM_TEAM, "text" => "(Kontrola nápovědy na S1)"],
			["team_id" => 1, "direction" => Message::TO_TEAM, "text" => "Pro šifru S1 jste ještě žádnou nápovědu nedostali."],
			["team_id" => 1, "direction" => Message::TO_TEAM, "text" => "Nyní můžete zažádat o nápovědu za 10 bodů."],
		]);
	}

	function testHintApply() {
		$this->setupHintFixture();

		list($status, $data) = $this->rest("GET", "hint_apply", [], ["cipher" => "S1"]);

		$this->assertEquals(200, $status);
		$this->assertEquals(["Nápověda k šifře S1: Čárka tečka čárka, tak začíná Klárka"], $data["response"]);
		$this->assertEquals(-10, $this->db->equery("SELECT points FROM team WHERE team_id = 1"));
		$this->assertMessages([
			["team_id" => 1, "direction" => Message::FROM_TEAM, "text" => "(Žádost o nápovědu na S1)"],
			["team_id" => 1, "direction" => Message::TO_TEAM, "text" => "Nápověda k šifře S1: Čárka tečka čárka, tak začíná Klárka"],
		]);
	}
}
