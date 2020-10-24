<?php

namespace Sova\Model;

class TeamRepo extends CRUD {

	function get($team_id, $pswd) {
		return $this->db->squery("
			SELECT team.*, code AS pswd, game.name AS game_name FROM team
			JOIN game ON game.game_id = team.game_id 
			JOIN code ON code.team_id = team.team_id 
			WHERE team.team_id = ? AND code = ?
		", $team_id, $pswd);
	}

	function list() {
		return $this->db->aquery("
			SELECT team.*, code AS pswd FROM team 
			JOIN code ON team.team_id = code.team_id
			WHERE team.game_id = ? ORDER BY name", 
		Game::current());
	}

	function create($team) {
		$team["game_id"] = Game::current();
		Code::prepare($team["pswd"]);
		try {
			$this->db->execute("INSERT INTO team (game_id, name, phone, email) VALUES (:game_id, :name, phone, :email)", $team, true);
			$team["team_id"] = $this->db->lastInsertId();
			$this->db->execute("INSERT INTO code (game_id, team_id, code) VALUES (:game_id, :team_id, :pswd)", $team, true);
		} catch (DBException $ex) {
			$this->db->execute("DELETE FROM team WHERE team_id = :team_id", $team);
			throw $ex;
		}
		return $team;
	}

	public function update($team) {
		Code::prepare($team["pswd"]);
		$this->db->execute("UPDATE team SET name = :name, phone = :phone, email = :email WHERE team_id = :team_id", $team);
		$this->db->execute("UPDATE code SET code = :pswd WHERE team_id = :team_id", $team);
		return $team;
	}

	public function delete($team) {
		$this->db->execute("DELETE FROM team WHERE team_id = :team_id", $team, true);
		return $team;
	}
}
