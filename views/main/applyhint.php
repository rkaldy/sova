<?php if ($gameState == \Sova\Model\Game::CURRENT) { ?>

<p id="response"><?php if (isset($response)) echo $response; ?></p>

<p id="hintcount">Aktuálně máte <?php echo $hintCount ?> nevyužitých nápověd.</p>

<h3>Zadejte číslo šifry, ke které chcete nápovědu</h3>

<form method="POST" action="applyhint">
  <p>
    <input type="text" name="cipher" class="focused">
    <input type="submit" value="Odeslat">
  </p>
</form>

<?php } else if ($gameState == \Sova\Model\Game::FUTURE) { ?>
<p>Hra ještě nezačala.</p>
<?php } else if ($gameState == \Sova\Model\Game::PAST) { ?>
<p>Hra již skončila.</p>
<?php } ?>

