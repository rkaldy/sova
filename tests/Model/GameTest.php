<?php
namespace Sova\Model;

use Sova\DB;

class GameTest {

	function setUp(): void {
		$this->db = DB::get();
		$this->db->execute("INSERT INTO user VALUES (1, 'user1', '')");
		$this->db->execute("INSERT INTO user VALUES (2, 'user2', '')");
		$this->db->execute("INSERT INTO user VALUES (3, 'user3', '')");
		$this->db->execute("INSERT INTO game VALUES (1, 2, 'game1', '2020-01-01', '2020-01-02')");
		$this->db->execute("INSERT INTO game VALUES (2, 3, 'game2', '2020-02-01', '2020-02-02')");
		$this->db->execute("INSERT INTO game VALUES (3, 3, 'game3', '2020-03-01', '2020-03-02')");
	}

	function tearDown(): void {
		$_SESSION = array();
	}

	function testNoOwnedGame() {
		$_SESSION["user_id"] = 1;
		$this->assertEquals(0, Game::setOwnedGame());
		$this->assertFalse(Game::selected());
	}

	function testSingleOwnedGame() {
		$_SESSION["user_id"] = 2;
		$this->assertEquals(1, Game::setOwnedGame());
		$this->assertTrue(Game::selected());
		$this->assertEquals(1, Game::current());
		$this->assertEquals("game1", Game::currentName());
	}

	function testMultipleOwnedGames() {
		$_SESSION["user_id"] = 3; 
		$games = Game::setOwnedGame();
		$this->assertCount(2, $games);
	}
}
