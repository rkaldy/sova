<?php
namespace Sova\Model;

use Sova\Repo\UserRepo;

class User extends ModelBase {

	public function prepare(array &$user) {
		if (empty($user["pswd"])) {
			unset($user["pswd"]);
		} else {
			$user["pswd"] = password_hash($user["pswd"], PASSWORD_BCRYPT);
		}
	}

	public function login(string $login, string $pswd) {
		$user = $this->repo->get($login);
		if (!isset($user) || !password_verify($pswd, $user["pswd"])) {
			return false;
		}
		$_SESSION["user_id"] = $user["user_id"];
		$_SESSION["user_name"] = $user["login"];
		return true;
	}

	
	public static function logout() { 
        $_SESSION = [];
        session_destroy(); 
    }

	public static function logged() 	 { return isset($_SESSION["user_id"]); }
	public static function current() 	 { return $_SESSION["user_id"]; }
	public static function currentName() { return $_SESSION["user_name"]; }
	public static function super() 		 { return User::logged() && User::current() == 1; }
}
