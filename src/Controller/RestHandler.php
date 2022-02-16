<?php
namespace Sova\Controller;

use Sova\HttpException;
use Sova\Model\Game;
use Sova\Model\Graph;
use Sova\Model\Message;
use Sova\Model\Team;


class RestHandler {

	public function crud(string $resource, string $method, array $obj, array $params = []): array {
		$modelClass = "\\Sova\\Model\\".ucfirst($resource);
		if (!class_exists($modelClass)) {
			throw new HttpException(400, "Unknown resource: '$resource'");
		}
		$model = new $modelClass();

		switch ($method) {
			case "GET": 	if (isset($params["page"]) && isset($params["pageSize"])) {
								$from = ($params["page"] - 1) * $params["pageSize"];
								$limit = $params["pageSize"];
								$data = $model->list($from, $limit);
								return [
									"data" => $model->list($from, $limit),
									"itemsCount" => $model->count()
								];
							} else {
								return $model->list();
							}
			case "POST":	$model->create($obj);
							break;
			case "PUT":		$model->update($obj);
							break;
			case "DELETE":	$model->delete($obj);
							break;
			default:		throw new HttpException(405);
		}
		return $obj;
	}
	
	
	public function graph(array $args): array {
		list($vertices, $edges) = (new Graph())->build();
		return ["vertices" => $vertices, "edges" => $edges];
	}


	public function messages_recent(array $args): array {
		return (new Message())->recent($args["since"]);
	}


	public function team_login(array $args) {
		return (new Team())->webLogin($args["team_id"], $args["pswd"]);
	}
}
