<?php
namespace Sova\Model;

class Hint extends ModelBase {

	public function prepare(array &$hint) {
		$hint["game_id"] = Game::current();
		(new Code())->prepare($hint["code"]);
	}
}
