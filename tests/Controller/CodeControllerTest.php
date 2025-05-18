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
		$this->assertEquals(["Získali jste céčko. Aktuálně máte 1 nevyužitých céček."], CodeController::process("buben"));
		$this->assertEquals(["Získali jste céčko. Aktuálně máte 2 nevyužitých céček."], CodeController::process("divizna"));
		$this->assertEquals(["Toto céčko jste již zadali."], CodeController::process("buben"));
		$_SESSION["team_id"] = 2;
		$this->assertEquals(["Získali jste céčko. Aktuálně máte 1 nevyužitých céček."], CodeController::process("buben"));
	}

	function testUnavailableLoc() {
		$this->assertEquals(["Neznámý kód: KYBL"], CodeController::process("kybl")); 
	}

	function testVisitLoc() {
		$this->assertEquals(["Vítejte na stanovišti Start. Máte 15 bodů."], CodeController::process("pralinka"));
		$this->assertEquals(["Tento kód stanoviště jste již zadali."], CodeController::process("pralinka"));
	}

	function testVisitLocRank() {
		Settings::set("showRank", true);
		$this->assertEquals([
			"Vítejte na stanovišti Start. Máte 15 bodů.", 
			"Jste tu 1. První tu byl tým Parta Nic v ".$this->dbNow()."."
		], CodeController::process("pralinka"));
	}

	function testVisitFinish() {
		Settings::set("locFinish", 3);
		CodeController::process("zabradli");
		$this->assertEquals(["Gratulujeme, jste v cíli! Celkem jste dosáhli 20 bodů."], CodeController::process("podnos"));
	}

	function testUnavailableCipher() {
		$this->assertEquals(["Neznámý kód: KOBLIHA"], CodeController::process("kobliha"));
		$this->progressRepo->create(1, 12);
		$this->assertEquals([
			"Úspěšně jste vyluštili šifru S2. Máte 30 bodů.",
			"Další stanoviště Turniket se nachází na Pardubických boudách, hledej orga."
		], CodeController::process("kobliha"));
	}

	function testSolveCipher() {
		$this->assertEquals([
			"Úspěšně jste vyluštili šifru S1. Máte 30 bodů.",
			"Další stanoviště 1a se nachází na vrcholu Bílé hory."
		], CodeController::process("aberace"));
		$this->assertEquals(["Toto řešení šifry jste již zadali."], CodeController::process("aberace"));
	}

	function testSolveCipherRank() {
		Settings::set("showRank", true);
		$this->assertEquals([
			"Úspěšně jste vyluštili šifru S1. Máte 30 bodů.",
			"Jste 1. První ji vyluštil tým Parta Nic v {$this->dbNow()}.", 
			"Další stanoviště 1a se nachází na vrcholu Bílé hory."
		], CodeController::process("aberace"));
	}

	function testSolveActivityRank() {
		Settings::set("showRank", true);
		$this->assertEquals([
			"Úspěšně jste zvládli aktivitu A1. Máte 20 bodů.",
			"Jste 1. První ji dal tým Parta Nic v {$this->dbNow()}.", 
			"Další stanoviště 1b se nachází na vrcholu Černé hory."
		], CodeController::process("zabradli"));
	}

	function testSolveCipherWithLink() {
		$this->db->execute("UPDATE loc SET coord_lat = 50.08, coord_lon = 14.32 WHERE point_id = 2");
		Settings::set("linkMapyCz", "turisticka");
		$this->assertEquals([
			"Úspěšně jste vyluštili šifru S1. Máte 30 bodů.",
			'Další stanoviště 1a se nachází na vrcholu Bílé hory, <a href="https://mapy.cz/turisticka?q=50.0800000N%2014.3200000E">50.0800000N 14.3200000E</a>.'
		], CodeController::process("aberace"));
	}

	private function sendCode(int $teamId, string $code) {
		$_SESSION["team_id"] = $teamId;
		Progress::addFakeTime(1);
		return CodeController::process($code);
	}

	function testRank() {
		Settings::set("showRank", true);
		$this->db->execute("INSERT INTO team (team_id, game_id, name) VALUES (3, 1, 'abpopa')");

		$this->assertEquals([
			"Vítejte na stanovišti Start. Máte 15 bodů.", 
			"Jste tu 1. První tu byl tým Parta Nic v {$this->dbNow(1)}."
		], $this->sendCode(1, "pralinka"));
		$this->assertEquals([
			"Vítejte na stanovišti Start. Máte 15 bodů.", 
			"Jste tu 2. První tu byl tým Parta Nic v {$this->dbNow(1)}."
		], $this->sendCode(3, "pralinka"));
		$this->assertEquals([
			"Vítejte na stanovišti Start. Máte 15 bodů.", 
			"Jste tu 3. První tu byl tým Parta Nic v {$this->dbNow(1)}."
		], $this->sendCode(2, "pralinka"));
		
		$this->assertEquals([
			"Úspěšně jste vyluštili šifru S1. Máte 45 bodů.",
			"Jste 1. První ji vyluštil tým Redwool v {$this->dbNow(4)}.", 
			"Další stanoviště 1a se nachází na vrcholu Bílé hory."
		], $this->sendCode(2, "aberace"));
		$this->assertEquals([
			"Úspěšně jste vyluštili šifru S1. Máte 45 bodů.",
			"Jste 2. První ji vyluštil tým Redwool v {$this->dbNow(4)}.", 
			"Další stanoviště 1a se nachází na vrcholu Bílé hory."
		], $this->sendCode(1, "aberace"));
		$this->assertEquals([
			"Úspěšně jste vyluštili šifru S1. Máte 45 bodů.",
			"Jste 3. První ji vyluštil tým Redwool v {$this->dbNow(4)}.", 
			"Další stanoviště 1a se nachází na vrcholu Bílé hory."
		], $this->sendCode(3, "aberace"));
	}


	private static function stripTimes($ranks) {
		foreach ($ranks as &$rank) {
			unset($rank["last_cipher_time"]);
			unset($rank["imunity"]);
			if ($rank["finish_time"] != "-") {
				$rank["finish_time"] = "+";
			}
		}
		return $ranks;
	}
 
	function testRankTotal() {
		Settings::set("locVisitMandatory", true);
		$this->db->execute("INSERT INTO team (team_id, game_id, name) VALUES (3, 1, 'abpopa')");
		
		$this->assertEquals([
			["name" => "abpopa", "points" => 0, "finish_time" => "-"],
			["name" => "Parta Nic", "points" => 0, "finish_time" => "-"],
			["name" => "Redwool", "points" => 0, "finish_time" => "-"],
		], self::stripTimes($this->progressRepo->rankTotal(1)));

		$this->sendCode(2, "pralinka");
		$this->assertEquals([
			["name" => "Redwool", "points" => 15, "finish_time" => "-"],
			["name" => "abpopa", "points" => 0, "finish_time" => "-"],
			["name" => "Parta Nic", "points" => 0, "finish_time" => "-"],
		], self::stripTimes($this->progressRepo->rankTotal(1)));

		$this->sendCode(1, "pralinka");
		$this->sendCode(3, "pralinka");
		$this->sendCode(3, "aberace");
		$this->sendCode(1, "zabradli");
		$this->assertEquals([
			["name" => "abpopa", "points" => 45, "finish_time" => "-"],
			["name" => "Parta Nic", "points" => 35, "finish_time" => "-"],
			["name" => "Redwool", "points" => 15, "finish_time" => "-"],
		], self::stripTimes($this->progressRepo->rankTotal(1)));
		
		$this->sendCode(2, "aberace");
		$this->assertEquals([
			["name" => "abpopa", "points" => 45, "finish_time" => "-"],
			["name" => "Redwool", "points" => 45, "finish_time" => "-"],
			["name" => "Parta Nic", "points" => 35, "finish_time" => "-"],
		], self::stripTimes($this->progressRepo->rankTotal(1)));
		
		$this->sendCode(2, "zabradli");
		$this->sendCode(2, "kybl");
		$this->sendCode(2, "podnos");
		$this->sendCode(2, "kobliha");
		$this->assertEquals([
			["name" => "Redwool", "points" => 95, "finish_time" => "-"],
			["name" => "abpopa", "points" => 45, "finish_time" => "-"],
			["name" => "Parta Nic", "points" => 35, "finish_time" => "-"],
		], self::stripTimes($this->progressRepo->rankTotal(1)));
		
		$this->sendCode(1, "aberace");
		$this->sendCode(1, "kybl");
		$this->sendCode(1, "podnos");
		$this->sendCode(1, "kobliha");
		$this->sendCode(1, "medved");
		$this->sendCode(1, "zidle");
		$this->sendCode(1, "priboj");
		$this->sendCode(1, "tabulka");
		$this->sendCode(1, "kalendar");
		$this->sendCode(1, "skluzavka");
		$this->assertEquals([
			["name" => "Parta Nic", "points" => 185, "finish_time" => "-"],
			["name" => "Redwool", "points" => 95, "finish_time" => "-"],
			["name" => "abpopa", "points" => 45, "finish_time" => "-"],
		], self::stripTimes($this->progressRepo->rankTotal(1)));

		$this->sendCode(3, "zabradli");
		$this->sendCode(3, "kybl");
		$this->sendCode(3, "podnos");
		$this->sendCode(3, "kobliha");
		$this->sendCode(3, "medved");
		$this->sendCode(3, "zidle");
		$this->sendCode(3, "priboj");
		$this->sendCode(3, "tabulka");
		$this->sendCode(3, "kalendar");
		$this->sendCode(3, "salvej");
		$this->assertEquals([
			["name" => "Parta Nic", "points" => 185, "finish_time" => "-"],
			["name" => "abpopa", "points" => 185, "finish_time" => "-"],
			["name" => "Redwool", "points" => 95, "finish_time" => "-"],
		], self::stripTimes($this->progressRepo->rankTotal(1)));

		(new Settings())->save(["locFinish" => 7]);
		$this->assertEquals([
			["name" => "abpopa", "points" => 185, "finish_time" => "+"],
			["name" => "Parta Nic", "points" => 185, "finish_time" => "-"],
			["name" => "Redwool", "points" => 95, "finish_time" => "-"],
		], self::stripTimes($this->progressRepo->rankTotal(1)));
	}

}
