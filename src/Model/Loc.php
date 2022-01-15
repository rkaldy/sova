<?php
namespace Sova\Model;

use Sova\Model\Message;
use Sova\Repo\ProgressRepo;

class Loc extends ModelBase {

	public function prepare(array &$loc) {
		$loc["game_id"] = Game::current();
		(new Code())->prepare($loc["code"]);
	}


	public function checkPreviousPointsVisited(int $locId) {
		if (!$this->repo->hasPreviousPoints($locId)) {
			return true;
		} else {
			return $this->repo->previousPointsVisited(Team::current(), $locId);
		}
	}


	public static function buildNextLocMessage(array &$ret, array $next) {
		if (count($next) == 1) {
			$ret[] = new Text("loc.next", $next[0]["description"]);
		} else if (count($next) > 1) {
			$nextLocs = [];
			foreach ($next as $loc) {
				$nextLocs[] = $loc["name"] . "/" . $loc["description"];
			}
			$ret[] = new Text("loc.next.multi", join("; ", $nextLocs));
		}
	}


	public function visit(array $loc, string $code) {
		if (!$this->checkPreviousPointsVisited($loc["point_id"])) {
			return new Text("code.unknown", $code);
		}
		
		$progress = new Progress();
		if (!$progress->create($loc)) {
			return new Text("loc.already");
		}
		
		$hint = new Hint();
		$nextLocs = [];
		foreach ($this->repo->getNextPoints($loc["point_id"]) as $point) {
			if ($point["is_cipher"]) {
				if ($this->repo->visitedAllLocsWithCipher(Team::current(), $point["point_id"])) {
					$hint->amend($point);
				}
			} else {
				$nextLocs[] = $point;
			}
		}

		if (Settings::showRank()) {		
			list($rank, $firstTeam, $firstTime) = $progress->getRank($loc);
			$ret = [new Text("loc.visited", $loc["name"], $rank, $firstTeam, $firstTime)];
		} else {
			$ret = [new Text("loc.visited.no-rank", $loc["name"])];
		}

		Loc::buildNextLocMessage($ret, $nextLocs);
		return $ret;
	}
}
