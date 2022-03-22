<div id="grid"></div>

<script>
$(function() {
	setGrid({
		table: "team", 
		fields: 
		[
			{ name: "team_id", title: "Číslo", type: "text", width: "10ex", readOnly: true },
			{ name: "name", title: "Název", type: "text", width: "20ex", validate: "required" },
			{ name: "pswd", title: "Heslo", type: "text", width: "15ex", readOnly: "true" },
			{ name: "phone", title: "Telefon", type: "text", width: "15ex" },
			{ name: "email", title: "Email", type: "text", width: "30ex" },
			{ name: "members", title: "Členové", type: "array", width: "40ex", validate: "required" },
			{ name: "accomodation", title: "Ubytování", type: "checkbox", width: "10ex" },
			{ name: "tshirt", title: "Triček", type: "text", width: "10ex" },
			{ name: "fee", title: "Platba", type: "text", width: "10ex", readOnly: true},		
			{ name: "paid", title: "Zaplatil?", type: "checkbox", width: "10ex" },
			{ name: "remark", title: "Poznámky", type: "text", width: "20ex", readOnly: true },
			{ type: "control", width: "10ex" }
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
  <tr>
    <th>Platba</th>
	<td>Účastnický poplatek + ubytování + trička (ceny nastavíte v <a href="settings">Nastavení</a>)</td>
  </tr>
</table>
