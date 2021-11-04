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
		"unihints" => "Univerzální nápovědy",
		"graph" => "Schéma",
		"texts" => "Texty",
		"teams" => "Týmy",
		"progress" => "Postup týmů",
		"rank" => "Pořadí",
		"messages" => "Zprávy",
		"broadcast" => "Zpráva týmům",
		"settings" => "Nastavení",
		"logout" => "Logout"
	);
}
?>
<!doctype html>
<html lang="cs">
  <head>
    <title>Sova 2.0 | admin</title>
<?php if (DEVELOPMENT) { ?>
    <link rel="stylesheet" href="../node_modules/jquery-datetimepicker/jquery.datetimepicker.css">
    <link rel="stylesheet" href="../node_modules/jsgrid/dist/jsgrid.css">
<?php } else { ?>
    <link rel="stylesheet" href="https://unpkg.com/jquery-datetimepicker@2.5.21/build/jquery.datetimepicker.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jsgrid/1.5.3/jsgrid.min.css">
<?php } ?>
    <link rel="stylesheet" href="../static/selectpure.css">
    <link rel="stylesheet" href="../static/sova.css">
    <link rel="stylesheet" href="../static/admin.css">
    <link rel="icon" href="../static/favicon.png" sizes="32x32" type="image/png">
  </head>
  <body>

<?php if (DEVELOPMENT) { ?>
    <script src="../node_modules/jquery/dist/jquery.js"></script>
    <script src="../node_modules/jquery-datetimepicker/build/jquery.datetimepicker.full.js"></script>
	<script src="../node_modules/select-pure/dist/bundle.min.js"></script>
    <script src="../node_modules/jsgrid/dist/jsgrid.js"></script>
  <?php if ($action == "graph") { ?>
	<script src="../node_modules/vis-network/dist/vis-network.js"></script>
  <?php } ?>
<?php } else { ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.min.js"></script>
	<script src="https://unpkg.com/select-pure@0.6.1/dist/bundle.min.js"></script>
    <script src="../static/jsgrid.min.js"></script>
  <?php if ($action == "graph") { ?>
	<script src="https://unpkg.com/vis-network@8.5.2/dist/vis-network.min.js"></script>
  <?php } ?>
<?php } ?>
    <script src="../static/jsgrid.custom.js"></script>
    
    <header>
      <a href=".">
        <div id="logo">
          <img src="../static/owl.png" height="30">
        </div>
      </a>
      <div id="title">
        <h1>SOVA <span id="version">2.0</span> <span id="section">admin</span></h1>
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
<?php echo $_contents ?>
    </div>
  </body>
</html>
