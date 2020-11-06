<div id="grid"></div>

<script>
$(function() {
	users = $.ajax({
		type: "GET",
		url: "../api/user",
		error: ajaxErrorHandler
	}).done(function(users) {
		setGrid({
			table: "game",
			width: "50%",
			confirmDeleting: true,
			fields: 
			[
				{ name: "name", title: "Název hry", type: "text", width: 50, validate: "required" },
				{ name: "start_time", title: "Datum a čas začátku", type: "datetime", width: 40 },
				{ name: "end_time", title: "Datum a čas konce", type: "datetime", width: 40, validate: "required" },
				{ name: "owner_id", title: "Správce", type: "select", items: users, valueField: "user_id", textField: "login", width: 30 },
				{ type: "control", width: 5 }
			]
		});
	});
});

</script>

<h3>Legenda</h3>
<table id="legend">
  <tr>
    <th>Datum a čas začátku</th>
    <td>Čas, od kterého mohou týmy posílat kódy do Sovy.</th>
  </tr>
  <tr>
    <th>Datum a čas konce</th>
    <td>Čas, do kterého mohou týmy posílat kódy do Sovy.</th>
  </tr>
  <tr>
    <th>Správce</th>
    <td>Uživatel, který zadává data do Sovy pro tuto hru.</td>
  </tr>
</table>
