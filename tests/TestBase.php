<?php
namespace Sova;

use PHPUnit\Framework\TestCase;
use Sova\Model\Settings;

class TestBase extends TestCase {

	protected $db;

	function setUp(): void {
		$this->db = DB::get();
		$this->db->execute("INSERT INTO user VALUES (1, 'admin', ?)", password_hash("nimda", PASSWORD_BCRYPT, array("cost" => 4)));
		$this->db->execute("INSERT INTO user VALUES (2, 'user', ?)", password_hash("swordfish", PASSWORD_BCRYPT, array("cost" => 4)));
		$this->db->execute("INSERT INTO game VALUES (1, 2, 'game1', '2020-01-01', '2020-01-02')");
		$this->db->execute("INSERT INTO game VALUES (2, 2, 'game2', '2020-02-01', '2020-02-02')");
		$this->db->execute("INSERT INTO text (game_id, code, text) SELECT 1, code, text FROM text WHERE game_id IS NULL");
		$this->db->execute("INSERT INTO text (game_id, code, text) SELECT 2, code, text FROM text WHERE game_id IS NULL");
		$this->db->execute("INSERT INTO settings (game_id) VALUES (1)");
		$this->db->execute("INSERT INTO settings (game_id) VALUES (2)");
		$_SESSION["game_id"] = 1;
		(new Settings())->load();
	}

	function tearDown(): void {
		$this->db->execute("DELETE FROM game");
		$this->db->execute("DELETE FROM user");
		$_SESSION = array();
	}

	function dbNow($offset = 0) {
		return $this->db->equery("SELECT DATE_FORMAT(DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL ? MINUTE), '%H:%i') FROM DUAL", $offset);
	}
}
