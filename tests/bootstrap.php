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
	echo "done\n";
	
	register_shutdown_function(function() {
		$pdo = new PDO("mysql:host=".DB_HOST, DB_USER, DB_PASS);
		$pdo->exec("DROP DATABASE ".DB_NAME);
	});
} else {
	echo "Preparing database...";
	$pdo->exec("USE ".DB_NAME);
	foreach ($pdo->query("SHOW TABLES") as $table) {
		if ($table[0] != 'wordlist') {
			$pdo->exec("DELETE FROM {$table[0]}");
		}
	}
	echo "done\n";
}
