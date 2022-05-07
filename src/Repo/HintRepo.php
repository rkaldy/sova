<?php
namespace Sova\Repo;

use \PDO;
use Sova\DBException;

class HintRepo extends RepoBase {

	public const NONE = 0;
    public const HINT = 1;
	public const HOWTO = 2;
	public const SOLUTION = 3;
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

	public function getUnusedCCodeCount(int $teamId) {
		return $this->db->equery("SELECT COUNT(*) FROM hint WHERE team_id = ? AND type IS NULL", $teamId);
	}

	public function getAppliedHintType(int $teamId, int $cipherId) {
		$ret = $this->db->equery("SELECT MAX(type) FROM hint WHERE team_id = ? AND cipher_id = ?", $teamId, $cipherId);
		return is_null($ret) ? self::NONE : $ret;
	}

	public function applyByPoints(int $teamId, int $cipherId, int $type, ?int $timeOffset = 0) {
		$this->db->execute("INSERT INTO hint (team_id, cipher_id, time, type) VALUES (?, ?, DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL ? MINUTE), ?)", [$teamId, $cipherId, $timeOffset, $type]);
	}

	public function applyByCCodes(int $teamId, ?int $cipherId, int $type, int $price, ?int $timeOffset = 0) {
		$ccodes = $this->db->aquery("SELECT ccode_id FROM hint WHERE team_id = ? AND type IS NULL LIMIT ?", $teamId, $price);
		foreach ($ccodes as $ccode) {
			$this->db->execute("UPDATE hint SET cipher_id = ?, time = DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL ? MINUTE), type = ? WHERE team_id = ? AND ccode_id = ?", [$cipherId, $timeOffset, $type, $teamId, $ccode["ccode_id"]]);
		}
	}
	
	public function haveImunity(int $teamId) {
		return $this->db->equery("SELECT COUNT(*) FROM hint WHERE team_id = ? AND type = ?", $teamId, self::IMUNITY) != 0;
	}

}
