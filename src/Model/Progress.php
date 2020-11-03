<?php
namespace Sova\Model;

class Progress extends ModelBase {

	public function create(int $pointId) {
		return $this->repo->create(Team::current(), $pointId);
	}

	public function getRank(int $pointId) {
		$rank = $this->repo->rankAtPoint(Team::current(), $pointId);
		$firstTeam = $this->repo->firstTeamAtPoint($pointId);
		return [$rank, $firstTeam["name"], $firstTeam["time"]];
	}
}
