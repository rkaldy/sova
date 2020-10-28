<?php
namespace Sova;

class Response {

	public $status;
	public $headers = ["Content-Type" => "text/html; charset=utf-8"];
	public $data;

	public function __construct(int $status, ?string $data) {
		$this->status = $status;
		$this->data = $data;
	}

	public function addHeader($name, $value) {
		$this->headers[$name] = $value;
		return $this;
	}

	public function send() {
		http_response_code($this->status);
		foreach ($this->headers as $name => $value) {
			header("$name: $value");
		}
		echo $this->data;
	}
}
