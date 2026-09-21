<?php
namespace Sova\Model;

use Sova\TestBase;

class GameTest extends TestBase {

	protected $game;
	protected $settings;

	function setUp(): void {
		parent::setUp();
		$this->game = new Game();
		$this->settings = new Settings();
		unset($_SESSION["game_id"]);
	}

	function tearDown(): void {
		$this->db->execute("UPDATE settings SET gameStart = NULL, gameEnd = NULL WHERE game_id = 1");
		parent::tearDown();
	}

	function testLogin() {
		$this->assertTrue($this->game->login("game2", "swordfish"));
		$this->assertTrue(Game::selected());
		$this->assertEquals(2, Game::current());
		$this->assertEquals("game2", Game::currentName());
		$this->assertEquals(Game::level(), Game::ADMIN);
	}

	function testLoginSuperuser() {
		$this->assertTrue($this->game->login("superuser", "nimda"));
		$this->assertFalse(Game::selected());
		$this->assertEquals(Game::level(), Game::SUPERUSER);
	}

	function testBadLogin() {
		$this->assertFalse($this->game->login("bad", "swordfish"));
		$this->assertFalse($this->game->login("superuser", "bad"));
		$this->assertFalse(Game::selected());
	}

	function testChangeCurrentPassword() {
		$_SESSION["game_id"] = 1;
		$this->game->changeCurrentPassword("newsecret");
		$_SESSION = [];

		$this->assertFalse($this->game->login("game1", "samara"));
		$this->assertTrue($this->game->login("game1", "newsecret"));
	}

	function testChangeCurrentPasswordEmptyKeepsOriginal() {
		$_SESSION["game_id"] = 1;
		$this->game->changeCurrentPassword("");
		$_SESSION = [];

		$this->assertTrue($this->game->login("game1", "samara"));
	}

	function testState() {
		$_SESSION["game_id"] = 1;
		$this->db->execute("UPDATE settings SET gameStart = DATE_SUB(NOW(), INTERVAL 1 HOUR), gameEnd = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE game_id = 1");
		$this->settings->load();
		$this->assertEquals(Game::CURRENT, Game::state());
		
		$this->db->execute("UPDATE settings SET gameStart = DATE_SUB(NOW(), INTERVAL 2 HOUR), gameEnd = DATE_SUB(NOW(), INTERVAL 1 HOUR) WHERE game_id = 1");
		$this->settings->load();
		$this->assertEquals(Game::PAST, Game::state());
		
		$this->db->execute("UPDATE settings SET gameStart = DATE_ADD(NOW(), INTERVAL 1 HOUR), gameEnd = DATE_ADD(NOW(), INTERVAL 2 HOUR) WHERE game_id = 1");
		$this->settings->load();
		$this->assertEquals(Game::FUTURE, Game::state());
	}
}
