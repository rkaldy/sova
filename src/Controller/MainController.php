<?php
namespace Sova\Controller;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Sova\View;
use Sova\Model\Team;
use Sova\Model\Code;
use Sova\Model\MessageRepo;

class MainController {

	const ACTIONS_PUBLIC = array("login");

	public function __invoke(Request $req, Response $resp, array $args) {
		$action = isset($args["action"]) ? $args["action"] : "login";
		if (!method_exists($this, $action)) {
			$view = new View("error", array("error" => "Neznámá akce: '$action'"));
		} else if (!in_array($action, self::ACTIONS_PUBLIC) && !Team::logged()) {
			$view = new View("main/login", array("flash" => "Platnost přihlášení vypršela. Přihlašte se prosím znovu."));
		} else {
			$view = $this->$action($req->getParsedBody());
		}

		$view->addField("action", $action);
		if (Team::logged()) {
			$view->addField("team", Team::currentName());
		}
		$output = $view->render("main-layout");
		$resp->getBody()->write($output);
		return $resp;
	}

	public function login($args) {
		if (isset($args["team_id"])) {
			if (Team::login($args["team_id"], $args["pswd"])) {
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
			$response = (new CodeController())->process($args["code"]);
		} else {
			$response = null;
		}
		return new View("main/code", array("response" => $response));
	}	
}
