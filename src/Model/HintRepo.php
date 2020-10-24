<?php
namespace Sova\Model;

class HintRepo extends CRUD {

	public function get($id) {
		return $this->db->squery("SELECT * FROM hint WHERE hint_id = ?", $id);
	}

	public function list() {
		return $this->db->aquery("SELECT hint.*, code FROM hint NATURAL JOIN code WHERE game_id = ?", Game::current());
	}

	public function create($hint) {
		$hint["game_id"] = Game::current();
		Code::prepare($hint["code"]);
		try {
			$this->db->execute("INSERT INTO hint (game_id) VALUES (:game_id)", $hint, true);
			$hint["hint_id"] = $this->db->lastInsertId();
			$this->db->execute("INSERT INTO code (game_id, hint_id, code) VALUES (:game_id, :hint_id, :code)", $hint, true);
		} catch (DBException $ex) {
			$this->db->execute("DELETE FROM hint WHERE hint_id = :hint_id", $hint);
			throw $ex;
		}
		return $hint;
	}

	public function update($hint) {
		Code::prepare($hint["code"]);
		$this->db->execute("UPDATE code SET code = :code WHERE hint_id = :hint_id", $hint);
		return $hint;
	}

	public function delete($hint) {
		$this->db->execute("DELETE FROM hint WHERE hint_id = :hint_id", $hint, true);
		return $hint;
	}
}
