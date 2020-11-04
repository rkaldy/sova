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

	public function rankTotal(int $gameId) {
		return $this->db->aquery("
			SELECT team.name, ciphers.count AS solved, DATE_FORMAT(last_cipher.time, '%H:%i:%s') AS last_cipher_time, last_loc.name AS last_loc
			FROM team
			JOIN (
				SELECT team_id, COUNT(cipher.point_id) AS count 
				FROM progress
				NATURAL JOIN point
				NATURAL JOIN cipher
				WHERE game_id = :game_id
				GROUP BY team_id
			) ciphers ON ciphers.team_id = team.team_id
			JOIN (
				SELECT team_id, time FROM (
					SELECT team_id, time, ROW_NUMBER() OVER (PARTITION BY team_id ORDER BY time DESC) AS rank
					FROM progress
					NATURAL JOIN point
					NATURAL JOIN cipher
					WHERE game_id = :game_id
				) loctimes
				WHERE rank = 1
			) last_cipher ON last_cipher.team_id = team.team_id
			JOIN (
				SELECT team_id, name, time FROM (
					SELECT team_id, point.name, time, ROW_NUMBER() OVER (PARTITION BY team_id ORDER BY time DESC) AS rank
					FROM progress
					NATURAL JOIN point
					NATURAL JOIN loc
					WHERE game_id = :game_id
				) loctimes
				WHERE rank = 1
			) last_loc ON last_loc.team_id = team.team_id
			ORDER BY ciphers.count DESC, last_cipher.time				
		", ["game_id" => $gameId]);
	}
}
