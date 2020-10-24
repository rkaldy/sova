<?php
namespace Sova\Controller;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Sova\View;
use Sova\Model\Team;

class MainController {

	public function __invoke(Request $req, Response $resp, array $args) {
		$action = isset($args["action"]) ? $args["action"] : "login";
		if (method_exists($this, $action)) {
			$view = $this->$action($req->getParsedBody());
		} else {
			$view = new View("error", array("error" => "Neznámá akce: '$action'"));
		}
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
}
