<?php
namespace Sova\Repo;

class GameRepo extends RepoBase {
	
	public function get(int $gameId) {
		return $this->db->squery("SELECT * FROM game WHERE game_id = ?", $gameId);
	}

	public function getOwned(int $ownerId) {
		return $this->db->aquery("SELECT game_id, name FROM game WHERE owner_id = ? ORDER BY name", $ownerId);
	}

	public function getIdByName(string $name, int $ownerId) {
		return $this->db->equery("SELECT game_id FROM game WHERE owner_id = ? AND name = ?", $ownerId, $name);
	}

	public function list() {
		return $this->db->aquery("SELECT * FROM game ORDER BY name");
	}

	public function create(array &$game) {
		$this->db->execute("INSERT INTO game (owner_id, name) VALUES (:owner_id, :name)", $game, true);
		$game["game_id"] = $this->db->lastInsertId();
		$this->db->execute("INSERT INTO text (game_id, code, text) SELECT ?, code, text FROM text WHERE game_id IS NULL", $game["game_id"]);
		$this->db->execute("INSERT INTO settings (game_id) VALUES (?)", $game["game_id"]);
	}

	public function update(array $game) {
		$this->db->execute("UPDATE game SET owner_id = :owner_id, name = :name WHERE game_id = :game_id", $game);
	}

	public function delete(array $game) {
		$this->db->execute("DELETE FROM game WHERE game_id = :game_id", $game, true);
	}
}
