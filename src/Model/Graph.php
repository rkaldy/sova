<?php
namespace Sova\Model;

use Sova\Repo\GraphRepo;

class Graph {

	protected $repo;

	public function __construct() {
		$this->repo = new GraphRepo();
	}

	public function get() {
		$points = $this->repo->pointsWithNext(Game::current());
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


	public function sort() {
		$points = $this->repo->points(Game::current());
		$steps = $this->repo->steps(Game::current());

		$edgesFrom = array();
		$edgesTo = array();
		foreach (array_keys($points) as $p) {
			$edgesFrom[$p] = array();
			$edgesTo[$p] = array();
		}
		foreach ($steps as $step) {
			extract($step);
			$edgesFrom[$from_point_id][$to_point_id] = 1;
			$edgesTo[$to_point_id][$from_point_id] = 1;
		}

		$sortedVertices = $this->topoSort($points, $edgesFrom, $edgesTo);
		$this->repo->sort($sortedVertices);
	}


	public function topoSort(array $vertices, array $edgesFrom, array $edgesTo) {
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

	
	public function sortAndGet() {
		if (!$this->repo->isSorted(Game::current())) {
			$this->sort();
		}
		return $this->get();
	}
}
