<div id="grid"></div>

<script>

var longText = function(item) {
	return item["name_int"] == null ? item["name"] : item["name"] + ": " + item["name_int"];
}

$(function() {
	locs = $.ajax({
		type: "GET",
		url: "../api/loc",
		error: ajaxErrorHandler
	}).done(function(locs) {
	setGrid({
		table: "cipher", 
		fields: 
		[
			{ name: "name", title: "Číslo", type: "text", width: "10ex", validate: "required" },
			{ name: "name_int", title: "Interní název", type: "text", width: "20ex" },
			{ name: "activity", title: "Aktivita?", type: "checkbox", width: "10ex" },
			{ name: "code", title: "Řešení", type: "text", width: "20ex", validate: codeValidator },
			{ name: "hint", title: "Nápověda", type: "textarea", width: "30ex" },
			{ name: "howto", title: "Postup", type: "textarea", width: "40ex" },
			{ name: "points", title: "Body", type: "text", width: "10ex", validate: integerValidator },
			{ name: "points_by_rank", title: "Body dle pořadí", type: "text", width: "10ex", validate: integerValidator },
			{ name: "prev", title: "Umístění", type: "multiselect", width: "15ex", items: locs, valueField: "point_id", textField: "name", longTextField: longText },
			{ name: "next", title: "Následující stanoviště", type: "multiselect", width: "20ex", items: locs, valueField: "point_id", textField: "name", longTextField: longText },
			{ type: "control", width: "10ex" }
		]
	});
});
});
</script>

<h3>Legenda</h3>
<table id="legend">
  <tr>
    <th>Číslo</th>
    <td>Identifikační kód šifry/aktivity (S1, S2A...), kterou týmy zadávají např. pro nápovědu.</td>
  </tr>
  <tr>
    <th>Interní název</th>
    <td>Krátký název (Morseovka, Slaňování...), který je viditelný jen ve statistikách pro orgy <i>(nepovinné)</i>.</td>
  </tr>
  <tr>
    <th>Řešení</th>
    <td>Řešení šifry či kód, který týmy dostanou po zdolání aktivity. Necháte-li prázdné, vygeneruje se náhodné slovo.</td>
  </tr>
  <tr>
    <th>Nápověda</th>
    <td>Nápověda, kterou Sova pošle po daném čase anebo oproti kódu univerzální nápovědy <i>(nepovinné)</i>.</td>
  </tr>
  <tr>
    <th>Postup</th>
    <td>Přesný popis, jak vyluštit šifru. <i>(nepovinné)</i></td>
  </tr>
  <tr>
    <th>Body</th>
    <td>Týmu se přičtou po odeslání řešení do Sovy <i>(nepovinné)</i></td>
  </tr>
  <tr>
    <th>Body dle pořadí</th>
    <td>Je-li zadané, pak první tým, který vyluští šifru, dostane plný počet bodů, druhý o zadaný počet méně a další podobně <i>(nepovinné)</i><br>Ověřte si, že základní počet bodů je natolik velký, že i poslední tým dostane kladné body.</td>
  </tr>
  <tr>
    <th>Umístění</h3>
    <td>Stanoviště, na němž se šifra či aktivita nachází. Stanovišť může být i více, pokud se šifra skládá z více částí na různých místech</td>
  </tr>
  <tr>
    <th>Následující stanoviště</th>
    <td>Stanoviště, na které šifra ukazuje. Stanovišť může být i více, v takovém případě po vyřešení šifry/aktivity Sova vrátí polohu všech těchto stanovišť.</td>
  </tr>
</table>
