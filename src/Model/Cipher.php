<?php
namespace Sova\Model;

use Sova\Repo\ProgressRepo;

class Cipher extends ModelBase {

	public function prepare(array &$cipher) {
		$cipher["game_id"] = Game::current();
		(new Code())->prepare($cipher["code"]);
	}

	public function checkPreviousCiphersSolved(array $cipher) {
		if (!$this->repo->hasPreviousCiphers($cipher["point_id"])) {
			return true;
		}
		$progressRepo = new ProgressRepo();
		$teamId = Team::current();
		foreach ($cipher["prev"] as $prevLoc) {
			if (!$progressRepo->previousCipherSolved($teamId, $prevLoc)) {
				return false;
			}
		}
		return true;
	}
}
