<?php
namespace Sova\App;

use PHPUnit\Framework\TestCase;
use Mockery;
use Sova\Application;
use Sova\Request;
use Sova\HttpException;


class ApplicationTest extends TestCase {

	function testParseUrl() {
		$this->assertEquals(["sova"], Application::parseUrl("/sova"));
		$this->assertEquals(["sova"], Application::parseUrl("/sova/"));
		$this->assertEquals(["sova"], Application::parseUrl("/sova?a=1&b=2"));
		$this->assertEquals(["sova", "admin"], Application::parseUrl("/sova/admin"));
		$this->assertEquals(["sova", "admin"], Application::parseUrl("/sova/admin/"));
		$this->assertEquals([], Application::parseUrl("/"));
		$this->assertEquals([], Application::parseUrl(""));
	}

	function testBuildRequest() {
		$app = new Application();
		$req = $app->buildRequest([], ["REQUEST_METHOD" => "DELETE", "REQUEST_URI" => "/", "CONTENT_TYPE" => "application/json"]);
		$this->assertEquals(new Request("DELETE", "/", [], null), $req);
	}

	function testBuildRequestDataUrlencoded() {
		$app = Mockery::mock("\\Sova\\Application")->makePartial();
		$app->shouldReceive("getRequestData")->andReturn("a=1&b=2");
		
		$req = $app->buildRequest([], ["REQUEST_METHOD" => "POST", "REQUEST_URI" => "/", "CONTENT_TYPE" => "application/x-www-form-urlencoded"]);
		$this->assertEquals(new Request("POST", "/", [], ["a" => 1, "b" => 2]), $req);
		
		try {
			$app->buildRequest([], ["REQUEST_METHOD" => "POST", "REQUEST_URI" => "/", "CONTENT_TYPE" => "text/xml"]);
			$this->fail("Should throw HttpException");
		} catch (HttpException $e) {
			$this->assertEquals(400, $e->getCode());
		}
	}

	function testBuildRequestDataJson() {
		$app = Mockery::mock("\\Sova\\Application")->makePartial();
		$app->shouldReceive("getRequestData")->andReturn('{"c": 3, "d": 4}');
		
		$req = $app->buildRequest([], ["REQUEST_METHOD" => "PUT", "REQUEST_URI" => "/", "CONTENT_TYPE" => "application/json"]);
		$this->assertEquals(new Request("PUT", "/", [], ["c" => 3, "d" => 4]), $req);
	}
}
