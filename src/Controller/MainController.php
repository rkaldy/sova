<?php
namespace Sova\Controller;

use Sova\Request;
use Sova\Response;
use Sova\View;
use Sova\Model\Team;
use Sova\Model\Code;
use Sova\Model\Hint;
use Sova\Model\Message;

class MainController {

	const ACTIONS_PUBLIC = array("login");

	public function process(Request $req, array $path): Response {
		if (empty($path)) {
			$action = Team::logged() ? "code" : "login";
		} else {
			$action = $path[0];
		}
		
		if (!method_exists($this, $action)) {
			$view = new View("error", array("error" => "Neznámá akce: '$action'"));
		} else if (!in_array($action, self::ACTIONS_PUBLIC) && !Team::logged()) {
			$view = new View("main/login", array("flash" => "Platnost přihlášení vypršela. Přihlašte se prosím znovu."));
		} else {
			$view = $this->$action($req->params, $req->data);
		}

		$view->addField("action", $action);
		if (Team::logged()) {
			$view->addField("team", Team::currentName());
		}

		$output = $view->render("main-layout");
		return new Response(200, $output);
	}

	function login($params, $data) {
		if (isset($data["team_id"])) {
			if ((new Team())->login((int)$data["team_id"], $data["pswd"])) {
				return new View("main/code");
			} else {
				return new View("main/login", array("flash" => "Špatné číslo týmu nebo heslo"));
			}
		}
		return new View("main/login");
	}

	function logout($params, $data) {
		Team::logout();
		return new View("main/login");
	}

	function code($params, $data) {
		if (isset($data["code"])) {
			$response = CodeController::process($data["code"]);
		} else {
			$response = null;
		}
		return new View("main/code", ["response" => $response]);
	}	

	function applyhint($params, $data) {
		$hint = new Hint();
		if (isset($data["cipher"])) {
			$cipherName = Code::polish($data["cipher"]);
			$message = new Message();
			$message->sendToSova((new Text("hint.request", $cipherName))->format());
			$response = $hint->apply($cipherName)->format();
			$message->sendToTeam($response);
		} else {
			$response = null;
		}
		return new View("main/applyhint", ["response" => $response, "hintCount" => $hint->unusedHintCount()]);
	}

	function messages($params, $data) {
		$page = isset($params["page"]) ? $params["page"] : 1;
		list($messages, $count) = (new Message())->listForTeam($page, 20);
		return new View("main/messages", ["messages" => $messages, "totalCount" => $count, "page" => $page]);
	}

}
