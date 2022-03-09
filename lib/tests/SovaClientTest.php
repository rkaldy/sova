<?php
use PHPUnit\Framework\TestCase;
use Sova\DB;

require __DIR__."/../SovaClient.php";

class SovaClientTest extends TestCase {

	function setUp(): void {
        $this->db = DB::get();
        $this->db->execute("INSERT INTO superuser VALUES (?)", password_hash("nimda", PASSWORD_BCRYPT, ["cost" => 4]));
		$this->db->execute("INSERT INTO game VALUES (1, 'game1', ?)", password_hash("swordfish", PASSWORD_BCRYPT, ["cost" => 4]));
		$this->sova = new SovaClient("http://nelly/sova", "game1", "swordfish");
	}

	function tearDown(): void {
		$this->db->execute("DELETE FROM game");
		$this->db->execute("DELETE FROM superuser");
	}

	function testList() {
		$this->db->execute("INSERT INTO team (game_id, team_id, name, members, additional) VALUES (1, 1, 'Parta Nic', '[\"Rumcajs\",\"Manka\",\"Cipísek\"]', '{\"a\": 1, \"b\": 2}')");
		$this->db->execute("INSERT INTO team (game_id, team_id, name, members, additional) VALUES (1, 2, 'Redwool', '[\"Knížepán\"]', NULL)");
		$this->db->execute("INSERT INTO code (game_id, team_id, code) VALUES (1, 1, 'KURE')");
		$this->db->execute("INSERT INTO code (game_id, team_id, code) VALUES (1, 2, 'PRAK')");

		$teams = $this->sova->list();

		$this->assertEquals(2, count($teams));
		$this->assertEquals("Parta Nic", $teams[0]["name"]);
		$this->assertEquals(["Rumcajs", "Manka", "Cipísek"], $teams[0]["members"]);
		$this->assertEquals(["a" => 1, "b" => 2], $teams[0]["additional"]);
		$this->assertEquals("Redwool", $teams[1]["name"]);
		$this->assertEquals(["Knížepán"], $teams[1]["members"]);
	}

	function testLogin() {
		$this->db->execute("INSERT INTO team (game_id, team_id, name, members, additional) VALUES (1, 1, 'Parta Nic', '[\"Rumcajs\",\"Manka\",\"Cipísek\"]', '{\"a\": 1, \"b\": 2}')");
		$this->db->execute("INSERT INTO team (game_id, team_id, name, members, additional) VALUES (1, 2, 'Redwool', '[\"Knížepán\"]', NULL)");
		$this->db->execute("INSERT INTO code (game_id, team_id, code) VALUES (1, 1, 'KURE')");
		$this->db->execute("INSERT INTO code (game_id, team_id, code) VALUES (1, 2, 'PRAK')");

		$team = $this->sova->login(1, "KURE");
		$this->assertEquals("Parta Nic", $team["name"]);
		$this->assertEquals("KURE", $team["pswd"]);
		$this->assertEquals(["Rumcajs", "Manka", "Cipísek"], $team["members"]);

		$team = $this->sova->login(1, "PRAK");
		$this->assertNull($team);
	}

	function testCreate() {
		$team = $this->sova->create("Parta Nic", "123", "parta@nic.cz", ["Rumcajs", "Manka", "Cipísek"], false, 2, "Poznámka", ["a" => 1, "b" => 2]);
		$this->assertEquals("Parta Nic", $team["name"]);
		$this->assertNotNull($team["pswd"]);
		$this->assertNotNull($team["team_id"]);
		$this->assertNotEquals(0, $team["team_id"]);
		$this->assertEquals(1, $team["game_id"]);
		
		$teams = $this->sova->list();
		$this->assertEquals(1, count($teams));
		$this->assertEquals("Parta Nic", $teams[0]["name"]);

		$this->expectException(\Exception::class);
		$team = $this->sova->create("Parta Nic", "123", "parta@nic.cz", [], false, 2, "Poznámka", null);
	}

	function testUpdate() {
		$team = $this->sova->create("Parta Nic", "123", "parta@nic.cz", ["Rumcajs", "Manka", "Cipísek"], false, 2, "Poznámka", ["a" => 1, "b" => 2]);
		$team = $this->sova->update($team["team_id"], "Parta Nic", 456, "parta@nic.cz", ["Kníže,pán"], false, 2, "Poznámka", null);
		$this->assertEquals(456, $team["phone"]);
		$this->assertEquals(["Kníže,pán"], $team["members"]);
		$this->assertNull($team["additional"]);
		
		$teams = $this->sova->list();
		$this->assertEquals(1, count($teams));
		$this->assertEquals(["Kníže,pán"], $teams[0]["members"]);
	}

	function testDelete() {
		$this->db->execute("INSERT INTO team (game_id, team_id, name, members, additional) VALUES (1, 1, 'Parta Nic', '[\"Rumcajs\",\"Manka\",\"Cipísek\"]', '{\"a\": 1, \"b\": 2}')");
		$this->db->execute("INSERT INTO team (game_id, team_id, name, members, additional) VALUES (1, 2, 'Redwool', '[\"Knížepán\"]', NULL)");
		$this->db->execute("INSERT INTO code (game_id, team_id, code) VALUES (1, 1, 'KURE')");
		$this->db->execute("INSERT INTO code (game_id, team_id, code) VALUES (1, 2, 'PRAK')");

		$this->sova->delete(1);

		$teams = $this->sova->list();
		$this->assertEquals(1, count($teams));
		$this->assertEquals("Redwool", $teams[0]["name"]);
	}
}
