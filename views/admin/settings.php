<?php
const SEPARATOR = 1;

const TEXT = 1;
const BOOL = 2;
const ENUM = 3;
const DATETIME = 4;
const PASSWORD = 5;


$settings = [
	[
		"var" 	=> "gameStart",
		"type"	=> DATETIME,
		"label"	=> "Začátek hry",
		"desc"	=> "Čas od kdy Sova začne přijímat kódy stanovišť a šifer"
	],
	[
		"var" 	=> "gameEnd",
		"type"	=> DATETIME,
		"label"	=> "Konec hry",
		"desc"	=> "Čas do kdy bude Sova přijímat kódy stanovišť a šifer"
	],
	[
		"var"	=> "locVisitMandatory", 
		"type"	=> BOOL, 
		"label" => "Povinné kódy místa", 
		"desc"	=> "Je-li zaškrtnuto, týmu musí odeslat kód stanoviště po příchodu na něj, jinak Sova nepřijme řešení šifry na tomto stanovišti.<br>Není-li zaškrtnuto, tým může odeslat řešení šifry i bez odeslání kódu stanoviště, pouze jim v takovém případě Sova nepošle časovou nápovědu/řešení."
	],
	[
		"var" 	=> "showRank",
		"type"	=> BOOL,
		"label"	=> "Zobrazovat pořadí",
		"desc"	=> "Je-li zaškrtnuto, Sova po odeslání kódu šifry či stanoviště pošle zpátky i pořadí týmu na daném místě a celkově."
	],
	[
		"var"	=> "locFinish",
		"type"	=> ENUM,
		"label"	=> "Cílové stanoviště",
		"enumValues" => $locs
	],
	[
		"var"	=> "finishPointThreshold",
		"type"	=> TEXT,
		"label"	=> "Počet bodů nutných k nalezení cíle",
		"desc"  => "Použijte v případě, že polohu cílového stanoviště (typicky váza na Lavině) nezískáte vyluštěním konkrétní šifry, ale dosažením daného množství bodů.<br>Tuto hranici můžete během hry měnit, informaci o snížení ale rozešlete ručně v sekci Zpráva týmům."
	],
	[
		"var"	=> "linkMapyCz",
		"type"	=> ENUM,
		"label"	=> "Odkaz na mapy.cz",
		"desc"	=> "Má-li stanoviště zadané souřadnice, zobrazí se týmům jako odkaz na mapy.cz",
		"enumValues" => ["turisticka" => "Turistická", "zimni" => "Zimní"]
	],
	SEPARATOR,
	[	
		"var"	=> "gamePrice",
		"type"	=> TEXT,
		"label"	=> "Účastnický poplatek",
		"desc"	=> "za tým",
	],
	[	
		"var"	=> "accomodationPrice",
		"type"	=> TEXT,
		"label"	=> "Cena ubytování",
		"desc"	=> "za člověka",
	],
	[	
		"var"	=> "tshirtPrice",
		"type"	=> TEXT,
		"label"	=> "Cena trička"
	],
	SEPARATOR,
	[
		"var"	=> "hintPoints",
		"type"	=> TEXT,
		"label"	=> "Cena nápovědy (v bodech)"
	],
	[
		"var"	=> "howtoPoints",
		"type"	=> TEXT,
		"label"	=> "Cena postupu (v bodech)",
		"desc"  => "navíc k ceně nápovědy"
	],
	[
		"var"	=> "solutionPoints",
		"type"	=> TEXT,
		"label"	=> "Cena řešení (v bodech)",
		"desc"  => "navíc k ceně postupu"
	],
	[
		"var"	=> "hintCCodes",
		"type"	=> TEXT,
		"label"	=> "Cena nápovědy (v céčkách)"
	],
	[
		"var"	=> "howtoCCodes",
		"type"	=> TEXT,
		"label"	=> "Cena postupu (v céčkách)",
		"desc"  => "navíc k ceně nápovědy"
	],
	[
		"var"	=> "solutionCCodes",
		"type"	=> TEXT,
		"label"	=> "Cena řešení (v céčkách)",
		"desc"  => "navíc k ceně postupu"
	],
	[
		"var"	=> "imunityCCodes",
		"type"	=> TEXT,
		"label"	=> "Cena imunity (v céčkách)"
	],
	[
		"var"	=> "deductPoints",
		"type"	=> BOOL,
		"label"	=> "Mají týmy možnost si samy odečíst body?"
	],
	SEPARATOR,
	[
		"var"	=> "pswd",
		"type"	=> PASSWORD,
		"label"	=> "Nové heslo do admin sekce",
		"desc"  => "Ponechte prázdné, pokud heslo nechcete měnit."
	]
];

?>

<h2>Nastavení</h2>

<p id="flash"><?php if (isset($flash)) echo $flash ?></p>

<form method="POST" action="settings">
<table>
<?php 
foreach ($settings as $item) {
	if ($item == SEPARATOR) {
		echo '<tr><td colspan="2"><hr></td></tr>'."\n";
		continue;
	}
	$desc = null;
	extract($item);
	echo "<tr>\n";
	echo "  <th><label for=\"$var\">$label";
	if (isset($desc)) {
		echo "<br><small>$desc</small>";
	}
	echo "</label></th>\n";
	echo "  <td>";
	switch ($type) {
		case TEXT:
		case DATETIME:
			echo "<input type=\"text\" id=\"$var\" name=\"$var\" value=\"{$fields[$var]}\">";
		   	break;
		case PASSWORD:
			echo "<input type=\"password\" id=\"$var\" name=\"$var\" maxlength=\"50\">";
		   	break;
		case BOOL: 
			echo "<input type=\"checkbox\" id=\"$var\" name=\"$var\"";
		  	if ($fields[$var]) {
				echo ' checked="checked"';
			}
			echo ">";
			break;
		case ENUM:
			echo "<select id=\"$var\" name=\"$var\">\n";
			echo "  <option value=\"\">-</option>\n";
			foreach ($enumValues as $key => $value) {
				echo "    <option value=\"$key\"";
				if ($fields[$var] == $key) {
					echo ' selected="selected"';
				}
				echo ">$value</option>\n";
			}
			echo "  </select>";
			break;
	}
	echo "</td>\n";
	echo "</tr>\n";
}
?>
  <tr>
    <th></th>
	<td><input type="submit" value="Uložit"></td>
  </tr>
</table>
</form>

<h2>Reset hry</h2>
<p>Reset smaže všechna dynamická data v dané hře (postup týmů, nápovědy, zprávy, body, popř. získaná céčka), ale ponechá statická data (týmy, stanoviště, šifry). Použijte jej na vyčištění databáze po anuálním testování Sovy.</p>
<form method="POST" action="reset">
  <input type="submit" value="Reset">
</form>


<script type="text/javascript">
<?php
foreach ($settings as $item) {
	if (!is_array($item)) {
		continue;
	}
	extract($item);
	if ($type == DATETIME) {
		echo " jQuery('#$var').datetimepicker({'format': 'Y-m-d H:i:s'});\n";
	}
}
?>
</script>
