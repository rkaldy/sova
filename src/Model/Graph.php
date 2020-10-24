<?php
namespace Sova\Model;

use Sova\DB;

class Graph {

	public static function get() {
		$db = DB::get();
		$points = array();
		$ret = $db->query("
			SELECT point_id, ISNULL(cipher.point_id) as isloc, name, name_int, GROUP_CONCAT(next.to_point_id SEPARATOR ',') AS next
			FROM point NATURAL LEFT JOIN cipher 
			LEFT JOIN step AS next ON next.from_point_id = point_id
			WHERE game_id = ?
			GROUP BY point_id
			ORDER BY sort_id, isloc DESC
		", Game::current());
		while ($row = $ret->fetch(\PDO::FETCH_ASSOC)) {
			$points[$row["point_id"]] = $row;
		}

		$vertices = array();
		$edges = array();
		$removedVertices = array();
		foreach ($points as $id => $point) {
			if (isset($removedVertices[$id])) continue;
			extract($point);
			$next = empty($next) ? array() : explode(",", $next);
			if ($isloc && count($next) == 1) {
				$nextCipher = $points[$next[0]];
				if (isset($nextCipher["name_int"])) {
					$name = "{$nextCipher["name"]}/{$nextCipher["name_int"]}: $name";
				} else {
					$name = "{$nextCipher["name"]}: $name";
				}
				$type = "loc_cipher";
				$removedVertices[$next[0]] =  1;
				$next = explode(",", $nextCipher["next"]);
				foreach ($next as $nid) {
					$edges[] = array($id, $nid);
				}
			} else {
				if (isset($name_int)) {
					$name .= "/$name_int";
				}
				$type = $isloc ? "loc" : "cipher";
				foreach ($next as $nid) {
					$edges[] = array($id, $nid);
				}
			}
			$vertices[] = array("point_id" => $id, "type" => $type, "name" => $name);
		}
		return array($vertices, $edges);
	}


	public static function isSorted() {
		$db = DB::get();
		return $db->equery("SELECT COUNT(*) FROM point WHERE game_id = ? AND sort_id IS NULL", Game::current()) == 0;
	}


	public static function sort() {
		$db = DB::get();
		$vertices = $db->dquery("SELECT point_id, 1 FROM point WHERE game_id = ? ORDER BY name", Game::current());
		$steps = $db->aquery("SELECT step.* FROM step JOIN point ON from_point_id = point_id WHERE game_id = ?", Game::current());

		$edgesFrom = array();
		$edgesTo = array();
		foreach (array_keys($vertices) as $v) {
			$edgesFrom[$v] = array();
			$edgesTo[$v] = array();
		}
		foreach ($steps as $step) {
			extract($step);
			$edgesFrom[$from_point_id][$to_point_id] = 1;
			$edgesTo[$to_point_id][$from_point_id] = 1;
		}

		$sortedVertices = self::topoSort($vertices, $edgesFrom, $edgesTo);

		$stmt = $db->prepare("UPDATE point SET sort_id = ? WHERE point_id = ?");
		foreach ($sortedVertices as $point_id => $sort_id) {
			$stmt->execute(array($sort_id, $point_id));
		}
	}


	public static function topoSort(array $vertices, array $edgesFrom, array $edgesTo) {
		$sortedVertices = array();

		$sortId = 1;
		while (!empty($vertices)) {
			$found = false;
			foreach (array_keys($vertices) as $v) {
				if (empty($edgesTo[$v])) {
					$sortedVertices[$v] = $sortId;
					$sortId++;
					foreach (array_keys($edgesFrom[$v]) as $w) {
						unset($edgesTo[$w][$v]);
					}
					unset($vertices[$v]);
					$found = true;
					break;
				}
			}
			if (!$found) {
				$sortId = 9999;
				while (!empty($vertices)) {
					$found = false;
					foreach (array_keys($vertices) as $v) {
						if (empty($edgesFrom[$v])) {
							$sortedVertices[$v] = $sortId;
							$sortId--;
							foreach (array_keys($edgesTo[$v]) as $w) {
								unset($edgesFrom[$w][$v]);
							}
							unset($vertices[$v]);
							$found = true;
							break;
						}
					}
					if (!$found) {
						foreach (array_keys($vertices) as $v) {
							$sortedVertices[$v] = 5000;
						}
						$vertices = array();
					}
				}
			}
		}
		return $sortedVertices;
	}

	
	public static function sortAndGet() {
		if (!self::isSorted()) {
			self::sort();
		}
		return self::get();
	}
}
