<?php
if ($superuser) {
	$menu = array(
		"games" => "Hry",
		"users" => "Uživatelé",
		"logout" => "Logout"
	);
} else if (isset($game)) {
	$menu = array(
		"locs" => "Stanoviště",
		"ciphers" => "Šifry",
		"hints" => "Nápovědy",
		"graph" => "Schéma",
		"teams" => "Týmy",
		"progress" => "Postup týmů",
		"rank" => "Pořadí",
		"messages" => "Zprávy",
		"broadcast" => "Zpráva týmům",
		"logout" => "Logout"
	);
}
?>
<!doctype html>
<html lang="cs">
  <head>
    <title>Sova 2.0 | admin</title>
    <link rel="stylesheet" href="../js/datetimepicker/jquery.datetimepicker<?php echo $minify?>.css">
    <link rel="stylesheet" href="../js/selectpure/selectpure.css">
    <link rel="stylesheet" href="../js/jsgrid/jsgrid<?php echo $minify?>.css">
    <link rel="stylesheet" href="../js/jsgrid/jsgrid-theme<?php echo $minify?>.css">
    <link rel="stylesheet" href="../css/sova.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="icon" href="../css/favicon.png" sizes="32x32" type="image/png">
  </head>
  <body>
    <script src="../js/jquery-3.5.1<?php echo $minify?>.js"></script>
    <script src="../js/datetimepicker/jquery.datetimepicker.full<?php echo $minify?>.js"></script>
	<script src="../js/selectpure/selectpure<?php echo $minify?>.js"></script>
    <script src="../js/jsgrid/jsgrid<?php echo $minify?>.js"></script>
    <script src="../js/jsgrid/jsgrid.custom.js"></script>
<?php if ($action == "graph") { ?>
	<script src="../js/visjs/vis-network<?php echo $minify ?>.js"></script>
<?php } ?>
    
    <header>
      <a href=".">
        <div id="logo">
          <img src="../css/owl.png" height="30">
        </div>
      </a>
      <div id="title">
        <h1>SOVA <span id="version">2.0</span> admin</h1>
      </div>
      <div id="user">
        <?php echo isset($user) ? "uživatel: <b>$user</b>" : "nepřihlášený" ?><br>
        <?php echo isset($game) ? "hra: <b>$game</b>" : "" ?>
      </div>
      <div class="clear"></div>
    </header>

<?php 
if ($superuser || isset($game)) { 
	echo "<nav>";
	foreach ($menu as $act => $label) {
		echo "<a";
		if ($act == $action) {
			echo " class=\"selected\"";
		}
		echo " href=\"$act\">$label</a>";
	}
	echo "</nav>";
}
?>

    <div id="contents">
<?php echo $contents ?>
    </div>
  </body>
</html>
