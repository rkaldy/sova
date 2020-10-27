<?php
namespace Sova\Repo;

class GameRepo extends RepoBase {
	
	public function get(int $gameId, int $ownerId) {
		return $this->db->aquery("SELECT * FROM game WHERE game_id = ? AND owner_id = ?", $gameId, $ownerId);
	}

	public function getOwned(int $ownerId) {
		return $this->db->aquery("SELECT game_id, name FROM game WHERE owner_id = ? ORDER BY start_time DESC", $ownerId);
	}

	public function list() {
		return $this->db->aquery("SELECT * FROM game ORDER BY start_time DESC");
	}

	public function create(array &$game) {
		$this->db->execute("INSERT INTO game (owner_id, name, start_time, end_time) VALUES (:owner_id, :name, :start_time, :end_time)", $game, true);
		$game["game_id"] = $this->db->lastInsertId();
	}

	public function update(array $game) {
		$this->db->execute("UPDATE game SET owner_id = :owner_id, name = :name, start_time = :start_time, end_time = :end_time WHERE game_id = :game_id", $game);
	}

	public function delete(array $game) {
		$this->db->execute("DELETE FROM game WHERE game_id = :game_id", $game, true);
	}
}
