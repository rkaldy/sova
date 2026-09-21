<div id="grid"></div>

<script>
$(function() {
	setGrid({
		table: "game", 
		width: "30%",
		confirmDeleting: true,
		fields: 
		[
			{ name: "name", title: "Název", type: "text", width: 50, validate: "required" },
			{ name: "pswd", title: "Heslo", type: "text", width: 50 },
			{
				title: "",
				width: 10,
				align: "center",
				sorting: false,
				inserting: false,
				editing: false,
				itemTemplate: function(_, game) {
					var label = "Přihlásit jako admin hry " + game.name;
					var button = $("<button>")
						.attr({ type: "submit", title: label, "aria-label": label })
						.addClass("login-as-button")
						.text("⇥")
						.on("click", function(event) {
							event.stopPropagation();
						});

					return $("<form>")
						.attr({ method: "post", action: "loginAs" })
						.addClass("login-as-form")
						.append($("<input>").attr({ type: "hidden", name: "login" }).val(game.name))
						.append(button);
				}
			},
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
  <tr>
    <th>⇥</th>
    <td>Přihlásí superusera jako administrátora vybrané hry.</td>
  </tr>
</table>
