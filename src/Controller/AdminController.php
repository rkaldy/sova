<?php
namespace Sova\Controller;

use Sova\Request;
use Sova\Response;
use Sova\View;
use Sova\Redirect;
use Sova\Model\User;
use Sova\Model\Game;
use Sova\Model\Team;
use Sova\Model\Message;
use Sova\Model\Progress;
use Sova\Model\Settings;
use Sova\Repo\LocRepo;


class AdminController {

	const ACTIONS_SU = ["game", "user"];
	const PAGES = ["users", "games", "locs", "ciphers", "unihints", "teams", "graph", "texts", "messages", "stats"];

	public function process(Request $req, array $path): Response {
		$action = empty($path) ? "login" : $path[0];

		if ($action != 'login' && !User::logged()) {
			$view = new View("admin/login");
		} else if ($action != 'login' && $action != 'logout' && !User::super() && !Game::selected()) {
			$view = new View("admin/login");
		} else if (in_array($action, self::ACTIONS_SU) && !User::super()) {
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
		$view->addField("superuser", User::super());
		if (User::logged()) {
			$view->addField("user", User::currentName());
		}
		if (Game::selected()) {
			$view->addField("game", Game::currentName());
		}
		$output = $view->render("admin-layout");
		return new Response(200, $output);
	}


	public function login($args) {
		$user = new User();
		$game = new Game();
		if (isset($args["login"])) {
			if ($user->login($args["login"], $args["pswd"])) {
				if (User::super()) {
					return new Redirect("games");
				}
				$games = $game->getOwnedGames();
				if (count($games) == 0) {
					return new View("admin/login", array("flash" => "Tento uživatel nemá nastavenou žádnou hru"));
				} else if (count($games) == 1) {
                    $game->setCurrentGame($games[0]["game_id"]);
					return new Redirect("locs");
				} else {
					return new View("admin/selectgame", array("games" => $ret));
				}
			} else {
				return new View("admin/login", array("flash" => "Špatný login nebo heslo"));
			}
		} else if (isset($args["game_id"])) {
			if ($game->setCurrentGame($args["game_id"])) { 
				return new Redirect("locs");
			} else {
				return new View("admin/login");
			}
		} else {
			return new View("admin/login");
		}
	}


	public function logout($args) {
		User::logout();
		return new View("admin/login");
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

	public function settings($args) {
		$settings = new Settings();
		if (!empty($args)) {
			$ret = $settings->set($args);
		}
		$fields = $settings->get();
		if (isset($ret)) {
			$fields["flash"] = $ret;
		}
		return new View("admin/settings", $fields);
	}
}
