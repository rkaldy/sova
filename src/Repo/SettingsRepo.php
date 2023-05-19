<?php
namespace Sova\Repo;

class SettingsRepo extends RepoBase {

	public function get(int $gameId) {
		return $this->db->squery("SELECT * FROM settings WHERE game_id = ?", $gameId);
	}

	public function set(int $gameId, array $settings) {
		$sql = "UPDATE settings SET";
		$first = true;
		foreach ($settings as $key => $value) {
			if (!$first) {
				$sql .= ",";
			}
			$sql .= " $key = :$key";
			$first = false;
		}
		$stmt = $this->db->execute($sql, $settings);
	}

	public function reset(int $gameId) {
		$this->db->execute("UPDATE team SET points = 0 WHERE game_id = ?", $gameId);
		$this->db->execute("DELETE FROM hints WHERE team_id IN (SELECT team_id FROM team WHERE game_id = ?)", $gameId);
		$this->db->execute("DELETE FROM progress WHERE team_id IN (SELECT team_id FROM team WHERE game_id = ?)", $gameId);
		$this->db->execute("DELETE FROM message WHERE team_id IN (SELECT team_id FROM team WHERE game_id = ?)", $gameId);
	}
}
