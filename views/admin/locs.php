<div id="grid"></div>

<script>
$(function() {
	setGrid({
		table: "loc", 
		width: "80%",
		fields: 
		[
			{ name: "name", title: "Název", type: "text", width: 20 , validate: "required" },
			{ name: "description", title: "Popis", type: "textarea", width: 80 },
			{ name: "code", title: "Vstupní kód", type: "text", width: 20, validate: codeValidator },
			{ name: "min_ciphers_solved", title: "Minimální počet šifer", type: "number", width: 15 },
			{ name: "end_time", title: "Deadline", type: "datetime", width: 30 },
			{ type: "control", width: 5 }
		]
	});
});
</script>

<h3>Legenda</h3>
<table id="legend">
  <tr>
    <th>Název</th>
    <td>Krátký název stanoviště (Černá hora, Sedlo pod Holubníkem...).</td>
  </tr>
  <tr>
    <th>Popis</th>
    <td>Přesný popis polohy, který systém pošle po úspěšném vyluštění předchozí šifry <i>(nepovinné</i>.</td>
  </tr>
  <tr>
    <th>Vstupní kód</th>
    <td>Kód, který týmy zadají do Sovy po příchodu na stanoviště. Necháte-li prázdný, vygeneruje se náhodné slovo.</td>
  </tr>
  <tr>
    <th>Minimální počet šifer</th>
    <td>Systém pošle polohu stanoviště v okamžiku, kdy tým vyluští odpovídající počet šifer <i>(nepovinné)</i>.</td>
  </tr>
  <tr>
    <th>Deadline</th>
    <td>Časový limit, do něhož je třeba dojít na stanoviště <i>(nepovinné)</i>.</td>
  </tr>
</table>
