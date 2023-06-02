<table>
  <thead>
    <td>Pořadí</td>
    <td>Tým</td>
	<td>Počet bodů</td>
	<td>Příchod do cíle</td>
	<td>Poslední šifra vyluštěna</td>
    <td>Imunita?</td>
  </thead>
  <tbody>
<?php for ($i = 0; $i < count($teams); $i++) { ?>
	<tr<?php if ($i % 2 == 1) echo ' class="alt-row"' ?>>
	  <th><?php echo $i+1 ?></th>
	  <td><?php echo $teams[$i]["name"] ?></td>
	  <td><?php echo $teams[$i]["points"] ?></td>
	  <td><?php echo $teams[$i]["finish_time"] ?></td>
	  <td><?php echo $teams[$i]["last_cipher_time"] ?></td>
      <td><?php echo $teams[$i]["imunity"] ? "ano" : "-" ?></td>
	</tr>
<?php } ?>
  </tbody>
</table>

<p>Pořadí se počítá podle těchto kritérií:</p>
<ol type="1">
  <li>počet bodů</li>
  <li>čas příchodu do cíle</li>
  <li>čas vyluštění poslední šifry</li>
</ol>
