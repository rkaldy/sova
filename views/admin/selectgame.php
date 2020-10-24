<h2>Vyber hru</h2>

<form method="POST" action="login">
  <select name="game_id">
<?php foreach ($games as $g) { ?>
    <option value="<?php echo $g['game_id'] ?>"><?php echo $g['name'] ?></option>
<?php } ?>
  </select>
  <input type="submit" value="Vybrat" />
</form>
