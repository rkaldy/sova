<?php
namespace Sova\Repo;

class SettingsRepo extends RepoBase {

	public function get(int $gameId) {
		return $this->db->dquery("SELECT name, value FROM settings WHERE game_id = ?", $gameId);
	}

	public function set(int $gameId, array $settings) {
		$stmt = $this->db->prepare("UPDATE settings SET value = ? WHERE game_id = ? AND name = ?");
		foreach ($settings as $name => $value) {
			$stmt->execute([$value, $gameId, $name]);
		}
	}
}
