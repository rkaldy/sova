<?php
namespace Sova\Model;

use Sova\DBException;

class CipherRepo extends CRUD {

	public function get($id) {
		return $this->db->squery("SELECT * FROM cipher NATURAL JOIN point WHERE cipher.point_id = ?", $id);
	}

	function list() {
		$ciphers = $this->db->aquery("
			SELECT point.*, cipher.*, code, GROUP_CONCAT(DISTINCT prev.from_point_id ORDER BY prev.from_point_id SEPARATOR ',') AS prev, GROUP_CONCAT(DISTINCT next.to_point_id ORDER BY next.to_point_id SEPARATOR ',') AS next 
			FROM cipher 
			NATURAL JOIN point
			JOIN code ON code.point_id = cipher.point_id
			LEFT JOIN step AS prev ON prev.to_point_id = cipher.point_id
			LEFT JOIN step AS next ON next.from_point_id = cipher.point_id
			WHERE point.game_id = ?
			GROUP BY cipher.point_id
			ORDER BY sort_id, name
		", Game::current());
		foreach ($ciphers as &$cipher) {
			$cipher["prev"] = empty($cipher["prev"]) ? array() : explode(",", $cipher["prev"]);
			$cipher["next"] = empty($cipher["next"]) ? array() : explode(",", $cipher["next"]);
		}
		return $ciphers;
	}

	private function addPrevNextLocs($cipher) {
		if (isset($cipher["prev"])) {
			foreach ($cipher["prev"] as $prev) {
				$this->db->execute("INSERT INTO step (from_point_id, to_point_id) VALUES (?, ?)", array($prev, $cipher["point_id"]), true);
			}
		}
		if (isset($cipher["next"])) {
			foreach ($cipher["next"] as $next) {
				$this->db->execute("INSERT INTO step (from_point_id, to_point_id) VALUES (?, ?)", array($cipher["point_id"], $next), true);
			}
		}
	}

	function create($cipher) {
		$cipher["game_id"] = Game::current();
		Code::prepare($cipher["code"]);
		try {
			$this->db->execute("INSERT INTO point (game_id, sort_id, name) VALUES (:game_id, :sort_id, :name)", $cipher, true);
			$cipher["point_id"] = $this->db->lastInsertId();
			$this->db->execute("INSERT INTO cipher (point_id, name_int, solution_timeout, hint, hint_timeout) VALUES (:point_id, :name_int, :solution_timeout, :hint, :hint_timeout)", $cipher, true);
			$this->db->execute("INSERT INTO code (game_id, point_id, code) VALUES (:game_id, :point_id, :code)", $cipher, true);
			$this->addPrevNextLocs($cipher);
			return $cipher;
		} catch (DBException $ex) {
			$this->db->execute("DELETE FROM point WHERE point_id = :point_id", $cipher);
			throw $ex;
		}
	}

	function update($cipher) {
		Code::prepare($cipher["code"]);
		$this->db->execute("UPDATE point SET sort_id = :sort_id, name = :name WHERE point_id = :point_id", $cipher);
		$this->db->execute("UPDATE cipher SET name_int = :name_int, solution_timeout = :solution_timeout, hint = :hint, hint_timeout = :hint_timeout WHERE point_id = :point_id", $cipher);
		$this->db->execute("UPDATE code SET code = :code WHERE point_id = :point_id", $cipher);
		$this->db->execute("DELETE FROM step WHERE from_point_id = :point_id OR to_point_id = :point_id", $cipher);
		$this->addPrevNextLocs($cipher);
		return $cipher;
	}

	function delete($cipher) {
		$this->db->execute("DELETE FROM point WHERE point_id = :point_id", $cipher, true);
		return $cipher;
	}
}
