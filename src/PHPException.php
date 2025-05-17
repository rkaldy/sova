<?php
namespace Sova;

class PHPException extends \Exception {
	
	public string $file;
	public int $line;
	public $context;
	
	public function __construct($message, $code, $file, $line, $context) {
		parent::__construct($message, $code);
		$this->file = $file;
		$this->line = $line;
		$this->context = $context;
	}
}
