<?php
namespace Sova;


class Application {

	protected $baseUrl = "/";
	protected $routes = [];


	public static function parseUrl(string $url): array {
		if ($qmpos = strpos($url, "?")) {
			$url = substr($url, 0, $qmpos);
		}
		$url = trim($url, "/");
		return empty($url) ? [] : explode("/", $url);
	}
	
	public function setBaseUrl(string $baseUrl) {
		$this->baseUrl = $baseUrl;
		return $this;
	}

	public function addRoute($route, $controller) {
		$this->routes[] = [self::parseUrl($route), $controller];
		return $this;
	}


	public function run() {
		set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext) {
			throw new PHPException($errstr, $errno, $errfile, $errline, $errcontext);
		});
		
		try {
			list($controllerClass, $request) = $this->buildRequest($_GET, $_SERVER);
			$response = (new $controllerClass())->process($request);
		} 
		catch (HttpException $e) {
			$response = new Response($e->getCode(), $e->getMessage());
		} 
		catch (PHPException $e) {
			$response = new Response(
				500,
				"<html><head><title>Error</title></head><body><h1>Error</h1><p><b>File: {$e->file}<br>Line: {$e->line}</b><p><p>".$e->getMessage()."</p></body></html>"
			);
		}
		catch (\Throwable $e) {
			$response = new Response(
				500,
				"<html><head><title>Error</title></head><body><h1>Error</h1><p>".$e->getMessage()."</p><h2>Stack trace</h2><pre>".$e->getTraceAsString().".</pre></body></html>"
			);
		}

		if (DEVELOPMENT) {
			$this->addPerformanceStats($response);
		}
		$response->send();
	}


	public function buildRequest(array $getVars, array $serverVars) {
		$url = self::parseUrl($serverVars["REQUEST_URI"]);
		$method = $serverVars["REQUEST_METHOD"];
		$queryParams = $getVars;
	
		if ($method == "GET") {
			$data = [];
		} else if (substr($serverVars["CONTENT_TYPE"], 0, 33) == "application/x-www-form-urlencoded") {
			parse_str($this->getRequestData(), $data);
		} else if (substr($serverVars["CONTENT_TYPE"], 0, 16) == "application/json") {
			$data = json_decode($this->getRequestData(), true);
		} else {
			throw new HttpException(400, "Unsupported content type: ".$serverVars["CONTENT_TYPE"]);
		}

		$baseUrl = self::parseUrl($this->baseUrl);
		$basePart = array_slice($url, 0, count($baseUrl));
		if ($basePart != $baseUrl) {
			throw new HttpException(404, "The requested URL doesn't match the base {$this->baseUrl}");
		}

		$routePart = array_slice($url, count($baseUrl));
		foreach ($this->routes as list($route, $controller)) {
			$routeLen = count($route);
			if ($route == array_slice($routePart, 0, $routeLen)) {
				return [$controller, new Request($method, array_slice($routePart, $routeLen), $queryParams, $data)];
			}
		}
		throw new HttpException(404, "No route for url: ".$serverVars["REQUEST_URI"]);
	}

	public function getRequestData() {
		return file_get_contents("php://input");
	}


	public function addPerformanceStats(Response &$response) {
		$execTime = (microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]) * 1000;
		$response->addHeader("X-Execution-Time", number_format($execTime, 3)." ms");
		$memUsage = memory_get_usage();
		if ($memUsage > 1000000) {
			$memUsageFormatted = number_format($memUsage / 1000000, 1)." MB";
		} else if ($memUsage > 1000) {
			$memUsageFormatted = number_format($memUsage / 1000, 1)." kB";
		} else {
			$memUsageFormatted = "$memUsage B";
		}
		$response->addHeader("X-Memory-Usage", $memUsageFormatted);
	}
}
