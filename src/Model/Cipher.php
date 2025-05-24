<?php
namespace Sova\Model;

use Sova\Repo\LocRepo;
use Sova\Repo\ProgressRepo;

class Cipher extends ModelBase {

	public function prepare(array &$cipher) {
		$cipher["game_id"] = Game::current();
		(new Code())->prepare($cipher["code"]);
		$this->prepareBooleans($cipher, ["activity", "all_locs_mandatory"]);
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
		if ($progress->isDone($cipher)) {
			return new Text("$type.already");
		}
		
		$progress->create($cipher);
		foreach ($cipher["prev"] AS $prev) {
			$loc = ["point_id" => $prev];
			if (!$progress->isDone($loc)) {
				$progress->create($loc);
			}
		}

                $team = new Team();
		$points = $cipher["points"];
		if ($cipher["points_by_rank"]) {
			$rank = (new ProgressRepo())->rankAtPoint(Team::current(), $cipher["point_id"]);
			$points -= ($rank - 1) * $cipher["points_by_rank"];
		}
		$team->addPoints($points);

		$ret = [new Text("$type.solved", $cipher["name"], $team->points())];
		if (Settings::get("showRank")) {
			list($rank, $firstTeam, $firstTime) = $progress->getRank($cipher);
			$ret[] = new Text("$type.rank", $rank, $firstTeam, $firstTime);
		}
		foreach ($this->repo->getNextLocs($cipher) as $loc) {
			$ret[] = new Text("loc.next", $loc["name"], Loc::getDescription($loc));
		}
		
		return $ret;
	}

	public function teamCipherStatus() {
		return $this->repo->teamCipherStatus(Team::current());
	}
}
