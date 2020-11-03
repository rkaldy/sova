<?php
namespace Sova\Model;

use Sova\Controller\Text;
use Sova\Model\Message;
use Sova\Repo\ProgressRepo;

class Loc extends ModelBase {

	public function prepare(array &$loc) {
		$loc["game_id"] = Game::current();
		(new Code())->prepare($loc["code"]);
	}


	public function checkPreviousCiphersSolved(int $locId) {
		if (!$this->repo->hasPreviousCiphers($locId)) {
			return true;
		} else {
			return $this->repo->previousCipherSolved(Team::current(), $locId);
		}
	}


	public function visit(array $loc, string $code) {
		if (!$this->checkPreviousCiphersSolved($loc["point_id"])) {
			return new Text("code.unknown", $code);
		}
		
		if (!(new ProgressRepo())->create(Team::current(), $loc["point_id"])) {
			return new Text("loc.already");
		}
		
		$hint = new Hint();
		foreach ($this->repo->getNextCiphers($loc["point_id"]) as $cipher) {
			if ($this->repo->visitedAllLocsWith(Team::current(), $cipher["point_id"])) {
				$hint->amend($cipher);
			}
		}
		
		return new Text("loc.visited", $loc["name"]);
	}
}
