<?php
namespace Sova\Controller;

use Sova\Request;
use Sova\TestBase;
use Sova\Model\Game;

class AdminControllerTest extends TestBase {

	function testLoginAsRequiresSuperuser() {
		$request = new Request("POST", "/admin/loginAs", [], ["login" => "game2"]);

		$response = (new AdminController())->process($request, ["loginAs"]);

		$this->assertEquals(200, $response->status);
		$this->assertStringContainsString("Nedostatečná práva k akci 'loginAs'", $response->data);
		$this->assertEquals(Game::ADMIN, Game::level());
	}

	function testLoginAsSuperuser() {
		unset($_SESSION["game_id"], $_SESSION["game_name"]);
		$_SESSION["level"] = Game::SUPERUSER;
		$request = new Request("POST", "/admin/loginAs", [], ["login" => "game2"]);

		$response = (new AdminController())->process($request, ["loginAs"]);

		$this->assertEquals(301, $response->status);
		$this->assertEquals("locs", $response->headers["Location"]);
		$this->assertEquals(2, Game::current());
		$this->assertEquals("game2", Game::currentName());
		$this->assertEquals(Game::ADMIN, Game::level());
	}
}
