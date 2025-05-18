<p id="hintcount">Aktuálně máte <?php echo $points ?> bodů
<?php if ($ccodes) { echo "a $ccodes nevyužitých céček"; } ?>
.</p>

<p id="flash"><?php if (isset($flash)) echo $flash; ?></p>

<form method="POST" action="checkhint">
  <p>
    <label for="cipher">Číslo šifry</label>
    <input type="text" id="cipher" name="cipher" class="focused">
    <input type="submit" value="Zkontrolovat">
  </p>
</form>

<p><?php echo $imunityMsg ?></p>
<?php if ($imunityAvailable) { ?>
<form method="POST" action="imunity">
  <input type="submit" value="Koupit imunitu">
</form>
<?php } ?>

