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
		} 
		else if (isset($entity["point_id"])) {
			$locModel = new Loc();
			$cipherModel = new Cipher();
			if ($loc = $locModel->repo()->get($entity["point_id"])) {
				$response = $locModel->visit($loc, $code);
			}
			else if ($cipher = $cipherModel->repo()->get($entity["point_id"])) {
				$response = $cipherModel->visit($cipher, $code);
			}
			else {
				$response = new Text("code.unknown", $code);
			}
		} else {
			$response = new Text("code.unknown", $code);
		}
		
		$response = $response->format();
		$message->sendToTeam($response);
		return $response;
	}
}
