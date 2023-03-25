<div id="graph"></div>

<div id="graph-legend">
<span class="graph-legend" style="background-color: #8080f0"></span> Stanoviště
<span class="graph-legend" style="background-color: #f08080"></span> Šifra
<span class="graph-legend" style="background-color: #f080f0"></span> Stanoviště se šifrou
</div>

<script type="text/javascript">

var colors = {
	loc: "#8080F0",
	cipher: "#F08080",
	loc_cipher: "#F080F0"
}

$(function() {
	
	var height = $(window).height() - $("header").height() - $("nav").height() - $("#graph-legend").height() - 100;
	data = $.ajax({
		type: "GET",
		url: "../api/graph",
		error: ajaxErrorHandler
	}).done(function(data) {
		var graph = new vis.Network(
			document.getElementById("graph"),
			{
				nodes: $.map(data["vertices"], function(v, i) {
					return {
						id: v.point_id,
						label: v.name,
						color: colors[v.type],
						fixed: v.isStart
					}
				}),
				edges: $.map(data["edges"], function(e, i) {
					return { from: e[0], to: e[1] };
				})
			},
			{
				height: height + "px",
				physics: { enabled: true, wind: { x: 1, y: 0 }},
				nodes: { shape: "box", margin: 15 },
				edges: { arrows: "to", color: "#a0a0a0", smooth: false, length: 100 }
			}
		);
	});
});

</script>
