<?php
namespace Sova\Model;

class Message extends ModelBase {
	
	public const FROM_TEAM = 1;
	public const TO_TEAM = 2;

	public function sendToSova(string $message) {
		$this->repo->create(array("team_id" => Team::current(), "direction" => self::FROM_TEAM, "text" => $message));
	}

	public function sendToTeam(string $message, int $cipherId = null, int $afterMinutes = null) {
		$this->repo->create(array("team_id" => Team::current(), "cipher_id" => $cipherId, "direction" => self::TO_TEAM, "time" => $afterMinutes, "text" => $message));
	}
}
