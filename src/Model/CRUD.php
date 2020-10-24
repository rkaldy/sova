<?php
namespace Sova\Model;

use Sova\DB;

class CRUD {

	protected $db;

	public function __construct() {
		$this->db = DB::get();
	}

	public function restCRUD($method, $obj) {
		switch ($method) {
			case "GET":    return $this->list();
			case "POST":   return $this->create($obj); 
			case "PUT":    return $this->update($obj); 
			case "DELETE": return $this->delete($obj); 
		}
	}
}
