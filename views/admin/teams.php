<div id="grid"></div>

<script>
$(function() {
	setGrid({
		table: "team", 
		width: "100%",
		fields: 
		[
			{ name: "team_id", title: "Číslo", type: "text", width: 3, readOnly: true },
			{ name: "name", title: "Název", type: "text", width: 15, validate: "required" },
			{ name: "pswd", title: "Heslo", type: "text", width: 10, validate: codeValidator },
			{ name: "phone", title: "Telefon", type: "text", width: 10 },
			{ name: "email", title: "Email", type: "text", width: 10 },
			{ name: "members", title: "Členové", type: "text", width: 30, validate: "required" },
			{ name: "accomodation", title: "Ubytování", type: "checkbox", width: 3 },
			{ name: "tshirt", title: "Triček", type: "text", width: 3 },
			{ name: "paid", title: "Zaplatil?", type: "checkbox", width: 3 },
			{ name: "remark", title: "Poznámky", type: "text", width: 20, readOnly: true },
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
