<?php
namespace Sova\Repo;

use Sova\DBException;

class ProgressRepo extends RepoBase {

    public function create(int $teamId, int $pointId, $timeOffset = 0) {
        try {
            $this->db->execute("INSERT INTO progress (team_id, point_id, time) values (?, ?, DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL ? MINUTE))", [$teamId, $pointId, $timeOffset]);
            return true;
        } catch (DBException $ex) {
            if ($ex->getCode() == 1062) {
                return false;
            }
            throw $ex;
        }
    }

    public function isDone(int $teamId, int $pointId) {
        return $this->db->equery("SELECT 1 FROM progress WHERE team_id = ? AND point_id = ?", $teamId, $pointId) != 0;
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


	public function rankTotal(int $gameId): array {
		return $this->db->aquery("
            SELECT 
				team.name, 
				team.points,
                IFNULL(DATE_FORMAT(finish.time, '%H:%i:%s'), '-') AS finish_time, 
				IFNULL(DATE_FORMAT(last_cipher.time, '%H:%i:%s'), '-') AS last_cipher_time,
				imunity.team_id IS NOT NULL AS imunity
            FROM team
            LEFT JOIN (
                SELECT team_id, MAX(time) as time
                FROM progress
                NATURAL JOIN point
                NATURAL JOIN cipher
                WHERE game_id = :game_id
                GROUP BY team_id
            ) last_cipher ON last_cipher.team_id = team.team_id
            LEFT JOIN (
                SELECT team_id, progress.time
                FROM settings
                JOIN progress ON progress.point_id = settings.locFinish
                WHERE settings.game_id = :game_id
			) finish ON finish.team_id = team.team_id
			LEFT JOIN (
				SELECT DISTINCT team_id
				FROM hint
				WHERE type = 4
			) imunity ON imunity.team_id = team.team_id
            ORDER BY points DESC, -finish.time DESC, last_cipher.time, team.name
        ", ["game_id" => $gameId]);
	}


	public function cipherProgress(int $gameId) {
		return $this->db->query("
			SELECT team_id, progress.point_id, UNIX_TIMESTAMP(time) AS time
			FROM progress
			NATURAL JOIN point
			NATURAL JOIN cipher
			WHERE game_id = ?
			ORDER BY time
		", $gameId);
	}

    public function progressByTeam(int $gameId) {
        return $this->db->aquery("
            SELECT team.team_id, team.name AS team_name, point.name AS point_name, progress.time, UNIX_TIMESTAMP(progress.time) as time_sec, ISNULL(cipher.point_id) AS is_loc
            FROM progress
            JOIN team ON team.team_id = progress.team_id
            JOIN point ON point.point_id = progress.point_id
            LEFT JOIN cipher ON cipher.point_id = progress.point_id
            WHERE team.game_id = ?
            ORDER BY team.team_id, progress.time
        ", $gameId);
    }

    public function locStatus(int $gameId) {
        return $this->db->aquery("
            SELECT point_id, team.name AS team_name, DATE_FORMAT(time, '%H:%i:%s') AS time, time >=DATE_SUB(CURRENT_TIMESTAMP(), INTERVAL 1 MINUTE) AS recent, team.points
            FROM progress
            NATURAL JOIN team
            NATURAL JOIN (
                SELECT team_id, max(time) as time
                FROM progress 
                NATURAL JOIN point
                NATURAL JOIN loc
                WHERE game_id = :game_id
                GROUP BY team_id
            ) last
            ORDER BY point_id, time
        ", ["game_id" => $gameId]);
    }

	public function cipherStatus(int $gameId) {
		return $this->db->aquery("
			SELECT point.point_id, point.name, team.name AS team_name, DATE_FORMAT(progress.time, '%H:%i:%s') AS time, progress.time >=DATE_SUB(CURRENT_TIMESTAMP(), INTERVAL 1 MINUTE) AS recent, IFNULL(MAX(hint.type), 0) AS hint_type
            FROM progress
            JOIN point ON point.point_id = progress.point_id
			JOIN cipher ON cipher.point_id = point.point_id
            JOIN team ON team.team_id = progress.team_id
			LEFT JOIN hint ON hint.team_id = progress.team_id AND hint.cipher_id = progress.point_id
            WHERE point.game_id = ?
			GROUP BY point.point_id, progress.team_id
	        ORDER BY point.point_id, time
		", $gameId);
	}
}
