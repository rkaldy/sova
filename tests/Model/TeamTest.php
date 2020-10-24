<?php
namespace Sova\Model;

use Sova\TestBase;

class TeamTest extends TestBase {

	function setUp(): void {
		parent::setUp();
		$this->db->execute("INSERT INTO team (team_id, game_id, name) VALUES (1, 2, 'Parta Nic')");
		$this->db->execute("INSERT INTO code (team_id, game_id, code) VALUES (1, 2, 'PRAK')");
		$_SESSION = array();
	}

	function tearDown(): void {
		$this->db->execute("DELETE FROM team");
		parent::tearDown();
	}

	function testLogin() {
		$this->assertTrue(Team::login(1, "prak"));
		$this->assertTrue(Team::logged());
		$this->assertEquals(1, Team::current());
		$this->assertEquals("Parta Nic", Team::currentName());
		$this->assertEquals(2, Game::current());
		$this->assertEquals("game2", Game::currentName());
	}

	function testBadLogin() {
		$this->assertFalse(Team::login(2, "prak"));
		$this->assertFalse(Team::login(1, "bad"));
		$this->assertFalse(Team::logged());
		$this->assertFalse(Game::selected());
	}
}
