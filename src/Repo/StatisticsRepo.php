<?php
namespace Sova\Repo;

class StatisticsRepo extends RepoBase {

    public function query($sql, ...$params): array {
        $ret = [];
        $stmt = $this->db->execute($sql, $params);
        while ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            $ret[] = $row;
        }
        return $ret;
    }

	public function ciphers(int $gameId, int $activity) {
		return $this->db->daquery("
			SELECT 
				cipher.point_id,
				COUNT(DISTINCT IF(hint.type IS NULL, solved.team_id, NULL)) AS solved,
				COUNT(DISTINCT IF(hint.type = 1, solved.team_id, NULL)) AS solved_with_hint,
				COUNT(DISTINCT IF(hint.type = 2, solved.team_id, NULL)) AS solved_with_howto,
				COUNT(DISTINCT IF(hint.type = 3, solved.team_id, NULL)) AS solved_with_solution,
				COUNT(DISTINCT IF(solved.point_id IS NULL, arrive.team_id, NULL)) AS not_solved
			FROM cipher
			NATURAL JOIN point
			JOIN step ON step.to_point_id = cipher.point_id
			LEFT JOIN progress arrive ON arrive.point_id = step.from_point_id
			LEFT JOIN progress solved ON solved.point_id = cipher.point_id AND solved.team_id = arrive.team_id
			LEFT JOIN (
				SELECT cipher_id, team_id, MAX(type) AS type
				FROM hint
				GROUP BY cipher_id, team_id
			) hint ON hint.cipher_id = cipher.point_id AND hint.team_id = solved.team_id
			WHERE game_id = ? AND cipher.activity = ?
			GROUP BY cipher.point_id
		", $gameId, $activity);
	}

	public function fastestSolved(int $gameId, int $activity) {
		return $this->db->query("
			SELECT cipher.point_id, team.name AS team_name, TIMEDIFF(solved.time, MAX(arrive.time)) AS solve_time
			FROM cipher 
			NATURAL JOIN point
			JOIN step ON step.to_point_id = cipher.point_id
			JOIN progress solved ON solved.point_id = cipher.point_id
			JOIN progress arrive ON arrive.point_id = step.from_point_id AND arrive.team_id = solved.team_id
			JOIN team ON team.team_id = solved.team_id
			WHERE point.game_id = ? AND cipher.activity = ?
			GROUP BY cipher.point_id, team.team_id
			HAVING solve_time >= '00:01:00'
			ORDER BY cipher.point_id, solve_time
		", $gameId, $activity);
	}

	public function ccodes(int $gameId) {
		return $this->query("
			SELECT team.name, COUNT(ccode_id) AS count
			FROM team
			JOIN hint ON hint.team_id = team.team_id
			WHERE game_id = ? AND hint.ccode_id IS NOT NULL
			GROUP BY team.team_id
			ORDER BY count DESC
		", $gameId);
	}

	public function points(int $gameId) {
		return $this->query("
			SELECT
				team.name,
				IFNULL(earned.cipher_points, 0) AS cipher_points,
				IFNULL(earned.activity_points, 0) AS activity_points,
				IFNULL(earned.loc_points, 0) AS loc_points,
				IFNULL(-spent.hint_points, 0) AS hint_points,
				IFNULL(-spent.howto_points, 0) AS howto_points,
				IFNULL(-spent.solution_points, 0) AS solution_points
			FROM team
			LEFT JOIN (
				SELECT
					team_id,
					SUM(IF(activity = 0, point.points, 0)) AS cipher_points,
					SUM(IF(activity = 1, point.points, 0)) AS activity_points,
					SUM(IF(cipher.point_id IS NULL, point.points, 0)) AS loc_points
    			FROM progress
				JOIN point ON point.point_id = progress.point_id
				LEFT JOIN cipher ON cipher.point_id = progress.point_id
				WHERE point.game_id = ?
				GROUP BY team_id
			) earned ON earned.team_id = team.team_id
			LEFT JOIN (
				SELECT
					hint.team_id,
					SUM(IF(hint.type = 1, settings.hintPoints, 0)) AS hint_points,
					SUM(IF(hint.type = 2, settings.howtoPoints, 0)) AS howto_points,
					SUM(IF(hint.type = 3, settings.solutionPoints, 0)) AS solution_points
				FROM hint
				JOIN team hint_team ON hint_team.team_id = hint.team_id
				JOIN settings ON settings.game_id = hint_team.game_id
				WHERE hint_team.game_id = ? AND hint.ccode_id IS NULL
				GROUP BY hint.team_id
			) spent ON spent.team_id = team.team_id
			WHERE team.game_id = ?
			ORDER BY team.name
		", $gameId, $gameId, $gameId);
	}

	public function barchartRace(int $gameId) {
		return $this->db->query("
				SELECT team_id, UNIX_TIMESTAMP(time) AS time, point.points
				FROM progress
				NATURAL JOIN point
				WHERE game_id = :gameId AND points != 0
			UNION ALL
				SELECT team_id, UNIX_TIMESTAMP(time) AS time,
					CASE
						WHEN hint.type = 1 THEN -hintPoints
						WHEN hint.type = 2 THEN -howtoPoints
						WHEN hint.type = 3 THEN -solutionPoints
				END AS points
				FROM hint
				NATURAL JOIN team
				JOIN settings ON settings.game_id = :gameId
				WHERE team.game_id = :gameId AND hint.ccode_id IS NULL
			ORDER BY time
		", ["gameId" => $gameId]);
	}

}
