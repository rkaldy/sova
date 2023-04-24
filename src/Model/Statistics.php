<?php
namespace Sova\Model;

use Sova\Repo\StatisticsRepo;
use Sova\Repo\CipherRepo;
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
			[["Tým", "Počet bodů", "Příchod do cíle", "Čas poslední vyřešené šifry/aktivity"]],
			(new ProgressRepo())->rankTotal(Game::current())
		);
	}

	public function ciphers() {
		$ret = [];
		for ($activity = 0; $activity <= 1; $activity++) {
			$ciphers = (new CipherRepo())->listAsArray(Game::current(), $activity);
			$stat = $this->repo->ciphers(Game::current(), $activity);
			if ($activity == 0) {
				$ret[] = ["Šifra", "Vyluštili", "Vyluštili s nápovědou", "Vyluštili s postupem", "Vyluštili s řešením", "Nevyluštili"];
			} else {
				$ret[] = [];
				$ret[] = ["Aktivita", "Dokončili", "", "", "", "Nedokončili"];
			}
			foreach ($ciphers as $cid => $name) {
				$ret[] = array_merge([$name], array_values($stat[$cid]));
			}
		}
		return $ret;
	}

	public function fastestsolved() {
		$ret = [];
		for ($activity = 0; $activity <= 1; $activity++) {
			$ciphers = (new CipherRepo())->listAsArray(Game::current(), $activity);
			$stat = $this->repo->fastestSolved(Game::current(), $activity);
			$map = [];
			$cid = null;
			while ($row = $stat->fetch(PDO::FETCH_ASSOC)) {
				if ($row["point_id"] != $cid) {
					$cid = $row["point_id"];
					$map[$cid] = [$row["team_name"], $row["solve_time"]];
				}
			}
			if ($activity == 0) {
				$ret[] = ["Šifra", "Nejrychlejší tým", "Čas vyluštění"];
			} else {
				$ret[] = [];
				$ret[] = ["Aktivita", "Nejrychlejší tým", "Čas dokončení"];
			}
			foreach ($ciphers as $cid => $name) {
				if (isset($map[$cid])) {
					$ret[] = array_merge([$name], array_values($map[$cid]));
				} else {
					$ret[] = [$name, "-", "-"];
				}
			}
		}
		return $ret;
	}

	public function ccodes() {
		return array_merge(
			[["Tým", "Počet céček"]],
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
