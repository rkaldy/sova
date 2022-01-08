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

	public function list(int $from = null, $limit = null) {
		return $this->repo->list(Game::current(), $from, $limit);		
	}

	public function create(array &$obj) {
		$this->prepare($obj);
		$this->repo->create($obj);
	}

	public function update(array &$obj) {
		$this->prepare($obj);
		$this->repo->update($obj);
	}

	public function delete(array &$obj) {
		$this->repo->delete($obj);
	}
}
