<p id="hintcount"><?php echo $hintStatus ?></p>

<p id="flash"><?php if (isset($response)) echo $response; ?></p>

<?php if ($gameState == \Sova\Model\Game::CURRENT) { ?>

<form method="POST" action="applyhint">
  <input type="hidden" name="cipher" value="<?php echo $cipher ?>">
  <p>
    <input type="submit" name="for" value="Koupit za body">
    <input type="submit" name="for" value="Koupit za céčka">
  </p>
</form>

<?php } else if ($gameState == \Sova\Model\Game::FUTURE) { ?>
<p>Hra ještě nezačala.</p>
<?php } else if ($gameState == \Sova\Model\Game::PAST) { ?>
<p>Hra již skončila.</p>
<?php } ?>

