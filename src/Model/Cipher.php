<?php
namespace Sova\Model;

use Sova\Repo\LocRepo;
use Sova\Repo\ProgressRepo;

class Cipher extends ModelBase {

	public function prepare(array &$cipher) {
		$cipher["game_id"] = Game::current();
		$codes = $cipher["code"] ?? [];
		if (!is_array($codes)) {
			$codes = explode(",", $codes);
		}
		$codes = array_filter(array_map("trim", $codes));
		if (empty($codes)) {
			$codes = [(new Code())->generate()];
		} else {
			$codes = array_map([Code::class, "polish"], $codes);
		}
		$cipher["code"] = array_values(array_unique($codes));
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
		
		$points = $this->computePoints($cipher);
		(new Team())->addPoints($points);

		$progress->create($cipher, $points);
		if (!Settings::get("locVisitMandatory")) {
            foreach ($cipher["prev"] AS $prev) {
                $loc = ["point_id" => $prev];
                if (!$progress->isDone($loc)) {
					$progress->create($loc, 0);
                }
            }
        }

        return $this->generateMessages($cipher);
	}

	public function solveForce(int $teamId, int $cipherId) {
		$cipher = $this->repo->get($cipherId);
		if (!isset($cipher)) {
			return new Text("Neznámá šifra/aktivita");
		}
		$type = $cipher["activity"] ? "activity" : "cipher";
		
        $team = new Team();
        $progress = new Progress();
		if ($progress->isDone($cipher, $teamId)) {
			return new Text("$type.already");
		}

		$points = $this->computePoints($cipher);
		$team->addPoints($points, $teamId);
		$progress->create($cipher, $points, $teamId);

		$message = new Text("$type.solved", $cipher["name"], $team->points($teamId));
		(new Message())->sendToTeam($message->format(), $teamId);
        return $message;
	}

    protected function computePoints(array $cipher) {
   		$points = $cipher["points"];
		if ($cipher["points_by_rank"]) {
			$rank = (new ProgressRepo())->nextRankAtPoint($cipher["point_id"], Progress::getFakeTimeOffset());
			$points -= ($rank - 1) * $cipher["points_by_rank"];
		}
        return $points;
    }


    protected function generateMessages(array $cipher) {
        $team = new Team();
        $progress = new Progress();
        $points = $team->points();
		$type = $cipher["activity"] ? "activity" : "cipher";

		$ret = [new Text("$type.solved", $cipher["name"], $points)];
		if (Settings::get("showRank")) {
			list($rank, $firstTeam, $firstTime) = $progress->getRank($cipher);
			$ret[] = new Text("$type.rank", $rank, $firstTeam, $firstTime);
		}
		foreach ($this->repo->getNextLocs($cipher) as $loc) {
			$ret[] = new Text("loc.next", $loc["name"], Loc::getDescription($loc));
		}
        
        if (Settings::get("locFinish") && Settings::get("finishPointThreshold")) {
            if ($team->points() >= Settings::get("finishPointThreshold")) {
                $loc = (new LocRepo())->get(Settings::get("locFinish"));
                $ret[] = new Text("loc.finish", Loc::getDescription($loc));
            }
        }
        return $ret;
    }
 

	public function teamCipherStatus() {
		return $this->repo->teamCipherStatus(Team::current());
	}
}
