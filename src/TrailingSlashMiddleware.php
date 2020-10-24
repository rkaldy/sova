<?php
namespace Sova;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class TrailingSlashMiddleware {

	public function __invoke(Request $request, RequestHandler $handler): Response {
		$path = $request->getUri()->getPath();
		if (substr($path, -6) == '/admin') {
			$uri = $request->getUri()->withPath($path."/");
			$response = new Response();
			return $response->withHeader("Location", (string)$uri)->withStatus(301);
		}
		return $handler->handle($request);
	}
}
