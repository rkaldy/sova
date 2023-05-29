<?php
namespace Sova\CRUD;

use Sova\DBException;

class TeamCrudTest extends CrudTestBase {

	function setUp(): void {
		parent::setUp();
		$this->db->execute("INSERT INTO team (team_id, game_id, name) VALUES (1, 1, 'Parta Nic')");
		$this->db->execute("INSERT INTO code (team_id, game_id, code) VALUES (1, 1, 'PRAK')");
	}

	function testCreate() {
		$team = $this->create(["name" => "Redwool", "pswd" => "KURE", "members" => ["Rumcajs", "Manka" ,"Cipísek"], "accomodation" => 0, "paid" => 0, "tshirt" => 2, "additional" => ["a" => 1, "b" => 2]]);
		$this->assertEquals([
			["game_id" => 1, "team_id" => 1, "name" => "Parta Nic", "pswd" => "PRAK", "phone" => null, "email" => null, "members" => [], "accomodation" => 1, "paid" => 0, "tshirt" => 0, "remarks" => null, "additional" => null, "points" => 0, "fee" => 0],
			["game_id" => 1, "team_id" => $team["team_id"], "name" => "Redwool", "pswd" => "KURE", "phone" => null, "email" => null, "members" => ["Rumcajs", "Manka", "Cipísek"], "accomodation" => 0, "paid" => 0, "tshirt" => 2, "remarks" => null, "additional" => ["a" => 1, "b" => 2 ], "points" => 0, "fee" => 0]
		], $this->list());
	}

	function testCreateWithGameId() {
		unset($_SESSION["game_id"]);
		$team = $this->create(["name" => "Pípy z Lípy", "pswd" => "ZIDLE", "members" => [], "accomodation" => 0, "paid" => 0, "tshirt" => 0, "game_id" => 2]);
		$_SESSION["game_id"] = 2;
		$this->assertEquals([
			["game_id" => 2, "team_id" => $team["team_id"], "name" => "Pípy z Lípy", "pswd" => "ZIDLE", "phone" => null, "email" => null, "members" => [], "accomodation" => 0, "paid" => 0, "tshirt" => 0, "remarks" => null, "additional" => null, "points" => 0, "fee" => 0]
		], $this->list());
	}

	function testCreateGeneratePassword() {
		$team = $this->create(["name" => "Redwool", "members" => ["Rumcajs", "Manka" ,"Cipísek"], "accomodation" => 0, "paid" => 0, "tshirt" => 2, "additional" => ["a" => 1, "b" => 2], "points" => 0, "fee" => 0]);
		$this->assertGreaterThan(4, strlen($team["pswd"]));
	}

	function testcreateDuplicatePassword() {
		try {
			$team = $this->create(["name" => "Redwool", "pswd" => "prak", "members" => [], "accomodation" => 0, "paid" => 0, "tshirt" => 2]);
			$this->fail("Should throw DBException");
		} catch (DBException $ex) {
			$this->assertEquals(1062, $ex->getCode());
		}
		$this->assertEquals(array(
			["game_id" => 1, "team_id" => 1, "name" => "Parta Nic", "pswd" => "PRAK", "phone" => null, "email" => null, "members" => [], "accomodation" => 1, "paid" => 0, "tshirt" => 0, "remarks" => null, "additional" => null, "points" => 0, "fee" => 0]
		), $this->list());
	}

	function testUpdate() {
		$team = $this->list()[0];
		$team["name"] = "Pípy z Lípy";
		$team["pswd"] = null;
		$this->update($team);
		$this->assertEquals(array(
			["game_id" => 1, "team_id" => 1, "name" => "Pípy z Lípy", "pswd" => "PRAK", "phone" => null, "email" => null, "members" => [], "accomodation" => 1, "paid" => 0, "tshirt" => 0, "remarks" => null, "additional" => null, "points" => 0, "fee" => 0]
		), $this->list());
	}

	function testDelete() {
		$this->delete($this->list()[0]);
		$this->assertCount(0, $this->list());
	}
}
