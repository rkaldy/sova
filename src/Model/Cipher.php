<?php
namespace Sova\Model;

use Sova\Repo\LocRepo;
use Sova\Repo\ProgressRepo;

class Cipher extends ModelBase {

	public function prepare(array &$cipher) {
		$cipher["game_id"] = Game::current();
		(new Code())->prepare($cipher["code"]);
		$this->prepareBooleans($cipher, ["activity"]);
		if (empty($cipher["points"])) {
			$cipher["points"] = 0;
		}
	}

	public function isReachable(array $cipher) {
		if (Settings::get("locVisitMandatory")) {
			return $this->repo->previousLocsVisited(Team::current(), $cipher);
		} else {
			return !$this->repo->hasPreviousCiphers($cipher) || $this->repo->previousCiphersSolved(Team::current(), $cipher);
		}
	}

	public function solve(array $cipher, string $code) {
		if (!$this->isReachable($cipher)) {
			return new Text("code.unknown", $code);
		}

		$type = $cipher["activity"] ? "activity" : "cipher";

		$progress = new Progress();
		if (!$progress->create($cipher)) {
			return new Text("$type.already");
		}

		foreach ($cipher["prev"] AS $prev) {
			$loc = ["point_id" => $prev];
			$progress->create($loc);
		}

        $team = new Team();
		$team->addPoints($cipher["points"]);

		$ret = [new Text("$type.solved", $cipher["name"], $team->points())];
		if (Settings::get("showRank")) {
			list($rank, $firstTeam, $firstTime) = $progress->getRank($cipher);
			$ret[] = new Text("$type.rank", $rank, $firstTeam, $firstTime);
		}
		foreach ($this->repo->getNextLocsNotVisited(Team::current(), $cipher) as $loc) {
			$ret[] = new Text("loc.next", $loc["name"], Loc::getDescription($loc));
		}
		
		return $ret;
	}

	public function teamCipherStatus() {
		return $this->repo->teamCipherStatus(Team::current());
	}
}
