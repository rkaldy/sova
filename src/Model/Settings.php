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
		$settings = $this->repo->get(Game::current());
		$settings["locs"] = $this->locRepo->list(Game::current());
		if (isset($settings["gameStart"])) {
			$settings["gameStartTimestamp"] = strtotime($settings["gameStart"]);
		}
		if (isset($settings["gameEnd"])) {
			$settings["gameEndTimestamp"] = strtotime($settings["gameEnd"]);
		}
		return $settings;
	}

	public function set($settings) {
		if (!empty($settings["locFinish"])) {
			if (!$this->locRepo->isLoc($settings["locFinish"])) {
				return "Chyba: Neznámé číslo stanoviště";
			}
		}
		$settings["locVisitMandatory"] = (int)isset($settings["locVisitMandatory"]);
		$settings["showRank"] = (int)isset($settings["showRank"]);
		$this->repo->set(Game::current(), $settings);
		return "Nastavení bylo uloženo";
	}

	public static function isset(string $key) {
		return isset($_SESSION["settings"][$key]);
	}

	public static function value(string $key) {
		return $_SESSION["settings"][$key];
	}


	public function load() {
		$_SESSION["settings"] = $this->get();
	}

	public static function isLocVisitMandatory() 	{ return $_SESSION["settings"]["locVisitMandatory"]; }
	public static function showRank()			 	{ return $_SESSION["settings"]["showRank"]; }
	public static function getFinish()				{ return $_SESSION["settings"]["locFinish"]; }
}
