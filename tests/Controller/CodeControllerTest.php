<?php
namespace Sova\Controller;

use Sova\GameTestBase;
use Sova\Model\Team;
use Sova\Model\Message;

class CodeControllerTest extends GameTestBase {

	function getFutureMessages() {
		$messages = [];
		$stmt = $this->db->query("SELECT text FROM message WHERE team_id = ? AND direction = ? AND time > NOW() ORDER BY time", Team::current(), Message::TO_TEAM);
		while ($row = $stmt->fetch()) {
			$messages[] = $row["text"];
		}
		return $messages;
	}

	function testBadCode() {
		$this->assertEquals("Neznámý kód: BAD", CodeController::process("bad"));
	}

	function testAddHint() {
		$this->assertEquals("Získali jste univerzální nápovědu. Aktuálně máte 1 nevyužitých nápověd.", CodeController::process("buben"));
		$this->assertEquals("Získali jste univerzální nápovědu. Aktuálně máte 2 nevyužitých nápověd.", CodeController::process("divizna"));
		$this->assertEquals("Tento kód nápovědy jste již zadali.", CodeController::process("buben"));
		$_SESSION["team_id"] = 2;
		$this->assertEquals("Získali jste univerzální nápovědu. Aktuálně máte 1 nevyužitých nápověd.", CodeController::process("buben"));
	}

	function testUnavailableLoc() {
		$this->assertEquals("Neznámý kód: KYBL", CodeController::process("kybl")); 
	}

	function testVisitLoc() {
		$this->assertEquals("Dostali jste se na stanoviště Start.", CodeController::process("pralinka"));
		$this->assertEquals("Tento kód stanoviště jste již zadali.", CodeController::process("pralinka"));
	}

	function testTimeHints() {
		$this->assertEquals("Získali jste univerzální nápovědu. Aktuálně máte 1 nevyužitých nápověd.", CodeController::process("divizna"));
		$this->assertEmpty($this->getFutureMessages());
		$this->assertEquals("Dostali jste se na stanoviště Start.", CodeController::process("pralinka"));
		$this->assertEquals([
			"Přišel čas na nápovědu k šifře S1a: Čárka tečka čárka, tak začíná Klárka.",
			"Přišel čas na nápovědu k šifře S1b: Zkus ji luštit poslepu.",
			"Přišel čas na řešení šifry S1a: ABERACE"
		], $this->getFutureMessages());
		(new MainController())->applyhint(null, ["cipher" => "S1a"]);
		$this->assertEquals([
			"Přišel čas na nápovědu k šifře S1b: Zkus ji luštit poslepu.",
			"Přišel čas na řešení šifry S1a: ABERACE"
		], $this->getFutureMessages());
	}
}
