<div id="grid"></div>

<script>
$(function() {
	setGrid({
		table: "text", 
		width: "80%",
		inserting: false,
		fields: 
		[
			{ name: "code", title: "Kód", type: "text", width: 20, readOnly: true },
			{ name: "text", title: "Popis", type: "text", width: 80, validate: "required" },
			{ type: "control", width: 5, deleteButton: false }
		]
	});
});
</script>

<p>V této tabulce můžete změnit znění zpráv pro tuto hru. Jen je třeba ponechat stejný počet placeholderů „%s“.</p>
