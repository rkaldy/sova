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


	public function visit(array $loc, string $code) {
		if (!$this->checkPreviousPointsVisited($loc["point_id"])) {
			return new Text("code.unknown", $code);
		}
		
		$progress = new Progress();
		if (!$progress->create($loc)) {
			return new Text("loc.already");
		}
		
		$hint = new Hint();
		foreach ($this->repo->getNextCiphers($loc["point_id"]) as $cipher) {
			if ($this->repo->visitedAllLocsWithCipher(Team::current(), $cipher["point_id"])) {
				$hint->amend($cipher);
			}
		}

		list($rank, $firstTeam, $firstTime) = $progress->getRank($loc);
		
		return new Text("loc.visited", $loc["name"], $rank, $firstTeam, $firstTime);
	}
}
