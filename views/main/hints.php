<p id="hintcount">Aktuálně máte <?php echo $points ?> bodů<?php if ($ccodes > 0) { echo " a $ccodes nevyužitých céček"; } ?>.</p>

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

<?php if ($deductPoints) { ?>

<p>Máte-li strach, že byste mohli vyhrát, kdykoliv během hry si můžete, i opakovaně, odečíst libovolný počet bodů.</p>
<form method="POST" action="deduct">
  <label for="points">Počet bodů k odečtení</label>
  <input type="ext" id="points" name="points">
  <input type="submit" value="Odečíst">
</form>

<?php } ?>
