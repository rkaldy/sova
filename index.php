<?php
use Slim\Factory\AppFactory;
use Sova\TrailingSlashMiddleware;
use Sova\Controller\MainController;
use Sova\Controller\AdminController;
use Sova\Controller\RestController;

require __DIR__ . "/vendor/autoload.php";
require "config.php";

if (DEVELOPMENT) {
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
}

session_start();

$app = AppFactory::create();
$app->setBasePath(BASE_PATH);

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
if (DEVELOPMENT) {
	$app->addErrorMiddleware(true, true, true);
}
$app->add(new TrailingSlashMiddleware());

$app->map(["GET", "POST"], "/admin/{action}",  AdminController::class);
$app->get("/admin/", AdminController::class);
$app->map(["GET", "POST", "PUT", "DELETE"], "/api/{resource}[/{page}[/{pageSize}]]", RestController::class);
$app->map(["GET", "POST"], "/{action}", MainController::class);
$app->get("/", MainController::class);

$app->run();
