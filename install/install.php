<?php
require "../config.php";

function progress($msg) {
	echo "<p>$msg</p>";
	flush();
	ob_flush();
}

if (DEVELOPMENT) {
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
}

try {
	$pdo = new PDO("mysql:host=".DB_HOST, DB_USER, DB_PASS);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$pdo->exec("USE ".DB_NAME);
	$flash = "Upozornění: Databáze '".DB_NAME."' již existuje. Budete-li pokračovat v instalaci, veškerá data budou smazána.";
} catch (PDOException $e) {
	if ($e->getCode() != 42000) {
		$error = "Nelze se připojit k databázi. Upravte nejprve přihlašovací údaje v souboru <samp>config.php</samp>.";
		$errorDesc = $e->getMessage();
	}
}
?>
<!doctype html>
<html lang="cs">
  <head>
	<meta charset="UTF-8">
    <title>Sova 2.0 | installer</title>
    <link rel="stylesheet" href="../css/sova.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="icon" href="../css/favicon.png" sizes="32x32" type="image/png">
  </head>
  <body>
    <header>
      <a href=".">
        <div id="logo">
          <img src="../css/owl.png" height="30">
        </div>
      </a>
      <div id="title">
        <h1>SOVA <span id="version">2.0</span> <span id="section">installer</span></h1>
      </div>
      <div class="clear"></div>
    </header>

    <div id="contents">
<?php
if (isset($error)) {
	echo "<p>$error</p><pre>$errorDesc</pre>";
}
else { 
	if (empty($_POST["db_root_login"]) || empty($_POST["db_root_pswd"]) || empty($_POST["su_login"]) || empty($_POST["su_pswd"])) {
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$flash = "Vyplňte všechny údaje.";
		}
		include("form.php");
	} else {
		try {
			$pdo = new PDO("mysql:host=".DB_HOST, $_POST["db_root_login"], $_POST["db_root_pswd"]);
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			progress("Vytvářím databázi...");
			$pdo->exec("CREATE DATABASE IF NOT EXISTS ".DB_NAME);
			$pdo->exec("USE ".DB_NAME);
			progress("Vytvářím tabulky...");
			$pdo->exec(file_get_contents(__DIR__."/db.create.sql"));
			progress("Vytvářím superuživatele...");
			$stmt = $pdo->prepare("INSERT INTO user (user_id, login, pswd) VALUES (?, ?, ?)");
			$stmt->execute(array(1, $_POST["su_login"], password_hash($_POST["su_pswd"], PASSWORD_BCRYPT)));
?>
	<p>Hotovo. Přejděte na <a href="..">hlavní stránku</a> nebo do <a href="../admin">administrace</a>.</p>
<?php
		} catch (PDOException $e) {
			if ($e->getCode() == 1045) {
				$flash = "Chybný login nebo heslo do databáze.";
				include("form.php");
			} else {
				echo "<p><b>Chyba:</b></p><pre>".$e->getMessage()."</pre><p><a href=\".\">Zpět</a>";
			}
		}
	}
}
?>
    </div>
  </body>
</html>
