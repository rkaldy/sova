<div id="pagerContainer"></div>
<div id="grid"></div>

<script>
$(function() {
	setGrid({
		table: "message", 
		width: "90%",
		inserting: false,
		editing: false,
		paging: true,
		pageIndex: 1,
		pageSize: 20,
		pagerContainer: $("#pagerContainer"),
		rowClass: function(item, index) {
			return item.direction == 1 ? "from-team" : "to-team";
		},
		fields: 
		[
			{ name: "time", title: "Čas", type: "text", width: 10 },
			{ name: "direction_str", title: "Směr", type: "text", width: 2 },
			{ name: "name", title: "Tým", type: "text", width: 20  },
			{ name: "text", title: "Zpráva", type: "text", width: 50 },
		]
	});
});

$("#pager").on("change", function() {
    var page = parseInt($(this).val(), 10);
    $("#grid").jsGrid("openPage", page);
});
</script>

