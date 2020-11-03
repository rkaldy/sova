<?php
namespace Sova\Repo;

class HintRepo extends RepoBase {

	public function alreadyHas($teamId, $unihintId) {
		return $this->db->equery("SELECT COUNT(*) FROM hint WHERE team_id = ? AND unihint_id = ?", $teamId, $unihintId);
	}

	public function addToTeam($teamId, $unihintId) {
		$this->db->execute("INSERT INTO hint (team_id, unihint_id) VALUES (?, ?)", [$teamId, $unihintId]);
	}

	public function getUnusedHintCount($teamId) {
		return $this->db->equery("SELECT COUNT(*) FROM hint WHERE team_id = ? AND cipher_id IS NULL", $teamId);
	}

	public function getUnusedHintId($teamId) {
		return $this->db->equery("SELECT unihint_id FROM hint WHERE team_id = ? AND cipher_id IS NULL LIMIT 1", $teamId);
	}

	public function alreadyApplied($teamId, $cipherId) {
		return $this->db->equery("SELECT count(*) FROM hint WHERE team_id = ? AND cipher_id = ? AND time <= NOW()", $teamId, $cipherId);
	}

	public function apply($teamId, $unihintId, $cipherId, $type) {
		$this->db->execute("DELETE FROM hint WHERE team_id = ? AND cipher_id = ? AND time > NOW()", [$teamId, $cipherId]);
		$this->db->execute("UPDATE hint SET cipher_id = ?, time = NOW(), type = ? WHERE team_id = ? AND unihint_id = ?", [$cipherId, $type, $teamId, $unihintId]);
	}
}
