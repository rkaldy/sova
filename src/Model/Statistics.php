<?php
namespace Sova\Model;

use Sova\Repo\StatisticsRepo;
use Sova\Repo\ProgressRepo;


class Statistics {

	protected $repo;
   
	public function __construct() {
		$this->repo	= new StatisticsRepo();
	}

	public function rank() {
		return array_merge(
			[["Tým", "Příchod do cíle", "Počet vyluštěných šifer", "Čas poslední vyluštěné šifry", "Poslední navštívené stanoviště"]],
			(new ProgressRepo())->rankTotal(Game::current())
		);
	}

	public function ciphers() {
		return array_merge(
			[["Šifra", "Tým", "Doba luštění"]],
			$this->repo->ciphers(Game::current())
		);
	}

	public function hints() {
		return array_merge(
			[["Šifra", "Vyluštili", "Vyluštili s nápovědou", "Vyluštili s postupem","Nevyluštili"]],
			$this->repo->hints(Game::current())
		);
	}

	public function zakys() {
		$progress = (new ProgressRepo())->progress(Game::current());
		$times = [];
		for ($i = 1; $i < count($progress); $i++) {
			$prog = $progress[$i];
			$prev = $progress[$i-1];
			if ($prog["team_id"] == $prev["team_id"] && !$prog["is_loc"]) {
				$timediff = $prog["time_sec"] - $prev["time_sec"];
				$times[] = [$prog["team_name"], $prog["point_name"], (int)$timediff];
			}
		}
		usort($times, function($a, $b) { return -($a[2] <=> $b[2]); });
		return array_merge([["Tým", "Vyluštěná šifra", "Čas"]], array_slice($times, 0, 10));
	}
}
