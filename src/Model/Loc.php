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


	public static function getDescription(array $loc): string {
		$desc = $loc["description"];
		if (isset($loc["coord_lat"]) && isset($loc["coord_lon"])) {
			$coord = "{$loc["coord_lat"]}N {$loc["coord_lon"]}E";
			if (!empty(Settings::value("linkMapyCz"))) {
				$desc .= ', <a href="https://mapy.cz/'.Settings::value("linkMapyCz")."?x={$loc["coord_lon"]}&y={$loc["coord_lat"]}&z=15\">$coord</a>";
			} else {
				$desc .= ", $coord";
			}
		}
		return $desc;
	}


	public function isFinish(array $loc) {
		return $loc["point_id"] == Settings::value("locFinish");
	}


	public function visit(array $loc, string $code) {
		if (!$this->checkPreviousPointsVisited($loc["point_id"])) {
			return new Text("code.unknown", $code);
		}
		
		$progress = new Progress();
		if (!$progress->create($loc)) {
			return new Text("loc.already");
		}

		$finish = $this->isFinish($loc) ? ".finish" : "";
		if (Settings::showRank()) {		
			list($rank, $firstTeam, $firstTime) = $progress->getRank($loc);
			$ret = [new Text("loc$finish.visited", $loc["name"], $rank, $firstTeam, $firstTime)];
		} else {
			$ret = [new Text("loc$finish.visited.no-rank", $loc["name"])];
		}
		
		$hint = new Hint();
		foreach ($this->repo->getNextPoints($loc["point_id"]) as $point) {
			if ($point["is_cipher"]) {
				if ($this->repo->visitedAllLocsWithCipher(Team::current(), $point["point_id"])) {
					$hint->amend($point);
				}
			} else {
				$ret[] = new Text("loc.next", $point["name"], self::getDescription($point));
			}
		}

		return $ret;
	}
}
