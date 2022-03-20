<?php if ($gameState == \Sova\Model\Game::CURRENT) { ?>

<p id="response"><?php if (isset($response)) echo $response; ?></p>

<p id="hintcount">Aktuálně máte <?php echo $points ?> bodů a <?php echo $hintCount ?> nevyužitých céček.</p>

<form method="POST" action="applyhint">
  <p>
    <label for="cipher">Číslo šifry</label>
	<input type="text" id="cipher" name="cipher" class="focused">
    <fieldset>
      <legend>Typ nápovědy</legend>
      <input type="radio" name="type" id="type1" value="1">
      <label for="type1">Standardní (1 céčko nebo 12 bodů)</label><br>
      <input type="radio" name="type" id="type2" value="1">
      <label for="type2">Postup (24 bodů)</label><br>
      <input type="radio" name="type" id="type3" value="1">
	  <label for="type3">Řešení (30 bodů)</label>
    </fieldset>
    <input type="submit" value="Odeslat">
  </p>
</form>

<?php } else if ($gameState == \Sova\Model\Game::FUTURE) { ?>
<p>Hra ještě nezačala.</p>
<?php } else if ($gameState == \Sova\Model\Game::PAST) { ?>
<p>Hra již skončila.</p>
<?php } ?>

