<?php
namespace Sova\Model;

use Sova\Repo\GameRepo;

class Game extends ModelBase {

    const CURRENT = 1;
    const PAST = 2;
    const FUTURE = 3;

	public function setCurrentGame($gameId) {
        $game = $this->repo->get($gameId);
        if ($game["owner_id"] != User::current()) {
            return false;
        }
        $_SESSION["game_id"] = $game["game_id"];
        $_SESSION["game_name"] = $game["name"];
        (new Settings())->load();
        return true;
    }

    public function getOwnedGames() {
		return $this->repo->getOwned(User::current());
    }

    public function getIdByName(string $name) {
        return $this->repo->getIdByName($name, User::current());
	}

	public static function state() {
		$now = time();
		if (Settings::isset("gameStartTimestamp") && $now < Settings::value("gameStartTimestamp")) {
			return self::FUTURE;
		} else if (Settings::isset("gameEndTimestamp") && $now > Settings::value("gameEndTimestamp")) {
			return self::PAST;
		} else {
			return self::CURRENT;
		}
	}

	public static function selected() 	 { return isset($_SESSION["game_id"]); }
	public static function current() 	 { return $_SESSION["game_id"]; }
	public static function currentName() { return $_SESSION["game_name"]; }
}
