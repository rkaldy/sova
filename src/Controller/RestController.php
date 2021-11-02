<?php
namespace Sova\Controller;

use Sova\Request;
use Sova\Response;
use Sova\DBException;
use Sova\HttpException;
use Sova\Model\User;
use Sova\Model\Game;


class RestController {

	const RES_SU_READ = ["user"];
	const RES_SU_WRITE = ["game", "user"];

	
	public function process(Request $req, array $path): Response {
		try {
			$resource = $path[0];
			if (isset($path[1])) {
				$query = $path[1];
			}

			$this->authenticate();
			$this->authorize($resource, $req->method);
            $this->setGame($req);

			if ($req->method == "GET" && isset($query)) {
				$controllerClass = "\\Sova\\Controller\\REST\\" . ucfirst($resource) . "Controller";
				if (!class_exists($controllerClass)) {
					throw new HttpException(400, "Unknown resource: '$resource'");
				}
				$controller = new $controllerClass();
				if (!method_exists($controller, $query)) {
					throw new HttpException(400, "Method $resource.$query not found");
				}
				$ret = $controller->$query($req->params);
			} else {
				$ret = $this->crud($resource, $req->method, $req->data, $req->params);
			}
			$status = 200;
		} 
		catch (DBException $ex) {
			$status = 422;
			$ret = ["error" => $ex->getMessage(), "code" => $ex->getCode()];
			if (DEVELOPMENT) {
				$ret["sql_query"] = $ex->query;
				$ret["sql_params"] = $ex->params;
			}
		}
		catch (HttpException $ex) {
			$status = $ex->getCode();
			$ret = ["error" => $ex->getMessage()];
		}
		catch (\Exception $ex) {
			$status = 422;
			$ret = ["error" => $ex->getMessage()];
			if (DEVELOPMENT) {
				$ret["file"] = $ex->getFile();
				$ret["line"] = $ex->getLine();
			}
		}
		
		$jsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        if (isset($req->params["pretty"])) {
            $jsonFlags |= JSON_PRETTY_PRINT;
        }
		$resp = new Response($status, json_encode($ret, $jsonFlags));
		$resp->addHeader("Content-Type", "application/json; charset=UTF-8");
		return $resp;
	}


	public function authenticate() {
		if (!User::logged()) {
			if (empty($_SERVER["HTTP_AUTHORIZATION"])) {
				throw new HttpException(401, "Unauthenticated");
			}
			list($user, $password) = explode(":", base64_decode(substr($_SERVER["HTTP_AUTHORIZATION"], 6)));
			if (!(new User())->login($_SERVER["PHP_AUTH_USER"], $_SERVER["PHP_AUTH_PW"])) {
				throw new HttpException(401, "Authentication failed");
			}
		}
	}


	public function authorize(string $resource, string $method) {
		if (User::super()) {
			return;
		}
		if ($method == "GET") {
			if (in_array($resource, self::RES_SU_READ)) {
				throw new HttpException(403);
			}
		} else {
			if (in_array($resource, self::RES_SU_WRITE)) {
				throw new HttpException(403);
			}
		}
	}


    public function setGame(Request $req) {
        if (isset($req->params["game_id"])) {
            $_SESSION["game_id"] = $req->params["game_id"];
        } else if (isset($req->params["game"])) {
            $gameId = (new Game())->getIdByName($req->params["game"]);
            if (!isset($gameId)) {
                throw new HttpException(400, "Unknown game: " . $req->params["game"]);
            }
            $_SESSION["game_id"] = $gameId;
        }
    }

	
	public function crud(string $resource, string $method, array $obj, array $params = []): array {
		$modelClass = "\\Sova\\Model\\".ucfirst($resource);
		if (!class_exists($modelClass)) {
			throw new HttpException(400, "Unknown resource: '$resource'");
		}
		$model = new $modelClass();
		$repo = $model->repo();

		switch ($method) {
			case "GET": 	$gameId = Game::selected() ? Game::current() : null;
							if (isset($params["page"]) && isset($params["pageSize"])) {
								$from = ($params["page"] - 1) * $params["pageSize"];
								$limit = $params["pageSize"];
								return $repo->list($gameId, $from, $limit);
							} else {
								return $repo->list($gameId);
							}
			case "POST":	$model->prepare($obj);
							$repo->create($obj);
							break;
			case "PUT":		$model->prepare($obj);
							$repo->update($obj);
							break;
			case "DELETE":	$repo->delete($obj);
							break;
			default:		throw new HttpException(405);
		}
		return $obj;
	}
}
