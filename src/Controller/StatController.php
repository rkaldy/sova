<?php
namespace Sova\Controller;

use Sova\Request;
use Sova\Response;
use Sova\Redirect;
use Sova\Model\Game;
use Sova\Model\Statistics;
use Sova\HttpException;


class StatController {

	public function process(Request $req, array $path): Response {
		if (!Game::selected()) {
			return (new Redirect("login"))->buildResponse();
		}
		if (!isset($req->params["type"])) {
			throw new HttpException(400, "No stats type defined");
		}

		$type = $req->params["type"];
		$stats = $this->buildStats($type);
		$resp = new Response(200, $this->toCSV($stats));
		$resp->addHeader("Content-Type", "text/csv");
		$resp->addHeader("Content-Disposition", "attachment; filename=\"stat-$type.csv\"");
		return $resp;
	}

	public function buildStats($type) {
		$stat = new Statistics();
		if (!method_exists($stat, $type)) {
			throw new HttpException(400, "Invalid statistics type: $type");
		}
		return $stat->$type();
	}

	public function toCSV(array $data) {
		$output = "";
		foreach (array_values($data) as $row) {
			$output .= join(",", $row) . "\n";
		}
		return $output;
	}
}
