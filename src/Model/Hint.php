<?php
namespace Sova\Model;

use Sova\Repo\HintRepo;
use Sova\Repo\TeamRepo;
use Sova\Repo\CipherRepo;
use Sova\Repo\ProgressRepo;

class Hint extends ModelBase {

	public function addCCode(int $ccodeId) {
		$hint = ["team_id" => Team::current(), "ccode_id" => $ccodeId];
		if (!$this->repo->addCCode($hint)) {
			return new Text("ccode.add.already");
		} 
		$count = $this->repo->getUnusedCCodeCount($hint["team_id"]);
		return new Text("ccode.add.success", $count);
	}

	
	public function unusedCCodeCount() {
		return $this->repo->getUnusedCCodeCount(Team::current());
	}


	protected function price(int $type) {
		switch ($type) {
			case HintRepo::HINT: $typeStr = "hint"; break;
			case HintRepo::HOWTO: $typeStr = "howto"; break;
			case HintRepo::SOLUTION: $typeStr = "solution"; break;
		}
		if (Settings::get("{$typeStr}CCodes") == 0) {
			return [Settings::get("{$typeStr}Points"), "points"];
		}
		$ccodes = $this->repo->getUnusedCCodeCount(Team::current());
		if ($ccodes >= Settings::get("{$typeStr}CCodes")) {
			return [Settings::get("{$typeStr}CCodes"), "ccodes"];
		} else {
			return [Settings::get("{$typeStr}Points"), "points.no-ccode"];
		}
	}


	protected function doCheck(string $cipherName) {
		$cipher = (new CipherRepo())->getByName($cipherName, Game::current());
		$teamId = Team::current();

		if (!isset($cipher)) {
			return new Text("cipher.unknown", $cipherName);
		} else if ($cipher["activity"]) {
			return new Text("hint.apply.activity");
		} else if ((new ProgressRepo())->isDone($teamId, $cipher["point_id"])) {
			return new Text("hint.apply.solved", $cipher["name"]);
		} else if (!(new Cipher())->isReachable($cipher)) {
			if (Settings::get("locVisitMandatory")) {
				return new Text("cipher.no-previous-loc", $cipher["name"]);
			} else {
				return new Text("cipher.no-previous-cipher", $cipher["name"]);
			}
		} else if (empty($cipher["hint"])) {
			return new Text("hint.apply.no-hint");
		}

		$appliedHintType = $this->repo->getAppliedHintType($teamId, $cipher["point_id"]);
		if ($appliedHintType == HintRepo::SOLUTION) {
			return new Text("hint.apply.already", $cipher["name"]);
		}

		return [$cipher, $appliedHintType];
	}


	public function check(string $cipherName) {
		$ret = $this->doCheck($cipherName);
		if ($ret instanceof Text) {
			return [false, [$ret]];
		} 
		list($cipher, $appliedHintType) = $ret;

		list($price, $unit) = $this->price($appliedHintType + 1);
		switch ($appliedHintType) {
			case HintRepo::NONE: $nextType = "nápovědu"; break;
			case HintRepo::HINT: $type = "nápovědu"; $nextType = "postup"; break;
			case HintRepo::HOWTO: $type = "postup"; $nextType = "řešení"; break;
		}
		if ($appliedHintType == HintRepo::NONE) {
			return [true, [new Text("hint.apply.no-history", $cipherName), new Text("hint.apply.$unit", $nextType, $price)]];
		} else {
			return [true, [new Text("hint.apply.history", $cipherName, $type), new Text("hint.apply.$unit", $nextType, $price)]];
		}
	}


	public function apply(string $cipherName) {
		$ret = $this->doCheck($cipherName);
		if ($ret instanceof Text) {
			return $ret;
		} 
		
		list($cipher, $appliedHintType) = $ret;
		$type = $appliedHintType + 1;
		list($price, $unit) = $this->price($type);
		$teamId = Team::current();

		$timeOffset = (new Progress())->getFakeTimeOffset();
		if ($unit == "ccodes") {
			$this->repo->applyByCCodes($teamId, $cipher["point_id"], $type, $price, $timeOffset);
		} else {
			(new TeamRepo())->addPoints($teamId, -$price);
			$this->repo->applyByPoints($teamId, $cipher["point_id"], $type, $timeOffset);
		}

		switch ($type) {
			case HintRepo::HINT: return new Text("hint.text.hint", $cipher["name"], $cipher["hint"]);
			case HintRepo::HOWTO: return new Text("hint.text.howto", $cipher["name"], $cipher["howto"]);
			case HintRepo::SOLUTION: return new Text("hint.text.solution", $cipher["name"], $cipher["code"]);
		}
	}


	public function imunityStatus(int $ccodeCount) {
		$price = Settings::get("imunityCCodes");
		$available = false;
		if ($price == 0) {
			$ret = new Text("");
        } else if ($this->repo->haveImunity(Team::current())) {
            $ret = new Text("imunity.already");
        } else if ($ccodeCount < $price) {
            $ret = new Text("imunity.insufficient");
        } else {
            $available = true;
            $ret = new Text("imunity.available", $price);
		}
		return [$available, $ret];
	}


	public function applyImunity() {
		$price = Settings::get("imunityCCodes");
		if ($this->repo->haveImunity(Team::current())) {
			return new Text("imunity.already");
		} else if ($this->repo->getUnusedCCodeCount(Team::current()) < $price) {
			return new Text("imunity.insufficient");
		}
		$this->repo->applyByCCodes(Team::current(), null, HintRepo::IMUNITY, $price);
		return new Text("imunity.success");
	}
}
