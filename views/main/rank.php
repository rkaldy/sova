<table>
  <thead>
    <td>Tým</td>
	<td>Vyluštěno šifer</td>
	<td>Poslední šifra vyluštěna</td>
	<td>Na stanovišti</td>
  </thead>
  <tbody>
<?php for ($i = 0; $i < count($teams); $i++) { ?>
	<tr<?php if ($i % 2 == 1) echo ' class="alt-row"' ?>>
	  <td><?php echo $teams[$i]["name"] ?></td>
	  <td><?php echo $teams[$i]["solved"] ?></td>
	  <td><?php echo $teams[$i]["last_cipher_time"] ?></td>
	  <td><?php echo $teams[$i]["last_loc"] ?></td>
	</tr>
<?php } ?>
  </tbody>
</table>

<p>Pořadí se počítá podle těchto kritérií:</p>
<ol type="1">
  <li>čas dojití do cíle</li>
  <li>počet vyluštěných šifer</li>
  <li>čas vyluštění poslední šifry</li>
</ol>
