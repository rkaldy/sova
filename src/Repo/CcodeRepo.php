<?php
namespace Sova\Repo;

class CcodeRepo extends RepoBase {

	public function get(int $id) {
		return $this->db->squery("SELECT ccode.*, code.code FROM ccode NATURAL JOIN code WHERE ccode_id = ?", $id);
	}

	public function list(int $gameId) {
		return $this->db->aquery("SELECT ccode.ccode_id, code, cond_loc_id FROM ccode NATURAL JOIN code WHERE game_id = ?", $gameId);
	}

	public function create(array &$ccode) {
		try {
			$this->db->execute("INSERT INTO ccode (game_id, cond_loc_id) VALUES (:game_id, :cond_loc_id)", $ccode, true);
			$ccode["ccode_id"] = $this->db->lastInsertId();
			$this->db->execute("INSERT INTO code (game_id, ccode_id, code) VALUES (:game_id, :ccode_id, :code)", $ccode, true);
		} catch (DBException $ex) {
			$this->db->execute("DELETE FROM ccode WHERE ccode_id = :ccode_id", $ccode);
			throw $ex;
		}
	}

	public function update(array $ccode) {
		$this->db->execute("UPDATE code SET code = :code WHERE ccode_id = :ccode_id", $ccode);
		$this->db->execute("UPDATE ccode SET cond_loc_id = :cond_loc_id WHERE ccode_id = :ccode_id", $ccode);
	}

	public function delete(array $ccode) {
		$this->db->execute("DELETE FROM ccode WHERE ccode_id = :ccode_id", $ccode, true);
	}

	public function isAvailable(array $ccode, int $team_id) {
		return $this->db->equery("SELECT 1 FROM progress WHERE team_id = ? AND point_id = ?", $team_id, $ccode["cond_loc_id"]) == 1;
	}
}
