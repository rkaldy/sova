<?php
namespace Sova\App;

use PHPUnit\Framework\TestCase;
use Sova\DB;

class DBTest extends TestCase {

	protected $db;

	function setUp(): void {
		$this->db = DB::get();
	}

	function tearDown(): void {
		$this->db->execute("DROP TABLE IF EXISTS test"); 
	}

	function testPrepareSQLSimple() {
		$this->db->execute(
			"SELECT * FROM game WHERE name = ? AND pswd = ?",
			["lavina", "secret"]
		);
		$this->assertEquals(["lavina", "secret"], $this->db->getLastParams());
	}

	function testPrepareSQLScalarParam() {
		$this->db->execute(
			"SELECT * FROM game WHERE name = ?",
			"lavina"
		);
		$this->assertEquals(["lavina"], $this->db->getLastParams());
	}

	function testPrepareSQLAssoc() {
		$this->db->execute(
			"SELECT * FROM game WHERE name = :name AND pswd = :pswd",
			["name" => "lavina", "pswd" => "secret"]
		);
		$this->assertEquals(["lavina", "secret"], $this->db->getLastParams());
	}

	function testPrepareSQLAssocMoreParams() {
		$this->db->execute(
			"SELECT * FROM game WHERE name = :name AND pswd = :pswd",
			["dummy" => 42, "name" => "lavina", "pswd" => "secret"]
		);
		$this->assertEquals(["lavina", "secret"], $this->db->getLastParams());
	}
	
	function testPrepareSQLAssocMissingParam() {
		$this->db->execute(
			"SELECT * FROM game WHERE name = :name AND pswd = :pswd",
			["name" => "lavina"]
		);
		$this->assertEquals(["lavina", null], $this->db->getLastParams());
	}

	function testPrepareSQLAssocEmptyParam() {
		$this->db->execute(
			"SELECT * FROM game WHERE name = :name AND pswd = :pswd",
			["name" => "lavina", "pswd" => ""]
		);
		$this->assertEquals(["lavina", null], $this->db->getLastParams());
	}

	function testExecute() {
		$this->db->exec(
			"CREATE TABLE test (
				id int(11) NOT NULL,
				name varchar(20) NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
		");
		$this->db->execute("INSERT INTO test VALUES (?, ?)", [1, "one"]);
		$this->db->execute("INSERT INTO test VALUES (:id, :name)", ["id" => 2, "name" => "two", "dummy" => "hello"]);
		$this->db->execute("INSERT INTO test VALUES (:id, :name)", ["id" => 3]);
		$rows = $this->db->aquery("SELECT * FROM test ORDER BY id");
		$this->assertEquals(
			array(
				["id" => "1", "name" => "one"],
				["id" => "2", "name" => "two"],
				["id" => "3", "name" => null]
			),
			$rows
		);
		$this->db->exec("DROP TABLE test");
	}
}
