<div id="grid"></div>

<script>
$(function() {
    locs = $.ajax({
        type: "GET",
        url: "../api/loc",
        error: ajaxErrorHandler
	}).done(function(locs) {

	setGrid({
		table: "ccode", 
		width: "30%",
		fields: 
		[
			{ name: "code", title: "C-kód", type: "text", width: "10ex", validate: codeValidator },
			{ name: "cond_loc_id", title: "Po stanovišti", type: "select", width: "15ex", items: locs, valueField: "point_id", textField: "name", longTextField: "description" },
			{ type: "control", width: "10ex" }
		]
	});
});
});
</script>

<h3>Legenda</h3>

<table id="legend">
  <tr>
    <th>C-kód</th>
	<td>Po jeho zadání do Sovy má tým možnost si vyžádat nápovědu či zakoupit jinou výhodu do hry. Necháte-li prázdný, vygeneruje se náhodné slovo.</td>
  </tr>
  <tr>
	<th>Po stanovišti</th>
	<td>Kód lze zadat až poté, co tým navštíví dané stanoviště.</td>
  </tr>
</table>
