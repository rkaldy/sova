<?php
namespace Sova\Model;

use Sova\Repo\LocRepo;
use Sova\Repo\ProgressRepo;
use Sova\Controller\Text;

class Cipher extends ModelBase {

	public function prepare(array &$cipher) {
		$cipher["game_id"] = Game::current();
		(new Code())->prepare($cipher["code"]);
	}

	public function checkAllPreviousCiphersSolved(array $cipher) {
		if (!$this->repo->hasPreviousCiphers($cipher)) {
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

	public function checkSomePreviousCipherSolved(array $cipher) {
		return !$this->repo->hasPreviousCiphers($cipher) || $this->repo->previousCiphersSolved(Team::current(), $cipher);
	}

	public function solve(array $cipher, string $code) {
		if (!$this->checkSomePreviousCipherSolved($cipher)) {
			return new Text("code.unknown", $code);
		}

		if (!(new ProgressRepo())->create(Team::current(), $cipher["point_id"])) {
			return new Text("cipher.already");
		}

		$this->repo->deletePendingHintsForParallelCiphers(Team::current(), $cipher);

		$ret = [new Text("cipher.solved", $cipher["name"])];
		$next = $this->repo->getNextLocs($cipher);
		if (count($next) == 1) {
			$ret[] = new Text("loc.next", $next[0]["description"]);
		} else if (count($next) > 1) {
			$nextLocs = [];
			foreach ($next as $loc) {
				$nextLocs[] = $loc["description"];
			}
			$ret[] = new Text("loc.next.multi", join("; ", $nextLocs));
		}
		return $ret;
	}
}
