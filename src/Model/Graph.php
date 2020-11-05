<?php
namespace Sova\Model;

use Sova\Repo\GraphRepo;

class Graph {

	public function build() {
		$ret = (new GraphRepo())->points(Game::current());
		$points = [];
		foreach ($ret as $point) {
			$point["prev"] = empty($point["prev"]) ? [] : explode(",", $point["prev"]);
			$point["next"] = empty($point["next"]) ? [] : explode(",", $point["next"]);
			$points[$point["point_id"]] = $point;
		}
		$vertices = [];
		$edges = [];
		
		foreach ($points as $id => $point) {
			if (!isset($points[$id])) continue;
			extract($point);
			$nextCipher = isset($next[0]) ? $points[$next[0]] : null;
			if ($isloc && count($next) == 1 && count($nextCipher["prev"]) == 1 && count($nextCipher["next"]) > 0) {	
				if (isset($nextCipher["name_int"])) {
					$name = "$name: {$nextCipher["name"]}/{$nextCipher["name_int"]}";
				} else {
					$name = "$name: {$nextCipher["name"]}: $name";
				}
				$type = "loc_cipher";
				foreach ($nextCipher["next"] as $nid) {
					$edges[] = [$id, $nid];
				}
				unset($points[$next[0]]);
			} else {
				if (isset($name_int)) {
					$name .= "/$name_int";
				}
				$type = $isloc ? "loc" : "cipher";
				foreach ($next as $nid) {
					$edges[] = [$id, $nid];
				}
			}
			$vertices[] = ["point_id" => $id, "type" => $type, "name" => $name, "isStart" => count($prev) == 0];
		}
		return array($vertices, $edges);
	}
}
