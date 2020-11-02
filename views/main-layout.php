<?php
$menu = [
	"code" => "Zadej kód",
	"applyhint" => "Použít nápovědu",
	"messages" => "Historie zpráv",
	"rank" => "Pořadí",
	"logout" => "Logout"
];
?>
<!doctype html>
<html lang="cs">
  <head>
    <title>Sova 2.0</title>
    <link rel="stylesheet" href="static/sova.css">
    <link rel="icon" href="static/favicon.png" sizes="32x32" type="image/png">
  </head>
  <body>

    <header>
      <div id="logo">
        <img src="static/owl.png" height="50">
      </div>
      <div id="title">
        <h1>SOVA <span id="version">2.0</span></h1>
        <div id="user">
<?php 
if (isset($team)) {
    echo "tým: $team";
} else {
    echo '<small>nepřihlášený</small>';
}
?>
        </div>
      </div>
      <div id="time">
        <small>Čas vytvoření stránky</small><br>
<?php
$now = new DateTime('now');
echo $now->format('j.n.Y H:i:s');
?>
      </div>
      <div class="clear"></div>
    </header>

<?php
if (isset($team)) {
	echo "<nav>";
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
<?php echo $contents; ?>
    </div>

    <script type="text/javascript">
	  elems = document.getElementsByClassName("focused");
	  if (elems.length != 0) {
		  elems[0].focus();
	  }
    </script>
  </body>
</html>
