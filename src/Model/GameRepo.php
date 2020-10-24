<?php
namespace Sova\Model;

class GameRepo extends CRUD {
	
	public function get($gameId, $owner) {
		return $this->db->aquery("SELECT * FROM game WHERE game_id = ? AND owner_id = ?", $gameId, $owner);
	}

	public function getOwned($owner) {
		return $this->db->aquery("SELECT game_id, name FROM game WHERE owner_id = ? ORDER BY start_time DESC", $owner);
	}

	public function list() {
		return $this->db->aquery("SELECT * FROM game ORDER BY start_time DESC");
	}

	public function create($game) {
		$this->db->execute("INSERT INTO game (owner_id, name, start_time, end_time) VALUES (:owner_id, :name, :start_time, :end_time)", $game, true);
		$game["game_id"] = $this->db->lastInsertId();
		return $game;
	}

	public function update($game) {
		$this->db->execute("UPDATE game SET owner_id = :owner_id, name = :name, start_time = :start_time, end_time = :end_time WHERE game_id = :game_id", $game);
		return $game;
	}

	public function delete($game) {
		$this->db->execute("DELETE FROM game WHERE game_id = :game_id", $game, true);
		return $game;
	}
}
