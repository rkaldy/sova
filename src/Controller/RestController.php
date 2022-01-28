<?php
namespace Sova\Controller;

use Sova\Request;
use Sova\Response;
use Sova\DBException;
use Sova\HttpException;
use Sova\PHPException;
use Sova\Model\Team;
use Sova\Model\Game;


class RestController {

	public function process(Request $req, array $path): Response {
		try {
			if (empty($path)) {
                throw new HttpException(400, "No resource specified");
            }
            $resource = $path[0];

			$this->authenticate();
			$this->authorize($resource, $req->method);

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
			$ret = ["error" => $ex->getMessage(), "file" => $ex->getFile(), "line" => $ex->getLine(), "trace" => $ex->getTraceAsString()];
		}
		catch (\Throwable $ex) {
			$status = 500;
			if (DEVELOPMENT) {
			    $ret = ["error" => $ex->getMessage(), "file" => $ex->getFile(), "line" => $ex->getLine(), "trace" => $ex->getTraceAsString()];
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
		if (!Game::selected() && !Team::logged() && !Game::superuser()) {
			if (empty($_SERVER["HTTP_AUTHORIZATION"])) {
				throw new HttpException(401, "Unauthenticated");
			}
			list($user, $password) = explode(":", base64_decode(substr($_SERVER["HTTP_AUTHORIZATION"], 6)));
			if (!(new Game())->login($user, $password)) {
				throw new HttpException(401, "Authentication failed");
			}
		}
	}


	public function authorize(string $resource, string $method) {
		if ($resource == "game" && $method != "GET" && !Game::superuser()) {
			throw new HttpException(403);
		}
	}
}
