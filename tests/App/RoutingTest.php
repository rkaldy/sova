<?php
namespace Sova\App;

use PHPUnit\Framework\TestCase;
use Mockery;
use Sova\Application;
use Sova\Request;
use Sova\Response;
use Sova\HttpException;


class RoutingTest extends TestCase {

	protected $app;
	protected $controller, $path;

	function setUp(): void {
		$this->app = Mockery::mock("\\Sova\\Application")->makePartial();
		$this->app->shouldReceive("runController")->with(Mockery::capture($this->controller), Mockery::any(), Mockery::capture($this->path));
	}

	function testRouting() {
		$this->app->addRoute("/admin", "AdminController")
			       ->addRoute("/api/v2", "RestController");

		$this->app->route(new Request("GET", "/admin/", null, null));
		$this->assertEquals("AdminController", $this->controller);
		$this->assertEquals([], $this->path);
		
		$this->app->route(new Request("GET", "/admin/login", null, null));
		$this->assertEquals("AdminController", $this->controller);
		$this->assertEquals(["login"], $this->path);
		
		$this->app->route(new Request("GET", "/api/v2/", null, null));
		$this->assertEquals("RestController", $this->controller);
		$this->assertEquals([], $this->path);
		
		$this->app->route(new Request("GET", "/api/v2/messages/1/100", null, null));
		$this->assertEquals("RestController", $this->controller);
		$this->assertEquals(["messages", 1, 100], $this->path);
		
		try {
			$this->app->route(new Request("GET", "/", null, null));
			$this->fail("Should throw HttpException");
		} catch (HttpException $e) {
			$this->assertEquals(404, $e->getCode());
		}
	}

	function testRoutingWithEmptyRoute() {
		$this->app->addRoute("/admin", "AdminController")
			      ->addRoute("/", "MainController");
		
		$this->app->route(new Request("GET", "/admin/", null, null));
		$this->assertEquals("AdminController", $this->controller);
		$this->assertEquals([], $this->path);
			
		$this->app->route(new Request("GET", "/admin/login", null, null));
		$this->assertEquals("AdminController", $this->controller);
		$this->assertEquals(["login"], $this->path);
		
		$this->app->route(new Request("GET", "/", null, null));
		$this->assertEquals("MainController", $this->controller);
		$this->assertEquals([], $this->path);
		
		$this->app->route(new Request("GET", "/login", null, null));
		$this->assertEquals("MainController", $this->controller);
		$this->assertEquals(["login"], $this->path);
	}

	function testBaseUrl() {
		$this->app->setBaseUrl("/sova")
			      ->addRoute("/admin", "AdminController")
			      ->addRoute("/", "MainController");
		
		$this->app->route(new Request("GET", "/sova/", null, null));
		$this->assertEquals("MainController", $this->controller);
		$this->assertEquals([], $this->path);

		$this->app->route(new Request("GET", "/sova/login", null, null));
		$this->assertEquals("MainController", $this->controller);
		$this->assertEquals(["login"], $this->path);

		$this->app->route(new Request("GET", "/sova/admin/login", null, null));
		$this->assertEquals("AdminController", $this->controller);
		$this->assertEquals(["login"], $this->path);

		try {
			$this->app->route(new Request("GET", "/login", null, null));
			$this->fail("Should throw HttpException");
		} catch (HttpException $e) {
			$this->assertEquals(404, $e->getCode());
		}
	}

	function testTrailingSlash() {
		$this->app->setBaseUrl("/sova")
			      ->addRoute("/admin", "AdminController", true)
			      ->addRoute("/", "MainController");

		$resp = $this->app->route(new Request("GET", "/sova/login", null, null));
		$this->assertEquals("MainController", $this->controller);
		$this->assertEquals(["login"], $this->path);

		$resp = $this->app->route(new Request("GET", "/sova/admin", null, null));
		$this->assertEquals(301, $resp->status);
		$this->assertEquals("/sova/admin/", $resp->headers["Location"]);

		$resp = $this->app->route(new Request("GET", "/sova/admin/login", null, null));
		$this->assertEquals("AdminController", $this->controller);
		$this->assertEquals(["login"], $this->path);
	}
}
