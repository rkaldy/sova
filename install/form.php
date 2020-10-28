<?php
if (isset($flash)) {
	echo "<p><b>$flash</b></p>";
}
?>
	  <form method="post" action=".">
		<table>
          <tr>
		    <td colspan="2">
			  Přihlašovací údaje pro správce databáze (tedy uživatele, který má práva na vytváření tabulek)<br>
              Tento účet se použije jen pro instalaci databáze, pro běžný provoz Sova použije údaje z <samp>config.php</samp>.
			</td>
		  </tr>
		  <tr>
		    <th>login</th>
			<td><input type="text" name="db_root_login"></td>
		  </tr>
		  <tr> 
		    <th>heslo</th>
			<td><input type="password" name="db_root_pswd"></td>
		  </tr>
		  <tr>
		    <td colspan="2" style="padding-top: 2ex">
			  Přihlašovací údaje pro superuživatele Sovy.<br>
			  Superuživatel má jako jediný práva vytvářet nové hry a spravovat ostatní uživatele.
			</td>
		  </tr>
		  <tr>
		    <th>login</th>
			<td><input type="text" name="su_login"></td>
		  </tr>
		  <tr> 
		    <th>heslo</th>
			<td><input type="password" name="su_pswd"></td>
		  </tr>
		  <tr>
		    <td></td>
			<td><input type="submit" value="Spustit instalaci"></td>
		  </tr>
		</table>
	  </form>
