<p id="flash"><?php echo $msg ?></p>

<form method="POST" action="solve">
  <table>
    <tr>
      <td><label for="ciphers">Šifra/aktivita</label></td>
      <td>
        <select name="cipherId" id="ciphers">
          <?php foreach ($ciphers as $cipher) { ?>
            <option value="<?php echo $cipher["point_id"] ?>" <?php if ($cipher["point_id"] == $lastCipherSelected) echo 'selected="selected"' ?>><?php echo $cipher["name"] ?></option>
          <?php } ?>
        </select>
      </td>
    </tr>
    <tr>
      <td><label for="teams">Tým</label></td>
      <td>
        <select name="teamId" id="teams">
          <?php foreach ($teams as $team) { ?>
            <option value="<?php echo $team["team_id"] ?>"><?php echo $team["name"] ?></option>
          <?php } ?>
        </select>
      </td>
    </tr>
    <tr>
      <td><input type="submit" value="Zapsat"></td>
      <td></td>
    </tr>
  </table>
</form>
