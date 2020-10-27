<?php
namespace Sova\Model;

use Sova\GameTestBase;
use Sova\Controller\CodeController;

class CodeControllerTest extends GameTestBase {

	function testBadCode() {
		$_SESSION["team_id"] = 1;
		$this->assertEquals("Neznámý kód: BAD", CodeController::process("bad"));
	}

	function testAddHint() {
		$_SESSION["team_id"] = 1;
		$this->assertEquals("Získali jste univerzální nápovědu. Aktuálně máte 1 nevyužitých nápověd.", CodeController::process("buben"));
		$this->assertEquals("Získali jste univerzální nápovědu. Aktuálně máte 2 nevyužitých nápověd.", CodeController::process("divizna"));
		$this->assertEquals("Tento kód nápovědy jste již zadali.", CodeController::process("buben"));
		$_SESSION["team_id"] = 2;
		$this->assertEquals("Získali jste univerzální nápovědu. Aktuálně máte 1 nevyužitých nápověd.", CodeController::process("buben"));
	}
}
