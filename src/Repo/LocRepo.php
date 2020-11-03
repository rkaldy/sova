<?php
namespace Sova\Repo;

use Sova\DBException;

class LocRepo extends PointRepo {

	public function isLoc(int $id) {
		return $this->db->equery("SELECT COUNT(*) FROM loc WHERE point_id = ?", $id) != 0;
	}

	public function get(int $id) {
		$loc = $this->db->squery("
			SELECT loc.point_id, name, description, end_time, min_ciphers_solved, 
			  GROUP_CONCAT(DISTINCT prev.from_point_id ORDER BY prev.from_point_id SEPARATOR ',') AS prev,
			  GROUP_CONCAT(DISTINCT next.to_point_id ORDER BY next.to_point_id SEPARATOR ',') AS next
			FROM loc 
			NATURAL JOIN point 
			LEFT JOIN step AS prev ON prev.to_point_id = loc.point_id
			LEFT JOIN step AS next ON next.from_point_id = loc.point_id
			WHERE loc.point_id = ?
		", $id);
		$this->flattenPrevNext($loc);
		return $loc;
	}

	public function list($gameId) {
		return $this->db->aquery("
			SELECT loc.point_id, name, description, end_time, min_ciphers_solved, code FROM loc
			NATURAL JOIN point
			NATURAL JOIN code
			WHERE point.game_id = ? 
			ORDER BY sort_id, name
		", $gameId);
	}

	function create(array &$loc) {
		try {
			$this->db->execute("INSERT INTO point (game_id, sort_id, name) VALUES (:game_id, :sort_id, :name)", $loc, true);
			$loc["point_id"] = $this->db->lastInsertId();
			$this->db->execute("INSERT INTO loc (point_id, description, end_time, min_ciphers_solved) VALUES (:point_id, :description, :end_time, :min_ciphers_solved)", $loc, true);
			$this->db->execute("INSERT INTO code (game_id, code, point_id) VALUES (:game_id, :code, :point_id)", $loc, true);
		} catch (DBException $ex) {
			$this->db->execute("DELETE FROM point WHERE point_id = :point_id", $loc);
			throw $ex;
		}
	}

	function update(array $loc) {
		$this->db->execute("UPDATE point SET sort_id = :sort_id, name = :name WHERE point_id = :point_id", $loc);
		$this->db->execute("UPDATE loc SET description = :description, end_time = :end_time, min_ciphers_solved = :min_ciphers_solved WHERE point_id = :point_id", $loc);
		$this->db->execute("UPDATE code SET code = :code WHERE point_id = :point_id", $loc);
	}

	function delete(array $loc) {
		$this->db->execute("DELETE FROM point WHERE point_id = :point_id", $loc, true);
	}

	
	public function hasPreviousCiphers(int $locId) {
		return $this->db->equery("SELECT COUNT(*) FROM step WHERE to_point_id = ?", $locId) != 0;
	}

	public function previousCipherSolved(int $teamId, int $locId) {
		return $this->db->equery("
			SELECT COUNT(*) FROM loc
			JOIN step ON loc.point_id = step.to_point_id
			JOIN progress ON progress.point_id = step.from_point_id
			WHERE team_id = ? AND loc.point_id = ?
		", $teamId, $locId) != 0;
	}
	
	public function getNextCiphers(int $locId) {
		return $this->db->aquery("
			SELECT point.*, cipher.*, code.code 
			FROM point
			NATURAL JOIN cipher
			NATURAL JOIN code
			JOIN step ON step.to_point_id = point.point_id
			WHERE from_point_id = ?
		", $locId);
	}

	public function visitedAllLocsWith(int $teamId, int $cipherId) {
		return $this->db->equery("
			SELECT COUNT(*) FROM step
			LEFT JOIN progress ON step.from_point_id = progress.point_id AND progress.team_id = ?
			WHERE step.to_point_id = ? AND progress.team_id IS NULL
		", $teamId, $cipherId) == 0;
	}
}

