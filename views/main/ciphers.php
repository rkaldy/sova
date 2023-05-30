<?php 
for ($i = 0; $i < count($ciphers); $i++) { 
	echo "<div class=\"message";
	if ($i % 2 == 1) echo " alt-row";
	echo "\">\n";
	
	echo "<b>{$ciphers[$i]["name"]}";
    if ($ciphers[$i]["time"] != "-") {
		echo " - vyřešeno {$ciphers[$i]["time"]}";
	}
	echo "</b><br>\n";

	if ($ciphers[$i]["hint"] != "-") {
		echo "Nápověda: {$ciphers[$i]["hint"]}<br>\n";
	}
	if ($ciphers[$i]["solution"] != "-") {
		echo "Řešení: {$ciphers[$i]["solution"]}<br>\n";
	}

	echo "</div>\n";
}
