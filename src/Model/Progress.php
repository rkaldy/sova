<?php
namespace Sova\Model;

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

	public static function addFakeTime($minutes) {
		self::$fakeTimeOffset += $minutes;
	}

	public static function resetFakeTime() {
		self::$fakeTimeOffset = 0;
	}
}
