<table>
  <thead>
    <tr>
      <td>Šifra</td>
      <td>Vyluštěno</td>
      <td>Nápověda</td>
    </tr>
  </thead>
  <tbody>
<?php for ($i = 0; $i < count($ciphers); $i++) { ?>
	<tr<?php if ($i % 2 == 1) echo ' class="alt-row"' ?>>
      <th><?php echo $ciphers[$i]["name"] ?></th>
      <td><?php echo $ciphers[$i]["time"] ?></td>
      <td><?php echo $ciphers[$i]["hint"] ?></td>
    </tr>
<?php } ?>
  </tbody>
</table>
