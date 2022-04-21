<?php
namespace Sova\Controller;

use Sova\Request;
use Sova\Response;
use Sova\View;
use Sova\Redirect;
use Sova\Model\Game;
use Sova\Model\Team;
use Sova\Model\Loc;
use Sova\Model\Message;
use Sova\Model\Progress;
use Sova\Model\Settings;


class AdminController {

	const ACTIONS_SU = ["game", "user"];
	const PAGES = ["users", "games", "locs", "ciphers", "ccodes", "teams", "graph", "texts", "messages", "stats"];

	public function process(Request $req, array $path): Response {
		$action = empty($path) ? "login" : $path[0];

		if ($action != 'login' && !Game::selected() && !Game::superuser()) {
			$view = new View("admin/login");
		} else if (in_array($action, self::ACTIONS_SU) && !Game::superuser()) {
			$view = new View("error", "Nedostatečná práva k akci '$action'");
		} else if (in_array($action, self::PAGES)) {
			$view = new View("admin/$action");
		} else if (method_exists($this, $action)) {
			$args = array_merge($req->params, $req->data);
			$view = $this->$action($args);
			if ($view instanceof Redirect) {
				return $view->buildResponse();
			}
		} else {
			$view = new View("error", array("error" => "Neznámá akce: '$action'"));
		}
	
		$view->addField("action", $action);
		$view->addField("superuser", Game::superuser());
		if (Game::selected()) {
			$view->addField("game", Game::currentName());
		}
		$output = $view->render("admin-layout");
		return new Response(200, $output);
	}


	public function login($args) {
		$game = new Game();
		if (isset($args["login"])) {
			if ($game->login($args["login"], $args["pswd"])) {
				if (Game::superuser()) {
					return new Redirect("games");
				}
				return new Redirect("locs");
			} else {
				return new View("admin/login", array("flash" => "Špatný login nebo heslo"));
			}
		} else {
			return new View("admin/login");
		}
	}


	public function logout($args) {
		Game::logout();
		return new Redirect("login");
	}


	public function broadcast($args) {
		$teams = (new Team())->list();
		return new View("admin/broadcast", array("teams" => $teams));
	}

	public function broadcast_send($args) {
		if (isset($args["team"])) {
			(new Message())->broadcast($args["team"], $args["message"]);
		}
		return new Redirect("messages");
	}

	public function rank($args) {
		return new View("main/rank", ["teams" => (new Progress())->rankTotal()]);
	}

	public function progress() {
		list($progress, $maxSolved) = (new Progress())->locStatus();
		return new View("admin/progress", [
			"progress" => $progress,
			"maxSolved" => $maxSolved
		]);
	}

	public function cipherStatus() {
		return new View("admin/cipherstatus", ["progress" => (new Progress())->cipherStatus()]);
	}

	public function settings($args) {
		$settings = new Settings();
		$flash = null;
		if (!empty($args)) {
			$flash = $settings->save($args);
		}
		return new View("admin/settings", ["fields" => $_SESSION["settings"], "locs" => (new Loc())->locs(), "flash" => $flash]);
	}
}
