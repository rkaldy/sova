<?php
namespace Sova\Model;

class UserRepo extends CRUD {
	
	public function get($login) {
		return $this->db->squery("SELECT * FROM user WHERE login = ?", $login);
	}

	public function list() {
		return $this->db->aquery("SELECT user_id, login FROM user ORDER BY login");
	}
	
	public function create($user) {
		if (empty($user["pswd"])) {
			throw new \Exception("Heslo nesmí být prázdné");
		}
		$user["pswd"] = password_hash($user["pswd"], PASSWORD_BCRYPT);
		$this->db->execute("INSERT INTO user (login, pswd) VALUES (:login, :pswd)", $user, true);
		$user["user_id"] = $this->db->lastInsertId();
		unset($user["pswd"]);
		return $user;
	}

	public function update($user) {
		if (empty($user["pswd"])) {
			unset($user["pswd"]);
			$this->db->execute("UPDATE user SET login = :login WHERE user_id = :user_id", $user);
		} else {
			$user["pswd"] = password_hash($user["pswd"], PASSWORD_BCRYPT);
			$this->db->execute("UPDATE user SET login = :login, pswd = :pswd WHERE user_id = :user_id", $user);
			unset($user["pswd"]);
		}
		return $user;
	}

	public function delete($user) {
		$this->db->execute("DELETE FROM user WHERE user_id = :user_id", $user, true);
		return $user;
	}
}
