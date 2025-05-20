document.getElementById("menubtn").onclick = function(self) {
	nav = document.getElementById("nav");
	points = document.getElementById("pointcount");
	if (nav != null) {
		//console.log(nav.style.display);
		if (nav.style.display == "block") {
			nav.style.display = "none";
			points.style.display = "inline-blocK";
			self.target.style.opacity = 1;
		} else {
			nav.style.display = "block";
			points.style.display = "none";
			self.target.style.opacity = 0.8;
		}
	}
};

elems = document.getElementsByClassName("focused");
if (elems.length != 0) {
    elems[0].focus();
}

