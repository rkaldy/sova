<?php
namespace Sova;

class Redirect {

	protected $url;

	public function __construct($uri) {
		$this->uri = $uri;
	}

	public function buildResponse($resp) {
		return $resp->withHeader("Location", $this->uri)->withStatus(301);
	}
}
