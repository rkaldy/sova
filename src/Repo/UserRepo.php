<?php
namespace Sova\Repo;

class UserRepo extends RepoBase {
	
	public function get(string $login) {
		return $this->db->squery("SELECT * FROM user WHERE login = ?", $login);
	}

	public function list() {
		return $this->db->aquery("SELECT user_id, login FROM user ORDER BY login");
	}
	
	public function create(array &$user) {
		if (empty($user["pswd"])) {
			throw new \Exception("Heslo nesmí být prázdné");
		}
		$this->db->execute("INSERT INTO user (login, pswd) VALUES (:login, :pswd)", $user, true);
		$user["user_id"] = $this->db->lastInsertId();
		unset($user["pswd"]);
	}

	public function update(array &$user) {
		if (isset($user["pswd"])) {
			$this->db->execute("UPDATE user SET login = :login, pswd = :pswd WHERE user_id = :user_id", $user);
			unset($user["pswd"]);
		} else {
			$this->db->execute("UPDATE user SET login = :login WHERE user_id = :user_id", $user);
		}
	}

	public function delete(array $user) {
		if ($user["user_id"] == 1) {
			throw new \Exception("Superuživatele nelze smazat");
		}
		$this->db->execute("DELETE FROM user WHERE user_id = :user_id", $user, true);
	}
}
