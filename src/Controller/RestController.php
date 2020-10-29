<?php
namespace Sova\Controller;

use Sova\Request;
use Sova\Response;
use Sova\DBException;
use Sova\HttpException;
use Sova\Model\User;
use Sova\Model\Game;
use Sova\Model\Graph;
use Sova\Model\Message;

class RestController {

	const RES_SU_READ = array("user");
	const RES_SU_WRITE = array("game", "user");

	
	public function process(Request $req, array $path): Response {
		try {
			$resource = $path[0];
			if (!User::logged()) {
				throw new HttpException(401);
			} else if ($req->method == "GET" && in_array($resource, self::RES_SU_READ) && !User::super()) {
				throw new HttpException(403);
			} else if (in_array($resource, self::RES_SU_WRITE) && !User::super()) {
				throw new HttpException(403);
			}

			if (method_exists($this, $resource)) {
				if ($req->method != "GET") {
					throw new HttpException(405);
				}
				$ret = $this->$resource($req->params);
			} else {
				$ret = $this->crud($resource, $req->method, $req->data);
			}
			$status = 200;
		} 
		catch (DBException $ex) {
			$status = 422;
			$ret = array("error" => $ex->getMessage(), "code" => $ex->getCode());
			if (DEVELOPMENT) {
				$ret["sql_query"] = $ex->query;
				$ret["sql_params"] = $ex->params;
			}
		}
		catch (HttpException $ex) {
			$status = $ex->getCode();
			$ret = array("error" => $ex->getMessage());
		}
		catch (\Exception $ex) {
			$status = 422;
			$ret = array("error" => $ex->getMessage());
		}

		$resp = new Response($status, json_encode($ret));
		$resp->addHeader("Content-Type", "application/json; charset=UTF-8");
		return $resp;
	}

	
	public function crud(string $resource, string $method, $obj): array {
		$modelClass = "\\Sova\\Model\\".ucfirst($resource);
		if (!class_exists($modelClass)) {
			throw new HttpException(400, "Unknown resource: '$resource'");
		}
		$model = new $modelClass();
		$repo = $model->repo();

		switch ($method) {
			case "GET": 	$gameId = Game::selected() ? Game::current() : null;
							return $repo->list($gameId);
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


	function graph(array $args): array {
		list($vertices, $edges) = (new Graph())->sortAndGet();
		return array("vertices" => $vertices, "edges" => $edges);
	}

	function messages(array $args): array {
		$message = new Message();
		return array("data" => $message->list($args["page"], $args["pageSize"]), "itemsCount" => $message->count());
	}
}
