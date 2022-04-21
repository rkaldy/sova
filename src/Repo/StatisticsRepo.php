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

	public function ciphers(int $gameId) {
		return $this->query("
			SELECT point.name, team.name, TIMEDIFF(solved.time, MAX(arrive.time)) AS solve_time
			FROM cipher 
			NATURAL JOIN point
			JOIN step ON step.to_point_id = cipher.point_id
			JOIN loc ON loc.point_id = step.from_point_id
			JOIN progress solved ON solved.point_id = cipher.point_id
			JOIN progress arrive ON arrive.point_id = step.from_point_id AND arrive.team_id = solved.team_id
			JOIN team ON team.team_id = solved.team_id
			WHERE point.game_id = ?
			GROUP BY point.name, team.name
			HAVING solve_time >= '00:01:00'
			ORDER BY loc.order_id, point.name, solve_time
		", $gameId);
	}

	public function hints(int $gameId) {
		return $this->query("
			SELECT 
				name,
				COUNT(DISTINCT IF(hint.type IS NULL, solved.team_id, NULL)) AS solved,
				COUNT(DISTINCT IF(hint.type = 1, solved.team_id, NULL)) AS solved_with_hint,
				COUNT(DISTINCT IF(hint.type = 2, solved.team_id, NULL)) AS solved_with_howto,
				COUNT(DISTINCT IF(hint.type = 3, solved.team_id, NULL)) AS solved_with_solution,
				COUNT(DISTINCT IF(solved.point_id IS NULL, arrive.team_id, NULL)) AS not_solved
			FROM cipher
			NATURAL JOIN point
			JOIN step ON step.to_point_id = cipher.point_id
			JOIN progress arrive ON arrive.point_id = step.from_point_id
			LEFT JOIN progress solved ON solved.point_id = cipher.point_id AND solved.team_id = arrive.team_id
			LEFT JOIN (
				SELECT cipher_id, team_id, MAX(type) AS type
				FROM hint
				GROUP BY cipher_id, team_id
			) hint ON hint.cipher_id = cipher.point_id AND hint.team_id = solved.team_id
			WHERE game_id = ?
			GROUP BY cipher.point_id
			ORDER BY name
		", $gameId);
	}

	public function solvedCiphers(int $gameId, string $to) {
		return $this->squery("
			SELECT team_id, COUNT(cipher.point_id) 
			FROM progress 
			NATURAL JOIN point
			NATURAL JOIN cipher 
			WHERE game_id = ? AND time < ?
			GROUP BY team_id
		", $gameId, $to);
	}

	public function ccodes(int $gameId) {
		return $this->query("
			SELECT team.name, COUNT(hint_id) AS ccodes
			FROM team
			LEFT JOIN hint ON hint.team_id = team.team_id AND hint.ccode_id IS NOT NULL
			WHERE game_id = ?
			GROUP BY team.team_id
			ORDER BY ccodes DESC
		", $gameId);
	}
}
