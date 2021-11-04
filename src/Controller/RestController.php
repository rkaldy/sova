<?php
namespace Sova\Controller;

use Sova\Request;
use Sova\Response;
use Sova\DBException;
use Sova\HttpException;
use Sova\PHPException;
use Sova\Model\User;
use Sova\Model\Game;


class RestController {

	const RES_SU_READ = ["user"];
	const RES_SU_WRITE = ["game", "user"];

	
	public function process(Request $req, array $path): Response {
		try {
			if (empty($path)) {
                throw new HttpException(400, "No resource specified");
            }
            $resource = $path[0];

			$this->authenticate();
			$this->authorize($resource, $req->method);
            $this->setGame($req);

			$handler = new RestHandler();
			if ($req->method == "GET" && method_exists($handler, $resource)) {
				$ret = $handler->$resource($req->params);
			} else {
				$ret = $handler->crud($resource, $req->method, $req->data, $req->params);
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
		catch (PHPException $ex) {
			$status = 500;
			$ret = ["error" => $ex->getMessage(), "file" => $ex->getFile(), "line" => $ex->getLine()];
		}
		catch (\Throwable $ex) {
			$status = 500;
			if (DEVELOPMENT) {
			    $ret = ["error" => $ex->getMessage(), "file" => $ex->getFile(), "line" => $ex->getLine()];
			} else {
                $ret = ["error" => "Internal server error" ];
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
				throw new HttpException(401, "Unauthorized");
			}
			list($user, $password) = explode(":", base64_decode(substr($_SERVER["HTTP_AUTHORIZATION"], 6)));
			if (!(new User())->login($user, $password)) {
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
}
