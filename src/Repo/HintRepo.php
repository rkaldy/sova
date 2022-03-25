<?php
namespace Sova\Repo;

use \PDO;
use Sova\DBException;

class HintRepo extends RepoBase {

    public const NORMAL = 1;
	public const SOLUTION = 2;
	public const ABSOLUTE = 3;
    public const IMUNITY = 4;


	public function addCCode(array $hint) {
		try {
			$this->db->execute("INSERT INTO hint (team_id, ccode_id) VALUES (:team_id, :ccode_id)", $hint);
			return true;
		} catch (DBException $ex) {
			if ($ex->getCode() == 1062) {
				return false;
			}
			throw $ex;
		}
	}

	public function getUnusedHintCount(int $teamId) {
		return $this->db->equery("SELECT COUNT(*) FROM hint WHERE team_id = ? AND type IS NULL", $teamId);
	}

	public function getUnusedHint(int $teamId, int $count = 1) {
		return $this->db->aquery("SELECT * FROM hint WHERE team_id = ? AND type IS NULL LIMIT ?", $teamId, $count);
	}

	public function alreadyApplied(array $hint) {
		return $this->db->equery("SELECT COUNT(*) FROM hint WHERE team_id = :team_id AND cipher_id = :cipher_id AND time <= NOW()", $hint) != 0;
	}

	public function imunityApplied(int $teamId) {
		return $this->db->equery("SELECT COUNT(*) FROM hint WHERE team_id = ? AND type = ?", $teamId, self::IMUNITY) != 0;
	}

	public function apply(array $hint) {
		if ($hint["type"] != self::IMUNITY) {
			$this->db->execute("DELETE FROM hint WHERE team_id = :team_id AND cipher_id = :cipher_id AND type = :type AND time > NOW()", $hint);
		}
		$this->db->execute("UPDATE hint SET cipher_id = :cipher_id, time = NOW(), type = :type WHERE hint_id = :hint_id", $hint);
	}
}
