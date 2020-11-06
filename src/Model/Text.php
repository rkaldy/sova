<?php
namespace Sova\Model;

use Sova\Repo\TextRepo;

class Text extends ModelBase {
	public $code;
	public $args;

	public function __construct(string $code = null, ...$args) {
		parent::__construct();
		$this->code = $code;
		$this->args = $args;
	}

	public function prepare(array &$text) {
		$text["game_id"] = Game::current();
	}

	public function format() {
		return vsprintf($this->repo->get(Game::current(), $this->code), $this->args);
	}
}
