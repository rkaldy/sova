<p id="hintcount">Aktuálně máte <?php echo $points ?> bodů a <?php echo $ccodes ?> nevyužitých céček.</p>

<p id="flash"><?php if (isset($response)) echo $response; ?></p>

<form method="POST" action="applyhint">
  <input type="hidden" name="cipher" value="<?php echo $cipher ?>">
  <p>
    <input type="submit" value="Zažádat">
  </p>
</form>
