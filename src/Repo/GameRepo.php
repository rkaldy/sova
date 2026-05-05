<?php
namespace Sova\Repo;

class GameRepo extends RepoBase {
	
	public function get(string $name) {
		return $this->db->squery("SELECT * FROM game WHERE name = ?", $name);
	}

	public function list() {
		return $this->db->aquery("SELECT game_id, name FROM game ORDER BY name");
	}

	public function create(array &$game) {
		if (empty($game["pswd"])) {
			throw new \Exception("Heslo nesmí být prázdné");
		}
		$this->db->execute("INSERT INTO game (name, pswd) VALUES (:name, :pswd)", $game, true);
		$game["game_id"] = $this->db->lastInsertId();
		$this->db->execute("INSERT INTO text (game_id, code, text) SELECT ?, code, text FROM text WHERE game_id IS NULL", $game["game_id"]);
		$this->db->execute("INSERT INTO settings (game_id) VALUES (?)", $game["game_id"]);
		unset($game["pswd"]);
	}

	public function update(array &$game) {
		if (isset($game["pswd"])) {
			$this->db->execute("UPDATE game SET name = :name, pswd = :pswd WHERE game_id = :game_id", $game);
			unset($game["pswd"]);
		} else {
			$this->db->execute("UPDATE game SET name = :name WHERE game_id = :game_id", $game);
		}
	}

	public function delete(array $game) {
		$this->db->execute("DELETE FROM game WHERE game_id = :game_id", $game, true);
	}

	public function superuserPassword() {
		return $this->db->equery("SELECT pswd FROM superuser");
	}

	public function updatePassword(int $gameId, string $pswd) {
		$this->db->execute("UPDATE game SET pswd = ? WHERE game_id = ?", [$pswd, $gameId]);
	}
}
