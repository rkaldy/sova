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

	public function list($from = 0, $limit = 999999) {
		$messages = $this->repo->list(Game::current(), $from, $limit);
		$this->addDirectionStr($messages);
		return $messages;
	}

	public function count() {
		return $this->repo->count(Game::current());
	}

	public function listForTeam($from = 0, $limit = 999999) {
		$messages = $this->repo->listForTeam(Team::current(), $from, $limit);
		$this->addDirectionStr($messages);
		$count = $this->repo->countForTeam(Team::current());
		return [$messages, $count];
	}


	public function sendToSova(string $message) {
		$this->repo->create(["team_id" => Team::current(), "direction" => self::FROM_TEAM, "text" => $message]);
	}

	public function sendToTeam(string $message, int $hintId = null, int $afterMinutes = null) {
		$this->repo->create(["team_id" => Team::current(), "hint_id" => $hintId, "direction" => self::TO_TEAM, "time" => $afterMinutes, "text" => $message]);
	}

	public function broadcast(array $teams, string $message) {
		$this->repo->broadcast($teams, $message);
	}
}
