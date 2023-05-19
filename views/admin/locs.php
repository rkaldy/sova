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
		fields: 
		[
			{ name: "name", title: "Název", type: "text", width: "10ex", validate: "required" },
			{ name: "description", title: "Popis", type: "textarea", width: "40ex" },
			{ name: "order_id", title: "Pořadí", type: "text", width: "10ex", validate: integerValidator },
			{ name: "coord_lat", title: "Souřadnice (N)", type: "text", width: "20ex", validate: coordValidator },
			{ name: "coord_lon", title: "Souřadnice (E)", type: "text", width: "20ex", validate: coordValidator },
			{ name: "code", title: "Vstupní kód", type: "text", width: "20ex", validate: codeValidator },
			{ name: "points", title: "Body", type: "text", width: "10ex", validate: integerValidator },
			{ name: "next", title: "Následující stanoviště", type: "multiselect", width: "20ex", items: locs, valueField: "point_id", textField: "name", longTextField: "description" },
			{ type: "control", width: "12ex" }
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
    <th>Pořadí</th>
    <td>Číslo, podle nějž se budou řadit stanoviště v tabulkách a seznamech <i>(nepovinné)</i></td>
  </tr>
  <tr>
    <th>Souřadnice</th>
	<td>Severní a východní souřadnice stanoviště, ve stupních jako desetinná čísla. <i>(nepovinné, ale bez nich Sova nevygeneruje odkaz na mapy.cz)</i></td>
  </tr>
  <tr>
    <th>Body</th>
    <td>Týmu se přičtou po odeslání vstupního kódu do Sovy <i>(nepovinné)</i></td>
  </tr>
  <tr>
    <th>Vstupní kód</th>
    <td>Kód, který týmy zadají do Sovy po příchodu na stanoviště. Necháte-li prázdný, vygeneruje se náhodné slovo.</td>
  </tr>
  <tr>
    <th>Následující stanoviště</th>
	<td>Seznam stanovišť, jejichž polohu Sova oznámí ihned po zadání kódu tohoto stanoviště.</td>
  </tr>
</table>
