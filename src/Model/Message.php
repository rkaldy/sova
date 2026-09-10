<?php
namespace Sova\Model;

use PDO;

class Message extends ModelBase {
	
	public const FROM_TEAM = 1;
	public const TO_TEAM = 2;


	private function addDirectionStr(array &$messages) {
		foreach ($messages as &$msg) {
			$msg["direction_str"] = $msg["direction"] == self::FROM_TEAM ? "in" : "out";
		}
	}

	public function list($from = 0, $limit = 999999): array {
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

	public function recent(string $since) {
		$ret = [];
		$messages = $this->repo->recent(Team::current(), $since);
		while ($row = $messages->fetch(PDO::FETCH_NUM)) {
			$ret[] = $row[0];
		}
		return $ret;
	}


	public function sendToSova(string $message) {
		$this->repo->create(["team_id" => Team::current(), "direction" => self::FROM_TEAM, "text" => $message]);
	}

	public function sendToTeam(string $message, ?int $teamId = null) {
		$this->repo->create(["team_id" => $teamId ?? Team::current(), "direction" => self::TO_TEAM, "text" => $message]);
	}

	public function broadcast(array $teams, string $message) {
		$this->repo->broadcast($teams, $message);
	}
}
