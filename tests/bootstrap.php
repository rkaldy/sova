<?php
require __DIR__ . "/../vendor/autoload.php";
require __DIR__ . "/config.php";
require __DIR__ . "/../install/load.php";

error_reporting(E_ALL | E_STRICT);
date_default_timezone_set("Europe/Prague");

$pdo = new PDO("mysql:host=".DB_HOST, DB_USER, DB_PASS);

echo "Preparing database...";
$pdo->exec("USE ".DB_NAME);
$pdo->exec("DELETE FROM game");
echo "done\n";
