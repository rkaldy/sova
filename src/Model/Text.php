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
		$pattern = $this->repo->get(Game::current(), $this->code);
		if (!isset($pattern)) {
			throw new \Exception("No text pattern defined for $this->code");
		}
		return vsprintf($pattern, $this->args);
	}
}
