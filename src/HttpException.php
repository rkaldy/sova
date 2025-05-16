<?php
namespace Sova;

class HttpException extends \Exception {
	public function __construct($httpCode, $msg = null) {
		parent::__construct(isset($msg) ? $msg : "HTTP error", $httpCode);
	}
}

