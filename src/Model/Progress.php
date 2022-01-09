<?php
namespace Sova\Model;

class Progress extends ModelBase {

	public function create(array &$obj) {
		return $this->repo->create(Team::current(), $obj["point_id"]);
	}

	public function getRank(array $obj) {
		$rank = $this->repo->rankAtPoint(Team::current(), $obj["point_id"]);
		$firstTeam = $this->repo->firstTeamAtPoint($obj["point_id"]);
		return [$rank, $firstTeam["name"], $firstTeam["time"]];
	}

	public function rankTotal() {
		return $this->repo->rankTotal(Game::current());
	}
}
