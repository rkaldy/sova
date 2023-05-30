<p id="hintcount">Aktuálně máte <?php echo $points ?> bodů a <?php echo $ccodes ?> nevyužitých céček.</p>

<p id="flash"><?php if (isset($response)) echo $response; ?></p>

<?php if ($gameState == \Sova\Model\Game::CURRENT) { ?>

<form method="POST" action="applyhint">
  <input type="hidden" name="cipher" value="<?php echo $cipher ?>">
  <p>
    <input type="submit" value="Zažádat">
  </p>
</form>

<?php } else if ($gameState == \Sova\Model\Game::FUTURE) { ?>
<p>Hra ještě nezačala.</p>
<?php } else if ($gameState == \Sova\Model\Game::PAST) { ?>
<p>Hra již skončila. Vraťte se do cíle.</p>
<?php } ?>

