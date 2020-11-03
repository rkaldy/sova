<?php
namespace Sova\Controller;

use Sova\GameTestBase;
use Sova\Model\Team;
use Sova\Model\Message;
use Sova\Repo\ProgressRepo;

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
		CodeController::process("divizna");
		$this->assertEmpty($this->getFutureMessages());
		CodeController::process("pralinka");
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

	function testUnavailableCipher() {
		$this->assertEquals("Neznámý kód: KOBLIHA", CodeController::process("kobliha"));
		(new ProgressRepo())->create(1, 12);
		$this->assertEquals("Úspěšně jste vyluštili šifru S2. Poloha dalšího stanoviště je: Pardubické boudy, hledej orga.", CodeController::process("kobliha"));
	}

	function testSolveCipher() {
		$this->assertEquals("Úspěšně jste vyluštili šifru S1a. Poloha dalšího stanoviště je: Vrchol Bílé hory.", CodeController::process("aberace"));
		$this->assertEquals("Toto řešení šifry jste již zadali.", CodeController::process("aberace"));
	}

	function testDeletePendingHints() {
		(new ProgressRepo())->create(1, 13);
		$this->assertEquals("Dostali jste se na stanoviště Turniket.", CodeController::process("medved"));
		$this->assertEquals([
			"Přišel čas na nápovědu k šifře S3a: Křižovatka, železnice, Suchý.",
			"Přišel čas na nápovědu k šifře S3b: Jedničky a nuly.",
			"Přišel čas na řešení šifry S3a: KALENDAR"
		], $this->getFutureMessages());
		$this->assertEquals("Úspěšně jste vyluštili šifru S3b. Poloha dalšího stanoviště je: Kóta 1019 nad Pražskou boudou.", CodeController::process("skluzavka"));
		$this->assertEmpty($this->getFutureMessages());
	}


	function testWalkthrough() {
		$this->assertEquals("Dostali jste se na stanoviště Start.", CodeController::process("pralinka"));
		$this->assertEquals([
			"Přišel čas na nápovědu k šifře S1a: Čárka tečka čárka, tak začíná Klárka.",
			"Přišel čas na nápovědu k šifře S1b: Zkus ji luštit poslepu.",
			"Přišel čas na řešení šifry S1a: ABERACE"
		], $this->getFutureMessages());
		$this->assertEquals("Úspěšně jste vyluštili šifru S1a. Poloha dalšího stanoviště je: Vrchol Bílé hory.", CodeController::process("aberace"));
		$this->assertEquals([
			"Přišel čas na nápovědu k šifře S1b: Zkus ji luštit poslepu.",
		], $this->getFutureMessages());
		$this->assertEquals("Dostali jste se na stanoviště Bílá hora.", CodeController::process("kybl"));
		$this->assertEquals("Získali jste univerzální nápovědu. Aktuálně máte 1 nevyužitých nápověd.", CodeController::process("buben"));
		$this->assertEquals("Úspěšně jste vyluštili šifru S1b. Poloha dalšího stanoviště je: Vrchol Černé hory.", CodeController::process("zabradli"));
		$this->assertEmpty($this->getFutureMessages());
		$this->assertEquals("Získali jste univerzální nápovědu. Aktuálně máte 2 nevyužitých nápověd.", CodeController::process("divizna"));
		$this->assertEquals("Dostali jste se na stanoviště Černá hora.", CodeController::process("podnos"));
		$this->assertEquals([
			"Přišel čas na nápovědu k šifře S2: Krzyz.",
		], $this->getFutureMessages());
		(new MainController())->applyhint(null, ["cipher" => "S2"]);
		$this->assertEmpty($this->getFutureMessages());
		$this->assertEquals("Úspěšně jste vyluštili šifru S2. Poloha dalšího stanoviště je: Pardubické boudy, hledej orga.", CodeController::process("kobliha"));
		$this->assertEquals("Dostali jste se na stanoviště Turniket.", CodeController::process("medved"));
		$this->assertEquals([
			"Přišel čas na nápovědu k šifře S3a: Křižovatka, železnice, Suchý.",
			"Přišel čas na nápovědu k šifře S3b: Jedničky a nuly.",
			"Přišel čas na řešení šifry S3a: KALENDAR"
		], $this->getFutureMessages());
		(new MainController())->applyhint(null, ["cipher" => "S3b"]);
		$this->assertEquals([
			"Přišel čas na nápovědu k šifře S3a: Křižovatka, železnice, Suchý.",
			"Přišel čas na řešení šifry S3a: KALENDAR"
		], $this->getFutureMessages());
		$this->assertEquals("Úspěšně jste vyluštili šifru S3a. Poloha dalšího stanoviště je: Kóta 1019 nad Pražskou boudou.", CodeController::process("kalendar"));
		$this->assertEmpty($this->getFutureMessages());
		$this->assertEquals("Dostali jste se na stanoviště Cíl.", CodeController::process("salvej"));
	}
}
