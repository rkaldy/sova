<?php
namespace Sova\Model;

class Game {

	public static function setOwnedGame($gameId = null) {
		$repo = new GameRepo();
		if (isset($gameId)) {
			$games = $repo->get($gameId, User::current());
		} else {
			$games = $repo->getOwned(User::current());
		}
		if (count($games) == 0) {
			return 0;
		} else if (count($games) == 1) {
			$_SESSION["game_id"] = $games[0]["game_id"];
			$_SESSION["game_name"] = $games[0]["name"];
			return 1;
		} else {
			return $games;
		}
	}

	public static function selected() { return isset($_SESSION["game_id"]); }
	public static function current() { return $_SESSION["game_id"]; }
	public static function currentName() { return $_SESSION["game_name"]; }
}
