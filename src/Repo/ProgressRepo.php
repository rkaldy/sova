<?php
namespace Sova\Repo;

class ProgressRepo extends RepoBase {

	public function create(int $teamId, int $pointId, int $type) {
		$this->db->execute("INSERT INTO progress (team_id, point_id, type) values (?, ?, ?)", array($teamId, $pointId, $type));
	}

	public function previousCipherSolved(int $teamId, int $locId) {
		return $this->db->equery("
			SELECT COUNT(*) FROM loc
			JOIN step ON loc.point_id = step.to_point_id
			JOIN progress ON progress.point_id = step.from_point_id
			WHERE team_id = ? AND loc.point_id = ?
		", $teamId, $locId) != 0;
	}

	public function previousLocVisited(int $teamId, int $cipherId) {
		return $this->db->equery("
			SELECT COUNT(*) FROM cipher
			JOIN step ON cipher.point_id = step.to_point_id
			JOIN progress ON progress.point_id = step.from_point_id
			WHERE team_id = ? AND cipher.point_id = ?
		", $teamId, $cipherId) != 0;
	}
}
