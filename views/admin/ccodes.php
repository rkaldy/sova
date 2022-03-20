<div id="grid"></div>

<script>
$(function() {
	setGrid({
		table: "ccode", 
		width: "20%",
		fields: 
		[
			{ name: "code", title: "C-kód", type: "text", width: 20, validate: codeValidator },
			{ type: "control", width: 5 }
		]
	});
});
</script>

<h3>Legenda</h3>

<table id="legend">
  <tr>
    <th>C-kód</th>
    <td>Po jeho zadání do Sovy má tým možnost si vyžádat nápovědu či zakoupit jinou výhodu do hry. Necháte-li prázdný, vygeneruje se náhodné slovo.</td>
  </tr>
</table>
