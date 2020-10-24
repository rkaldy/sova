<?php
namespace Sova\Model;

use Sova\TestBase;
use Sova\DBException;

class TeamRepoTest extends TestBase {

	protected $repo;

	function setUp(): void {
		parent::setUp();
		$this->db->execute("INSERT INTO team (team_id, game_id, name) VALUES (1, 1, 'Parta Nic')");
		$this->db->execute("INSERT INTO code (team_id, game_id, code) VALUES (1, 1, 'PRAK')");
		$this->repo = new TeamRepo();
	}

	function tearDown(): void {
		$this->db->execute("DELETE FROM team");
		parent::tearDown();
	}

	function testCreate() {
		$team = $this->repo->create(array("name" => "Redwool", "pswd" => "KURE"));
		$this->assertEquals(array(
			array("game_id" => 1, "team_id" => 1, "name" => "Parta Nic", "pswd" => "PRAK", "phone" => null, "email" => null),
			array("game_id" => 1, "team_id" => $team["team_id"], "name" => "Redwool", "pswd" => "KURE", "phone" => null, "email" => null)
		), $this->repo->list());
	}

	function testcreateDuplicatePassword() {
		try {
			$team = $this->repo->create(array("name" => "Redwool", "pswd" => "prak"));
			$this->fail("Should throw DBException");
		} catch (DBException $ex) {
			$this->assertEquals(1062, $ex->getCode());
		}
		$this->assertEquals(array(
			array("game_id" => 1, "team_id" => 1, "name" => "Parta Nic", "pswd" => "PRAK", "phone" => null, "email" => null)
		), $this->repo->list());
	}

	function testUpdate() {
		$team = $this->repo->list()[0];
		$team["name"] = "Pípy z Lípy";
		$team["pswd"] = "kure";
		$this->repo->update($team);
		$this->assertEquals(array(
			array("game_id" => 1, "team_id" => 1, "name" => "Pípy z Lípy", "pswd" => "KURE", "phone" => null, "email" => null)
		), $this->repo->list());
	}

	function testDelete() {
		$this->repo->delete($this->repo->list()[0]);
		$this->assertCount(0, $this->repo->list());
	}
}
