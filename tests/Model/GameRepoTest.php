<?php
namespace Sova\Model;

use Sova\TestBase;
use Sova\DBException;

class GameRepoTest extends TestBase {

	protected $repo;

	function setUp(): void {
		parent::setUp();
		$this->repo = new GameRepo();
	}

	function testCreate() {
		$newGame = $this->repo->create(array("name" => "game3", "owner_id" => "2", "start_time" => "2020-03-01", "end_time" => "2020-03-02"));
		$this->assertEquals(array(
			array("game_id" => $newGame["game_id"], "owner_id" => 2, "name" => "game3", "start_time" => "2020-03-01 00:00:00", "end_time" => "2020-03-02 00:00:00"),
			array("game_id" => 2, "owner_id" => 2, "name" => "game2", "start_time" => "2020-02-01 00:00:00", "end_time" => "2020-02-02 00:00:00"),
			array("game_id" => 1, "owner_id" => 2, "name" => "game1", "start_time" => "2020-01-01 00:00:00", "end_time" => "2020-01-02 00:00:00")
		), $this->repo->list());
	}

	function testCreateFKViolation() {
		try {
			$this->repo->create(array("name" => "game3", "owner_id" => "99", "start_time" => "2020-03-01", "end_time" => "2020-03-02"));
			$this->fail("Should throw DBException");
		} catch (DBException $ex) {
			$this->assertEquals(1452, $ex->getCode());
		}
	}

	function testUpdate() {
		$game = $this->repo->list()[1];
		$game["name"] = "lavina";
		$this->repo->update($game);
		$this->assertEquals(array(
			array("game_id" => 2, "owner_id" => 2, "name" => "game2", "start_time" => "2020-02-01 00:00:00", "end_time" => "2020-02-02 00:00:00"),
			array("game_id" => 1, "owner_id" => 2, "name" => "lavina",  "start_time" => "2020-01-01 00:00:00", "end_time" => "2020-01-02 00:00:00")
		), $this->repo->list());
	}

	function testDelete() {
		$game = $this->repo->list()[0];
		$this->repo->delete($game);
		$this->assertEquals(array(
			array("game_id" => 1, "owner_id" => 2, "name" => "game1",  "start_time" => "2020-01-01 00:00:00", "end_time" => "2020-01-02 00:00:00")
		), $this->repo->list());
	}
}
