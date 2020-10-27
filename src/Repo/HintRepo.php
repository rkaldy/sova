<?php
namespace Sova\Repo;

class HintRepo extends RepoBase {

	public function get(int $id) {
		return array("hint_id" => $id);
	}

	public function list(int $gameId) {
		return $this->db->aquery("SELECT hint.hint_id, code FROM hint NATURAL JOIN code WHERE game_id = ?", $gameId);
	}

	public function create(array &$hint) {
		try {
			$this->db->execute("INSERT INTO hint (game_id) VALUES (:game_id)", $hint, true);
			$hint["hint_id"] = $this->db->lastInsertId();
			$this->db->execute("INSERT INTO code (game_id, hint_id, code) VALUES (:game_id, :hint_id, :code)", $hint, true);
		} catch (DBException $ex) {
			$this->db->execute("DELETE FROM hint WHERE hint_id = :hint_id", $hint);
			throw $ex;
		}
	}

	public function update(array $hint) {
		$this->db->execute("UPDATE code SET code = :code WHERE hint_id = :hint_id", $hint);
	}

	public function delete(array $hint) {
		$this->db->execute("DELETE FROM hint WHERE hint_id = :hint_id", $hint, true);
	}
}
