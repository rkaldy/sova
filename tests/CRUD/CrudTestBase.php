<?php
namespace Sova\CRUD;

use Sova\TestBase;
use Sova\Controller\RestController;

class CrudTestBase extends TestBase {

	protected $rest;
	protected $resource;

	function setUp(): void {
		parent::setUp();
		$this->rest = new RestController();
		preg_match("/([A-Za-z]+)CrudTest/", get_class($this), $match);
		$this->resource = $match[1];
	}

	function list() {
		return $this->rest->crud($this->resource, "GET", array());
	}

	function create(array $obj) {
		return $this->rest->crud($this->resource, "POST", $obj);
	}

	function update(array $obj) {
		return $this->rest->crud($this->resource, "PUT", $obj);
	}

	function delete(array $obj) {
		return $this->rest->crud($this->resource, "DELETE", $obj);
	}
}
