<?php
namespace Sova\Model;

use \Sova\Repo\HintRepo;


class Ccode extends ModelBase {

	protected $hintRepo;

	public function __construct() {
		parent::__construct();
		$this->hintRepo = new HintRepo();
	}

	public function prepare(array &$ccode) {
		$ccode["game_id"] = Game::current();
		(new Code())->prepare($ccode["code"]);
	}

	public function add(int $ccodeId) {
		$ccode = $this->repo->get($ccodeId);
		if (!$this->isAvailable($ccode)) {
			return new Text("code.unknown", $ccode["code"]);
		}
		$hint = ["team_id" => Team::current(), "ccode_id" => $ccodeId];
		if (!$this->hintRepo->addCCode($hint)) {
			return new Text("ccode.add.already");
		} 
		$count = $this->hintRepo->getUnusedCCodeCount($hint["team_id"]);
		return new Text("ccode.add.success", $count);
	}

	protected function isAvailable(array &$ccode) {
		if (isset($ccode["cond_loc_id"])) {
			return $this->repo->isAvailable($ccode, Team::current());
		} else {
            return true;
		}
	}
	
	public function unusedCount() {
		return $this->hintRepo->getUnusedCCodeCount(Team::current());
	}

}
