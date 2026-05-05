<?php
namespace Sova\Model;

use Sova\Repo\GameRepo;

class Game extends ModelBase {

	const CURRENT = 1;
	const PAST = 2;
	const FUTURE = 3;


	public function prepare(array &$game) {
		if (empty($game["pswd"])) {
			unset($game["pswd"]);
		} else {
			$game["pswd"] = password_hash($game["pswd"], PASSWORD_BCRYPT);
		}
	}


	public function login(string $login, string $pswd) {
		if ($login == "superuser") {
			if (password_verify($pswd, $this->repo->superuserPassword())) {
				$_SESSION["superuser"] = 1;
				return true;;
			} else {
				return false;
			}
		}

		$game = $this->repo->get($login);
		if (!isset($game) || !password_verify($pswd, $game["pswd"])) {
			return false;
		}
		$_SESSION["game_id"] = $game["game_id"];
		$_SESSION["game_name"] = $game["name"];
		(new Settings())->load();
		return true;
	}


	public function changeCurrentPassword(string $pswd) {
		if (!empty($pswd)) {
			$this->repo->updatePassword(self::current(), password_hash($pswd, PASSWORD_BCRYPT));
		}
	}


	public static function logout() {
		$_SESSION = [];
		session_destroy();
	}


	public static function state() {
		$now = time();
		if (Settings::isset("gameStartTimestamp") && $now < Settings::get("gameStartTimestamp")) {
			return self::FUTURE;
		} else if (Settings::isset("gameEndTimestamp") && $now > Settings::get("gameEndTimestamp")) {
			return self::PAST;
		} else {
			return self::CURRENT;
		}
	}


	public static function selected() 	 { return isset($_SESSION["game_id"]); }
	public static function current() 	 { return isset($_SESSION["game_id"]) ? $_SESSION["game_id"] : null; }
	public static function currentName() { return $_SESSION["game_name"]; }
	public static function superuser()	 { return isset($_SESSION["superuser"]); }
}
