<?php
namespace Sova\Model;

use Sova\TestBase;
use Sova\AppException;
use Sova\Repo\HintRepo;
use Sova\Model\Game;

class TeamTest extends TestBase {

	protected $team;

	function setUp(): void {
		parent::setUp();
		$this->db->execute("INSERT INTO team (team_id, game_id, name) VALUES (1, 2, 'Parta Nic')");
		$this->db->execute("INSERT INTO code (team_id, game_id, code) VALUES (1, 2, 'PRAK')");
		$this->team = new Team();
		$_SESSION = array();
	}

	function testLogin() {
		$this->assertTrue($this->team->login(1, "prak"));
		$this->assertTrue(Team::logged());
		$this->assertEquals(Game::level(), Game::TEAM);
		$this->assertEquals(1, Team::current());
		$this->assertEquals("Parta Nic", Team::currentName());
		$this->assertEquals(2, Game::current());
		$this->assertEquals("game2", Game::currentName());
	}

	function testBadLogin() {
		$this->assertFalse($this->team->login(2, "prak"));
		$this->assertFalse($this->team->login(1, "bad"));
		$this->assertFalse(Team::logged());
		$this->assertFalse(Game::selected());
	}

	function testDeductPoints() {
		$this->team->login(1, "prak");
		Settings::set("deductPoints", true);

		$this->assertEquals(25, $this->team->deductPoints("25"));
		$this->assertEquals(-25, $this->team->points());
		$this->assertEquals(
			["type" => HintRepo::DEDUCT_POINTS, "points" => 25, "ccode_id" => null, "cipher_id" => null],
			$this->db->squery("SELECT type, points, ccode_id, cipher_id FROM hint WHERE team_id = 1")
		);
	}

	function testDeductPointsDisabled() {
		$this->team->login(1, "prak");
		$this->expectException(AppException::class);
		$this->expectExceptionMessage("Odečítání bodů není povoleno.");
		$this->team->deductPoints(25);
	}

}
