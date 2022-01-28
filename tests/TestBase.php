<?php
namespace Sova;

use PHPUnit\Framework\TestCase;
use Sova\Model\Settings;

class TestBase extends TestCase {

	protected $db;

	function setUp(): void {
		$this->db = DB::get();
		$this->db->execute("INSERT INTO superuser VALUES (?)", password_hash("nimda", PASSWORD_BCRYPT, ["cost" => 4]));
		$this->db->execute("INSERT INTO game VALUES (1, 'game1', ?)", password_hash("samara", PASSWORD_BCRYPT, ["cost" => 4]));
		$this->db->execute("INSERT INTO game VALUES (2, 'game2', ?)", password_hash("swordfish", PASSWORD_BCRYPT, ["cost" => 4]));
		$this->db->execute("INSERT INTO text (game_id, code, text) SELECT 1, code, text FROM text WHERE game_id IS NULL");
		$this->db->execute("INSERT INTO text (game_id, code, text) SELECT 2, code, text FROM text WHERE game_id IS NULL");
		$this->db->execute("INSERT INTO settings (game_id) VALUES (1)");
		$this->db->execute("INSERT INTO settings (game_id, gameStart, gameEnd) VALUES (2, DATE_ADD(NOW(), INTERVAL 1 HOUR), DATE_ADD(NOW(), INTERVAL 2 HOUR))");
		$_SESSION["game_id"] = 1;
		(new Settings())->load();
	}

	function tearDown(): void {
		$this->db->execute("DELETE FROM game");
		$_SESSION = array();
	}

	function dbNow($offset = 0) {
		return $this->db->equery("SELECT DATE_FORMAT(DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL ? MINUTE), '%H:%i') FROM DUAL", $offset);
	}
}
