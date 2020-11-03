<?php
namespace Sova\Repo;

class UnihintRepo extends RepoBase {

	public function get(int $id) {
		return array("unihint_id" => $id);
	}

	public function list(int $gameId) {
		return $this->db->aquery("SELECT unihint.unihint_id, code FROM unihint NATURAL JOIN code WHERE game_id = ?", $gameId);
	}

	public function create(array &$unihint) {
		try {
			$this->db->execute("INSERT INTO unihint (game_id) VALUES (:game_id)", $unihint, true);
			$unihint["unihint_id"] = $this->db->lastInsertId();
			$this->db->execute("INSERT INTO code (game_id, unihint_id, code) VALUES (:game_id, :unihint_id, :code)", $unihint, true);
		} catch (DBException $ex) {
			$this->db->execute("DELETE FROM unihint WHERE unihint_id = :unihint_id", $unihint);
			throw $ex;
		}
	}

	public function update(array $unihint) {
		$this->db->execute("UPDATE code SET code = :code WHERE unihint_id = :unihint_id", $unihint);
	}

	public function delete(array $unihint) {
		$this->db->execute("DELETE FROM unihint WHERE unihint_id = :unihint_id", $unihint, true);
	}
}
