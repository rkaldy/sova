<?php
namespace Sova\Model;

use Sova\Repo\HintRepo;
use Sova\Repo\TeamRepo;
use Sova\Repo\CipherRepo;
use Sova\Repo\ProgressRepo;

class Hint extends ModelBase {

	protected function price(array $cipher, int $type) {
		switch ($type) {
			case HintRepo::HINT: $typeStr = "hint"; break;
			case HintRepo::HOWTO: $typeStr = "howto"; break;
			case HintRepo::SOLUTION: $typeStr = "solution"; break;
		}
		$pointPrice = Settings::get("${typeStr}Points");
		$ccodePrice = Settings::get("${typeStr}CCodes");
		$multiplier = $cipher["price_multiplier"];
		if (isset($multiplier)) {
			$pointPrice = (int)round($pointPrice * $multiplier);
			$ccodePrice = (int)round($ccodePrice * $multiplier);
		}

		return [$pointPrice, $ccodePrice];
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

		list($pointPrice, $ccodePrice) = $this->price($cipher, $appliedHintType + 1);
		switch ($appliedHintType) {
			case HintRepo::NONE: $nextType = "nápovědu"; break;
			case HintRepo::HINT: $type = "nápovědu"; $nextType = "postup"; break;
			case HintRepo::HOWTO: $type = "postup"; $nextType = "řešení"; break;
		}
		if ($appliedHintType == HintRepo::NONE) {
			$history = new Text("hint.apply.no-history", $cipherName);
		} else {
			$history = new Text("hint.apply.history", $cipherName, $type);
		}
		return [true, [$history, new Text("hint.apply.price", $nextType, $pointPrice, $ccodePrice)]];
	}


	public function apply(string $cipherName, bool $forCCodes) {
		$ret = $this->doCheck($cipherName);
		if ($ret instanceof Text) {
			return $ret;
		} 
		
		list($cipher, $appliedHintType) = $ret;
		$type = $appliedHintType + 1;
		list($pointPrice, $ccodePrice) = $this->price($cipher, $type);
		$teamId = Team::current();

		if ($forCCodes && ($ccodePrice > $this->repo->getUnusedCCodeCount($teamId))) {
			return new Text("hint.apply.no-ccode");
		}

		$timeOffset = (new Progress())->getFakeTimeOffset();
		if ($forCCodes) {
			$this->repo->applyByCCodes($teamId, $cipher["point_id"], $type, $ccodePrice, $timeOffset);
		} else {
			(new TeamRepo())->addPoints($teamId, -$pointPrice);
			$this->repo->applyByPoints($teamId, $cipher["point_id"], $type, $timeOffset);
		}

		switch ($type) {
			case HintRepo::HINT: return new Text("hint.text.hint", $cipher["name"], $cipher["hint"]);
			case HintRepo::HOWTO: return new Text("hint.text.howto", $cipher["name"], $cipher["howto"]);
			case HintRepo::SOLUTION: return new Text("hint.text.solution", $cipher["name"], $cipher["code"]);
		}
	}


	public function sellCCode(int $sign) {
		$team = new Team();
		$teamId = Team::current();
		if ($this->repo->getUnusedCCodeCount($teamId) <= 0) {
			return new Text("ccode.sell.none");
		}
		$timeOffset = (new Progress())->getFakeTimeOffset();
		$points = Settings::get("pointsForCCode");

		$team->addPoints($points * $sign);
		$this->repo->applyByCCodes($teamId, null, HintRepo::POINTS, 1, $timeOffset);
		return new Text("ccode.sell." . ($sign == 1 ? "add" : "sub"), $points);
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
