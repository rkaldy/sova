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

	protected function prepareBooleans(array &$obj, array $fields) {
		foreach ($fields as $field) {
			if ($obj[$field] === "true") {
				$obj[$field] = 1;
			} else if ($obj[$field] === "false") {
				$obj[$field] = 0;
			} else {
				$obj[$field] = (int)$obj[$field];
			}
		}
	}

	public function list(int $from = null, $limit = null): array {
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
