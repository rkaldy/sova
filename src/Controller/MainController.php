<?php
namespace Sova\Controller;

use Sova\Request;
use Sova\Response;
use Sova\View;
use Sova\HttpException;
use Sova\Model\Team;
use Sova\Model\Game;
use Sova\Model\Code;
use Sova\Model\Hint;
use Sova\Model\Message;
use Sova\Model\Progress;
use Sova\Model\Text;
use Sova\Model\Settings;


class MainController {

	const ACTIONS_PUBLIC = ["login"];
	const ACTIONS_DURING_GAME = ["code", "applyhint"];

	public function process(Request $req, array $path): Response {
		if (empty($path)) {
			$action = Team::logged() ? "code" : "login";
		} else {
			$action = $path[0];
		}
		
		if (!method_exists($this, $action)) {
			$view = new View("error", ["error" => "Neznámá akce: '$action'"]);
		} else if (!in_array($action, self::ACTIONS_PUBLIC) && !Team::logged()) {
			$view = new View("main/login", ["flash" => "Platnost přihlášení vypršela. Přihlašte se prosím znovu."]);
		} else {
			$view = $this->$action($req->params, $req->data);
		}

		$view->addField("action", $action);
		$view->addField("gameState", Game::state());
		if (Team::logged()) {
			$view->addField("team", Team::currentName());
			$view->addField("game", Game::currentName());
		    $view->addField("showRank", Settings::value("showRank"));
		}

		$output = $view->render("main-layout");
		return new Response(200, $output);
	}


	public function login($params, $data) {
		if (isset($data["team_id"])) {
			if ((new Team())->login((int)$data["team_id"], $data["pswd"])) {
				return $this->code([], []);
			} else {
				return new View("main/login", ["flash" => "Špatné číslo týmu nebo heslo"]);
			}
		}
		return new View("main/login");
	}


	public function logout($params, $data) {
		Team::logout();
		return new View("main/login");
	}


	public function code($params, $data) {
		if (isset($data["code"])) {
			$response = CodeController::process($data["code"]);
		} else if (isset($params["code"])) {
			$response = CodeController::process($params["code"]);
		} else {
			$response = [];
		}
		return new View("main/code", ["response" => $response]);
	}	


	public function applyhint($params, $data) {
		$hint = new Hint();
		if (isset($data["cipher"])) {
			if (Game::state() != Game::CURRENT) {
				throw new HttpException(403);
			}
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


	public function messages($params, $data) {
		$page = isset($params["page"]) ? $params["page"] : 1;
		list($messages, $count) = (new Message())->listForTeam(($page - 1) * 15, 15);
		return new View("main/messages", ["messages" => $messages, "totalCount" => $count, "page" => $page]);
	}


	public function rank($params, $data) {
		return new View("main/rank", ["teams" => (new Progress())->rankTotal()]);
	}
}
