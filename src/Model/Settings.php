<?php
namespace Sova\Model;

use Sova\Repo\LocRepo;

class Settings extends ModelBase {

	const BOOLEANS = ["locVisitMandatory", "showRank"];
	var $locRepo;

	public function __construct() {
		parent::__construct();
		$this->locRepo = new LocRepo();
	}

	public function load() {
		$settings = $this->repo->get(Game::current());
		if (isset($settings["gameStart"])) {
			$settings["gameStartTimestamp"] = strtotime($settings["gameStart"]);
		}
		if (isset($settings["gameEnd"])) {
			$settings["gameEndTimestamp"] = strtotime($settings["gameEnd"]);
		}
		$_SESSION["settings"] = $settings;
	}

	public function save($settings) {
		if (!empty($settings["locFinish"])) {
			if (!$this->locRepo->isLoc($settings["locFinish"])) {
				return "Chyba: Neznámé číslo stanoviště";
			}
		}
		foreach (self::BOOLEANS as $var) {
			$settings[$var] = (int)isset($settings[$var]);
		}
		$this->repo->set(Game::current(), $settings);
		$_SESSION["settings"] = $settings;
		return "Nastavení bylo uloženo";
	}

	public static function isset(string $key) {
		return isset($_SESSION["settings"][$key]);
	}

	public static function set(string $key, $value) {
		$_SESSION["settings"][$key] = $value;
	}

	public static function get(string $key) {
		return $_SESSION["settings"][$key];
	}
}
