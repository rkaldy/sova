<?php
namespace Sova\Model;

use Sova\Repo\LocRepo;

class Progress extends ModelBase {

	protected static $fakeTimeOffset = 0;

	public function create(array &$obj) {
		return $this->repo->create(Team::current(), $obj["point_id"], self::$fakeTimeOffset);
	}

	public function getRank(array $obj) {
		$rank = $this->repo->rankAtPoint(Team::current(), $obj["point_id"]);
		$firstTeam = $this->repo->firstTeamAtPoint($obj["point_id"]);
		return [$rank, $firstTeam["name"], $firstTeam["time"]];
	}

	public function rankTotal() {
		return $this->repo->rankTotal(Game::current());
	}

	public function locStatus() {
		$locs = (new LocRepo())->listAsArray(Game::current());
		$status = $this->repo->locStatus(Game::current());
		if (empty($status)) {
			return null;
		}
		$ret = [];
		foreach ($locs as $id => $name) {
			$teams = [];
			foreach ($status as $stat) {
				if ($stat["point_id"] == $id) {
					$teams[] = $stat;
				}
			}
			$ret[] = ["point_id" => $id, "name" => $name, "teams" => $teams];
		}
		return $ret;
	}

	public function cipherStatus() {
		$ciphers = $this->repo->cipherStatus(Game::current());
		$ret = [];
		$current = null;
		foreach ($ciphers as $cipher) {
			if ($current == null || $cipher["point_id"] != $current["point_id"]) {
				if ($current != null) {
					$ret[] = $current;
				}
				$current = ["point_id" => $cipher["point_id"], "name" => $cipher["name"], "teams" => []];
			}
			$current["teams"][] = ["name" => $cipher["team_name"], "time" => $cipher["time"], "recent" => $cipher["recent"], "hint_type" => $cipher["hint_type"]];
		}
		if ($current != null) {
			$ret[] = $current;
		}
		return $ret;
	}




	public static function addFakeTime($minutes) {
		self::$fakeTimeOffset += $minutes;
	}

	public static function resetFakeTime() {
		self::$fakeTimeOffset = 0;
	}
}
