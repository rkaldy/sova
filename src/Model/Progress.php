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

	public function teamStatus() {
		$locs = (new LocRepo())->listSimple(Game::current());
		$status = $this->repo->teamStatus(Game::current());
		$ret = [];
		foreach ($locs as $loc) {
			$teams = [];
			foreach ($status as $stat) {
				if ($stat["point_id"] == $loc["point_id"]) {
					$teams[] = $stat;
				}
			}
			$loc["teams"] = $teams;
			$ret[] = $loc;
		}

		$maxSolved = 0;
		foreach ($status as $stat) {
			if ($stat["solved"] > $maxSolved) {
				$maxSolved = $stat["solved"];
			}
		}
		return [$ret, $maxSolved];
	}

	public static function addFakeTime($minutes) {
		self::$fakeTimeOffset += $minutes;
	}

	public static function resetFakeTime() {
		self::$fakeTimeOffset = 0;
	}
}
