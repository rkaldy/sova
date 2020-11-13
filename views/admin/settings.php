<h2>Nastavení</h2>

<p id="flash"><?php if (isset($flash)) echo $flash ?></p>

<form method="POST" action="settings">
<table>
  <tr>
    <th>
      <label for="locVisitMandatory">
	    Povinné kódy místa<br>
  	    <small>Je-li zaškrtnuto, týmu musí odeslat kód stanoviště po příchodu na něj, jinak Sova nepřijme řešení šifry na tomto stanovišti.<br>
	    Není-li zaškrtnuto, tým může odeslat řešení šifry i bez odeslání kódu stanoviště, pouze jim v takovém případě Sova nepošle časovou nápovědu/řešení.</small>
	  </label>
    </th>
    <td><input type="checkbox" name="locVisitMandatory" <?php if ($locVisitMandatory) echo 'checked="checked"' ?>></td>
  </tr>
  <tr>
    <th>
      <label for="locFinish">Cílové stanoviště</label>
    </th>
    <td>
	  <select name="locFinish">
	    <option value="">(nevybráno)</option>
<?php foreach ($locs as $loc) { ?>
	    <option value="<?php echo $loc["point_id"] ?>" <?php if ($loc["point_id"] == $locFinish) echo 'selected="selected"' ?>><?php echo $loc["name"] ?></option>
<?php } ?>
      </select>
    </td>
  </tr>
  <tr>
    <th></th>
	<td><input type="submit" value="Uložit"></td>
  </tr>
</table>
</form>
