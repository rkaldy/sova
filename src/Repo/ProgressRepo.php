<?php
namespace Sova\Repo;

use Sova\DBException;

class ProgressRepo extends RepoBase {

	public function create(int $teamId, int $pointId) {
		try {
			$this->db->execute("INSERT INTO progress (team_id, point_id) values (?, ?)", [$teamId, $pointId]);
			return true;
		} catch (DBException $ex) {
			if ($ex->getCode() == 1062) {
				return false;
			}
			throw $ex;
		}
	}

	public function rankAtPoint(int $teamId, int $pointId) {
		return $this->db->equery("
			SELECT rank FROM
			  (SELECT team_id, ROW_NUMBER() OVER (ORDER BY time) AS rank FROM progress WHERE point_id = ?) rank_table
			WHERE team_id = ?
		", $pointId, $teamId);
	}

	public function firstTeamAtPoint(int $pointId) {
		return $this->db->squery("
			SELECT name, DATE_FORMAT(time, '%H:%i') AS time FROM progress
			JOIN team ON team.team_id = progress.team_id
			WHERE point_id = ?
			ORDER BY progress.time LIMIT 1
		", $pointId);
	}
}
