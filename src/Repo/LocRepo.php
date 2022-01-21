<?php
namespace Sova\Repo;

use Sova\DBException;

class LocRepo extends PointRepo {

	public function isLoc(int $id) {
		return $this->db->equery("SELECT COUNT(*) FROM loc WHERE point_id = ?", $id) != 0;
	}

	public function get(int $id) {
		$loc = $this->db->squery("
			SELECT loc.*, point.name,
			  GROUP_CONCAT(DISTINCT prev.from_point_id ORDER BY prev.from_point_id SEPARATOR ',') AS prev,
			  GROUP_CONCAT(DISTINCT next.to_point_id ORDER BY next.to_point_id SEPARATOR ',') AS next
			FROM loc 
			NATURAL JOIN point 
			LEFT JOIN step AS prev ON prev.to_point_id = loc.point_id
			LEFT JOIN step AS next ON next.from_point_id = loc.point_id
			WHERE loc.point_id = ?
		", $id);
		self::flattenPrevNext($loc);
		return $loc;
	}

	public function list($gameId) {
		$locs = $this->db->aquery("
			SELECT loc.*, point.name, code,
			  GROUP_CONCAT(DISTINCT next.point_id ORDER BY next.order_id, name SEPARATOR ',') AS next			
			FROM loc
			NATURAL JOIN point
			NATURAL JOIN code
			LEFT JOIN step ON step.from_point_id = loc.point_id
			LEFT JOIN loc next ON next.point_id = step.to_point_id
			WHERE point.game_id = ? 
			GROUP BY loc.point_id
			ORDER BY order_id, name
		", $gameId);
		foreach ($locs as &$loc) {
			self::flattenPrevNext($loc);
		};
		return $locs;
	}

	public function listSimple($gameId) {
		return $this->db->aquery("
			SELECT point_id, name
			FROM point
			NATURAL JOIN loc
			WHERE game_id = ?
            ORDER BY order_id, name
		", $gameId);
	}

	protected function addNextLocs(array &$loc) {
		$this->db->execute("DELETE FROM step WHERE from_point_id = :point_id AND EXISTS (SELECT 1 FROM loc WHERE point_id = step.to_point_id)", $loc);
		if (isset($loc["next"])) {
			foreach ($loc["next"] as $next) {
				$this->db->execute("INSERT INTO step (from_point_id, to_point_id) VALUES (?, ?)", array($loc["point_id"], $next), true);
			}
		}
	}

	function create(array &$loc) {
		try {
			$this->db->execute("INSERT INTO point (game_id, name) VALUES (:game_id, :name)", $loc, true);
			$loc["point_id"] = $this->db->lastInsertId();
			$this->db->execute("INSERT INTO loc (point_id, description, order_id, coord_lat, coord_lon, solved_cipher_count, end_time) VALUES (:point_id, :description, :order_id, :coord_lat, :coord_lon, :solved_cipher_count, :end_time)", $loc, true);
			$this->db->execute("INSERT INTO code (game_id, code, point_id) VALUES (:game_id, :code, :point_id)", $loc, true);
			$this->addNextLocs($loc);
		} catch (DBException $ex) {
			$this->db->execute("DELETE FROM point WHERE point_id = :point_id", $loc);
			throw $ex;
		}
	}

	function update(array $loc) {
		$this->db->execute("UPDATE point SET name = :name WHERE point_id = :point_id", $loc);
		$this->db->execute("UPDATE loc SET description = :description, order_id = :order_id, coord_lat = :coord_lat, coord_lon = :coord_lon, solved_cipher_count = :solved_cipher_count, end_time = :end_time WHERE point_id = :point_id", $loc);
		$this->db->execute("UPDATE code SET code = :code WHERE point_id = :point_id", $loc);
		$this->addNextLocs($loc);
	}

	function delete(array $loc) {
		$this->db->execute("DELETE FROM point WHERE point_id = :point_id", $loc, true);
	}

	
	public function hasPreviousPoints(int $locId) {
		return $this->db->equery("SELECT COUNT(*) FROM step WHERE to_point_id = ?", $locId) != 0;
	}

	public function previousPointsVisited(int $teamId, int $locId) {
		return $this->db->equery("
			SELECT COUNT(*) FROM step
			JOIN progress ON progress.point_id = step.from_point_id
			WHERE team_id = ? AND step.to_point_id = ?
		", $teamId, $locId) != 0;
	}
	
	public function getNextPoints(int $locId) {
		return $this->db->aquery("
			SELECT point.*, loc.*, cipher.*, code.code, ISNULL(loc.point_id) AS is_cipher
			FROM point
			LEFT JOIN cipher ON point.point_id = cipher.point_id
			LEFT JOIN loc ON point.point_id = loc.point_id
			JOIN code ON point.point_id = code.point_id
			JOIN step ON step.to_point_id = point.point_id
			WHERE step.from_point_id = ?
		", $locId);
	}

	public function visitedAllLocsWithCipher(int $teamId, int $cipherId) {
		return $this->db->equery("
			SELECT COUNT(*) FROM step
			LEFT JOIN progress ON step.from_point_id = progress.point_id AND progress.team_id = ?
			WHERE step.to_point_id = ? AND progress.team_id IS NULL
		", $teamId, $cipherId) == 0;
	}

	public function getBySolvedCipherCount(int $gameId, int $solved) {
		return $this->db->aquery("
			SELECT point.name, loc.*
			FROM loc
			NATURAL JOIN point
			WHERE game_id = ? AND solved_cipher_count = ?
		", $gameId, $solved);
	}
}

