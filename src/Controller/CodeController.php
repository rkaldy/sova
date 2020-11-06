<?php
namespace Sova\Controller;

use Sova\Model\Code;
use Sova\Model\Loc;
use Sova\Model\Cipher;
use Sova\Model\Hint;
use Sova\Model\Game;
use Sova\Model\Message;
use Sova\Model\Text;

class CodeController {

	public static function process($request) {
		$code = Code::polish($request);
		$message = new Message();
		$message->sendToSova($code);

		$entity = (new Code())->get($code);
		if ($entity == null) {
			$response = new Text("code.unknown", $code);
		} 
		else if (isset($entity["unihint_id"])) {
			$hint = new Hint();
			$response = $hint->add($entity["unihint_id"]);
		} 
		else if (isset($entity["point_id"])) {
			$id = $entity["point_id"];
			$loc = new Loc();
			$cipher = new Cipher();
			if ($loc->repo()->isLoc($id)) {
				$response = $loc->visit($loc->repo()->get($id), $code);
			}
			else if ($cipher->repo()->isCipher($id)) {
				$response = $cipher->solve($cipher->repo()->get($id), $code);
			}
			else {
				$response = new Text("code.unknown", $code);
			}
		} else {
			$response = new Text("code.unknown", $code);
		}

		if (is_array($response)) {
			$responseStr = "";
			foreach ($response as $resp) {
				if (!empty($responseStr)) {
					$responseStr .= " ";
				}
				$responseStr .= $resp->format();				
			}
		} else {
			$responseStr = $response->format();
		}
		$message->sendToTeam($responseStr);
		return $responseStr;
	}
}
