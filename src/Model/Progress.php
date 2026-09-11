<?php
namespace Sova\Model;

use Sova\Repo\LocRepo;
use Sova\Repo\CipherRepo;

class Progress extends ModelBase {

	protected static $fakeTimeOffset = 0;

	public function create(array &$obj, int $points = 0, ?int $teamId = null) {
		return $this->repo->create($teamId ?? Team::current(), $obj["point_id"], $points, self::$fakeTimeOffset);
	}


	public function isDone(array $point, ?int $teamId = null) {
		return $this->repo->isDone($teamId ?? Team::current(), $point["point_id"]);
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

		$progress = [];
		foreach ($locs as $pid => $name) {
			$progress[$pid] = ["name" => $name, "teams" => []];
		}
		foreach ($status as $stat) {
			$pid = $stat["point_id"];
			unset($stat["point_id"]);
			$progress[$pid]["teams"][] = $stat;
		}
		return array_values($progress);
	}


	public function cipherStatus() {
		$ciphers = (new CipherRepo())->listAsArray(Game::current());
		$status = $this->repo->cipherStatus(Game::current());
		if (empty($status)) {
			return null;
		}

		$progress = [];
		foreach ($ciphers as $pid => $name) {
			$progress[$pid] = ["name" => $name, "teams" => []];

		}
		foreach ($status as $stat) {
			$pid = $stat["point_id"];
			unset($stat["point_id"]);
			$progress[$pid]["teams"][] = $stat;
		}
		return array_values($progress);
	}


	public static function getFakeTimeOffset() {
		return self::$fakeTimeOffset;
	}

	public static function addFakeTime($minutes) {
		self::$fakeTimeOffset += $minutes;
	}

	public static function resetFakeTime() {
		self::$fakeTimeOffset = 0;
	}
}
