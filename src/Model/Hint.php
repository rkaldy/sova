<?php
namespace Sova\Model;

use Sova\Repo\HintRepo;
use Sova\Repo\CipherRepo;
use Sova\Controller\Text;

class Hint extends ModelBase {

	public const NORMAL = 1;
	public const DEAD = 2;

	public function add($unihintId) {
		$teamId = Team::current();

		if ($this->repo->alreadyHas($teamId, $unihintId)) {
			return new Text("hint.add.already");
		} else {
			$this->repo->addToTeam($teamId, $unihintId);
			$count = $this->repo->getUnusedHintCount($teamId);
			return new Text("hint.add.success", $count);
		}
	}

	public function apply($cipherName) {
		$cipher = (new CipherRepo())->getByName($cipherName, Game::current());
		if (!isset($cipher)) {
			return new Text("cipher.unknown", $cipherName);
		}

		$teamId = Team::current();
		$unihintId = $this->repo->getUnusedHintId($teamId);
		$cipherId = $cipher["point_id"];

		if ($unihintId == null) {
			return new Text("hint.apply.no-hint");
		} else if ($this->repo->alreadyApplied($teamId, $cipherId)) {
			return new Text("hint.apply.already", $cipher["name"]);
		} else if (!(new Cipher())->checkPreviousCiphersSolved($cipher)) {
			if (count($cipher["prev"]) == 1) {
				return new Text("cipher.no-previous", $cipher["name"]);
			} else {
				return new Text("cipher.no-previous.multi", $cipher["name"], count($cipher["prev"]));
			}
		} else {
			$this->repo->apply($teamId, $unihintId, $cipherId, self::NORMAL);
			return new Text("hint.apply.success", $cipher["name"], $cipher["hint"]);
		}
	}

	public function unusedHintCount() {
		return $this->repo->getUnusedHintCount(Team::current());
	}
}
