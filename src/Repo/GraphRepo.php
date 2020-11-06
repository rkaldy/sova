<?php
namespace Sova\Repo;

class GraphRepo extends RepoBase {

	function points(int $gameId) {
		return $this->db->query("
			SELECT point_id, ISNULL(cipher.point_id) as isloc, name, name_int, 
				GROUP_CONCAT(DISTINCT prev.from_point_id SEPARATOR ',') AS prev,
				GROUP_CONCAT(DISTINCT next.to_point_id SEPARATOR ',') AS next
			FROM point 
			NATURAL LEFT JOIN cipher 
			LEFT JOIN step AS prev ON prev.to_point_id = point_id
			LEFT JOIN step AS next ON next.from_point_id = point_id
			WHERE game_id = ?
			GROUP BY point_id
			ORDER BY isloc DESC, name
		", $gameId);
	}
}
