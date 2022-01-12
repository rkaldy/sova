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
}
