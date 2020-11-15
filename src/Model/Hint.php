<?php
namespace Sova\Model;

use Sova\Repo\HintRepo;
use Sova\Repo\CipherRepo;

class Hint extends ModelBase {

	public const NO_HINT = 0;
	public const NORMAL = 1;
	public const ABSOLUTE = 2;

	
	public function add(int $unihintId) {
		$hint = ["team_id" => Team::current(), "unihint_id" => $unihintId];
		if (!$this->repo->addToTeam($hint)) {
			return new Text("hint.add.already");
		} 
		$count = $this->repo->getUnusedHintCount($hint["team_id"]);
		return new Text("hint.add.success", $count);
	}


	public function apply(string $cipherName) {
		$cipher = (new CipherRepo())->getByName($cipherName, Game::current());
		if (!isset($cipher)) {
			return new Text("cipher.unknown", $cipherName);
		}

		$hint = $this->repo->getUnusedHint(Team::current());
		if ($hint == null) {
			return new Text("hint.apply.no-hint");
		}
		$hint["cipher_id"] = $cipher["point_id"];
		$hint["type"] = self::NORMAL;

		if ($this->repo->alreadyApplied($hint)) {
			return new Text("hint.apply.already", $cipher["name"]);
		} else if (!(new Cipher())->isReachable($cipher)) {
			if (Settings::isLocVisitMandatory()) {
				return new Text("cipher.no-previous-loc", $cipher["name"]);
			} else {
				return new Text("cipher.no-previous-cipher", $cipher["name"]);
			}
		} else {
			$this->repo->apply($hint);
			return new Text("hint.apply.success", $cipher["name"], $cipher["hint"]);
		}
	}

	
	public function unusedHintCount() {
		return $this->repo->getUnusedHintCount(Team::current());
	}

	
	public function amend(array $cipher) {
		$message = new Message();
		if (isset($cipher["hint_timeout"])) {
			$hint = ["team_id" => Team::current(), "cipher_id" => $cipher["point_id"], "time" => $cipher["hint_timeout"], "type" => self::NORMAL ];
			$this->repo->amend($hint);
			$hintMsg = new Text("cipher.hint", $cipher["name"], $cipher["hint"]);
			$message->sendToTeam($hintMsg->format(), $hint["hint_id"], $cipher["hint_timeout"]);
		}
		if (isset($cipher["solution_timeout"])) {
			$hint = ["team_id" => Team::current(), "cipher_id" => $cipher["point_id"], "time" => $cipher["solution_timeout"], "type" => self::ABSOLUTE ];
			$this->repo->amend($hint);
			$solutionMsg = new Text("cipher.solution", $cipher["name"], $cipher["code"]);
			$message->sendToTeam($solutionMsg->format(), $hint["hint_id"], $cipher["solution_timeout"]);
		}
	}
}
