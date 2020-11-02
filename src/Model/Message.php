<?php
namespace Sova\Model;

class Message extends ModelBase {
	
	public const FROM_TEAM = 1;
	public const TO_TEAM = 2;


	private function addDirectionStr(array &$messages) {
		foreach ($messages as &$msg) {
			$msg["direction_str"] = $msg["direction"] == self::FROM_TEAM ? "in" : "out";
		}
	}

	public function list(int $page = 1, int $pageSize = 9999) {
		$messages = $this->repo->list(Game::current(), ($page - 1) * $pageSize, $pageSize);
		$this->addDirectionStr($messages);
		return [$messages, $this->repo->count(Game::current())];
	}

	public function listForTeam(int $page = 1, $pageSize = 9999) {
		$messages = $this->repo->listForTeam(Team::current(), ($page - 1) * $pageSize, $pageSize);
		$this->addDirectionStr($messages);
		return [$messages, $this->repo->countForTeam(Team::current())];
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
