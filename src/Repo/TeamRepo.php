<?php

namespace Sova\Repo;

class TeamRepo extends RepoBase {

	function get(int $team_id, string $pswd) {
		return $this->db->squery("
			SELECT team.*, code AS pswd, game.name AS game_name 
			FROM team
			NATURAL JOIN code
			JOIN game ON game.game_id = team.game_id 
			WHERE team.team_id = ? AND code = ?
		", $team_id, $pswd);
	}

	function list(int $gameId) {
		return $this->db->aquery("
			SELECT team.team_id, name, phone, email, code AS pswd 
			FROM team 
			NATURAL JOIN code
			WHERE team.game_id = ? ORDER BY name
		", $gameId);
	}

	function create(array &$team) {
		try {
			$this->db->execute("INSERT INTO team (game_id, name, phone, email) VALUES (:game_id, :name, :phone, :email)", $team, true);
			$team["team_id"] = $this->db->lastInsertId();
			$this->db->execute("INSERT INTO code (game_id, team_id, code) VALUES (:game_id, :team_id, :pswd)", $team, true);
		} catch (DBException $ex) {
			$this->db->execute("DELETE FROM team WHERE team_id = :team_id", $team);
			throw $ex;
		}
	}

	public function update($team) {
		$this->db->execute("UPDATE team SET name = :name, phone = :phone, email = :email WHERE team_id = :team_id", $team);
		$this->db->execute("UPDATE code SET code = :pswd WHERE team_id = :team_id", $team);
	}

	public function delete($team) {
		$this->db->execute("DELETE FROM team WHERE team_id = :team_id", $team, true);
	}
}
