<?php
namespace Sova\Model;

class Team extends ModelBase {

	public function prepare(array &$team) {
		if (Game::selected()) {
			$team["game_id"] = Game::current();
		}
		$this->prepareBooleans($team, ["accomodation", "paid"]);
	}

	public function list(int $from = null, $limit = null): array {
		$teams = $this->repo->list(Game::current());
		foreach ($teams as &$team) {
			$team["fee"] = (int)Settings::get("gamePrice") + (int)Settings::get("tshirtPrice") * (int)$team["tshirt"];
			if ($team["accomodation"] && !empty($team["members"])) {
				$team["fee"] += (int)Settings::get("accomodationPrice") * count($team["members"]);
			}
		}
		return $teams;
	}

	public function create(array &$team) {
		$this->prepare($team);
		(new Code())->prepare($team["pswd"]);
		$this->repo->create($team);
	}

	public function login(int $team_id, string $pswd): bool {
		$team = $this->repo->login($team_id, strtoupper(trim($pswd)));
		if (isset($team)) {
			$_SESSION['game_id'] = $team['game_id'];
			$_SESSION['game_name'] = $team['game_name'];
			$_SESSION['team_id'] = $team['team_id'];
			$_SESSION['team_name'] = $team['name'];
			(new Settings())->load();
			return true;
		} else {
			return false;
		}
	}

	public function webLogin(int $team_id, string $pswd) {
		return $this->repo->login($team_id, strtoupper(trim($pswd)));
	}

	public static function logout() { 
        $_SESSION = [];
        session_destroy(); 
	}


	public function points(): int {
		return $this->repo->points(self::current());
	}

	public function addPoints(int $add) {
		$this->repo->addPoints(self::current(), $add);
	}


	public static function logged() 	 { return isset($_SESSION['team_id']); }
	public static function current() 	 { return $_SESSION['team_id']; }
	public static function currentName() { return $_SESSION['team_name']; }
}
