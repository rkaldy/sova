<?php
namespace Sova\Controller;

use Sova\Request;
use Sova\Response;
use Sova\Redirect;
use Sova\Model\User;
use Sova\Model\Statistics;


class StatController {

	public function process(Request $req, array $path): Response {
		if (!User::logged()) {
			return (new Redirect("login"))->buildResponse();
		}

		$stats = $this->buildStats($req->params["type"]);
		$resp = new Response(200, $this->toCSV($stats));
		$resp->addHeader("Content-Type", "text/csv");
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
		foreach ($data as $row) {
			foreach ($row as $field) {
				$output .= "$field,";
			}
			$output .= "\n";
		}
		return $output;
	}
}
