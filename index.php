<?php
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/config.php";

use Sova\Application;

if (DEVELOPMENT) {
	error_reporting(E_ALL | E_STRICT);
	ini_set('display_errors', 1);
}

session_set_cookie_params(3600*24*3);
session_start();

$app = new Application();
$app->setBaseUrl(BASE_PATH);

$app->addRoute("/admin/stat", \Sova\Controller\StatController::class)
	->addRoute("/admin", \Sova\Controller\AdminController::class, true)
    ->addRoute("/api", \Sova\Controller\RestController::class)
    ->addRoute("/", \Sova\Controller\MainController::class);

$app->run();
