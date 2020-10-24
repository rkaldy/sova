<?php
namespace Sova\Controller;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Sova\DBException;
use Sova\RestException;
use Sova\Model\User;

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

			$repoName = "\\Sova\\Model\\".ucfirst($resource)."Repo";
			if (method_exists($this, $resource)) {
				if ($req->getMethod() != "GET") {
					throw new RestException(405);
				}
				$ret = $this->$resource();
			} else if (class_exists($repoName)) {
				$repo = new $repoName();
				$ret = $repo->restCRUD($req->getMethod(), $req->getParsedBody());
			} else {
				throw new RestException(400, "Unknown resource: '$resource'");
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

	function graph() {
		list($vertices, $edges) = \Sova\Model\Graph::sortAndGet();
		return array("vertices" => $vertices, "edges" => $edges);
	}
}
