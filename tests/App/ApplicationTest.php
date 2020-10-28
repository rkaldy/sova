<?php
namespace Sova\App;

use PHPUnit\Framework\TestCase;
use Mockery;
use Sova\Application;
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

	function testMethod() {
		$app = new Application();
		$app->addRoute("/", "Test");
		[$controller, $req] = $app->buildRequest([], ["REQUEST_METHOD" => "PUT", "REQUEST_URI" => "/", "CONTENT_TYPE" => "application/json"]);
		$this->assertEquals("PUT", $req->method);
	}

	function testRouting() {
		$app = new Application();
		$app->addRoute("/admin", "AdminController")
			->addRoute("/api/v2", "RestController");
		
		[$controller, $req] = $app->buildRequest([], ["REQUEST_METHOD" => "GET", "REQUEST_URI" => "/admin"]);
		$this->assertEquals("AdminController", $controller);
		$this->assertEquals([], $req->routePath);
			
		[$controller, $req] = $app->buildRequest([], ["REQUEST_METHOD" => "GET", "REQUEST_URI" => "/admin/"]);
		$this->assertEquals("AdminController", $controller);
		$this->assertEquals([], $req->routePath);
		
		[$controller, $req] = $app->buildRequest([], ["REQUEST_METHOD" => "GET", "REQUEST_URI" => "/admin/login"]);
		$this->assertEquals("AdminController", $controller);
		$this->assertEquals(["login"], $req->routePath);
		
		[$controller, $req] = $app->buildRequest([], ["REQUEST_METHOD" => "GET", "REQUEST_URI" => "/api/v2"]);
		$this->assertEquals("RestController", $controller);
		$this->assertEquals([], $req->routePath);
		
		[$controller, $req] = $app->buildRequest([], ["REQUEST_METHOD" => "GET", "REQUEST_URI" => "/api/v2/messages/1/100/"]);
		$this->assertEquals("RestController", $controller);
		$this->assertEquals(["messages", 1, 100], $req->routePath);
		
		try {
			$app->buildRequest([], ["REQUEST_METHOD" => "GET", "REQUEST_URI" => "/"]);
			$this->fail("Should throw HttpException");
		} catch (HttpException $e) {
			$this->assertEquals(404, $e->getCode());
		}
		
		try {
			$app->buildRequest([], ["REQUEST_METHOD" => "GET", "REQUEST_URI" => "/api"]);
			$this->fail("Should throw HttpException");
		} catch (HttpException $e) {
			$this->assertEquals(404, $e->getCode());
		}
	}

	function testRoutingWithEmptyRoute() {
		$app = new Application();
		$app->addRoute("/admin", "AdminController")
			->addRoute("/", "MainController");
		
		[$controller, $req] = $app->buildRequest([], ["REQUEST_METHOD" => "GET", "REQUEST_URI" => "/admin"]);
		$this->assertEquals("AdminController", $controller);
		$this->assertEquals([], $req->routePath);
			
		[$controller, $req] = $app->buildRequest([], ["REQUEST_METHOD" => "GET", "REQUEST_URI" => "/admin/login"]);
		$this->assertEquals("AdminController", $controller);
		$this->assertEquals(["login"], $req->routePath);
		
		[$controller, $req] = $app->buildRequest([], ["REQUEST_METHOD" => "GET", "REQUEST_URI" => "/"]);
		$this->assertEquals("MainController", $controller);
		$this->assertEquals([], $req->routePath);
		
		[$controller, $req] = $app->buildRequest([], ["REQUEST_METHOD" => "GET", "REQUEST_URI" => "/login"]);
		$this->assertEquals("MainController", $controller);
		$this->assertEquals(["login"], $req->routePath);
	}

	function testBaseUrl() {
		$app = new Application();
		$app->setBaseUrl("sova/")
			->addRoute("/admin", "AdminController")
			->addRoute("/", "MainController");
		
		[$controller, $req] = $app->buildRequest([], ["REQUEST_METHOD" => "GET", "REQUEST_URI" => "/sova"]);
		$this->assertEquals("MainController", $controller);
		$this->assertEquals([], $req->routePath);

		[$controller, $req] = $app->buildRequest([], ["REQUEST_METHOD" => "GET", "REQUEST_URI" => "/sova/login"]);
		$this->assertEquals("MainController", $controller);
		$this->assertEquals(["login"], $req->routePath);

		[$controller, $req] = $app->buildRequest([], ["REQUEST_METHOD" => "GET", "REQUEST_URI" => "/sova/admin/login"]);
		$this->assertEquals("AdminController", $controller);
		$this->assertEquals(["login"], $req->routePath);

		try {
			$app->buildRequest([], ["REQUEST_METHOD" => "GET", "REQUEST_URI" => "/login"]);
			$this->fail("Should throw HttpException");
		} catch (HttpException $e) {
			$this->assertEquals(404, $e->getCode());
		}
	}

	function testDataUrlencoded() {
		$app = Mockery::mock("\\Sova\\Application")->makePartial();
		$app->addRoute("/", "Test");
		$app->shouldReceive("getRequestData")->andReturn("a=1&b=2");
		
		[$controller, $req] = $app->buildRequest([], ["REQUEST_METHOD" => "GET", "REQUEST_URI" => "/"]);
		$this->assertEquals([], $req->data);

		[$controller, $req] = $app->buildRequest([], ["REQUEST_METHOD" => "POST", "REQUEST_URI" => "/", "CONTENT_TYPE" => "application/x-www-form-urlencoded"]);
		$this->assertEquals(["a" => 1, "b" => 2], $req->data);
		
		try {
			[$controller, $req] = $app->buildRequest([], ["REQUEST_METHOD" => "POST", "REQUEST_URI" => "/", "CONTENT_TYPE" => "text/xml"]);
			$this->fail("Should throw HttpException");
		} catch (HttpException $e) {
			$this->assertEquals(400, $e->getCode());
		}
	}

	function testDataJson() {
		$app = Mockery::mock("\\Sova\\Application")->makePartial();
		$app->addRoute("/", "Test");
		$app->shouldReceive("getRequestData")->andReturn('{"c": 3, "d": 4}');
		
		[$controller, $req] = $app->buildRequest([], ["REQUEST_METHOD" => "PUT", "REQUEST_URI" => "/", "CONTENT_TYPE" => "application/json"]);
		$this->assertEquals(["c" => 3, "d" => 4], $req->data);
	}
}
