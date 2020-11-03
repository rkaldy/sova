<?php
namespace Sova\Model;

use Sova\Repo\LocRepo;
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
		$teamId = Team::current();
		$locRepo = new LocRepo();
		foreach ($cipher["prev"] as $prevLoc) {
			if (!$locRepo->previousCipherSolved($teamId, $prevLoc)) {
				return false;
			}
		}
		return true;
	}

	public function solve(array $cipher, string $code) {
		if (!$this->checkPreviousCiphersSolved($cipher)) {
			return new Text("code.unknown", $code);
		}
		
		$teamId = Team::current();
		$message = new Message();
		$timeiSpent = $this->repo->getTimeSpent();
		if (isset($cipher["solution_timeout"]) && $timeVisited >= $cipher["solution_timeout"]) {
		} else if (isset($cipher["hint_timeout"]) && $timeVisited >= $cipher["hint_timeout"]) {
		} else {
		}

	}
}
