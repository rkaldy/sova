<?php
namespace Sova\Repo;

use Sova\DB;

class RepoBase {

	protected $db;

	public function __construct() {
		$this->db = DB::get();
	}
}
