<form method="POST" action="broadcast_send">
  <div class="left">
    <h3>Adresát</h3>
    <table>
	   <thead>
	      <tr>
		    <td>Vybrat vše</td>
			<td><input type="checkbox" id="selectAll"></td>
		  </tr>
	   </thead>
	   <tbody>
<?php for ($i = 0; $i < count($teams); $i++) { ?>
	      <tr<?php if ($i % 2 == 1) echo ' class="alt-row"'?>>
			<td><?php echo $teams[$i]["name"] ?></td>
			<td><input type="checkbox" name="team[]" value="<?php echo $teams[$i]["team_id"]?>"></td>
		  </tr>
<?php } ?>
      </tbody>
	</table>
  </div>
  <div class="left">
  <h3>Zpráva</h3>
  <p><textarea rows="4" cols="80" name="message"></textarea></p>
  <p><input type="submit" value="Rozeslat"></p>
  </div>
</form>

<script type="text/javascript">
$("#selectAll").change(function() {
	var checkboxes = $(this).closest("form").find(":checkbox");
	checkboxes.prop("checked", $(this).is(":checked"));
});
</script>
