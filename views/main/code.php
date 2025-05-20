<p id="flash"><?php echo join("<br>", $response) ?></p>

<h3>Zadejte kód</h3>
<form method="POST" action="code">
  <input type="text" name="code" class="focused">
  <input type="submit" value="Odeslat">
</form>
