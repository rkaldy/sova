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
			[["Tým", "Počet bodů", "Příchod do cíle", "Čas poslední vyřešené šifry/aktivity", "Poslední navštívené stanoviště", "Imunita?"]],
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
			[["Šifra", "Vyluštili", "Vyluštili s nápovědou", "Vyluštili s postupem", "Vyluštili s řešením", "Nevyluštili"]],
			$this->repo->hints(Game::current())
		);
	}

	public function ccodes() {
		return array_merge(
			[["Tým", "Počet céček"]],
			$this->repo->ccodes(Game::current())
		);
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
