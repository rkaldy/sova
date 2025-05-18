<?php
namespace Sova\Controller;

use Sova\Request;
use Sova\GameTestBase;
use Sova\View;
use Sova\Redirect;
use Sova\Model\Settings;

class MainControllerTest extends GameTestBase {
	
	private $controller;

	function setUp(): void {
		parent::setUp();
		$this->controller = new MainController();

		Settings::set("hintCCodes", 1);
		Settings::set("howtoCCodes", 2);
		Settings::set("solutionCCodes", 0);
		Settings::set("hintPoints", 10);
		Settings::set("howtoPoints", 20);
		Settings::set("solutionPoints", 30);
		Settings::set("imunityCCodes", 2);
	}

	function tearDown(): void {
		parent::tearDown();
	}

	function checkView($view, ?string $response, int $points, int $ccodes, bool $imunityAvailable = null, string $imunityMsg = null) {
		if ($view instanceof Redirect) {
			$view = $this->controller->hints([], []);
			$this->assertEquals($response, $_SESSION["flash"]);
		} else if (isset($response)) {
			$this->assertEquals($response, $view->fields["response"]);
		}
		$this->assertEquals($points, $view->fields["points"]);
		$this->assertEquals($ccodes, $view->fields["ccodes"]);
		if (isset($imunityAvailable) && isset($imunityMsg)) {
			$this->assertEquals($imunityAvailable, $view->fields["imunityAvailable"]);
			$this->assertEquals($imunityMsg, $view->fields["imunityMsg"]);
		}
	}

	function testHint() {
		$view = $this->controller->checkhint([], ["cipher" => "s4a"]);
		$this->checkView($view, "Na šifru S4a jste se ještě nemohli dostat, protože jste nevyluštili žádnou předchozí šifru. ", 0, 0);
		
		$view = $this->controller->checkhint([], ["cipher" => "S1"]);
		$this->checkView($view, "Pro šifru S1 jste ještě žádnou nápovědu nedostali. Nyní můžete zažádat o nápovědu. Protože nemáte dostatek céček, bude vás to stát 10 bodů. ", 0, 0);
		$view = $this->controller->applyhint([], ["cipher" => "S1"]);
		$this->checkView($view, "Nápověda k šifře S1: Čárka tečka čárka, tak začíná Klárka", -10, 0);
		
		$view = $this->controller->code([], ["code" => "divizna"]);
		$this->assertEquals("Získali jste céčko. Aktuálně máte 1 nevyužitých céček.", $view->fields["response"][0]);
		$view = $this->controller->code([], ["code" => "vcela"]);
		$this->assertEquals("Získali jste céčko. Aktuálně máte 2 nevyužitých céček.", $view->fields["response"][0]);
		$view = $this->controller->code([], ["code" => "buben"]);
		$this->assertEquals("Získali jste céčko. Aktuálně máte 3 nevyužitých céček.", $view->fields["response"][0]);

		$view = $this->controller->checkhint([], ["cipher" => "S1"]);
		$this->checkView($view, "Pro šifru S1 jste již dostali nápovědu. Nyní můžete zažádat o postup za 2 céček. ", -10, 3);
		$view = $this->controller->applyhint([], ["cipher" => "S1"]);
		$this->checkView($view, "Postup k šifře S1: Použij morseovku", -10, 1);

		$view = $this->controller->checkhint([], ["cipher" => "S1"]);
		$this->checkView($view, "Pro šifru S1 jste již dostali postup. Nyní můžete zažádat o řešení za 30 bodů. ", -10, 1);
		$view = $this->controller->applyhint([], ["cipher" => "S1"]);
		$this->checkView($view, "Řešení šifry S1: ABERACE", -40, 1);

		$view = $this->controller->checkhint([], ["cipher" => "S1"]);
		$this->checkView($view, "Pro šifru S1 jste již dostali řešení. Podívejte se do přehledu šifer. ", -40, 1);

		$view = $this->controller->code([], ["code" => "aberace"]);
		$view = $this->controller->code([], ["code" => "zabradli"]);

		$view = $this->controller->checkhint([], ["cipher" => "S1"]);
		$this->checkView($view, "Šifru S1 jste již vyluštili. ", 10, 1);

		$view = $this->controller->checkhint([], ["cipher" => "S2"]);
		$this->checkView($view, "Pro šifru S2 jste ještě žádnou nápovědu nedostali. Nyní můžete zažádat o nápovědu za 1 céček. ", 10, 1);
		$view = $this->controller->applyhint([], ["cipher" => "S2"]);
		$this->checkView($view, "Nápověda k šifře S2: Krzyz", 10, 0);
	}

	function testImunity() {
		$view = $this->controller->hints([], []);
		$this->checkView($view, null, 0, 0, false, "Nemáte dostatek céček pro získání imunity.");
		
		$this->controller->code([], ["code" => "buben"]);
		$this->controller->code([], ["code" => "divizna"]);
		$this->controller->code([], ["code" => "vcela"]);
		$view = $this->controller->hints([], []);
		$this->checkView($view, null, 0, 3, true, "Můžete si zakoupit imunitu za 2 céček.");

		$view = $this->controller->imunity([], []);
		$this->checkView($view, "Získali jste imunitu. Kdyby na vás při vyhlášení padlo organizování příštího ročníku, můžete ho odmítnout.", 0, 1, false, "Imunitu jste již dostali.");
	}

	function testGameTime() {
		Settings::set("gameStartTimestamp", time() + 3600);
		$view = $this->controller->handleAction(new Request("GET", null, [], []), "code");
		$this->assertEquals("Hra ještě nezačala.", $view->fields["error"]);
		$view = $this->controller->handleAction(new Request("GET", null, [], []), "messages");
		$this->assertEquals("Hra ještě nezačala.", $view->fields["error"]);

		Settings::set("gameStartTimestamp", time() - 3600);
		Settings::set("gameEndTimestamp", time() - 1800);
		$view = $this->controller->handleAction(new Request("GET", null, [], []), "code");
		$this->assertEquals("Hra již skončila.", $view->fields["error"]);
		$view = $this->controller->handleAction(new Request("GET", null, [], []), "messages");
		$this->assertFalse(isset($view->fields["error"]));
	}
}
