<?php
namespace Sova\Model;

use Sova\Repo\CodeRepo;


class Code {

	protected $repo;

	public function __construct() {
		$this->repo = new CodeRepo();
	}

	public static function polish($code) {
		return strtoupper(trim($code));
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
