<?php
namespace Sova\Model;

class Team {
	
	public static function login($team_id, $pswd) {
		$repo = new TeamRepo();
		$team = $repo->get($team_id, strtoupper(trim($pswd)));
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

	public static function logout() {
		session_destroy();
	}

	public static function logged() { return isset($_SESSION['team_id']); }
	public static function current() { return $_SESSION['team_id']; }
	public static function currentName() { return $_SESSION['team_name']; }
}
