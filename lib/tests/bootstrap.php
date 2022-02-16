<?php
require __DIR__ . "/../../vendor/autoload.php";
require __DIR__ . "/config.php";
require __DIR__ . "/../../install/load.php";

const DEVELOPMENT = true;
error_reporting(E_ALL | E_STRICT);
date_default_timezone_set("Europe/Prague");

$pdo = new PDO("mysql:host=".DB_HOST, DB_USER, DB_PASS);

if (CREATE_DB) {
	echo "Creating database...";
	$pdo->exec("DROP DATABASE IF EXISTS ".DB_NAME);
	$pdo->exec("CREATE DATABASE ".DB_NAME);
	$pdo->exec("USE ".DB_NAME);
	$pdo->exec(file_get_contents(__DIR__."/../../install/db.create.sql"));
	loadInfile($pdo, __DIR__."/../../install/wordlist.txt", "wordlist", ["word"]);
	loadInfile($pdo, __DIR__."/../../install/texts.txt", "text", ["code", "text"]);
	echo "done\n";
} else {
	echo "Preparing database...";
	$pdo->exec("USE ".DB_NAME);
	$pdo->exec("DELETE FROM game");
	$pdo->exec("DELETE FROM user");
	echo "done\n";
}
