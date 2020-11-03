<div id="grid"></div>

<script>
$(function() {
	setGrid({
		table: "unihint", 
		width: "20%",
		fields: 
		[
			{ name: "code", title: "Kód nápovědy", type: "text", width: 20, validate: codeValidator },
			{ type: "control", width: 5 }
		]
	});
});
</script>

<h3>Legenda</h3>

<table id="legend">
  <tr>
    <th>Kód nápovědy</th>
    <td>Kód univerzální nápovědy. Po jeho zadání do Sovy má tým možnost si vyžádat nápovědu k libovolné doposud nevyluštěné šifře. Necháte-li prázdný, vygeneruje se náhodné slovo.</td>
  </tr>
</table>
