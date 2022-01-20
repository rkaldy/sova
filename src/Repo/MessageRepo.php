<?php
namespace Sova\Repo;

class MessageRepo extends RepoBase {

	public const FROM_TEAM = 1;
	public const TO_TEAM = 2;


	public function list(int $gameId, int $limit, int $offset) {
		return $this->db->aquery("
			SELECT time, team.name AS name, direction, async, text
			FROM message NATURAL JOIN team
			WHERE team.game_id = ? AND time <= NOW()
			ORDER BY time DESC, direction DESC 
			LIMIT ?, ?
		", $gameId, $limit, $offset);
	}

	public function listForTeam(int $teamId, $limit, $offset) {
		return $this->db->aquery("
			SELECT time, direction, async, text
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

	public function create(array $message) {
		if (isset($message["time"])) {
			$message["async"] = 1;
			$this->db->execute("INSERT INTO message (team_id, hint_id, direction, async, time, text) VALUES (:team_id, :hint_id, :direction, :async, DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL :time MINUTE), :text)", $message);
		} else {
			$message["async"] = 0;
			$this->db->execute("INSERT INTO message (team_id, hint_id, direction, async, text) VALUES (:team_id, :hint_id, :direction, :async, :text)", $message);
		}
	}

	public function broadcast(array $teams, string $message) {
		$stmt = $this->db->prepare("INSERT INTO message (team_id, direction, async, text) VALUES (?, ?, ?, ?)");
		foreach ($teams as $team_id) {
			$stmt->execute([$team_id, self::TO_TEAM, 1, $message]);
		}
	}
}
