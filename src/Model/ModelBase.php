<?php
namespace Sova\Model;

use Sova\Repo\RepoBase;

class ModelBase {

	protected $repo;

	public function __construct() {
		$thisClass = get_class($this);
		$repoClass = "\\Sova\\Repo\\".substr($thisClass, strrpos($thisClass, "\\") + 1)."Repo";
		$this->repo = new $repoClass();
	}
	
	public function repo(): RepoBase {
		return $this->repo;
	}

	public function prepare(array &$obj) {}
}
