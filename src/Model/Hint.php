<?php
namespace Sova\Model;

use Sova\Repo\HintRepo;
use Sova\Repo\CipherRepo;
use Sova\Repo\ProgressRepo;

class Hint extends ModelBase {

	public function addCCode(int $ccodeId) {
		$hint = ["team_id" => Team::current(), "ccode_id" => $ccodeId];
		if (!$this->repo->addCCode($hint)) {
			return new Text("hint.add.already");
		} 
		$count = $this->repo->getUnusedHintCount($hint["team_id"]);
		return new Text("hint.add.success", $count);
	}


	public function apply(string $cipherName) {
		$cipher = (new CipherRepo())->getByName($cipherName, Game::current());
		if (!isset($cipher)) {
			return new Text("cipher.unknown", $cipherName);
		} else if (empty($cipher["hint"])) {
			return new Text("hint.no-hint");
		}

		$hints = $this->repo->getUnusedHint(Team::current());
		if (empty($hints)) {
			return new Text("hint.apply.no-ccode");
		}
		$hint = $hints[0];
		$hint["cipher_id"] = $cipher["point_id"];
		$hint["type"] = HintRepo::NORMAL;

		if ($this->repo->alreadyApplied($hint)) {
			return new Text("hint.apply.already", $cipher["name"]);
		} else if ((new ProgressRepo())->isDone(Team::current(), $cipher["point_id"])) {
			return new Text("hint.apply.solved", $cipher["name"]);
		} else if (!(new Cipher())->isReachable($cipher)) {
			if (Settings::get("locVisitMandatory")) {
				return new Text("cipher.no-previous-loc", $cipher["name"]);
			} else {
				return new Text("cipher.no-previous-cipher", $cipher["name"]);
			}
		} else {
			$this->repo->apply($hint);
			return new Text("hint.apply.success", $cipher["name"], $cipher["hint"]);
		}
	}


	public function applyImunity() {
		if ($this->repo->imunityApplied(Team::current())) {
			return new Text("hint.imunity.already");
		}

		$price = Settings::get("imunityPrice");
		$hints = $this->repo->getUnusedHint(Team::current(), $price);
		if (count($hints) < $price) {
			return new Text("hint.imunity.not-enough-ccodes");
		}

		foreach ($hints as &$hint) {
			$hint["type"] = HintRepo::IMUNITY;
			$this->repo->apply($hint);
		}
		return new Text("hint.imunity.success");
	}

	
	public function unusedHintCount() {
		return $this->repo->getUnusedHintCount(Team::current());
	}
}
