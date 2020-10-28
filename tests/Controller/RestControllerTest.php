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

	function tearDown(): void {
		$this->db->execute("DELETE FROM point");
		parent::tearDown();
	}

	function rest(string $method, string $res, array $in = array()): array {
		$req = new Request($method, [$res], [], $in);
		$resp = (new RestController())->process($req);
		return array($resp->status, json_decode($resp->data, true));
	}


	function testUnknownTable() {
		list($status, $data) = $this->rest("GET", "bad", array());
		$this->assertEquals(400, $status);
	}

	function testUnauthenticated() {
		unset($_SESSION["user_id"]);
		list($status, $data) = $this->rest("GET", "loc", array());
		$this->assertEquals(401, $status);
	}

	function testUnauthorized() {
		$_SESSION["user_id"] = 2;
		list($status, $data) = $this->rest("PUT", "game", array());
		$this->assertEquals(403, $status);
	}

	function testInvalidMethod() {
		list($status, $data) = $this->rest("POST", "graph", array());
		$this->assertEquals(405, $status);
	}

	function testGeneralError() {
		list($status, $data) = $this->rest("POST", "user", array("login" => "user", "pswd" => ""));
		$this->assertEquals(422, $status);
		$this->assertFalse(isset($data["code"]));
	}

	function testGeneralDatabaseError() {
		list($status, $data) = $this->rest("POST", "game", array("name" => "game3", "owner_id" => 99, "start_time" => "2020-01-01", "end_time" => "bad"));
		$this->assertEquals(422, $status);
	}

	function testGET() {
		list($status, $data) = $this->rest("GET", "user");
		$this->assertEquals(200, $status);
		$this->assertEquals(array(
			array("user_id" => 1, "login" => "admin"),
			array("user_id" => 2, "login" => "user")
		), $data);
	}

	function testPUT() {
		list($status, $data) = $this->rest("PUT", "user", array("user_id" => 2, "login" => "bigbrother"));
		$this->assertEquals(200, $status);
		$this->assertEquals(array("user_id" => 2, "login" => "bigbrother"), $data);
		list($status, $data) = $this->rest("GET", "user");
		$this->assertEquals(array(
			array("user_id" => 1, "login" => "admin"),
			array("user_id" => 2, "login" => "bigbrother")
		), $data);
	}

	function testPOST() {
		list($status, $data) = $this->rest("POST", "user", array("login" => "bigbrother", "pswd" => "bigpass"));
		$this->assertEquals(200, $status);
		$this->assertEquals("bigbrother", $data["login"]);
		$newUserId = $data["user_id"];
		list($status, $data) = $this->rest("GET", "user");
		$this->assertEquals(array(
			array("user_id" => 1, "login" => "admin"),
			array("user_id" => $newUserId, "login" => "bigbrother"),
			array("user_id" => 2, "login" => "user")
		), $data);
	}

	function testDELETE() {
		list($status, $data) = $this->rest("DELETE", "game", array("game_id" => 1, "owner_id" => 2, "name" => "game1"));
		$this->assertEquals(200, $status);
		$this->assertEquals(array("game_id" => 1, "owner_id" => 2, "name" => "game1"), $data);
		list($status, $data) = $this->rest("GET", "game");
		$this->assertEquals(array(
			array("game_id" => 2, "owner_id" => 2, "name" => "game2", "start_time" => "2020-02-01 00:00:00", "end_time" => "2020-02-02 00:00:00")
		), $data);
	}

	function testFKViolation() {
		list($status, $data) = $this->rest("POST", "game", array("name" => "game3", "owner_id" => 99, "start_time" => "2020-01-01", "end_time" => "2020-02-01"));
		$this->assertEquals(422, $status);
		$this->assertEquals(1452, $data["code"]);
	}

	function testPKViolation() {
		$_SESSION["user_id"] = 2;
		$_SESSION["game_id"] = 1;
		list($status, $data) = $this->rest("POST", "loc", array("name" => "Černá hora", "code" => "HOUBA"));
		$this->assertEquals(200, $status);
		list($status, $data) = $this->rest("POST", "cipher", array("name" => "S2", "code" => "HOUBA"));
		$this->assertEquals(422, $status);
		$this->assertEquals(1062, $data["code"]);
		
		$_SESSION["game_id"] = 2;
		list($status, $data) = $this->rest("POST", "loc", array("name" => "S2", "code" => "HOUBA"));
		$this->assertEquals(200, $status);
	}

}
