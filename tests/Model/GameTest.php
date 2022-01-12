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
	}

	function tearDown(): void {
		$this->db->execute("UPDATE settings SET gameStart = NULL, gameEnd = NULL WHERE game_id = 1");
		parent::tearDown();
	}

	function testState() {
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
