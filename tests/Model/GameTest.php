<?php
namespace Sova\Model;

use Sova\TestBase;

/*class GameTest extends TestBase {

	protected $game;

	function setUp(): void {
		parent::setUp();
		$this->db->execute("INSERT INTO user VALUES (3, 'user3', '')");
		$this->db->execute("INSERT INTO game VALUES (3, 3, 'game3', '2020-03-01', '2020-03-02')");
		$this->game = new Game();
		unset($_SESSION["game_id"]);
	}

	function testNoOwnedGame() {
		$_SESSION["user_id"] = 1;
		$this->assertEquals(0, $this->game->setOwnedGame());
		$this->assertFalse(Game::selected());
	}

	function testSingleOwnedGame() {
		$_SESSION["user_id"] = 3;
		$this->assertEquals(1, $this->game->setOwnedGame());
		$this->assertTrue(Game::selected());
		$this->assertEquals(3, Game::current());
		$this->assertEquals("game3", Game::currentName());
	}

	function testMultipleOwnedGames() {
		$_SESSION["user_id"] = 2; 
		$games = $this->game->setOwnedGame();
		$this->assertCount(2, $games);
	}
}*/
