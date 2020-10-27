<?php
namespace Sova\Repo;

class TeamHintRepo extends RepoBase {

	public function alreadyHas($teamId, $hintId) {
		return $this->db->equery("SELECT COUNT(*) FROM team_hint WHERE team_id = ? AND hint_id = ?", $teamId, $hintId);
	}

	public function addToTeam($teamId, $hintId) {
		$this->db->execute("INSERT INTO team_hint (team_id, hint_id) VALUES (?, ?)", array($teamId, $hintId));
	}

	public function getUnusedHintCount($teamId) {
		return $this->db->equery("SELECT COUNT(*) FROM team_hint WHERE team_id = ? AND cipher_id IS NULL", $teamId);
	}

	public function getUnusedHintId($teamId) {
		return $this->db->equery("SELECT hint_id FROM team_hint WHERE team_id = ? AND cipher_id IS NULL LIMIT 1", $teamId);
	}

	public function alreadyApplied($teamId, $cipherId) {
		return $this->db->equery("SELECT count(*) FROM team_hint WHERE team_id = ? and cipher_id = ?", $teamId, $cipherId);
	}

	public function apply($teamId, $hintId, $cipherId) {
		$this->db->execute("UPDATE team_hint SET cipher_id = ? WHERE team_id = ? AND hint_id = ?", array($cipherId, $teamId, $hintId));
	}
}
