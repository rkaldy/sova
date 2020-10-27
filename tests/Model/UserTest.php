<?php
namespace Sova\Model;

use Sova\TestBase;

class UserTest extends TestBase {

	protected $user;

	function setUp(): void {
		parent::setUp();
		$this->user = new User();
	}

	function testLogin() {
		$this->assertTrue($this->user->login("user", "swordfish"));
		$this->assertTrue(User::logged());
		$this->assertEquals(2, User::current());
		$this->assertEquals("user", User::currentName());
		$this->assertFalse(User::super());
	}

	function testLoginSuperuser() {
		$this->assertTrue($this->user->login("admin", "nimda"));
		$this->assertTrue(User::logged());
		$this->assertTrue(User::super());
	}

	function testBadLogin() {
		$this->assertFalse($this->user->login("bad", "swordfish"));
		$this->assertFalse($this->user->login("admin", "bad"));
		$this->assertFalse(User::logged());
	}

}
