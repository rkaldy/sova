<?php
namespace Sova\Model;

class Loc extends ModelBase {

	public function prepare(array &$loc) {
		$loc["game_id"] = Game::current();
		(new Code())->prepare($loc["code"]);
	}

	public function checkPreviousCipherSolved($loc) {
		
		
	}
}
