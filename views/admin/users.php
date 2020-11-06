<div id="grid"></div>

<script>
$(function() {
	setGrid({
		table: "user", 
		width: "30%",
		confirmDeleting: true,
		fields: 
		[
			{ name: "login", title: "Login", type: "text", width: 50, validate: "required" },
			{ name: "pswd", title: "Heslo", type: "text", width: 50 },
			{ type: "control", width: 20 }
		]
	});
});
</script>

<h3>Legenda</h3>
<table id="legend">
  <tr>
    <th>Heslo</th>
    <td>Nezadáte-li žádné, ponechá se stávající heslo.</td>
  </tr>
</table>
