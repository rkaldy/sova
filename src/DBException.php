<?php
namespace Sova;

class DBException extends \Exception {

	public $query;
	public $params;

	public function __construct($msg, $sqlCode = null) {
		parent::__construct(DEVELOPMENT ? $msg : 'Databázová chyba');
		$this->code = $sqlCode;
		$this->query = DB::get()->getLastQuery();
		$this->params = DB::get()->getLastParams();
	}

	public function getDesc() {
		if (DEVELOPMENT) {
			return "Description: $this->desc\nQuery: $this->query\nParams: ".var_export($this->params, true);
		} else {
			return $this->desc;
		}
	}
}
