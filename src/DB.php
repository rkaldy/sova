<?php
namespace Sova;

use PDO;

class DB extends PDO {
	
	protected static $instance;
	protected $lastQuery;
	protected $lastParams;

	
	public function __construct($dsn, $user, $pass) {
		try {
			parent::__construct($dsn, $user, $pass);
		} catch (Exception $ex) {
			throw new Exception("Chyba při připojení k databázi: ".$ex->getMessage());
		}
	}

	public static function get() {
		if (!isset(self::$instance)) {
			self::$instance = new DB("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=UTF8", DB_USER, DB_PASS);
		}
		return self::$instance;
	}


	public function getLastQuery() { return $this->lastQuery; }
	public function getLastParams() { return $this->lastParams; }

	
	public function bindValue(&$stmt, $name, $value) {
		if (is_array($value)) {
			$value = json_encode($value);
			$type = PDO::PARAM_STR;
		} else {
			$type = preg_match("/^[0-9]+$/", $value) ? PDO::PARAM_INT : PDO::PARAM_STR;
		}
		$stmt->bindValue($name, $value, $type);
		$this->lastParams[] = $value;
	}

	public function bindParams($sql, &$stmt, $params) {
		$this->lastQuery = $sql;
		$this->lastParams = array();
		if (!is_array($params)) {
			$params = array($params);
		} else if (isset($params[0]) && is_array($params[0])) {
			$params = $params[0];
		}
		if (strpos($sql, "?") !== false) {
			for ($i = 0; $i < count($params); $i++) {
				self::bindValue($stmt, $i + 1, $params[$i]);
			}
		} else {
			preg_match_all("/:\\w+/", $sql, $matches);
			foreach ($matches[0] as $match) {
				$name = substr($match, 1);
				if (isset($params[$name]) && $params[$name] !== "") {
					$value = $params[$name];
				} else {
					$value = null;
				}
				self::bindValue($stmt, $name, $value);
			}
		}
	}


	public function execute($sql, $params = array(), $checkAffectedRows = false) {
		$stmt = $this->prepare($sql);
		$this->bindParams($sql, $stmt, $params);
		if (SQL_LOG === true) {
			echo "\n$sql\n";
			if (!empty($this->lastParams)) {
				echo var_export($this->lastParams, true)."\n";
			}
		}
		$ok = $stmt->execute();
		if (!$ok) {
			throw new DBException($stmt->errorInfo()[2], $stmt->errorInfo()[1]);
		}
		if ($checkAffectedRows && $stmt->rowCount() == 0) {
			throw new DBException("No row affected");
		}
		return $stmt;
	}

	public function query($sql, ...$params) {
		return self::execute($sql, $params);
	}
	
	public function squery($sql, ...$params) {
		$stmt = self::execute($sql, $params);
		$ret = $stmt->fetch(PDO::FETCH_ASSOC);
		return $ret ? $ret : null;
	}
	
	public function equery($sql, ...$params) {
		$stmt = self::execute($sql, $params);
		$ret = $stmt->fetch(PDO::FETCH_NUM);
		return $ret ? $ret[0] : null;
	}
	
	public function aquery($sql, ...$params) {
		$stmt = self::execute($sql, $params);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function dquery($sql, ...$params) {
		$ret = [];
		$stmt = self::execute($sql, $params);
		while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$ret[$row[0]] = $row[1];
		}
		return $ret;
	}
	
	public function daquery($sql, ...$params) {
		$ret = [];
		$stmt = self::execute($sql, $params);
		while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$ret[row[0]] = array_slice($row, 1);
		}
		return $ret;
	}
}
