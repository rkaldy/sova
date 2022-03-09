<?php

namespace Sova\Repo;

class TeamRepo extends RepoBase {

	function list(int $gameId) {
		$teams = $this->db->aquery("
			SELECT team.*, code AS pswd 
			FROM team 
			NATURAL JOIN code
			WHERE team.game_id = ? ORDER BY name
		", $gameId);
		foreach ($teams as &$team) {
			$team["members"] = json_decode($team["members"], true);
			$team["additional"] = json_decode($team["additional"], true);
		}
		return $teams;
	}

	function create(array &$team) {
		try {
			$this->db->execute("INSERT INTO team (game_id, name, phone, email, members, accomodation, paid, tshirt, remarks, additional) VALUES (:game_id, :name, :phone, :email, :members, :accomodation, :paid, :tshirt, :remarks, :additional)", $team, true);
			$team["team_id"] = $this->db->lastInsertId();
			$this->db->execute("INSERT INTO code (game_id, team_id, code) VALUES (:game_id, :team_id, :pswd)", $team, true);
		} catch (DBException $ex) {
			$this->db->execute("DELETE FROM team WHERE team_id = :team_id", $team);
			throw $ex;
		}
	}

	public function update($team) {
		$this->db->execute("UPDATE team SET name = :name, phone = :phone, email = :email, members = :members, accomodation = :accomodation, paid = :paid, tshirt = :tshirt, remarks = :remarks, additional = :additional_str WHERE team_id = :team_id", $team);
		$this->db->execute("UPDATE code SET code = :pswd WHERE team_id = :team_id", $team);
	}

	public function delete($team) {
		$this->db->execute("DELETE FROM team WHERE team_id = :team_id", $team, true);
	}

	public function login(int $team_id, string $pswd) {
		$team = $this->db->squery("
			SELECT team.*, game.name AS game_name, code AS pswd
			FROM team
			NATURAL JOIN code
			JOIN game ON game.game_id = team.game_id 
			WHERE team.team_id = ? AND code = ?
			", $team_id, $pswd);
		if ($team) {
			$team["members"] = json_decode($team["members"], true);
			$team["additional"] = json_decode($team["additional"], true);
		}
		return $team;
	}
}
