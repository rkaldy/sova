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
		$team = $this->create(array("name" => "Redwool", "pswd" => "KURE"));
		$this->assertEquals(array(
			array("team_id" => 1, "name" => "Parta Nic", "pswd" => "PRAK", "phone" => null, "email" => null),
			array("team_id" => $team["team_id"], "name" => "Redwool", "pswd" => "KURE", "phone" => null, "email" => null)
		), $this->list());
	}

	function testcreateDuplicatePassword() {
		try {
			$team = $this->create(array("name" => "Redwool", "pswd" => "prak"));
			$this->fail("Should throw DBException");
		} catch (DBException $ex) {
			$this->assertEquals(1062, $ex->getCode());
		}
		$this->assertEquals(array(
			array("team_id" => 1, "name" => "Parta Nic", "pswd" => "PRAK", "phone" => null, "email" => null)
		), $this->list());
	}

	function testUpdate() {
		$team = $this->list()[0];
		$team["name"] = "Pípy z Lípy";
		$team["pswd"] = "kure";
		$this->update($team);
		$this->assertEquals(array(
			array("team_id" => 1, "name" => "Pípy z Lípy", "pswd" => "KURE", "phone" => null, "email" => null)
		), $this->list());
	}

	function testDelete() {
		$this->delete($this->list()[0]);
		$this->assertCount(0, $this->list());
	}
}
