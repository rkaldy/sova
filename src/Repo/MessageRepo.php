<?php
namespace Sova\Repo;

use Sova\RestException;

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

	public function listForTeam(int $teamId) {
		return $this->db->aquery("
			SELECT time, direction, text
			FROM message
			WHERE team_id = ? AND time <= NOW()
			ORDER BY time DESC, direction DESC 
		", $teamId);
	}

	public function count(int $gameId) {
		return $this->db->equery("
			SELECT COUNT(*) FROM message 
			NATURAL JOIN team
			WHERE team.game_id = ? AND time <= NOW()
		", $gameId);
	}

	public function create(array $message) {
		if (isset($message["time"])) {
			$this->db->execute("INSERT INTO message (team_id, cipher_id, direction, time, text) VALUES (:team_id, :cipher_id, :direction, DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL :time MINUTE), :text)", $message);
		} else {
			$this->db->execute("INSERT INTO message (team_id, cipher_id, direction, text) VALUES (:team_id, :cipher_id, :direction, :text)", $message);
		}
	}

	public function broadcast(array $teams, string $message) {
		$stmt = $this->db->prepare("INSERT INTO message (team_id, direction, text) VALUES (?, ?, ?)");
		foreach ($teams as $team_id) {
			$stmt->execute(array($team_id, self::TO_TEAM, $message));
		}
	}
}
