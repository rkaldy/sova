<?php
namespace Sova\Model;

use Sova\Model\Message;
use Sova\Repo\ProgressRepo;

class Loc extends ModelBase {

	public function prepare(array &$loc) {
		$loc["game_id"] = Game::current();
		(new Code())->prepare($loc["code"]);
		if (empty($loc["points"])) {
			$loc["points"] = 0;
		}
	}


	public function locs(): array {
		return $this->repo->listAsArray(Game::current());
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
			if (!empty(Settings::get("linkMapyCz"))) {
				$desc .= ', <a href="https://mapy.cz/'.Settings::get("linkMapyCz")."?q={$loc["coord_lat"]}N%20{$loc["coord_lon"]}E\">$coord</a>";
			} else {
				$desc .= ", $coord";
			}
		}
		return $desc;
	}


	public function isFinish(array $loc) {
		return $loc["point_id"] == Settings::get("locFinish");
	}


	public function visit(array $loc, string $code) {
		if (!$this->checkPreviousPointsVisited($loc["point_id"])) {
			return new Text("code.unknown", $code);
		}
		
		$progress = new Progress();
		if (!$progress->create($loc)) {
			return new Text("loc.already");
		}
		
        $team = new Team();
		$team->addPoints($loc["points"]);

		if ($this->isFinish($loc)) {
			$ret = [new Text("loc.finish.visited",$team->points())];
		} else {
			$ret = [new Text("loc.visited", $loc["name"], $team->points())];
		}
		if (Settings::get("showRank")) {		
			list($rank, $firstTeam, $firstTime) = $progress->getRank($loc);
			$ret[] = new Text("loc.rank", $rank, $firstTeam, $firstTime);
		}
		
		foreach ($this->repo->getNextPoints($loc["point_id"]) as $point) {
			if (!$point["is_cipher"]) {
				$ret[] = new Text("loc.next", $point["name"], self::getDescription($point));
			}
		}

		return $ret;
	}
}
