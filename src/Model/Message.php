<?php
namespace Sova\Model;

class Message extends ModelBase {
	
	public const FROM_TEAM = 1;
	public const TO_TEAM = 2;


	public function list($page, $pageSize) {
		$messages = $this->repo->list(Game::current(), ($page - 1) * $pageSize, $pageSize);
		foreach ($messages as &$msg) {
			$msg["name"] .= $msg["direction"] == self::FROM_TEAM ? " →" : " ←";
		}
		return [$messages, $this->repo->count()];
	}

	public function listForTeam($page, $pageSize) {
		return [$this->repo->listForTeam(Team::current(), ($page - 1) * $pageSize, $pageSize), $this->repo->countForTeam(Team::current()) ];
	}

	public function sendToSova(string $message) {
		$this->repo->create(["team_id" => Team::current(), "direction" => self::FROM_TEAM, "text" => $message]);
	}

	public function sendToTeam(string $message, int $cipherId = null, int $afterMinutes = null) {
		$this->repo->create(["team_id" => Team::current(), "cipher_id" => $cipherId, "direction" => self::TO_TEAM, "time" => $afterMinutes, "text" => $message]);
	}

	public function broadcast(array $teams, string $message) {
		$this->repo->broadcast($teams, $message);
	}
}
