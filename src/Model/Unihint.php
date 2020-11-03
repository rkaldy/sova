<?php
namespace Sova\Model;

class Unihint extends ModelBase {
	public function prepare(array &$unihint) {
		$unihint["game_id"] = Game::current();
		(new Code())->prepare($unihint["code"]);
	}
}
