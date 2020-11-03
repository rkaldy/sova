<?php
namespace Sova\Repo;

use Sova\DBException;

class ProgressRepo extends RepoBase {

	public function create(int $teamId, int $pointId) {
		try {
			$this->db->execute("INSERT INTO progress (team_id, point_id) values (?, ?)", [$teamId, $pointId]);
			return true;
		} catch (DBException $ex) {
			if ($ex->getCode() == 1062) {
				return false;
			}
			throw $ex;
		}
	}
}
