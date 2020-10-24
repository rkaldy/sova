<?php
namespace Sova\Model;

use Sova\DB;

class Code {

	public static function prepare(&$code) {
		if (empty($code)) {
			$code = Code::generate();
		} else {
			$code = strtoupper(trim($code));
		}
	}
	
	public static function generate() {
		$db = DB::get();
		do {
			$word = $db->equery("SELECT word FROM wordlist ORDER BY rand() LIMIT 1");
		} 
		while ($db->equery("SELECT COUNT(*) FROM code WHERE game_id = ? AND code = ?", Game::current(), $word) != 0);
		return $word;
	}

	public static function getForCode($code) {
		$ret = DB::get()->squery("SELECT * FROM code WHERE code = ?", strtoupper(trim($code)));
		if ($ret == null) {
			return null;
		} else if (isset($ret["point_id"])) {
			$loc = (new LocRepo())->get($ret["point_id"]);
			if (isset($loc)) {
				return $loc;
			} else {
				return (new CipherRepo())->get($ret["point_id"]);
			}
		} else if (isset($ret["hint_id"])) {
			return (new HintRepo())->get($ret["hint_id"]);
		}
		return null;
	}
}
