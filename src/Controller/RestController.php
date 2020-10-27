<?php
namespace Sova\Controller;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Sova\DBException;
use Sova\RestException;
use Sova\Model\User;
use Sova\Model\Game;
use Sova\Model\Graph;
use Sova\Model\Message;

class RestController {

	const RES_SU_READ = array("user");
	const RES_SU_WRITE = array("game", "user");

	
	public function __invoke(Request $req, Response $resp, array $args) {
		try {
			$resource = $args["resource"];
			if (!User::logged()) {
				throw new RestException(401);
			} else if ($req->getMethod() == "GET" && in_array($resource, self::RES_SU_READ) && !User::super()) {
				throw new RestException(403);
			} else if (in_array($resource, self::RES_SU_WRITE) && !User::super()) {
				throw new RestException(403);
			}

			if (method_exists($this, $resource)) {
				if ($req->getMethod() != "GET") {
					throw new RestException(405);
				}
				$ret = $this->$resource($args);
			} else {
				$ret = $this->crud($resource, $req->getMethod(), $req->getParsedBody());
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
		catch (RestException $ex) {
			$status = $ex->httpCode;
			$ret = array("error" => $ex->getMessage());
		}
		catch (\Exception $ex) {
			$status = 422;
			$ret = array("error" => $ex->getMessage());
		}

		$resp->getBody()->write(json_encode($ret));
		return $resp->withHeader("Content-Type", "application/json; charset=UTF-8")->withStatus($status);
	}

	
	public function crud(string $resource, string $method, array $obj): array {
		$modelClass = "\\Sova\\Model\\".ucfirst($resource);
		if (!class_exists($modelClass)) {
			throw new RestException(400, "Unknown resource: '$resource'");
		}
		$model = new $modelClass();
		$repo = $model->repo();

		switch ($method) {
			case "GET": 	return $repo->list(Game::current());
			case "POST":	$model->prepare($obj);
							$repo->create($obj);
							break;
			case "PUT":		$model->prepare($obj);
							$repo->update($obj);
							break;
			case "DELETE":	$repo->delete($obj);
							break;
			default:		throw new RestException(405);
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
