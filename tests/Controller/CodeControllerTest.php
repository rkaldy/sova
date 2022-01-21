<?php
namespace Sova\Controller;

use Sova\GameTestBase;
use Sova\Model\Team;
use Sova\Model\Message;
use Sova\Model\Settings;
use Sova\Model\Progress;
use Sova\Repo\ProgressRepo;

class CodeControllerTest extends GameTestBase {

	function setUp(): void {
		parent::setUp();
		$this->progressRepo = new ProgressRepo();
	}

	function tearDown(): void {
		parent::tearDown();
	}

	function getFutureMessages() {
		$messages = [];
		$stmt = $this->db->query("SELECT text FROM message WHERE team_id = ? AND direction = ? AND time > NOW(6) ORDER BY time", Team::current(), Message::TO_TEAM);
		while ($row = $stmt->fetch()) {
			$messages[] = $row["text"];
		}
		return $messages;
	}


	function testInvalidCode() {
		$this->assertEquals(["Kód musí být jednoslovné podstatné jméno."], CodeController::process(""));
		$this->assertEquals(["Kód musí být jednoslovné podstatné jméno."], CodeController::process("f*cky0u"));
	}

	function testBadCode() {
		$this->assertEquals(["Neznámý kód: BAD"], CodeController::process("bad"));
		$this->assertEquals(["Neznámý kód: BAD"], CodeController::process("báď"));
	}

	function testAddHint() {
		$this->assertEquals(["Získali jste univerzální nápovědu. Aktuálně máte 1 nevyužitých nápověd."], CodeController::process("buben"));
		$this->assertEquals(["Získali jste univerzální nápovědu. Aktuálně máte 2 nevyužitých nápověd."], CodeController::process("divizna"));
		$this->assertEquals(["Tento kód nápovědy jste již zadali."], CodeController::process("buben"));
		$_SESSION["team_id"] = 2;
		$this->assertEquals(["Získali jste univerzální nápovědu. Aktuálně máte 1 nevyužitých nápověd."], CodeController::process("buben"));
	}

	function testUnavailableLoc() {
		$this->assertEquals(["Neznámý kód: KYBL"], CodeController::process("kybl")); 
	}

	function testVisitLoc() {
		$this->assertEquals(["Vítejte na stanovišti Start. Jste tu 1. První tu byl tým Parta Nic v ".$this->dbNow()."."], CodeController::process("pralinka"));
		$this->assertEquals(["Tento kód stanoviště jste již zadali."], CodeController::process("pralinka"));
	}

	function testVisitLocNoRank() {
		$_SESSION["settings"]["showRank"] = 0;
		$this->assertEquals(["Vítejte na stanovišti Start."], CodeController::process("pralinka"));
	}

	function testVisitFinishNoRank() {
		$_SESSION["settings"]["locFinish"] = 3;
		$_SESSION["settings"]["showRank"] = 0;
		CodeController::process("zabradli");
		$this->assertEquals(["Gratulujeme, jste v cíli!"], CodeController::process("podnos"));
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
		$this->assertEquals(["Neznámý kód: KOBLIHA"], CodeController::process("kobliha"));
		$this->progressRepo->create(1, 12);
		$this->assertEquals([
			"Úspěšně jste vyluštili šifru S2. Jste 1. První ji vyluštil tým Parta Nic v ".$this->dbNow().". Máte vyluštěno celkem 2 šifer.",
			"Další stanoviště Turniket se nachází na Pardubických boudách, hledej orga."
		], CodeController::process("kobliha"));
	}

	function testSolveCipher() {
		$this->assertEquals([
			"Úspěšně jste vyluštili šifru S1a. Jste 1. První ji vyluštil tým Parta Nic v ".$this->dbNow().". Máte vyluštěno celkem 1 šifer.",
			"Další stanoviště 1a se nachází na vrcholu Bílé hory."
		], CodeController::process("aberace"));
		$this->assertEquals(["Toto řešení šifry jste již zadali."], CodeController::process("aberace"));
	}

	function testSolveCipherNoRank() {
		$_SESSION["settings"]["showRank"] = 0;
		$this->assertEquals([
			"Úspěšně jste vyluštili šifru S1a. Máte vyluštěno celkem 1 šifer.",
			"Další stanoviště 1a se nachází na vrcholu Bílé hory."
		], CodeController::process("aberace"));
	}

	function testSolveCipherWithLink() {
		$this->db->execute("UPDATE loc SET coord_lat = 50.08, coord_lon = 14.32 WHERE point_id = 2");
		$_SESSION["settings"]["linkMapyCz"] = "turisticka";
		$this->assertEquals([
			"Úspěšně jste vyluštili šifru S1a. Jste 1. První ji vyluštil tým Parta Nic v ".$this->dbNow().". Máte vyluštěno celkem 1 šifer.",
			'Další stanoviště 1a se nachází na vrcholu Bílé hory, <a href="https://mapy.cz/turisticka?q=50.0800000N%2014.3200000E">50.0800000N 14.3200000E</a>.'
		], CodeController::process("aberace"));
	}

	function testDeletePendingHints() {
		$this->progressRepo->create(1, 14);
		$this->assertEquals(["Vítejte na stanovišti 4. Jste tu 1. První tu byl tým Parta Nic v ".$this->dbNow()."."], CodeController::process("tabulka"));
		$this->assertEquals([
			"Přišel čas na nápovědu k šifře S4a: Křižovatka, železnice, Suchý.",
			"Přišel čas na nápovědu k šifře S4b: Jedničky a nuly.",
			"Přišel čas na řešení šifry S4a: KALENDAR"
		], $this->getFutureMessages());
		$this->assertEquals([
			"Úspěšně jste vyluštili šifru S4b. Jste 1. První ji vyluštil tým Parta Nic v ".$this->dbNow().". Máte vyluštěno celkem 2 šifer.",
			"Další stanoviště Cíl se nachází na kótě 1019 nad Pražskou boudou."
		], CodeController::process("skluzavka"));
		$this->assertEquals([
			"Přišel čas na nápovědu k šifře S4a: Křižovatka, železnice, Suchý.",
			"Přišel čas na řešení šifry S4a: KALENDAR"
		], $this->getFutureMessages());
	}

	function testDeleteParallelPendingHints() {
		$_SESSION["settings"]["deleteParallelHints"] = 1;
		$this->progressRepo->create(1, 14);
		$this->assertEquals(["Vítejte na stanovišti 4. Jste tu 1. První tu byl tým Parta Nic v ".$this->dbNow()."."], CodeController::process("tabulka"));
		$this->assertEquals([
			"Přišel čas na nápovědu k šifře S4a: Křižovatka, železnice, Suchý.",
			"Přišel čas na nápovědu k šifře S4b: Jedničky a nuly.",
			"Přišel čas na řešení šifry S4a: KALENDAR"
		], $this->getFutureMessages());
		$this->assertEquals([
			"Úspěšně jste vyluštili šifru S4b. Jste 1. První ji vyluštil tým Parta Nic v ".$this->dbNow().". Máte vyluštěno celkem 2 šifer.",
			"Další stanoviště Cíl se nachází na kótě 1019 nad Pražskou boudou."
		], CodeController::process("skluzavka"));
		$this->assertEmpty($this->getFutureMessages());
	}

	function testInactiveGame() {
		$_SESSION["game_id"] = 2;
		(new Settings())->load();
		$this->expectException("\Sova\HttpException");
		$this->expectExceptionCode(403);
		CodeController::process("pralinka");
	}


	private function sendCode(int $teamId, string $code) {
		$_SESSION["team_id"] = $teamId;
		Progress::addFakeTime(1);
		return CodeController::process($code);
	}

	function testRank() {
		$this->db->execute("INSERT INTO team (team_id, game_id, name) VALUES (3, 1, 'abpopa')");

        $this->assertEquals(["Vítejte na stanovišti Start. Jste tu 1. První tu byl tým Parta Nic v {$this->dbNow(1)}."], $this->sendCode(1, "pralinka"));
        $this->assertEquals(["Vítejte na stanovišti Start. Jste tu 2. První tu byl tým Parta Nic v {$this->dbNow(1)}."], $this->sendCode(3, "pralinka"));
        $this->assertEquals(["Vítejte na stanovišti Start. Jste tu 3. První tu byl tým Parta Nic v {$this->dbNow(1)}."], $this->sendCode(2, "pralinka"));
		
		$this->assertEquals([
			"Úspěšně jste vyluštili šifru S1a. Jste 1. První ji vyluštil tým Redwool v {$this->dbNow(4)}. Máte vyluštěno celkem 1 šifer.",
			"Další stanoviště 1a se nachází na vrcholu Bílé hory."
		], $this->sendCode(2, "aberace"));
		$this->assertEquals([
			"Úspěšně jste vyluštili šifru S1a. Jste 2. První ji vyluštil tým Redwool v {$this->dbNow(4)}. Máte vyluštěno celkem 1 šifer.",
			"Další stanoviště 1a se nachází na vrcholu Bílé hory."
		], $this->sendCode(1, "aberace"));
		$this->assertEquals([
			"Úspěšně jste vyluštili šifru S1a. Jste 3. První ji vyluštil tým Redwool v {$this->dbNow(4)}. Máte vyluštěno celkem 1 šifer.",
			"Další stanoviště 1a se nachází na vrcholu Bílé hory."
		], $this->sendCode(3, "aberace"));
	}


	private static function stripTimes($ranks) {
		foreach ($ranks as &$rank) {
			unset($rank["last_cipher_time"]);
			if ($rank["finish_time"] != "-") {
				$rank["finish_time"] = "+";
			}
		}
		return $ranks;
	}
 
	function testRankTotal() {
		$this->db->execute("INSERT INTO team (team_id, game_id, name) VALUES (3, 1, 'abpopa')");
		
		$this->assertEquals([
			["name" => "abpopa", "solved" => 0, "finish_time" => "-"],
			["name" => "Parta Nic", "solved" => 0, "finish_time" => "-"],
			["name" => "Redwool", "solved" => 0, "finish_time" => "-"],
		], self::stripTimes($this->progressRepo->rankTotal(1)));

		$this->sendCode(1, "pralinka");
		$this->sendCode(2, "aberace");
		$this->assertEquals([
			["name" => "Redwool", "solved" => 1, "finish_time" => "-"],
			["name" => "abpopa", "solved" => 0, "finish_time" => "-"],
			["name" => "Parta Nic", "solved" => 0, "finish_time" => "-"],
		], self::stripTimes($this->progressRepo->rankTotal(1)));

		$this->sendCode(3, "aberace");
		$this->sendCode(1, "zabradli");
		$this->assertEquals([
			["name" => "Redwool", "solved" => 1, "finish_time" => "-"],
			["name" => "abpopa", "solved" => 1, "finish_time" => "-"],
			["name" => "Parta Nic", "solved" => 1, "finish_time" => "-"],
		], self::stripTimes($this->progressRepo->rankTotal(1)));
		
		$this->sendCode(1, "aberace");
		$this->assertEquals([
			["name" => "Parta Nic", "solved" => 2, "finish_time" => "-"],
			["name" => "Redwool", "solved" => 1, "finish_time" => "-"],
			["name" => "abpopa", "solved" => 1, "finish_time" => "-"],
		], self::stripTimes($this->progressRepo->rankTotal(1)));
		
		$this->sendCode(2, "zabradli");
		$this->assertEquals([
			["name" => "Parta Nic", "solved" => 2, "finish_time" => "-"],
			["name" => "Redwool", "solved" => 2, "finish_time" => "-"],
			["name" => "abpopa", "solved" => 1, "finish_time" => "-"],
		], self::stripTimes($this->progressRepo->rankTotal(1)));
		
		$this->sendCode(2, "podnos");
		$this->assertEquals([
			["name" => "Parta Nic", "solved" => 2, "finish_time" => "-"],
			["name" => "Redwool", "solved" => 2, "finish_time" => "-"],
			["name" => "abpopa", "solved" => 1, "finish_time" => "-"],
		], self::stripTimes($this->progressRepo->rankTotal(1)));
		
		$this->sendCode(2, "kobliha");
		$this->assertEquals([
			["name" => "Redwool", "solved" => 3, "finish_time" => "-"],
			["name" => "Parta Nic", "solved" => 2, "finish_time" => "-"],
			["name" => "abpopa", "solved" => 1, "finish_time" => "-"],
		], self::stripTimes($this->progressRepo->rankTotal(1)));
		
		$this->sendCode(3, "zabradli");
		$this->sendCode(3, "kybl");
		$this->sendCode(3, "kobliha");
		$this->sendCode(3, "medved");
		$this->assertEquals([
			["name" => "Redwool", "solved" => 3, "finish_time" => "-"],
			["name" => "abpopa", "solved" => 3, "finish_time" => "-"],
			["name" => "Parta Nic", "solved" => 2, "finish_time" => "-"],
		], self::stripTimes($this->progressRepo->rankTotal(1)));

		$this->sendCode(3, "priboj");
		$this->sendCode(3, "kalendar");
		$this->sendCode(3, "skluzavka");
		$this->sendCode(1, "kobliha");
		$this->sendCode(1, "priboj");
		$this->sendCode(1, "skluzavka");
		$this->sendCode(1, "salvej");
		$this->assertEquals([
			["name" => "abpopa", "solved" => 6, "finish_time" => "-"],
			["name" => "Parta Nic", "solved" => 5, "finish_time" => "-"],
			["name" => "Redwool", "solved" => 3, "finish_time" => "-"],
		], self::stripTimes($this->progressRepo->rankTotal(1)));

        $settings = new Settings();
        $settings->set(["locFinish" => 7, "locVisitMandatory" => true]);
        $settings->load();
		$this->assertEquals([
			["name" => "Parta Nic", "solved" => 5, "finish_time" => "+"],
			["name" => "abpopa", "solved" => 6, "finish_time" => "-"],
			["name" => "Redwool", "solved" => 3, "finish_time" => "-"],
		], self::stripTimes($this->progressRepo->rankTotal(1)));
	}
}
