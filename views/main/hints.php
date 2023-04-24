<p id="hintcount">Aktuálně máte <?php echo $points ?> bodů a <?php echo $ccodes ?> nevyužitých céček.</p>

<p id="flash"><?php if (isset($flash)) echo $flash; ?></p>

<?php if ($gameState == \Sova\Model\Game::CURRENT) { ?>

<form method="POST" action="checkhint">
  <p>
    <label for="cipher">Číslo šifry</label>
    <input type="text" id="cipher" name="cipher" class="focused">
    <input type="submit" value="Zkontrolovat">
  </p>
</form>

<?php } else if ($gameState == \Sova\Model\Game::FUTURE) { ?>
<p>Hra ještě nezačala.</p>
<?php } else if ($gameState == \Sova\Model\Game::PAST) { ?>
<p>Hra již skončila.</p>
<?php } ?>

