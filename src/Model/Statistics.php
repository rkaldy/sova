<?php
namespace Sova\Model;

use Sova\Repo\StatisticsRepo;
use Sova\Repo\ProgressRepo;
use DateTime;
use DateTimeZone;
use PDO;


class Statistics {

	protected $repo;
   
	public function __construct() {
		$this->repo	= new StatisticsRepo();
	}

	public function rank() {
		return array_merge(
			[["Tým", "Počet bodů", "Příchod do cíle", "Čas poslední vyřešené šifry/aktivity", "Imunita?"]],
			(new ProgressRepo())->rankTotal(Game::current())
		);
	}

	public function ciphers() {
		$ciphers = $this->repo->ciphers(Game::current());
		$hints = $this->repo->hints(Game::current());
		$ret = [["Šifra", "Vyluštili", "Vyluštili s nápovědou", "Vyluštili s postupem", "Vyluštili s řešením", "Nevyluštili", "Nejrychlejší tým", "Nejrychejší čas"]];
		$cid = null;
		foreach ($ciphers as $cipher) {
			if ($cipher["point_id"] != $cid) {
				$cid = $cipher["point_id"];
				$hint = $hints[$cid];
				$ret[] = array_merge($hints[$cid], [$cipher["team_name"], $cipher["solve_time"]]);
			}
		}
		return $ret;
	}

	public function ccodes() {
		return array_merge(
			[["Tým", "Počet céček", "Čas nalezení posledního céčka"]],
			$this->repo->ccodes(Game::current())
		);
	}

	public function barchart() {
		$points = [];
		$ret = [];
		$ret[0] = [""];
		foreach ((new Team())->list() as $team) {
			$ret[$team["team_id"]] = [$team["name"]];
			$points[$team["team_id"]] = 0;
		}
		$progress = $this->repo->barchartRace(Game::current());
		$prog = $progress->fetch(PDO::FETCH_ASSOC);

		$from = DateTime::createFromFormat("Y-m-d H:i:s", Settings::get("gameStart"), new DateTimeZone("Europe/Prague"))->getTimestamp();
		$to = DateTime::createFromFormat("Y-m-d H:i:s", Settings::get("gameEnd"), new DateTimeZone("Europe/Prague"))->getTimestamp();
		for ($t = $from; $t <= $to; $t += 60) {
			while ($prog != null && $prog["time"] <= $t) {
				$points[$prog["team_id"]] += $prog["points"];
				$prog = $progress->fetch(PDO::FETCH_ASSOC);
			}
			$ret[0][] = date("H:i", $t);
			foreach (array_keys($points) as $teamId) {
				$ret[$teamId][] = $points[$teamId];
			}
			if ($prog == null) {
				break;
			}
		}
		return $ret;
	}
}
