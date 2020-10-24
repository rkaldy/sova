<!doctype html>
<html lang="cs">
  <head>
    <title>Sova 2.0</title>
    <link rel="stylesheet" href="css/sova.css">
    <link rel="icon" href="css/favicon.png" sizes="32x32" type="image/png">
  </head>
  <body>

    <header>
      <div id="logo">
        <img src="css/owl.png" height="50">
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

<?php if (!isset($error)) { ?>
    <nav>
<?php   if (isset($team)) { ?>
      <a href="code">Zadej kód</a>
      <a href="messages">Seznam zpráv</a>
      <a href="rank">Pořadí</a>
      <a href="logout">Logout</a>
<?php   }  else { ?>
      <a href="login">Login</a>
<?php   } ?>
    </nav>
<?php } ?>

    <div id="contents">
<?php echo $contents; ?>
    </div>
  </body>
</html>
