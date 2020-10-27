<?php
namespace Sova\Repo;

class GraphRepo extends RepoBase {

	function points(int $gameId) {
		return $this->db->dquery("SELECT point_id, 1 FROM point WHERE game_id = ? ORDER BY name", $gameId);
	}

	function steps(int $gameId) {
		return $this->db->aquery("SELECT step.* FROM step JOIN point ON from_point_id = point_id WHERE game_id = ?", $gameId);
	}

	function pointsWithNext(int $gameId) {
		$points = array();
		$ret = $this->db->query("
			SELECT point_id, ISNULL(cipher.point_id) as isloc, name, name_int, GROUP_CONCAT(next.to_point_id SEPARATOR ',') AS next
			FROM point NATURAL LEFT JOIN cipher 
			LEFT JOIN step AS next ON next.from_point_id = point_id
			WHERE game_id = ?
			GROUP BY point_id
			ORDER BY sort_id, isloc DESC
		", $gameId);
		while ($row = $ret->fetch(\PDO::FETCH_ASSOC)) {
			$points[$row["point_id"]] = $row;
		}
		return $points;
	}

	function isSorted(int $gameId) {
		return $this->db->equery("SELECT COUNT(*) FROM point WHERE game_id = ? AND sort_id IS NULL", $gameId) == 0;
	}

	function sort(array $points) {
		$stmt = $this->db->prepare("UPDATE point SET sort_id = ? WHERE point_id = ?");
		foreach ($points as $point_id => $sort_id) {
			$stmt->execute(array($sort_id, $point_id));
		}
	}
}
