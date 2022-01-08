<div id="grid"></div>

<script>
$(function() {
	setGrid({
		table: "message", 
		width: "70%",
		inserting: false,
		editing: false,
		paging: true,
		pageLoading: true,
		pageIndex: 1,
		pageSize: 20,
		rowClass: function(item, index) {
			return item.direction == 1 ? "from-team" : "to-team";
		},
		fields: 
		[
			{ name: "time", title: "Čas", type: "text", width: 10 },
			{ name: "direction_str", title: "Směr", type: "text", width: 2 },
			{ name: "name", title: "Tým", type: "text", width: 15  },
			{ name: "text", title: "Zpráva", type: "text", width: 50 },
		]
	});
});
</script>

