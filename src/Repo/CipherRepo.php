<?php
namespace Sova\Repo;

use Sova\DBException;

class CipherRepo extends PointRepo {

	protected const SQL = "
		    SELECT cipher.*, name, code, points,
			  GROUP_CONCAT(DISTINCT prev.from_point_id ORDER BY prev.from_point_id SEPARATOR ',') AS prev,
			  GROUP_CONCAT(DISTINCT next.to_point_id ORDER BY next.to_point_id SEPARATOR ',') AS next
			FROM cipher 
			NATURAL JOIN point 
			NATURAL JOIN code 
			LEFT JOIN step AS prev ON prev.to_point_id = cipher.point_id
			LEFT JOIN step AS next ON next.from_point_id = cipher.point_id";

	
	protected static function convert(array &$cipher) {
		self::convertBooleans($cipher, ["activity"]);
		self::flattenPrevNext($cipher);
	}


	public function isCipher(int $id) {
		return $this->db->equery("SELECT COUNT(*) FROM cipher WHERE point_id = ?", $id) != 0;
	}

	public function get(int $id) {
		$cipher = $this->db->squery(self::SQL." WHERE cipher.point_id = ?", $id);
		self::convert($cipher);
		return $cipher;
	}

	public function getByName(string $name, int $gameId) {
		$cipher = $this->db->squery(self::SQL." WHERE name = ? and point.game_id = ?", $name, $gameId);
		if (isset($cipher["point_id"])) {
			self::convert($cipher);
			return $cipher;
		} else {		
			return null;
		}
	}


	function list(int $gameId) {
		$ciphers = $this->db->aquery(
			self::SQL."
			WHERE point.game_id = ?
			GROUP BY cipher.point_id
			ORDER BY name
		", $gameId);
		foreach ($ciphers as &$cipher) {
			self::convert($cipher);
		}
		return $ciphers;
	}


	protected function addPrevNextLocs($cipher) {
		$this->db->execute("DELETE FROM step WHERE from_point_id = :point_id OR to_point_id = :point_id", $cipher);
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

	function create(array &$cipher) {
		try {
			$this->db->execute("INSERT INTO point (game_id, name, points) VALUES (:game_id, :name, :points)", $cipher, true);
			$cipher["point_id"] = $this->db->lastInsertId();
			$this->db->execute("INSERT INTO cipher (point_id, name_int, activity, hint) VALUES (:point_id, :name_int, :activity, :hint)", $cipher, true);
			$this->db->execute("INSERT INTO code (game_id, point_id, code) VALUES (:game_id, :point_id, :code)", $cipher, true);
			$this->addPrevNextLocs($cipher);
		} catch (DBException $ex) {
			$this->db->execute("DELETE FROM point WHERE point_id = :point_id", $cipher);
			throw $ex;
		}
	}

	function update(array &$cipher) {
		$this->db->execute("UPDATE point SET name = :name, points = :points WHERE point_id = :point_id", $cipher);
		$this->db->execute("UPDATE cipher SET name_int = :name_int, activity = :activity, hint = :hint, howto = :howto WHERE point_id = :point_id", $cipher);
		$this->db->execute("UPDATE code SET code = :code WHERE point_id = :point_id", $cipher);
		$this->addPrevNextLocs($cipher);
	}

	function delete(array $cipher) {
		$this->db->execute("DELETE FROM point WHERE point_id = :point_id", $cipher, true);
	}


	public function hasPreviousCiphers(array $cipher) {
		return $this->db->equery("
			SELECT COUNT(*) 
			FROM step AS prev
			JOIN step AS prev2 ON prev2.to_point_id = prev.from_point_id
			JOIN cipher ON cipher.point_id = prev2.from_point_id
			WHERE prev.to_point_id = ?
		", $cipher["point_id"]) != 0;
	}

	public function previousCiphersSolved(int $teamId, array $cipher) {
		return $this->db->equery("
			SELECT COUNT(*)
			FROM step AS prev
			JOIN step AS prev2 ON prev2.to_point_id = prev.from_point_id
			JOIN cipher ON cipher.point_id = prev2.from_point_id
			JOIN progress ON progress.point_id = cipher.point_id
			WHERE team_id = ? AND prev.to_point_id = ?
		", $teamId, $cipher["point_id"]) != 0;
	}

	public function previousLocsVisited(int $teamId, array $cipher) {
		return $this->db->equery("
			SELECT COUNT(*) FROM step
			LEFT JOIN progress ON point_id = step.from_point_id AND team_id = ?
			WHERE step.to_point_id = ? AND progress.point_id IS NULL
		", $teamId, $cipher["point_id"]) == 0;
	}

	public function getNextLocsNotVisited(int $teamId, array $cipher) {
		return $this->db->aquery("
			SELECT point.*, loc.*
			FROM point 
			NATURAL JOIN loc
			JOIN step ON step.to_point_id = loc.point_id
			LEFT JOIN progress ON progress.point_id = loc.point_id AND progress.team_id = ?
			WHERE step.from_point_id = ? AND progress.point_id IS NULL
		", $teamId, $cipher["point_id"]);
	}

	public function deletePendingHints(int $teamId, array $cipher) {
		$this->db->execute("DELETE FROM hint WHERE team_id = ? AND time > NOW() AND cipher_id = ?", [$teamId, $cipher["point_id"]]);
	}

	public function deletePendingHintsForParallelCiphers(int $teamId, array $cipher) {
		$this->db->execute("
			DELETE FROM hint 
			WHERE team_id = ? AND time > NOW() AND cipher_id IN (
				SELECT step2.from_point_id
				FROM step AS step1
				JOIN step AS step2 ON step1.to_point_id = step2.to_point_id
				WHERE step1.from_point_id = ?
			)", [$teamId, $cipher["point_id"]]);
	}

	public function solvedCipherCount(int $teamId) {
		return $this->db->equery("
			SELECT COUNT(*) FROM progress
			JOIN cipher ON cipher.point_id = progress.point_id
			WHERE team_id = ?
		", $teamId);
	}

	public function teamCipherStatus(int $teamId) {
		return $this->db->aquery("
			SELECT cipher_point.name, IFNULL(DATE_FORMAT(solved.time, '%H:%i:%s'), '-') AS time, IF(ISNULL(MAX(hint_id)) , '-', cipher.hint ) AS hint
			FROM progress
			NATURAL JOIN loc
			JOIN step ON step.from_point_id = loc.point_id
			JOIN cipher ON cipher.point_id = step.to_point_id
			JOIN point cipher_point ON cipher_point.point_id = cipher.point_id
			LEFT JOIN progress solved ON solved.team_id = progress.team_id AND solved.point_id = cipher.point_id
			LEFT JOIN hint ON hint.team_id = progress.team_id AND hint.cipher_id = cipher.point_id AND hint.time <= NOW()
			WHERE progress.team_id = ?
			GROUP BY cipher.point_id
			ORDER BY loc.order_id, cipher_point.name
		", $teamId);
	}
}
