<?php
namespace Sova\Controller;

use Sova\GameTestBase;
use Sova\Redirect;


class AdminControllerTest extends GameTestBase {

	function setUp(): void {
		parent::setUp();
		$this->main = new MainController();
		$this->admin = new AdminController();

		$this->db->execute("INSERT INTO team (team_id, game_id, name) VALUE (3, 2, 'Múzy')");
		$this->db->execute("INSERT INTO point (point_id, game_id, name, points) VALUES (10, 2, 'Start', 15)");
		$this->db->execute("INSERT INTO loc (point_id, description) VALUES (10, '')");
	}

	function tearDown(): void {
		parent::tearDown();
	}

	function checkReset() {
		$this->progressRepo->create(1, 1);
		$this->progressRepo->create(3, 10);
        $this->main->applyhint([], ["cipher" => "S1"]);
		$this->admin->reset();
		
	}

	function checkView($view, ?string $response, int $points, int $ccodes) {
		if ($view instanceof Redirect) {
			$view = $this->main->hints([], []);
        	$this->assertEquals($response, $_SESSION["flash"]);
		} else if (isset($response)) {
	        $this->assertEquals($response, $view->fields["response"]);
		}
        $this->assertEquals("Aktuálně máte $points bodů a $ccodes nevyužitých céček.", $view->fields["hintStatus"]);
	}
}
