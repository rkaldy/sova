<?php
namespace Sova\Model;

use Sova\Repo\ProgressRepo;

class Cipher extends ModelBase {

	public function prepare(array &$cipher) {
		$cipher["game_id"] = Game::current();
		(new Code())->prepare($cipher["code"]);
	}

	public function checkPreviousCiphersSolved(array $cipher) {
		$progressRepo = new ProgressRepo();
		$teamId = Team::current();
		print_r($cipher);
		if ($progressRepo->previousLocVisited($teamId, $cipher["point_id"])) {
			return true;
		} else {
			foreach ($cipher["prev"] as $prevLoc) {
				echo "prevLoc=$prevLoc\n";
				if (!$progressRepo->previousCipherSolved($teamId, $prevLoc)) {
					return false;
				}
			}
			return true;
		}
	}
}
