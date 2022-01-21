<h3>Nastavení</h3>
<p><input type="checkbox" id="notif" onchange="toggleNotif(this)"> <b>Upozorňovat na nové zprávy</b></p>
<p>Pokud zaškrtnete, bude se prohlížeč každých 15 sekund ptát serveru na nové zprávy. Berte přitom ohled na kapacitu baterek a limity vašeho mobilního internetu.</p>

<script type="text/javascript">
document.getElementById("notif").checked = (localStorage.getItem("notif.enabled") == "true");

function toggleNotif(target) {
	localStorage.setItem("notif.enabled", target.checked);
	if (target.checked) {
		now = new Date();
		localStorage.setItem("notif.last", now.format("yyyy-mm-dd hh:MM:ss"));
	}
	setNotifications();
}
</script>
