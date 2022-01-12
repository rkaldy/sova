<?php
namespace Sova\CRUD;

use Sova\DBException;

class GameCrudTest extends CrudTestBase {

	function testCreate() {
		$newGame = $this->create(["name" => "game3", "owner_id" => "2"]);
		$this->assertEquals(array(
			["game_id" => 1, "owner_id" => 2, "name" => "game1"],
			["game_id" => 2, "owner_id" => 2, "name" => "game2"],
			["game_id" => $newGame["game_id"], "owner_id" => 2, "name" => "game3"]
		), $this->list());
	}

	function testCreateFKViolation() {
		try {
			$this->create(["name" => "game3", "owner_id" => "99"]);
			$this->fail("Should throw DBException");
		} catch (DBException $ex) {
			$this->assertEquals(1452, $ex->getCode());
		}
	}

	function testUpdate() {
		$game = $this->list()[0];
		$game["name"] = "lavina";
		$this->update($game);
		$this->assertEquals(array(
			["game_id" => 2, "owner_id" => 2, "name" => "game2"],
			["game_id" => 1, "owner_id" => 2, "name" => "lavina"]
		), $this->list());
	}

	function testDelete() {
		$game = $this->list()[0];
		$this->delete($game);
		$this->assertEquals(array(
			["game_id" => 2, "owner_id" => 2, "name" => "game2"]
		), $this->list());
	}
}
