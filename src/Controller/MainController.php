<?php
namespace Sova\Controller;

use \DateTime;
use Sova\Request;
use Sova\Response;
use Sova\Redirect;
use Sova\View;
use Sova\HttpException;
use Sova\AppException;
use Sova\Model\Team;
use Sova\Model\Game;
use Sova\Model\Code;
use Sova\Model\Cipher;
use Sova\Model\Hint;
use Sova\Model\Message;
use Sova\Model\Progress;
use Sova\Model\Text;
use Sova\Model\Settings;

class MainController {

	const ACTIONS_PUBLIC = ["login"];
	const ACTIONS_BEFORE_GAME = ["logout"];
	const ACTIONS_AFTER_GAME = ["hints", "ciphers", "messages", "rank", "settings", "logout"];

	public function process(Request $req, array $path): Response {
		if (empty($path)) {
			$action = Team::logged() ? "code" : "login";
		} else {
			$action = $path[0];
		}

		$view = $this->handleAction($req, $action);	
		if ($view instanceof Redirect) {
			return $view->buildResponse();
		}
		
		$view->addField("action", $action);
		$view->addField("gameState", Game::state());
		if (isset($_SESSION["flash"])) {
			$view->addField("flash", $_SESSION["flash"]);
			unset($_SESSION["flash"]);
		}
		if (Team::logged()) {
			$view->addField("team", Team::currentName());
			$view->addField("game", Game::currentName());
			$view->addField("points", (new Team())->points());
			$view->addField("ccodes", (new Hint())->unusedCCodeCount());
			$view->addField("showRank", Settings::get("showRank"));
		}

		$output = $view->render("main-layout");
		return new Response(200, $output);
	}


	public function handleAction(Request $req, string $action) {
		try {
			if (!method_exists($this, $action)) {
				throw new AppException("Neznámá akce: '$action'");
			} else if (!in_array($action, self::ACTIONS_PUBLIC) && !Team::logged()) {
				return new View("main/login", ["flash" => "Platnost přihlášení vypršela. Přihlašte se prosím znovu."]);
			} else if (!in_array($action, self::ACTIONS_BEFORE_GAME) && (Game::state() == Game::FUTURE)) {
				throw new AppException("Hra ještě nezačala.");
			} else if (!in_array($action, self::ACTIONS_AFTER_GAME) && (Game::state() == Game::PAST)) {
				throw new AppException("Hra již skončila.");
			} else {
				return $this->$action($req->params, $req->data);
			}
		} 
		catch (AppException $ex) {
			return new View("error", ["error" => $ex->getMessage()]);
		}
	}


	public function login($params, $data) {
		if (isset($data["team_id"])) {
			if ((new Team())->login((int)$data["team_id"], $data["pswd"])) {
				return new Redirect("code");
			} else {
				return new View("main/login", ["flash" => "Špatné číslo týmu nebo heslo"]);
			}
		}
		return new View("main/login");
	}


	public function logout($params, $data) {
		Team::logout();
		return new Redirect("login");
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


	public function hints($params, $data) {
		$hint = new Hint();
		$ccodeCount = $hint->unusedCCodeCount();
		list($imunityAvailable, $imunityMsg) = $hint->imunityStatus($ccodeCount);
		return new View("main/hints", ["imunityAvailable" => $imunityAvailable, "imunityMsg" => $imunityMsg->format(), "deductPoints" => Settings::get("deductPoints")]);
	}

	public function checkhint($params, $data) {
		$hint = new Hint();
		$message = new Message();

		$cipherName = Code::polish($data["cipher"]);
		$message->sendToSova((new Text("hint.check", $cipherName))->format());
		list($ok, $ret) = $hint->check($cipherName);

		$response = "";
		foreach ($ret as $text) {
			$response .= $text->format();
			$response .= ' ';
		}
		$message->sendToTeam($response);

		if ($ok) {
			return new View("main/applyhint", ["response" => $response, "cipher" => $cipherName]);
		} else {
			return new Redirect("hints", $response);
		}
	}

	public function applyhint($params, $data) {
		$hint = new Hint();
		$message = new Message();

		$cipherName = Code::polish($data["cipher"]);
		$message->sendToSova((new Text("hint.request", $cipherName))->format());
		$response = $hint->apply($cipherName)->format();
		$message->sendToTeam($response);

		return new Redirect("hints", $response);
	}

	public function imunity($params, $data) {
		return new Redirect("hints", (new Hint())->applyImunity()->format());
	}

	public function deduct($params, $data) {
		$points = (new Team())->deductPoints($data["points"]);
		return new Redirect("hints", "Bylo vám odečteno $points bodů.");
	}


	public function messages($params, $data) {
		$page = isset($params["page"]) ? $params["page"] : 1;
		list($messages, $count) = (new Message())->listForTeam(($page - 1) * 15, 15);
		return new View("main/messages", ["messages" => $messages, "totalCount" => $count, "page" => $page]);
	}

	public function rank($params, $data) {
		return new View("main/rank", ["teams" => (new Progress())->rankTotal()]);
	}

	public function ciphers($params, $data) {
		return new View("main/ciphers", ["ciphers" => (new Cipher())->teamCipherStatus()]);
	}

	public function settings($params, $data) {
		return new View("main/settings", []);
	}
}
