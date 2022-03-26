<?php
namespace Sova\Repo;

class PointRepo extends RepoBase {

	protected static function flattenPrevNext(array &$point) {
		$point["prev"] = empty($point["prev"]) ? [] : explode(",", $point["prev"]);
		$point["next"] = empty($point["next"]) ? [] : explode(",", $point["next"]);
	}
}
