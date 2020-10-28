<?php
namespace Sova\Controller;

use Sova\Model\Code;
use Sova\Model\Loc;
use Sova\Model\Cipher;
use Sova\Model\Hint;
use Sova\Model\Game;
use Sova\Model\Message;


class CodeController {

	public static function process($request) {
		$code = Code::polish($request);
		$message = new Message();
		$message->sendToSova($code);

		$entity = (new Code())->get($code);
		if ($entity == null) {
			$response = new Text("code.unknown", $code);
		} 
		else if (isset($entity["hint_id"])) {
			$hint = new Hint();
			$response = $hint->add($entity["hint_id"]);
		} else {
			$response = new Text("code.unknown", $code);
		}
		
		$response = $response->format();
		$message->sendToTeam($response);
		return $response;
	}
}
