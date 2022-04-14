<?php
namespace Sova\Repo;

class MessageRepo extends RepoBase {

	public const FROM_TEAM = 1;
	public const TO_TEAM = 2;


	public function list(int $gameId, int $limit, int $offset) {
		return $this->db->aquery("
			SELECT time, team.name AS name, direction, text
			FROM message NATURAL JOIN team
			WHERE team.game_id = ? AND time <= NOW()
			ORDER BY time DESC, direction DESC 
			LIMIT ?, ?
		", $gameId, $limit, $offset);
	}

	public function listForTeam(int $teamId, $limit, $offset) {
		return $this->db->aquery("
			SELECT time, direction, text
			FROM message
			WHERE team_id = ? AND time <= NOW()
			ORDER BY time DESC, direction DESC
			LIMIT ?, ?
		", $teamId, $limit, $offset);
	}

	public function count(int $gameId) {
		return $this->db->equery("
			SELECT COUNT(*) FROM message 
			NATURAL JOIN team
			WHERE team.game_id = ? AND time <= NOW()
		", $gameId);
	}

	public function countForTeam($teamId) {
		return $this->db->equery("SELECT COUNT(*) FROM message WHERE team_id = ? AND time <= NOW()", $teamId);
	}

	public function recent($teamId, $since) {
		return $this->db->query("SELECT text FROM message WHERE team_id = ? AND time >= ? ORDER BY time", [$teamId, $since]);
	}

	public function create(array $message) {
		$this->db->execute("INSERT INTO message (team_id, direction, text) VALUES (:team_id, :direction, :text)", $message);
	}

	public function broadcast(array $teams, string $message) {
		$stmt = $this->db->prepare("INSERT INTO message (team_id, direction, text) VALUES (?, ?, ?)");
		foreach ($teams as $team_id) {
			$stmt->execute([$team_id, self::TO_TEAM, $message]);
		}
	}

}
