<?php
namespace Sova\Repo;

use Sova\DB;

class RepoBase {

	protected $db;

	public function __construct() {
		$this->db = DB::get();
	}

	public static function convertBooleans(array &$obj, array $fields) {
		foreach ($fields as $field) {
			$obj[$field] = (bool)$obj[$field];
		}
	}
}
