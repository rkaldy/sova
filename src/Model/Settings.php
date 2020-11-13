<?php
namespace Sova\Model;

use Sova\Repo\LocRepo;

class Settings extends ModelBase {

	var $locRepo;

	public function __construct() {
		parent::__construct();
		$this->locRepo = new LocRepo();
	}

	public function get() {
		$fields = $this->repo->get(Game::current());
		$fields["locs"] = $this->locRepo->list(Game::current());
		return $fields;
	}

	public function set($settings) {
		if (!empty($settings["locFinish"])) {
			if (!$this->locRepo->isLoc($settings["locFinish"])) {
				return "Chyba: Neznámé číslo stanoviště";
			}
		}
		$settings["locVisitMandatory"] = (int)($settings["locVisitMandatory"] == "on");
		$this->repo->set(Game::current(), $settings);
		return "Nastavení bylo uloženo";
	}


	public function load() {
		$_SESSION["settings"] = $this->get();
	}

	public static function isLocVisitMandatory() 	{ return $_SESSION["settings"]["locVisitMandatory"]; }
	public static function getFinish()				{ return $_SESSION["settings"]["locFinish"]; }
}
