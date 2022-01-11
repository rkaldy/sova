<?php
namespace Sova\Model;

use Sova\Repo\StatisticsRepo;

class Statistics {

	protected $repo;
   
	public function __construct() {
		$this->repo	= new StatisticsRepo();
	}

	public function rank() {
		return array_merge(
			[["Tým", "Čas v cíli", "Počet vyluštěných šifer", "Čas poslední vyluštěné šifry", "Poslední navštívené stanoviště"]],
			(new ProgressRepo())->rankTotal(Game::current())
		);
	}

	public function ciphers() {
		return array_merge(
			[["Šifra", "Tým", "Doba luštění"]],
			$this->repo->ciphers(Game::current())
		);
	}
}
