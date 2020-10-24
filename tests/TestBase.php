<?php
namespace Sova;

use PHPUnit\Framework\TestCase;

class TestBase extends TestCase {

	protected $db;

	function setUp(): void {
		$this->db = DB::get();
		$this->db->execute("INSERT INTO user VALUES (1, 'admin', ?)", password_hash("nimda", PASSWORD_BCRYPT, array("cost" => 4)));
		$this->db->execute("INSERT INTO user VALUES (2, 'user', ?)", password_hash("swordfish", PASSWORD_BCRYPT, array("cost" => 4)));
		$this->db->execute("INSERT INTO game VALUES (1, 2, 'game1', '2020-01-01', '2020-01-02')");
		$this->db->execute("INSERT INTO game VALUES (2, 2, 'game2', '2020-02-01', '2020-02-02')");
		$_SESSION["game_id"] = 1;
	}

	function tearDown(): void {
		$this->db->execute("DELETE FROM game");
		$this->db->execute("DELETE FROM user");
		$_SESSION = array();
	}
}
