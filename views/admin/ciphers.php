<div id="grid"></div>

<script>

var longText = function(item) {
	return item["name_int"] == null ? item["name"] : item["name"] + ": " + item["name_int"];
}

$(function() {
	locs = $.ajax({
		type: "GET",
		url: "../api/loc"
	}).done(function(locs) {
	setGrid({
		table: "cipher", 
		width: "100%",
		fields: 
		[
			{ name: "name", title: "Číslo šifry", type: "text", width: 20 , validate: "required" },
			{ name: "name_int", title: "Interní název", type: "text", width: 30  },
			{ name: "code", title: "Řešení", type: "text", width: 20, validate: codeValidatorReq },
			{ name: "hint", title: "Nápověda", type: "textarea", width: 40 },
			{ name: "hint_timeout", title: "Čas odeslání nápovědy", type: "number", width: 15 },
			{ name: "solution_timeout", title: "Čas odeslání řešení", type: "number", width: 15 },
			{ name: "prev", title: "Umístění šifry", type: "multiselect", width: 30, items: locs, valueField: "point_id", textField: "name", longTextField: longText },
			{ name: "next", title: "Následující stanoviště", type: "multiselect", width: 30, items: locs, valueField: "point_id", textField: "name", longTextField: longText },
			{ type: "control", width: 5 }
		]
	});
});
});
</script>

<h3>Legenda</h3>
<table id="legend">
  <tr>
    <th>Číslo šifry</th>
    <td>Identifikační kód šifry (S1, S2A...), kterou týmy zadávají např. pro univerzální nápovědu.</td>
  </tr>
  <tr>
    <th>Interní název</th>
    <td>Krátký název šifry (Morseovka, Skryté tečky...), který je viditelný jen ve statistikách pro orgy <i>(nepovinné)</i>.</td>
  </tr>
  <tr>
    <th>Řešení</th>
    <td>Řešení šifry, jednoslovný kód, který týmy zadají do Sovy po vyluštění šifry.</td>
  </tr>
  <tr>
    <th>Nápověda</th>
    <td>Nápověda, kterou Sova pošle po daném čase anebo oproti kódu univerzální nápovědy <i>(nepovinné)</i>.</td>
  </tr>
  <tr>
    <th>Čas odeslání nápovědy</th>
    <td>Čas v minutách po příchodu na stanoviště, po němž Sova pošle nápovědu <i>(nepovinné)</i>.</td>
  </tr>
  <tr>
    <th>Čas odeslání řešení</th>
    <td>Čas v minutách po příchodu na stanoviště, po němž Sova pošle řešení šifry <i>(nepovinné)</i>.</td>
  </tr>
  <tr>
    <th>Umístění šifry</h3>
	<td>Stanoviště, na němž se šifra nachází. Stanovišť může být i více, pokud se šifra skládá z více částí na různých místech. Čas pro odeslání nápovědy se spustí po odeslání kódů ze všech těchto stanovišť.</td>
  </tr>
  <tr>
    <th>Následující stanoviště</th>
	<td>Stanoviště, na které šifra ukazuje. Stanovišť může být i více, v takovém případě po vyluštění šifry Sova vrátí polohu všech těchto stanovišť.</td>
  </tr>
</table>
