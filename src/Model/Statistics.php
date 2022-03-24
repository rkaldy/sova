<?php
namespace Sova\Model;

use Sova\Repo\StatisticsRepo;
use Sova\Repo\ProgressRepo;
use \PDO;


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
		$progress = (new ProgressRepo())->progressByTeam(Game::current());
		$times = [];
		for ($i = 1; $i < count($progress); $i++) {
			$prog = $progress[$i];
			$prev = $progress[$i-1];
			if ($prog["team_id"] == $prev["team_id"] && !$prog["is_loc"]) {
				$timediff = $prog["time_sec"] - $prev["time_sec"];
				$times[] = [$prog["team_name"], $prev["point_name"].'→'.$prog["point_name"], gmdate("H:i:s", (int)$timediff)];
			}
		}
		usort($times, function($a, $b) { return -($a[2] <=> $b[2]); });
		return array_merge([["Tým", "Vyluštěná šifra", "Čas"]], array_slice($times, 0, 10));
	}

	public function barchart() {
		$solved = [];
		$ret = [];
		$ret[0] = [""];
		foreach ((new Team())->list() as $team) {
			$solved[$team["team_id"]] = 0;
			$ret[$team["team_id"]] = [$team["name"]];
		}
		$progress = (new ProgressRepo())->cipherProgress(Game::current());
		$prog = $progress->fetch(PDO::FETCH_ASSOC);
		for ($t = Settings::get("gameStartTimestamp") + 3600*3; $t < Settings::get("gameEndTimestamp"); $t += 60) {
			while ($prog != null && $prog["time"] <= $t) {
				$solved[$prog["team_id"]]++;
				$prog = $progress->fetch(PDO::FETCH_ASSOC);
			}
			$ret[0][] = date("H:i", $t);
			foreach (array_keys($solved) as $teamId) {
				$ret[$teamId][] = $solved[$teamId];
			}
			if ($prog == null) {
				break;
			}
			if ($t >= strtotime("2022-01-22 00:00:00") && $t < strtotime("2022-01-22 07:00:00")) {
				$t += 60*9;
			}
		}
		return $ret;
	}
}
