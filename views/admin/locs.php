<div id="grid"></div>

<script>
$(function() {
	locs = $.ajax({
		type: "GET",
		url: "../api/loc",
		error: ajaxErrorHandler
	}).done(function(locs) {
	setGrid({
		table: "loc", 
		width: "80%",
		fields: 
		[
			{ name: "name", title: "Název", type: "text", width: 20 , validate: "required" },
			{ name: "description", title: "Popis", type: "textarea", width: 80 },
			{ name: "code", title: "Vstupní kód", type: "text", width: 20, validate: codeValidator },
			{ name: "end_time", title: "Deadline", type: "datetime", width: 30 },
			{ name: "next", title: "Následující stanoviště", type: "multiselect", width: 30, items: locs, valueField: "point_id", textField: "name", longTextField: "description" },
			{ type: "control", width: 5 }
		]
	});
});
});
</script>

<h3>Legenda</h3>
<table id="legend">
  <tr>
    <th>Název</th>
    <td>Identifikační kód stanoviště (Start, Turniket, L1...), který se zobrazuje např. u pořadí týmů, proto by z názvu nemělo jít odvodit jeho polohu.</td>
  </tr>
  <tr>
    <th>Popis</th>
    <td>Přesný popis polohy, který systém pošle po úspěšném vyluštění předchozí šifry <i>(nepovinné)</i>.</td>
  </tr>
  <tr>
    <th>Vstupní kód</th>
    <td>Kód, který týmy zadají do Sovy po příchodu na stanoviště. Necháte-li prázdný, vygeneruje se náhodné slovo.</td>
  </tr>
  <tr>
    <th>Deadline</th>
    <td>Časový limit, do něhož je třeba dojít na stanoviště <i>(nepovinné)</i>.</td>
  </tr>
  <tr>
    <th>Následující stanoviště</th>
	<td>Seznam stanovišť, jejichž polohu Sova oznámí ihned po zadání kódu tohoto stanoviště.</td>
  </tr>
</table>
