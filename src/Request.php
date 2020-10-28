<?php
namespace Sova;

class Request {

	public $method;
	public $routePath;
	public $queryParams;
	public $data;

	public function __construct($method, $routePath, $queryParams, $data) {
		$this->method = $method;
		$this->routePath = $routePath;
		$this->queryParams = $queryParams;
		$this->data = $data;
	}
}
