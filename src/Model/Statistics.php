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
}
