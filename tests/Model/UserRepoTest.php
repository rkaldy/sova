<?php
namespace Sova\Model;

use Sova\TestBase;
use Sova\DBException;

class UserRepoTest extends TestBase {

	protected $repo;

	function setUp(): void {
		parent::setUp();
		$this->repo = new UserRepo();
	}

	function testCreate() {
		$this->repo->create(array("login" => "bigbrother", "pswd" => "bigpass"));
		$users = $this->repo->list();
		$this->assertCount(3, $this->repo->list());
		$user = $this->repo->get("bigbrother");
		$this->assertTrue(password_verify("bigpass", $user["pswd"]));
	}

	function testCreateEmptyPassword() {
		try {
			$this->repo->create(array("login" => "bigbrother", "pswd" => ""));
			$this->fail("Should throw Exception");
		} catch (\Exception $ex) {
			$this->assertTrue(true);
		}
	}

	function testUpdate() {
		$user = $this->repo->list()[0];
		$user["login"] = "bigbrother";
		$user["pswd"] = "bigpass";
		$this->repo->update($user);
		$this->assertEquals(array(
			array("user_id" => "1", "login" => "bigbrother"),
			array("user_id" => "2", "login" => "user")
		), $this->repo->list());
		$user = $this->repo->get("bigbrother");
		$this->assertTrue(password_verify("bigpass", $user["pswd"]));
	}

	function testUpdateEmptyPassword() {
		$user = $this->repo->list()[0];
		$user["login"] = "bigbrother";
		$user["pswd"] = "";
		$this->repo->update($user);
		$user = $this->repo->get("bigbrother");
		$this->assertTrue(password_verify("nimda", $user["pswd"]));
	}

	function testDelete() {
		$users = $this->repo->list();
		$this->repo->delete($users[0]);
		$this->assertEquals(array(
			array("user_id" => "2", "login" => "user")
		), $this->repo->list());
	}

	function testDeleteSuperuser() {
		try {
			$this->repo->delete(array("user_id" => 1, "login" => "admin"));
			$this->fail("Should throw Exception");
		} catch (\Exception $e) {
			$this->assertTrue(true);
		}
	}
}
