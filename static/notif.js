function checkNewMessages() {
	lastRun = localStorage.getItem("notif.last");
  	const xhttp = new XMLHttpRequest();
	xhttp.onload = function() {
		data = JSON.parse(this.responseText);
		if (data.length != 0) {
			document.getElementById("notif-text").innerHTML = data[0];
			document.getElementById("notif-window").style.display = "block";
		}
        now = new Date();
        localStorage.setItem("notif.last", now.format("yyyy-mm-dd HH:MM:ss"));
    }
	xhttp.open("GET", "api/messages_recent?since=" + lastRun, true);
	xhttp.send();
}

function closeNotifWindow(target) {
	target.style.display = "none";
}

var timer = null;
function setNotifications() {
	if (localStorage.getItem("notif.enabled") == "true") {
		timer = window.setInterval(checkNewMessages, 15000);
	} else if (timer != null) {
		window.clearTimeout(timer);
	}
}

setNotifications();
