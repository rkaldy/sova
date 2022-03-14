<?php
namespace Sova\Model;

class Ccode extends ModelBase {
	public function prepare(array &$ccode) {
		$ccode["game_id"] = Game::current();
		(new Code())->prepare($ccode["code"]);
	}
}
