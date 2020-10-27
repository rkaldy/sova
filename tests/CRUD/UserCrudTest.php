<?php
namespace Sova\CRUD;

use Sova\Repo\UserRepo;
use Sova\DBException;

class UserCrudTest extends CrudTestBase {

	protected $repo;

	function setUp(): void {
		parent::setUp();
		$this->repo = new UserRepo();
	}

	function testCreate() {
		$this->create(array("login" => "bigbrother", "pswd" => "bigpass"));
		$this->assertCount(3, $this->list());
		$user = $this->repo->get("bigbrother");
		$this->assertStringStartsWith("$2y$", $user["pswd"]);
		$this->assertTrue(password_verify("bigpass", $user["pswd"]));
	}

	function testCreateEmptyPassword() {
		try {
			$this->create(array("login" => "bigbrother", "pswd" => ""));
			$this->fail("Should throw Exception");
		} catch (\Exception $ex) {
			$this->assertTrue(true);
		}
	}

	function testUpdate() {
		$user = $this->list()[0];
		$user["login"] = "bigbrother";
		$user["pswd"] = "bigpass";
		$this->update($user);
		$this->assertEquals(array(
			array("user_id" => "1", "login" => "bigbrother"),
			array("user_id" => "2", "login" => "user")
		), $this->list());
		$user = $this->repo->get("bigbrother");
		$this->assertStringStartsWith("$2y$", $user["pswd"]);
		$this->assertTrue(password_verify("bigpass", $user["pswd"]));
	}

	function testUpdateEmptyPassword() {
		$user = $this->list()[0];
		$user["login"] = "bigbrother";
		$user["pswd"] = "";
		$this->update($user);
		$user = $this->repo->get("bigbrother");
		$this->assertTrue(password_verify("nimda", $user["pswd"]));
	}

	function testDelete() {
		$this->db->execute("INSERT INTO user VALUES (3, 'bigbrother', 'bigpass')");
		$this->delete(array("user_id" => 3));
		$this->assertEquals(array(
			array("user_id" => "1", "login" => "admin"),
			array("user_id" => "2", "login" => "user")
		), $this->list());
	}

	function testDeleteGameOwner() {
		try {
			$this->delete(array("user_id" => 2));
			$this->fail("Should throw Exception");
		} catch (DBException $e) {
			$this->assertEquals(1451, $e->getCode());
		}
	}

	function testDeleteSuperuser() {
		try {
			$this->delete(array("user_id" => 1));
			$this->fail("Should throw Exception");
		} catch (\Exception $e) {
			$this->assertTrue(true);
		}
	}
}
