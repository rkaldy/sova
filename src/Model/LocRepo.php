<?php
namespace Sova\Model;

use Sova\DBException;

class LocRepo extends CRUD {

	public function get($id) {
		return $this->db->squery("SELECT * FROM loc NATURAL JOIN point WHERE loc.point_id = ?", $id);
	}

	public function list() {
		return $this->db->aquery("
			SELECT point.*, loc.*, code FROM loc
			NATURAL JOIN point
			JOIN code ON loc.point_id = code.point_id 
			WHERE point.game_id = ? 
			ORDER BY sort_id, name
		", Game::current());
	}

	function create($loc) {
		$loc["game_id"] = Game::current();
		Code::prepare($loc["code"]);
		try {
			$this->db->execute("INSERT INTO point (game_id, sort_id, name) VALUES (:game_id, :sort_id, :name)", $loc, true);
			$loc["point_id"] = $this->db->lastInsertId();
			$this->db->execute("INSERT INTO loc (point_id, description, end_time, min_ciphers_solved) VALUES (:point_id, :description, :end_time, :min_ciphers_solved)", $loc, true);
			$this->db->execute("INSERT INTO code (game_id, code, point_id) VALUES (:game_id, :code, :point_id)", $loc, true);
			return $loc;
		} catch (DBException $ex) {
			$this->db->execute("DELETE FROM point WHERE point_id = :point_id", $loc);
			throw $ex;
		}
	}

	function update($loc) {
		Code::prepare($loc["code"]);
		$this->db->execute("UPDATE point SET sort_id = :sort_id, name = :name WHERE point_id = :point_id", $loc);
		$this->db->execute("UPDATE loc SET description = :description, end_time = :end_time, min_ciphers_solved = :min_ciphers_solved WHERE point_id = :point_id", $loc);
		$this->db->execute("UPDATE code SET code = :code WHERE point_id = :point_id", $loc);
		return $loc;
	}

	function delete($loc) {
		$this->db->execute("DELETE FROM point WHERE point_id = :point_id", $loc, true);
		return $loc;
	}
	
}

