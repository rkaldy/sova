<?php
namespace Sova\Repo;

use Sova\DBException;

class HintRepo extends RepoBase {

	public function addToTeam(array $hint) {
		try {
			$this->db->execute("INSERT INTO hint (team_id, unihint_id) VALUES (:team_id, :unihint_id)", $hint);
			return true;
		} catch (DBException $ex) {
			if ($ex->getCode() == 1062) {
				return false;
			}
			throw $ex;
		}
	}

	public function getUnusedHintCount($teamId) {
		return $this->db->equery("SELECT COUNT(*) FROM hint WHERE team_id = ? AND cipher_id IS NULL", $teamId);
	}

	public function getUnusedHint($teamId) {
		return $this->db->squery("SELECT * FROM hint WHERE team_id = ? AND cipher_id IS NULL LIMIT 1", $teamId);
	}

	public function alreadyApplied(array $hint) {
		return $this->db->equery("SELECT COUNT(*) FROM hint WHERE team_id = :team_id AND cipher_id = :cipher_id AND time <= NOW()", $hint) != 0;
	}

	public function apply(array $hint) {
		$this->db->execute("DELETE FROM hint WHERE team_id = :team_id AND cipher_id = :cipher_id AND type = :type AND time > NOW()", $hint);
		$this->db->execute("UPDATE hint SET cipher_id = :cipher_id, time = NOW(), type = :type WHERE team_id = :team_id AND unihint_id = :unihint_id", $hint);
	}

	public function amend(array &$hint) {
		$this->db->execute("INSERT INTO hint (team_id, cipher_id, time, type) VALUES (:team_id, :cipher_id, DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL :time MINUTE), :type)", $hint);
		$hint["hint_id"] = $this->db->lastInsertId();
	}
}
