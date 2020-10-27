<?php
namespace Sova\Model;

class Team extends ModelBase {

	public function prepare(array &$team) {
		$team["game_id"] = Game::current();
		(new Code())->prepare($team["pswd"]);
	}
	
	public function login(int $team_id, string $pswd) {
		$team = $this->repo->get($team_id, strtoupper(trim($pswd)));
		if (isset($team)) {
			$_SESSION['game_id'] = $team['game_id'];
			$_SESSION['game_name'] = $team['game_name'];
			$_SESSION['team_id'] = $team['team_id'];
			$_SESSION['team_name'] = $team['name'];
			return true;
		} else {
			return false;
		}
	}

	public static function logout() 	 { session_destroy(); }

	public static function logged() 	 { return isset($_SESSION['team_id']); }
	public static function current() 	 { return $_SESSION['team_id']; }
	public static function currentName() { return $_SESSION['team_name']; }
}
