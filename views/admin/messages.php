<div id="grid"></div>

<script>
$(function() {
	setGrid({
		table: "messages", 
		width: "50%",
		inserting: false,
		editing: false,
		paging: true,
		pageLoading: true,
		rowClass: function(item, index) {
			return item.direction == 1 ? "from-team" : "to-team";
		},
		fields: 
		[
			{ name: "time", title: "Čas", type: "text", width: 10 },
			{ name: "name", title: "Tým", type: "text", width: 20  },
			{ name: "text", title: "Zpráva", type: "text", width: 40 },
		]
	});
});
</script>

