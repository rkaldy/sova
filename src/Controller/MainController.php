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

	public function process(Request $req): Response {
		if (empty($req->routePath)) {
			$action = Team::logged ? "code" : "login";
		} else {
			$action = $req->routePath[0];
		}
		
		if (!method_exists($this, $action)) {
			$view = new View("error", array("error" => "Neznámá akce: '$action'"));
		} else if (!in_array($action, self::ACTIONS_PUBLIC) && !Team::logged()) {
			$view = new View("main/login", array("flash" => "Platnost přihlášení vypršela. Přihlašte se prosím znovu."));
		} else {
			$view = $this->$action($req->data);
		}

		$view->addField("action", $action);
		if (Team::logged()) {
			$view->addField("team", Team::currentName());
		}

		$output = $view->render("main-layout");
		return new Response(200, $output);
	}

	public function login($args) {
		if (isset($args["team_id"])) {
			if ((new Team())->login((int)$args["team_id"], $args["pswd"])) {
				return new View("main/code");
			} else {
				return new View("main/login", array("flash" => "Špatné číslo týmu nebo heslo"));
			}
		}
		return new View("main/login");
	}

	public function logout($args) {
		Team::logout();
		return new View("main/login");
	}

	public function code($args) {
		if (isset($args["code"])) {
			$response = CodeController::process($args["code"]);
		} else {
			$response = null;
		}
		return new View("main/code", array("response" => $response));
	}	

	public function applyhint($args) {
		$hint = new Hint();
		if (isset($args["cipher"])) {
			$cipherName = Code::polish($args["cipher"]);
			$message = new Message();
			$message->sendToSova((new Text("hint.request", $cipherName))->format());
			$response = $hint->apply($cipherName)->format();
			$message->sendToTeam($response);
		} else {
			$response = null;
		}
		return new View("main/applyhint", array("response" => $response, "hintCount" => $hint->unusedHintCount()));
	}
}
