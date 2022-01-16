<h2>Nastavení</h2>

<p id="flash"><?php if (isset($flash)) echo $flash ?></p>

<form method="POST" action="settings">
<table>
  <tr>
    <th>
      <label for="gameStart">
		Začátek hry<br>
        <small>Čas od kdy Sova začne přijímat kódy stanovišť a šifer</small>
      </label>
    </th>
	<td><input type="text" id="gameStart" name="gameStart" value="<?php echo $gameStart ?>"></td>
  </tr>
  <tr>
    <th>
      <label for="gameEnd">
		Konec hry<br>
        <small>Čas do kdy Sova bude přijímat kódy stanovišť a šifer</small>
      </label>
    </th>
	<td><input type="text" id="gameEnd" name="gameEnd" value="<?php echo $gameEnd ?>"></td>
  </tr>
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
      <label for="showRank">
	    Zobrazovat pořadí<br>
  	    <small>Je-li zaškrtnuto, Sova po odeslání kódu šifry či stanoviště pošle zpátky i pořadí týmu na daném místě a celkově.</small>
	  </label>
    </th>
    <td><input type="checkbox" name="showRank" <?php if ($showRank) echo 'checked="checked"' ?>></td>
  </tr>
  <tr>
    <th>
      <label for="deleteParallelHints">
	    Rušit časové nápovědy na paralelních šifrách?<br>
  	    <small>Vyřešíte-li šifru, zruší se tím časové nápovědy na všech paralelních šifrách (tj. šifrách vedoucích na stejné stanoviště jako právě vyluštěná šifra)</small>
	  </label>
    </th>
    <td><input type="checkbox" name="deleteParallelHints" <?php if ($deleteParallelHints) echo 'checked="checked"' ?>></td>
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
    <th>
	  <label for="linkMapyCz">
		Odkaz na mapy.cz<br>
        <small>Má-li stanoviště zadané souřadnice, zobrazí se týmům jako odkaz na mapy.cz</small>
      </label>
    </th>
    <td>
      <select name="linkMapyCz">
        <option value="">-</option>
        <option value="turisticka">Turistická</option>
        <option value="zimni">Zimní</option>
      </select>
    </td>
  </tr>
  <tr>
    <th></th>
	<td><input type="submit" value="Uložit"></td>
  </tr>
</table>
</form>

<script type="text/javascript">
  jQuery("#gameStart").datetimepicker({"format": "Y-m-d H:i:s"});
  jQuery("#gameEnd").datetimepicker({"format": "Y-m-d H:i:s"});
</script>
