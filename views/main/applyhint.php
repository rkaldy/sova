<p id="flash"><?php if (isset($response)) echo $response; ?></p>

<form method="POST" action="applyhint">
  <input type="hidden" name="cipher" value="<?php echo $cipher ?>">
  <p>
    <input type="submit" value="Zažádat">
  </p>
</form>
