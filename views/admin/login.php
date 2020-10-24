<h2>Přihlášení</h2>

<?php 
if (isset($flash)) echo "<p id=\"flash\">$flash</p>";
?>

<form method="post" action="login">
  <table>
    <tr>
      <th><label for="login">login</label></th>
      <td><input type="text" id="login" name="login" maxlength="50"></td>
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
	$('#login').focus();
});
</script>
