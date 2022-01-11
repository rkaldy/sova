<?php
namespace Sova\Repo;

class StatisticsRepo extends RepoBase {

	public function ciphers(int $gameId) {
		return $this->db->execute("
			SELECT point.name, team.name, TIMEDIFF(solved.time, arrive.time) AS solve_time
			FROM cipher 
			NATURAL JOIN point
			JOIN step ON step.to_point_id = cipher.point_id
			JOIN progress solved ON solved.point_id = cipher.point_id
			JOIN progress arrive ON arrive.point_id = step.from_point_id AND arrive.team_id = solved.team_id
			JOIN team ON team.team_id = solved.team_id
			WHERE point.game_id = ?
			ORDER BY point.name, solve_time
		", $gameId);
	}
}
