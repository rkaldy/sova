<?php
namespace Sova\Model;

use Sova\Repo\CodeRepo;


class Code {

	protected $repo;

	public function __construct() {
		$this->repo = new CodeRepo();
	}

	public static function polish($code) {
		return strtoupper(iconv("utf-8", "ascii//TRANSLIT", trim($code)));
	}

	public static function valid($code) {
		return preg_match("/^[A-Z]+$/", $code);
	}

	public function prepare(?string &$code) {
		if (empty($code)) {
			$code = $this->generate();
		} else {
			$code = self::polish($code);
		}
	}
	
	public function generate() {
		do {
			$code = $this->repo->randomCode();
		} while (!$this->repo->isUnique($code, Game::current()));
		return $code;
	}

	public function get($code) {
		return $this->repo->get($code, Game::current());
	}
}
