<?php
namespace Sova\Model;

use Sova\TestBase;

class UserTest extends TestBase {

	function testLogin() {
		$this->assertTrue(User::login("user", "swordfish"));
		$this->assertTrue(User::logged());
		$this->assertEquals(2, User::current());
		$this->assertEquals("user", User::currentName());
		$this->assertFalse(User::super());
	}

	function testLoginSuperuser() {
		$this->assertTrue(User::login("admin", "nimda"));
		$this->assertTrue(User::logged());
		$this->assertTrue(User::super());
	}

	function testBadLogin() {
		$this->assertFalse(User::login("bad", "swordfish"));
		$this->assertFalse(User::login("admin", "bad"));
		$this->assertFalse(User::logged());
	}

}
