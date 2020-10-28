<?php
namespace Sova;

class Redirect {

	protected $url;

	public function __construct($uri) {
		$this->uri = $uri;
	}

	public function buildResponse() {
		return (new Response(301, null))->addHeader("Location", $this->uri);
	}
}
