<?php
namespace Sova\Controller\REST;

use Sova\Model\User;
use Sova\Repo\GameRepo;

class GameController {

	function idbyname(array $args): array {
		$ret = (new GameRepo())->getIdByName($args["name"], User::current());
        return isset($ret) ? $ret : [];
	}
}
