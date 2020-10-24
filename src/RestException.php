<?php
namespace Sova;

class RestException extends \Exception {
	public $httpCode;

	public function __construct($httpCode, $msg = null) {
		parent::__construct($msg);
		$this->httpCode = $httpCode;
	}
}

