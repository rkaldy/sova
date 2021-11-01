<?php
namespace Sova\Controller;

use Sova\TestBase;
use Sova\DB;
use Sova\Request;
use Sova\Response;
use Mockery;

class RestControllerTest extends TestBase {

	function setUp(): void {
		parent::setUp();
		$_SESSION["user_id"] = 1;
	}

	function rest(string $method, string $path, array $in = [], array $params = []): array {
		$path = explode("/", $path);
		$req = new Request($method, "/", $params, $in);
		$resp = (new RestController())->process($req, $path);
		return [$resp->status, json_decode($resp->data, true)];
	}

	function testUnknownTable() {
		list($status, $data) = $this->rest("GET", "bad", []);
		$this->assertEquals(400, $status);
	}

	function testUnauthenticated() {
		unset($_SESSION["user_id"]);
		list($status, $data) = $this->rest("GET", "loc", []);
		$this->assertEquals(401, $status);
	}

	function testUnauthorized() {
		$_SESSION["user_id"] = 2;
		list($status, $data) = $this->rest("PUT", "game", []);
		$this->assertEquals(403, $status);
	}

	function testUnknownResource() {
		list($status, $data) = $this->rest("GET", "bad/bad");
		$this->assertEquals(400, $status);
		$this->assertEquals("Unknown resource: 'bad'", $data["error"]);
	}

	function testUnknownQuery() {
		list($status, $data) = $this->rest("GET", "game/bad");
		$this->assertEquals(400, $status);
		$this->assertEquals("Method game.bad not found", $data["error"]);
	}

	function testQuery() {
		$_SESSION["user_id"] = 2;
		list($status, $data) = $this->rest("GET", "game/idbyname", [], ["name" => "game2"]);
		$this->assertEquals(200, $status);
		$this->assertEquals(["game_id" => 2], $data);
	}

	function testGeneralError() {
		list($status, $data) = $this->rest("POST", "user", ["login" => "user", "pswd" => ""]);
		$this->assertEquals(422, $status);
		$this->assertFalse(isset($data["code"]));
	}

	function testGeneralDatabaseError() {
		list($status, $data) = $this->rest("POST", "game", ["name" => "game3", "owner_id" => 99, "start_time" => "2020-01-01", "end_time" => "bad"]);
		$this->assertEquals(422, $status);
	}

	function testBasicAuthentication() {
		unset($_SESSION["user_id"]);
		list($status, $data) = $this->rest("GET", "game", []);
		$this->assertEquals(401, $status);
		$this->assertFalse(isset($_SESSION["user_id"]));
		
		$_SERVER["PHP_AUTH_USER"] = "user";
		$_SERVER["PHP_AUTH_PW"] = "bad";
		list($status, $data) = $this->rest("GET", "game", []);
		$this->assertEquals(401, $status);
		$this->assertFalse(isset($_SESSION["user_id"]));

		$_SERVER["PHP_AUTH_USER"] = "user";
		$_SERVER["PHP_AUTH_PW"] = "swordfish";
		list($status, $data) = $this->rest("GET", "game", []);
		$this->assertEquals(200, $status);
		$this->assertEquals(2, $_SESSION["user_id"]);
		$this->assertEquals([
			["game_id" => 2, "name" => "game2", "owner_id" => 2, "start_time" => "2020-02-01 00:00:00", "end_time" => "2020-02-02 00:00:00"],
			["game_id" => 1, "name" => "game1", "owner_id" => 2, "start_time" => "2020-01-01 00:00:00", "end_time" => "2020-01-02 00:00:00"]
		], $data);
	}

	function testGET() {
		list($status, $data) = $this->rest("GET", "user");
		$this->assertEquals(200, $status);
		$this->assertEquals([
			["user_id" => 1, "login" => "admin"],
			["user_id" => 2, "login" => "user"]
		], $data);
	}

	function testPUT() {
		list($status, $data) = $this->rest("PUT", "user", ["user_id" => 2, "login" => "bigbrother"]);
		$this->assertEquals(200, $status);
		$this->assertEquals(["user_id" => 2, "login" => "bigbrother"], $data);
		list($status, $data) = $this->rest("GET", "user");
		$this->assertEquals([
			["user_id" => 1, "login" => "admin"],
			["user_id" => 2, "login" => "bigbrother"]
		], $data);
	}

	function testPOST() {
		list($status, $data) = $this->rest("POST", "user", ["login" => "bigbrother", "pswd" => "bigpass"]);
		$this->assertEquals(200, $status);
		$this->assertEquals("bigbrother", $data["login"]);
		$newUserId = $data["user_id"];
		list($status, $data) = $this->rest("GET", "user");
		$this->assertEquals([
			["user_id" => 1, "login" => "admin"],
			["user_id" => $newUserId, "login" => "bigbrother"],
			["user_id" => 2, "login" => "user"]
		], $data);
	}

	function testDELETE() {
		list($status, $data) = $this->rest("DELETE", "game", ["game_id" => 1, "owner_id" => 2, "name" => "game1"]);
		$this->assertEquals(200, $status);
		$this->assertEquals(["game_id" => 1, "owner_id" => 2, "name" => "game1"], $data);
		list($status, $data) = $this->rest("GET", "game");
		$this->assertEquals([
			["game_id" => 2, "owner_id" => 2, "name" => "game2", "start_time" => "2020-02-01 00:00:00", "end_time" => "2020-02-02 00:00:00"]
		], $data);
	}

	function testFKViolation() {
		list($status, $data) = $this->rest("POST", "game", ["name" => "game3", "owner_id" => 99, "start_time" => "2020-01-01", "end_time" => "2020-02-01"]);
		$this->assertEquals(422, $status);
		$this->assertEquals(1452, $data["code"]);
	}

	function testPKViolation() {
		$_SESSION["user_id"] = 2;
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

}
