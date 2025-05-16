<?php
namespace Sova;

class Redirect {

	protected $uri;

	public function __construct($uri, $flash = null) {
		$this->uri = $uri;
		$_SESSION["flash"] = $flash;
	}

	public function buildResponse() {
		return (new Response(301, null))->addHeader("Location", $this->uri);
	}
}
