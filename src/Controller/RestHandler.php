<?php
namespace Sova\Controller;

use Sova\HttpException;
use Sova\Model\Code;
use Sova\Model\Game;
use Sova\Model\Graph;
use Sova\Model\Hint;
use Sova\Model\Message;
use Sova\Model\Team;
use Sova\Model\Text;


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


    public function code(array $args): array {
        return ["response" => CodeController::process($args["code"])];        
    }


	public function hint_check(array $args): array {
        $hint = new Hint();
		$message = new Message();
        $cipherName = $args["cipher"];

		$message->sendToSova((new Text("hint.check", $cipherName))->format());
		list($ok, $texts) = $hint->check($cipherName);
        $response = [];
        foreach ($texts as $text) {
            $response[] = $text->format();
            $message->sendToTeam($text->format());
        }
		return ["success" => $ok, "response" => $response];
	}


	public function hint_apply(array $args): array {
        $hint = new Hint();
		$message = new Message();
        $cipherName = $args["cipher"];

		$message->sendToSova((new Text("hint.request", $cipherName))->format());
		$response = $hint->apply($cipherName)->format();
   		$message->sendToTeam($response);
		return ["response" => [$response]];
	}
	
    protected function formatTexts(array $texts): array {
		return $response;
	}
}
