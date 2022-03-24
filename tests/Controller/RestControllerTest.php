<?php
namespace Sova\Controller;

use Sova\TestBase;
use Sova\DB;
use Sova\Request;
use Sova\Response;
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
		$this->assertFalse(isset($_SESSION["game_id"]));
		
        $_SERVER["HTTP_AUTHORIZATION"] = "Basic ".base64_encode("game1:bad");
		list($status, $data) = $this->rest("GET", "game", []);
		$this->assertEquals(401, $status);
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

	function testFKViolation() {
		$_SESSION["team_id"] = 99;
		list($status, $data) = $this->rest("POST", "progress", ["point_id" => 99]);
		$this->assertEquals(422, $status);
		$this->assertEquals(1452, $data["code"]);
	}

	function testPKViolation() {
		$_SESSION["game_id"] = 1;
		list($status, $data) = $this->rest("POST", "loc", ["name" => "Černá hora", "code" => "HOUBA"]);
		$this->assertEquals(200, $status);
		list($status, $data) = $this->rest("POST", "cipher", ["name" => "S2", "code" => "HOUBA"]);
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
}
