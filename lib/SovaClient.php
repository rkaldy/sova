<?php
class SovaClient {

	protected $baseUrl;
	protected $auth;

	public function __construct($baseUrl, $game, $password) {
		$this->baseUrl = $baseUrl;
		$this->auth = "$game:$password";
	}


	public function list() {
		return $this->request("GET", "/api/team");
	}

	public function login(string $teamId, string $pswd) {
		return $this->request("GET", "/api/team_login?team_id=$teamId&pswd=$pswd");
	}

	public function create(string $name, string $phone, string $email, array $members, bool $accomodation, int $tshirt, string $remarks, array $additional = null) {
		return $this->write("POST", 0, $name, $phone, $email, $members, $accomodation, $tshirt, $remarks, $additional);
	}

	public function update(int $teamId, string $name, string $phone, string $email, array $members, bool $accomodation, int $tshirt, string $remarks, array $additional = null) {
		return $this->write("PUT", $teamId, $name, $phone, $email, $members, $accomodation, $tshirt, $remarks, $additional);
	}

	public function delete(int $teamId) {
		return $this->request("DELETE", "/api/team", ["team_id" => $teamId]);
	}


	protected function write(string $method, int $teamId, string $name, string $phone, string $email, array $members, bool $accomodation, int $tshirt, string $remarks, array $additional = null) {
		return $this->request($method, "/api/team", [
			"team_id" => $teamId,
			"name" => $name,
			"phone" => $phone,
			"email" => $email,
			"members" => $members,
			"accomodation" => $accomodation,
			"paid" => false,
			"tshirt" => $tshirt,
			"remarks" => $remarks,
			"additional" => $additional
		]);
	}

	protected function request(string $method, string $url, array $data = null) {
		try {
			$data = json_encode($data);
            $ch = curl_init($this->baseUrl . $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json", "Content-Length: ".strlen($data)]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_USERPWD, $this->auth);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$res = curl_exec($ch);
			$resData = json_decode($res, true);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($httpCode == 200) {
                return $resData;
            } else if ($httpCode == 422 && $resData["code"] == 1062) {
                throw new \Exception("Tento název již používá jiný tým");
            } else if ($httpCode == 500) {
                throw new \Exception("Interní chyba Sovy: ".strip_tags($res));
            } else if ($resData) {
                throw new \Exception("HTTP chyba: $httpCode / ${resData["error"]}");
            } else {
                throw new \Exception("Chyba při připojení k Sově: ".curl_error($ch));
            }
        }
        finally {
            curl_close($ch);
        }
		
	}
}
