<?php
if (isset($team)) {
	$menu = [
		"code" => "Zadej kód",
		"applyhint" => "Céčka",
		"ciphers" => "Přehled šifer",
		"messages" => "Historie zpráv",
		"settings" => "Nastavení"
	];
	if ($showRank) {
		$menu["rank"] = "Pořadí";
	}
	$menu["logout"] = "Logout";
}
?>
<!doctype html>
<html lang="cs">
  <head>
    <title>Sova 2.0</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="static/sova.css">
    <link rel="stylesheet" href="static/mobile.css">
	<link rel="icon" href="static/favicon.png" sizes="32x32" type="image/png">
  </head>
  <body>
    <script src="static/date.format.js"></script>
    <script src="static/notif.js"></script>

    <header>
      <div id="logo">
        <img src="static/owl.png">
      </div>
	  <div id="menubtn" <?php if (!isset($team)) { echo 'style="display:none"'; } ?>>
        <img src="static/menu.png">
      </div>
      <div id="title">
        <h1>
<?php 
if (isset($team)) {
    echo '<span id="version">'.$game.'</span>';
} else {
    echo 'SOVA <span id="version">2.0</span>';
}
?>
        </h1>
        <div id="user">
<?php 
if (isset($team)) {
    echo $team;
} else {
    echo '<small>nepřihlášený</small>';
}
?>
        </div>
      </div>
      <div id="time">
        <small>Čas vytvoření stránky</small>
<?php
$now = new DateTime('now');
echo $now->format('j.n.Y H:i:s');
?>
      </div>
      <div class="clear"></div>
    </header>

<?php
if (isset($team)) {
    echo '<nav id="nav">';
    foreach ($menu as $act => $label) {
        echo "<a";
        if ($act == $action) {
            echo ' class="selected"';
        }
        echo " href=\"$act\">$label</a>";
    }
    echo "</nav>";
} 
?>

    <div id="contents">
<?php echo $_contents; ?>
	</div>

	<div id="notif-window" onclick="closeNotifWindow(this)">
      <div id="notif-title">Nová zpráva</div>
      <div id="notif-text"></div>
	</div>

    <script src="static/sova.js"></script>
  </body>
</html>
