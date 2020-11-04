<?php
namespace Sova\Controller;

use Sova\GameTestBase;
use Sova\Model\Team;
use Sova\Model\Message;
use Sova\Repo\ProgressRepo;

class CodeControllerTest extends GameTestBase {

	protected $progresRepo;

	function setUp(): void {
		parent::setUp();
		$this->db->exec("ALTER TABLE progress CHANGE `time` `time` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP");
		$this->db->exec("ALTER TABLE message CHANGE `time` `time` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP");
		$this->progressRepo = new ProgressRepo();
	}

	function tearDown(): void {
		parent::tearDown();
		$this->db->exec("ALTER TABLE progress CHANGE `time` `time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");
		$this->db->exec("ALTER TABLE message CHANGE `time` `time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");
	}

	function getFutureMessages() {
		$messages = [];
		$stmt = $this->db->query("SELECT text FROM message WHERE team_id = ? AND direction = ? AND time > NOW(6) ORDER BY time", Team::current(), Message::TO_TEAM);
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
		$this->assertEquals("Dostali jste se na stanoviště Start. Jste tu 1. První tu byl tým Parta Nic v ".$this->dbNow().".", CodeController::process("pralinka"));
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
		$this->progressRepo->create(1, 12);
		$this->assertEquals("Úspěšně jste vyluštili šifru S2. Jste 1. První ji vyluštil tým Parta Nic v ".$this->dbNow().". Poloha dalšího stanoviště je: Pardubické boudy, hledej orga.", CodeController::process("kobliha"));
	}

	function testSolveCipher() {
		$this->assertEquals("Úspěšně jste vyluštili šifru S1a. Jste 1. První ji vyluštil tým Parta Nic v ".$this->dbNow().". Poloha dalšího stanoviště je: Vrchol Bílé hory.", CodeController::process("aberace"));
		$this->assertEquals("Toto řešení šifry jste již zadali.", CodeController::process("aberace"));
	}

	function testDeletePendingHints() {
		$this->progressRepo->create(1, 13);
		$this->assertEquals("Dostali jste se na stanoviště Turniket. Jste tu 1. První tu byl tým Parta Nic v ".$this->dbNow().".", CodeController::process("medved"));
		$this->assertEquals([
			"Přišel čas na nápovědu k šifře S3a: Křižovatka, železnice, Suchý.",
			"Přišel čas na nápovědu k šifře S3b: Jedničky a nuly.",
			"Přišel čas na řešení šifry S3a: KALENDAR"
		], $this->getFutureMessages());
		$this->assertEquals("Úspěšně jste vyluštili šifru S3b. Jste 1. První ji vyluštil tým Parta Nic v ".$this->dbNow().". Poloha dalšího stanoviště je: Kóta 1019 nad Pražskou boudou.", CodeController::process("skluzavka"));
		$this->assertEmpty($this->getFutureMessages());
	}


	private function sendCode(int $teamId, string $code) {
		$_SESSION["team_id"] = $teamId;
		return CodeController::process($code);
	}

	function testRank() {
		$this->db->execute("INSERT INTO team (team_id, game_id, name) VALUES (3, 1, 'abpopa')");

		$now = $this->dbNow();
        $this->assertEquals("Dostali jste se na stanoviště Start. Jste tu 1. První tu byl tým Parta Nic v $now.", $this->sendCode(1, "pralinka"));
        $this->assertEquals("Dostali jste se na stanoviště Start. Jste tu 2. První tu byl tým Parta Nic v $now.", $this->sendCode(3, "pralinka"));
        $this->assertEquals("Dostali jste se na stanoviště Start. Jste tu 3. První tu byl tým Parta Nic v $now.", $this->sendCode(2, "pralinka"));
		
		$now = $this->dbNow();
		$this->assertEquals("Úspěšně jste vyluštili šifru S1a. Jste 1. První ji vyluštil tým Redwool v $now. Poloha dalšího stanoviště je: Vrchol Bílé hory.", $this->sendCode(2, "aberace"));
		$this->assertEquals("Úspěšně jste vyluštili šifru S1a. Jste 2. První ji vyluštil tým Redwool v $now. Poloha dalšího stanoviště je: Vrchol Bílé hory.", $this->sendCode(1, "aberace"));
		$this->assertEquals("Úspěšně jste vyluštili šifru S1a. Jste 3. První ji vyluštil tým Redwool v $now. Poloha dalšího stanoviště je: Vrchol Bílé hory.", $this->sendCode(3, "aberace"));
	}


	private static function stripTimes($ranks) {
		foreach ($ranks as &$rank) {
			unset($rank["last_cipher_time"]);
		}
		return $ranks;
	}

	function testRankTotal() {
		$this->db->execute("INSERT INTO team (team_id, game_id, name) VALUES (3, 1, 'abpopa')");
		
		$this->sendCode(3, "aberace");
		$this->sendCode(2, "aberace");
		$this->sendCode(1, "zabradli");
		$this->assertEquals([
			["name" => "abpopa", "solved" => 1, "last_loc" => "Start"],
			["name" => "Redwool", "solved" => 1, "last_loc" => "Start"],
			["name" => "Parta Nic", "solved" => 1, "last_loc" => "Start"]
		], self::stripTimes($this->progressRepo->rankTotal(1)));
		
		$this->sendCode(1, "aberace");
		$this->assertEquals([
			["name" => "Parta Nic", "solved" => 2, "last_loc" => "Start"],
			["name" => "abpopa", "solved" => 1, "last_loc" => "Start"],
			["name" => "Redwool", "solved" => 1, "last_loc" => "Start"]
		], self::stripTimes($this->progressRepo->rankTotal(1)));
		
		$this->sendCode(2, "zabradli");
		$this->assertEquals([
			["name" => "Parta Nic", "solved" => 2, "last_loc" => "Start"],
			["name" => "Redwool", "solved" => 2, "last_loc" => "Start"],
			["name" => "abpopa", "solved" => 1, "last_loc" => "Start"]
		], self::stripTimes($this->progressRepo->rankTotal(1)));
		
		$this->sendCode(2, "podnos");
		$this->assertEquals([
			["name" => "Parta Nic", "solved" => 2, "last_loc" => "Start"],
			["name" => "Redwool", "solved" => 2, "last_loc" => "Černá hora"],
			["name" => "abpopa", "solved" => 1, "last_loc" => "Start"]
		], self::stripTimes($this->progressRepo->rankTotal(1)));
		
		$this->sendCode(2, "kobliha");
		$this->assertEquals([
			["name" => "Redwool", "solved" => 3, "last_loc" => "Bílá hora"],
			["name" => "Parta Nic", "solved" => 2, "last_loc" => "Start"],
			["name" => "abpopa", "solved" => 1, "last_loc" => "Start"]
		], self::stripTimes($this->progressRepo->rankTotal(1)));
		
		$this->sendCode(3, "zabradli");
		$this->sendCode(3, "kybl");
		$this->sendCode(3, "kobliha");
		$this->sendCode(3, "medved");
		$this->assertEquals([
			["name" => "Redwool", "solved" => 3, "last_loc" => "Bílá hora"],
			["name" => "abpopa", "solved" => 3, "last_loc" => "Turniket"],
			["name" => "Parta Nic", "solved" => 2, "last_loc" => "Start"]
		], self::stripTimes($this->progressRepo->rankTotal(1)));
		
		$this->sendCode(3, "kobliha");
	}
}
