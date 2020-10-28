<?php
namespace Sova;

class Request {

	public $method;
	public $url;
	public $params;
	public $data;

	public function __construct($method, $url, $params, $data) {
		$this->method = $method;
		$this->url = $url;
		$this->params = $params;
		$this->data = $data;
	}
}
