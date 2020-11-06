<?php
require __DIR__ . "/../vendor/autoload.php";
require __DIR__ . "/config.php";

const DEVELOPMENT = true;
error_reporting(E_ALL | E_STRICT);

$pdo = new PDO("mysql:host=".DB_HOST, DB_USER, DB_PASS);

if (CREATE_DB) {
	echo "Creating database...";
	$pdo->exec("CREATE DATABASE ".DB_NAME);
	$pdo->exec("USE ".DB_NAME);
	$pdo->exec(file_get_contents(__DIR__."/../install/db.create.sql"));
	$pdo->exec("LOAD DATA LOCAL INFILE '".__DIR__."/../install/wordlist.txt' INTO TABLE wordlist");
	$pdo->exec("LOAD DATA LOCAL INFILE '".__DIR__."/../install/texts.txt' INTO TABLE text FIELDS TERMINATED BY ';' (code, text)");
	echo "done\n";
	
	register_shutdown_function(function() {
		$pdo = new PDO("mysql:host=".DB_HOST, DB_USER, DB_PASS);
		$pdo->exec("DROP DATABASE ".DB_NAME);
	});
} else {
	echo "Preparing database...";
	$pdo->exec("USE ".DB_NAME);
	$pdo->exec("DELETE FROM game");
	$pdo->exec("DELETE FROM user");
	echo "done\n";
}
