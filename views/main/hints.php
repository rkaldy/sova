<p id="hintcount"><?php echo $hintStatus ?></p>

<p id="flash"><?php if (isset($flash)) echo $flash; ?></p>

<?php if ($gameState == \Sova\Model\Game::CURRENT) { ?>

<form method="POST" action="checkhint">
  <p>
    <label for="cipher">Číslo šifry</label>
    <input type="text" id="cipher" name="cipher" class="focused">
    <input type="submit" value="Zkontrolovat">
  </p>
</form>

<hr>

<form method="POST" action="sellccode">
  <p>
	<legend><?php echo $sellStatus ?></legend>
    <fieldset>
	  <input type="radio" name="sign" id="sell_add" value="1" checked>
      <label for="sell_add">přičíst</label>
	  <input type="radio" name="sign" id="sell_sub" value="-1">
      <label for="sell_sub">odečíst</label>
    </fieldset>
	<input type="submit" value="Prodat">
  </p>
</form>

<?php } else if ($gameState == \Sova\Model\Game::FUTURE) { ?>
<p>Hra ještě nezačala.</p>
<?php } else if ($gameState == \Sova\Model\Game::PAST) { ?>
<p>Hra již skončila.</p>
<?php } ?>

