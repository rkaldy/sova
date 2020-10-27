<?php
namespace Sova\Controller;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Sova\View;
use Sova\Redirect;
use Sova\Model\User;
use Sova\Model\Game;
use Sova\Model\Team;
use Sova\Model\Message;


class AdminController {

	const ACTIONS_SU = array("game", "user");


	public function __invoke(Request $req, Response $resp, array $args) {
		$action = isset($args["action"]) ? $args["action"] : "login";
		if (!method_exists($this, $action)) {
			$view = new View("error", array("error" => "Neznámá akce: '$action'"));
		} else if ($action != 'login' && !User::logged()) {
			$view = new View("admin/login");
		} else if ($action != 'login' && $action != 'logout' && !User::super() && !Game::selected()) {
			$view = new View("admin/login");
		} else if (in_array($action, self::ACTIONS_SU) && !User::super()) {
			$view = new View("error", "Nedostatečná práva k akci '$action'");
		} else {
			$view = $this->$action($req->getParsedBody());
			if ($view instanceof Redirect) {
				return $view->buildResponse($resp);
			}
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
		$resp->getBody()->write($output);
		return $resp;
	}

	public function login($args) {
		$user = new User();
		$game = new Game();
		if (isset($args["login"])) {
			if ($user->login($args["login"], $args["pswd"])) {
				if (User::super()) {
					return new Redirect("games");
				}
				$ret = $game->setOwnedGame();
				if ($ret == 0) {
					return new View("admin/login", array("flash" => "Tento uživatel nemá nastavenou žádnou hru"));
				} else if ($ret == 1) {
					return new Redirect("locs");
				} else {
					return new View("admin/selectgame", array("games" => $ret));
				}
			} else {
				return new View("admin/login", array("flash" => "Špatný login nebo heslo"));
			}
		} else if (isset($args["game_id"])) {
			$ret = $game->setOwnedGame($args["game_id"]);
			if ($ret == 1) {
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

	public function users($args) 	{ return new View("admin/users"); }
	public function games($args) 	{ return new View("admin/games"); }
	public function locs($args)		{ return new View("admin/locs"); }
	public function ciphers($args)	{ return new View("admin/ciphers"); }
	public function hints($args)	{ return new View("admin/hints"); }
	public function teams($args)	{ return new View("admin/teams"); }
	public function graph($args)	{ return new View("admin/graph"); }
	public function messages($args)	{ return new View("admin/messages"); }

	public function broadcast($args) {
		$teams = (new Team())->list();
		return new View("admin/broadcast", array("teams" => $teams));
	}

	public function broadcast_send($args) {
		(new Message())->broadcast($args["team"], $args["message"]);
		return new Redirect("messages");
	}
}
