<?php
namespace Sova\Model;

use Sova\Controller\Text;
use Sova\Model\Progress;
use Sova\Model\Message;
use Sova\Repo\ProgressRepo;

class Loc extends ModelBase {

	public function prepare(array &$loc) {
		$loc["game_id"] = Game::current();
		(new Code())->prepare($loc["code"]);
	}

	public function checkPreviousCipherSolved(int $locId) {
		if (!$this->repo->hasPreviousCiphers($locId)) {
			return true;
		} else {
			return (new ProgressRepo())->previousCipherSolved(Team::current(), $locId);
		}
	}

	public function visit(array $loc, string $code) {
		if (!$this->checkPreviousCipherSolved($loc["point_id"])) {
			return new Text("code.unknown", $code);
		}
		
		$teamId = Team::current();
		$message = new Message();

		if (!(new ProgressRepo())->create($teamId, $loc["point_id"], Progress::PASSED_NORMAL)) {
			return new Text("loc.already");
		}
		foreach ($this->repo->getNextCiphers($loc["point_id"]) as $cipher) {
			if (isset($cipher["hint_timeout"])) {
				$hintMsg = new Text("cipher.hint", $cipher["name"], $cipher["hint"]);
				$message->sendToTeam($hintMsg->format(), $cipher["point_id"], $cipher["hint_timeout"]);
			}
			if (isset($cipher["solution_timeout"])) {
				$solutionMsg = new Text("cipher.solution", $cipher["name"], $cipher["code"]);
				$message->sendToTeam($solutionMsg->format(), $cipher["point_id"], $cipher["solution_timeout"]);
			}
		}
		return new Text("loc.visited", $loc["name"]);
	}
}
