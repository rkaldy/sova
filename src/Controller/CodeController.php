<?php
namespace Sova\Controller;

use Sova\Model\Code;
use Sova\Model\Loc;
use Sova\Model\Cipher;
use Sova\Model\Hint;
use Sova\Model\Game;
use Sova\Model\Message;
use Sova\Model\Text;
use Sova\HttpException;


class CodeController {

	public static function process($request) {
		if (Game::state() != Game::CURRENT) {
			throw new HttpException(403);
		}

		$code = Code::polish($request);
		if (!Code::valid($code)) {
			return [(new Text("code.invalid"))->format()];
		}
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

		if (!is_array($response)) {
			$response = [$response];
		}
		$responseStr = [];
		foreach ($response as $resp) {
			$str = $resp->format();	
			$responseStr[] = $str;
			$message->sendToTeam($str);
		}
		return $responseStr;
	}
}
