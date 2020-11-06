<?php
namespace Sova\Repo;

class TextRepo extends RepoBase {

	public function list(int $gameId) {
		return $this->db->aquery("SELECT text_id, code, text FROM text WHERE game_id = ?", $gameId);
	}

	public function create(array $gameId) {
	}

	public function update(array $text) {
		$this->db->execute("UPDATE text SET text = :text WHERE text_id = :text_id", $text);
	}

	public function get(int $gameId, $code) {
		return $this->db->equery("SELECT text FROM text WHERE game_id = ? AND code = ?", $gameId, $code);
	}
}
