<?php
namespace Sova\Model;

use Sova\Repo\LocRepo;
use Sova\Repo\ProgressRepo;

class Cipher extends ModelBase {

	public function prepare(array &$cipher) {
		$cipher["game_id"] = Game::current();
		(new Code())->prepare($cipher["code"]);
	}

	public function isReachable(array $cipher) {
		if (Settings::isLocVisitMandatory()) {
			return $this->repo->previousLocsVisited(Team::current(), $cipher);
		} else {
			return !$this->repo->hasPreviousCiphers($cipher) || $this->repo->previousCiphersSolved(Team::current(), $cipher);
		}
	}

	public function checkSolvedCipherCount(array &$ret, int $solved) {
		foreach ((new LocRepo())->getBySolvedCipherCount(Game::current(), $solved) as $loc) {
			$ret[] = new Text("loc.by-solved-ciphers", $loc["name"], $loc["description"]);
		}
	}

	public function solve(array $cipher, string $code) {
		if (!$this->isReachable($cipher)) {
			return new Text("code.unknown", $code);
		}

		$progress = new Progress();
		if (!$progress->create($cipher)) {
			return new Text("cipher.already");
		}

		foreach ($cipher["prev"] AS $prev) {
			$loc = ["point_id" => $prev];
			$progress->create($loc);
		}

		$this->repo->deletePendingHintsForParallelCiphers(Team::current(), $cipher);

		$solved = $this->repo->solvedCipherCount(Team::current());

		if (Settings::showRank()) {
			list($rank, $firstTeam, $firstTime) = $progress->getRank($cipher);
			$ret = [new Text("cipher.solved", $cipher["name"], $rank, $firstTeam, $firstTime, $solved)];
		} else {
			$ret = [new Text("cipher.solved.no-rank", $cipher["name"], $solved)];
		}

		Loc::buildNextLocMessage($ret, $this->repo->getNextLocs($cipher));
		$this->checkSolvedCipherCount($ret, $solved);

		return $ret;
	}
}
