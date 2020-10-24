<div id="grid"></div>

<script>
$(function() {
	setGrid({
		table: "team", 
		width: "60%",
		fields: 
		[
			{ name: "team_id", title: "Číslo týmu", type: "text", width: 10, readOnly: true },
			{ name: "name", title: "Název", type: "text", width: 20, validate: "required" },
			{ name: "pswd", title: "Heslo", type: "text", width: 20, validate: codeValidator },
			{ name: "phone", title: "Telefon", type: "text", width: 20 },
			{ name: "email", title: "Email", type: "text", width: 20 },
			{ type: "control", width: 5 }
		]
	});
});
</script>

<h3>Legenda</h3>

<table id="legend">
  <tr>
    <th>Číslo týmu</th>
	<td>Automaticky generované číslo, které týmy zadají při přihlašování do Sovy.</td>
  </tr>
  <tr>
    <th>Heslo</th>
	<td>Heslo, které týmy zadají při přihlašování do Sovy. Necháte-li prázdné, vygeneruje se náhodné slovo.</td>
  </tr>
  <tr>
    <th>Telefon, email</th>
    <td>Kontaktní údaje na tým.</td>
  </tr>
</table>

<p>Nezapomeňte rozeslat čísla a hesla týmům před hrou.</p>
