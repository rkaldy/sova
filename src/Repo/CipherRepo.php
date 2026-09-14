<?php
namespace Sova\Repo;

use Sova\DBException;

class CipherRepo extends PointRepo {

	protected const SQL = "
			SELECT cipher.*, name,
			  GROUP_CONCAT(DISTINCT code.code SEPARATOR ', ') AS code,
			  points, points_by_rank,
			  GROUP_CONCAT(DISTINCT prev.from_point_id ORDER BY prev.from_point_id SEPARATOR ',') AS prev,
			  GROUP_CONCAT(DISTINCT next.to_point_id ORDER BY next.to_point_id SEPARATOR ',') AS next
			FROM cipher 
			NATURAL JOIN point 
			NATURAL JOIN code 
			LEFT JOIN step AS prev ON prev.to_point_id = cipher.point_id
			LEFT JOIN step AS next ON next.from_point_id = cipher.point_id";

	
	protected static function convert(array &$cipher) {
		self::convertBooleans($cipher, ["activity", "all_locs_mandatory"]);
		self::flattenPrevNext($cipher);
		$cipher["code"] = isset($cipher["code"]) ? array_map("trim", explode(",", $cipher["code"])) : [];
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


	function listAsArray(int $gameId, $activity = null) {
		return $this->db->dquery("
			SELECT point.point_id, CONCAT(point.name, ' / ', cipher.name_int) AS name
			FROM cipher
			NATURAL JOIN point
			LEFT JOIN step ON step.to_point_id = cipher.point_id
			LEFT JOIN loc ON loc.point_id = step.from_point_id
			WHERE point.game_id = ?" . (isset($activity) ? " AND cipher.activity = ?" : "") . "
			GROUP BY cipher.point_id
			ORDER BY MIN(loc.order_id), point.name
		", isset($activity) ? [$gameId, $activity] : [$gameId]);
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


	protected function addCodes(array $cipher) {
		foreach ($cipher["code"] as $code) {
			$this->db->execute(
				"INSERT INTO code (game_id, point_id, code) VALUES (?, ?, ?)",
				[$cipher["game_id"], $cipher["point_id"], $code],
				true
			);
		}
	}

	function create(array &$cipher) {
		try {
			$this->db->execute("INSERT INTO point (game_id, name, points, points_by_rank) VALUES (:game_id, :name, :points, IFNULL(:points_by_rank, 0))", $cipher, true);
			$cipher["point_id"] = $this->db->lastInsertId();
			$this->db->execute("INSERT INTO cipher (point_id, name_int, activity, hint, howto, all_locs_mandatory) VALUES (:point_id, :name_int, :activity, :hint, :howto, :all_locs_mandatory)", $cipher, true);
			$this->addCodes($cipher);
			$this->addPrevNextLocs($cipher);
		} catch (DBException $ex) {
			$this->db->execute("DELETE FROM point WHERE point_id = :point_id", $cipher);
			throw $ex;
		}
	}

	function update(array &$cipher) {
		$this->db->beginTransaction();
		try {
			$this->db->execute("UPDATE point SET name = :name, points = :points, points_by_rank = IFNULL(:points_by_rank, 0) WHERE point_id = :point_id", $cipher);
			$this->db->execute("UPDATE cipher SET name_int = :name_int, activity = :activity, hint = :hint, howto = :howto, all_locs_mandatory = :all_locs_mandatory WHERE point_id = :point_id", $cipher);
			$this->db->execute("DELETE FROM code WHERE point_id = :point_id", $cipher);
			$this->addCodes($cipher);
			$this->addPrevNextLocs($cipher);
			$this->db->commit();
		} catch (\Throwable $ex) {
			$this->db->rollBack();
			throw $ex;
		}
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
		if ($cipher["all_locs_mandatory"]) {
			return $this->db->equery("
				SELECT COUNT(*) FROM step
				LEFT JOIN progress ON point_id = step.from_point_id AND team_id = ?
				WHERE step.to_point_id = ? AND progress.point_id IS NULL
			", $teamId, $cipher["point_id"]) == 0;
		} else {
			return $this->db->equery("
				SELECT count(*) FROM step
				JOIN progress ON point_id = step.from_point_id AND team_id = ?
				WHERE step.to_point_id = ?
			", $teamId, $cipher["point_id"]) != 0;
		}
	}

	public function getNextLocs(array $cipher) {
		return $this->db->aquery("
			SELECT point.*, loc.*
			FROM point 
			NATURAL JOIN loc
			JOIN step ON step.to_point_id = loc.point_id
			WHERE step.from_point_id = ? 
		", $cipher["point_id"]);
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
			SELECT 
				cipher_point.name, 
				IFNULL(DATE_FORMAT(solved.time, '%H:%i:%s'), '-') AS time, 
				IF(MAX(hint.type) >= 1, cipher.hint, '-') AS hint,
				IF(MAX(hint.type) >= 2, cipher.howto, '-') AS howto,
				IF(MAX(hint.type) >= 3, SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT code.code), ',', 1), '-') AS solution
			FROM progress
			NATURAL JOIN loc
			JOIN step ON step.from_point_id = loc.point_id
			JOIN cipher ON cipher.point_id = step.to_point_id	
			JOIN point cipher_point ON cipher_point.point_id = cipher.point_id
			JOIN code ON code.point_id = cipher.point_id
			LEFT JOIN progress solved ON solved.team_id = progress.team_id AND solved.point_id = cipher.point_id
			LEFT JOIN hint ON hint.team_id = progress.team_id AND hint.cipher_id = cipher.point_id
			WHERE progress.team_id = ?
			GROUP BY cipher.point_id
			ORDER BY loc.order_id, cipher_point.name
		", $teamId);
	}
}
