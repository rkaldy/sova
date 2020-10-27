<?php
namespace Sova\Repo;

class CodeRepo extends RepoBase {

	function get(string $code, int $gameId) {
		return $this->db->squery("SELECT * FROM code WHERE game_id = ? AND code = ?", $gameId, $code);
	}

	function randomCode() {
		return $this->db->equery("SELECT word FROM wordlist ORDER BY rand() LIMIT 1");
	}

	function isUnique(string $code, int $gameId) {
		return $this->db->equery("SELECT COUNT(*) FROM code WHERE game_id = ? AND code = ?", $gameId, $code) == 0;
	}
}
