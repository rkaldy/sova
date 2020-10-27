<?php
namespace Sova\Model;

use Sova\DB;

class Code {

	public function prepare(?string &$code) {
		if (empty($code)) {
			$code = Code::generate();
		} else {
			$code = strtoupper(trim($code));
		}
	}
	
	public function generate() {
		$db = DB::get();
		do {
			$word = $db->equery("SELECT word FROM wordlist ORDER BY rand() LIMIT 1");
		} 
		while ($db->equery("SELECT COUNT(*) FROM code WHERE game_id = ? AND code = ?", Game::current(), $word) != 0);
		return $word;
	}

	public static function getForCode($code) {
		$ret = DB::get()->squery("SELECT * FROM code WHERE code = ?", $code);
		if ($ret == null) {
			return array(null, null);
		} else if (isset($ret["point_id"])) {
			$loc = (new LocRepo())->get($ret["point_id"]);
			if (isset($loc)) {
				return array("loc", $loc);
			} else {
				return array("cipher", (new CipherRepo())->get($ret["point_id"]));
			}
		} else if (isset($ret["hint_id"])) {
			return array("hint", (new HintRepo())->get($ret["hint_id"]));
		}
		return array(null, null);
	}

	public static function process($code) {
		self::prepare($code);
		$msgRepo = new MessageRepo();
		$msgRepo->sendToSova($code);

		$response = "";
		list($type, $entity) = self::getForCode($code);
		if ($type == null) {
			$response = "Neznámý kód: $code";
		} else if ($type == "hint") {
			$response = Hint::add($entity);
		}
		
		$msgRepo->sendToTeam($response);
		return $response;
	}
}
