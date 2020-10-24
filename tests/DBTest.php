<?php
namespace Sova;

use PHPUnit\Framework\TestCase;

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
			"SELECT * FROM user WHERE login = ? AND pswd = ?",
			array("joe", "secret")
		);
		$this->assertEquals(array("joe", "secret"), $this->db->getLastParams());
	}

	function testPrepareSQLScalarParam() {
		$this->db->execute(
			"SELECT * FROM user WHERE login = ?",
			"joe"
		);
		$this->assertEquals(array("joe"), $this->db->getLastParams());
	}

	function testPrepareSQLAssoc() {
		$this->db->execute(
			"SELECT * FROM user WHERE login = :login AND pswd = :pswd",
			array("login" => "joe", "pswd" => "secret")
		);
		$this->assertEquals(array("joe", "secret"), $this->db->getLastParams());
	}

	function testPrepareSQLAssocMoreParams() {
		$this->db->execute(
			"SELECT * FROM user WHERE login = :login AND pswd = :pswd",
			array("dummy" => 42, "login" => "joe", "pswd" => "secret")
		);
		$this->assertEquals(array("joe", "secret"), $this->db->getLastParams());
	}
	
	function testPrepareSQLAssocMissingParam() {
		$this->db->execute(
			"SELECT * FROM user WHERE login = :login AND pswd = :pswd",
			array("login" => "joe")
		);
		$this->assertEquals(array("joe", null), $this->db->getLastParams());
	}

	function testPrepareSQLAssocEmptyParam() {
		$this->db->execute(
			"SELECT * FROM user WHERE login = :login AND pswd = :pswd",
			array("login" => "joe", "pswd" => "")
		);
		$this->assertEquals(array("joe", null), $this->db->getLastParams());
	}

	function testExecute() {
		$this->db->exec(
			"CREATE TABLE test (
				id int(11) NOT NULL,
				name varchar(20) NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
		");
		$this->db->execute("INSERT INTO test VALUES (?, ?)", array(1, "one"));
		$this->db->execute("INSERT INTO test VALUES (:id, :name)", array("id" => 2, "name" => "two", "dummy" => "hello"));
		$this->db->execute("INSERT INTO test VALUES (:id, :name)", array("id" => 3));
		$rows = $this->db->aquery("SELECT * FROM test ORDER BY id");
		$this->assertEquals(
			array(
				array("id" => "1", "name" => "one"),
				array("id" => "2", "name" => "two"),
				array("id" => "3", "name" => null)
			),
			$rows
		);
		$this->db->exec("DROP TABLE test");
	}
}
