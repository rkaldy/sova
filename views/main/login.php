<h2>Přihlášení týmu</h2>

<?php 
if (isset($flash)) echo "<p id=\"flash\">$flash</p>";
?>

<p>Zadejte přihlašovací údaje, které vám přišly mailem.</p>

<form method="post" action="login">
  <table>
    <tr>
      <th><label for="team_id">číslo týmu</label></th>
      <td><input type="text" id="team_id" name="team_id" maxlength="50"></td>
    </tr>
    <tr>
      <th><label for="pswd">heslo</label></th>
	  <td><input type="password" id="pswd" name="pswd" maxlength="50"></td>
    </tr>
    <tr>
      <th></th>
	  <td><input type="submit" value="Přihlásit"></td>
    </tr>
  </table>
</form>

<script> 
$(function() {
	$('#team_id').focus();
});
</script>
