<?php
namespace Sova\Repo;

use Sova\DBException;

class ProgressRepo extends RepoBase {

	public function create(int $teamId, int $pointId, int $type) {
		try {
			$this->db->execute("INSERT INTO progress (team_id, point_id, type) values (?, ?, ?)", array($teamId, $pointId, $type));
			return true;
		} catch (DBException $ex) {
			if ($ex->getCode() == 1062) {
				return false;
			}
			throw $ex;
		}
	}

	public function previousCipherSolved(int $teamId, int $locId) {
		return $this->db->equery("
			SELECT COUNT(*) FROM loc
			JOIN step ON loc.point_id = step.to_point_id
			JOIN progress ON progress.point_id = step.from_point_id
			WHERE team_id = ? AND loc.point_id = ?
		", $teamId, $locId) != 0;
	}
}
