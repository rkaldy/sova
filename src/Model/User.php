<?php
namespace Sova\Model;

class User {
	
	public static function login($login, $pswd) {
		$repo = new UserRepo();
		$user = $repo->get($login);
		if (!isset($user) || !password_verify($pswd, $user["pswd"])) {
			return false;
		}
		$_SESSION["user_id"] = $user["user_id"];
		$_SESSION["user_name"] = $user["login"];
		return true;
	}

	public static function logout() {
		$_SESSION = array();
	}

	public static function logged() { return isset($_SESSION["user_id"]); }
	public static function current() { return $_SESSION["user_id"]; }
	public static function currentName() { return $_SESSION["user_name"]; }
	public static function super() { return User::logged() && User::current() == 1; }
}
