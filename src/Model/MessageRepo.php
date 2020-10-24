<?php
namespace Sova\Model;

use Sova\DB;

class MessageRepo {

	public const FROM_TEAM = 1;
	public const TO_TEAM = 2;

	protected $db;

	public function __construct() {
		$this->db = DB::get();
	}

	public function list($offset, $limit) {
		return $this->db->aquery("
			SELECT time, team.name, direction, text
			FROM message NATURAL JOIN team
			WHERE team.game_id = ?
			ORDER BY time DESC
			LIMIT ?, ?
		", Game::current(), $offset, $limit);
	}

	public function sendToSova(string $message) {
		$this->db->execute("INSERT INTO message (team_id, direction, text) VALUES (?, ?, ?)", array(Team::current(), self::FROM_TEAM, $message));
	}

	public function sendToTeam(string $message, $cipherId = null, $afterMinutes = 0) {
		$this->db->execute("INSERT INTO message (team_id, cipher_id, direction, time, text) VALUES (?, ?, ?, DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL ? MINUTE), ?)", array(Team::current(), $cipherId, self::TO_TEAM, $afterMinutes, $message));
	}

	public function broadcast(array $teams, string $message) {
		$stmt = $this->db->prepare("INSERT INTO message (team_id, direction, text) VALUES (?, ?, ?)");
		foreach ($teams as $team_id) {
			$stmt->execute(array($team_id, self::TO_TEAM, $message));
		}
	}
}
