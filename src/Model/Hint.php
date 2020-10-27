<?php
namespace Sova\Model;

use Sova\Repo\TeamHintRepo;
use Sova\Repo\CipherRepo;
use Sova\Controller\Text;

class Hint extends ModelBase {

	protected $teamHintRepo;

	public function __construct() {
		parent::__construct();
		$this->teamHintRepo = new TeamHintRepo();
	}

	public function prepare(array &$hint) {
		$hint["game_id"] = Game::current();
		(new Code())->prepare($hint["code"]);
	}


	public function add($hintId) {
		$teamId = Team::current();

		if ($this->teamHintRepo->alreadyHas($teamId, $hintId)) {
			return new Text("hint.add.already");
		} else {
			$this->teamHintRepo->addToTeam($teamId, $hintId);
			$count = $this->teamHintRepo->getUnusedHintCount($teamId);
			return new Text("hint.add.success", $count);
		}
	}

	public function apply($cipherName) {
		$cipher = (new CipherRepo())->getByName($cipherName, Game::current());
		if (!isset($cipher)) {
			return new Text("cipher.unknown", $cipherName);
		}

		$teamId = Team::current();
		$hintId = $this->teamHintRepo->getUnusedHintId($teamId);
		$cipherId = $cipher["point_id"];

		if ($hintId == null) {
			return new Text("hint.apply.no-hint");
		} else if ($this->teamHintRepo->alreadyApplied($teamId, $cipherId)) {
			return new Text("hint.apply.already", $cipher["name"]);
		} else if (!(new Cipher())->checkPreviousCiphersSolved($cipher)) {
			if (count($cipher["prev"]) == 1) {
				return new Text("cipher.no-previous", $cipher["name"]);
			} else {
				return new Text("cipher.no-previous.multi", $cipher["name"]. count($cipher["prev"]));
			}
		} else {
			$this->teamHintRepo->apply($teamId, $hintId, $cipherId);
			return new Text("hint.apply.success", $cipher["name"], $cipher["hint"]);
		}
	}

	public function unusedHintCount() {
		return $this->teamHintRepo->getUnusedHintCount(Team::current());
	}
}
