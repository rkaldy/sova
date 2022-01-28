<?php
namespace Sova\CRUD;

use Sova\Repo\GameRepo;
use Sova\DBException;

class GameCrudTest extends CrudTestBase {

	protected $repo;

	function setUp(): void {
		parent::setUp();
		$this->repo = new GameRepo();
	}

	function testCreate() {
		$this->create(array("name" => "Lavina", "pswd" => "bigsecret"));
		$this->assertCount(3, $this->list());
		$game = $this->repo->get("Lavina");
		$this->assertStringStartsWith("$2y$", $game["pswd"]);
		$this->assertTrue(password_verify("bigsecret", $game["pswd"]));
	}

	function testCreateEmptyPassword() {
		try {
			$this->create(array("name" => "Lavina", "pswd" => ""));
			$this->fail("Should throw Exception");
		} catch (\Exception $ex) {
			$this->assertTrue(true);
		}
	}

	function testUpdate() {
		$game = $this->list()[0];
		$game["name"] = "Lavina";
		$game["pswd"] = "bigsecret";
		$this->update($game);
		$this->assertEquals([
			["game_id" => "2", "name" => "game2"],
			["game_id" => "1", "name" => "Lavina"]
		], $this->list());
		$game = $this->repo->get("Lavina");
		$this->assertStringStartsWith("$2y$", $game["pswd"]);
		$this->assertTrue(password_verify("bigsecret", $game["pswd"]));
	}

	function testUpdateEmptyPassword() {
		$game = $this->list()[0];
		$game["name"] = "Lavina";
		$game["pswd"] = "";
		$this->update($game);
		$game = $this->repo->get("Lavina");
		$this->assertTrue(password_verify("samara", $game["pswd"]));
	}

	function testDelete() {
		$this->db->execute("INSERT INTO game VALUES (3, 'Lavina', 'bigsecret')");
		$this->delete(array("game_id" => 3));
		$this->assertEquals([
			["game_id" => "1", "name" => "game1"],
			["game_id" => "2", "name" => "game2"]
		], $this->list());
	}
}
