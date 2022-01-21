<table id="progress">
<?php for ($row = 0; $row < count($progress); $row += 3) { ?>
  <tr>
  <?php for ($col = 0; $col < 3; $col++) { ?>
    <td>
    <?php 
      if ($row + $col < count($progress)) {
        $loc = $progress[$row + $col];
    ?>
      <div class="locname"><?php echo $loc["name"] ?></div>
      <table>
      <?php foreach ($loc["teams"] as $team) {  ?>
		<tr<?php if ($team["recent"]) echo ' class="recent"' ?>>
          <th><?php echo $team["team_name"] ?></th>
          <td><?php echo $team["time"] ?></td>
		  <td<?php if ($team["solved"] == $maxSolved) echo ' class="maxSolved"' ?>><?php echo $team["solved"] ?> šifer</td>
        </tr>
      <?php } ?>
      </table>
    <?php } ?>
    </td>
  <?php } ?>
  </tr>
<?php } ?>
</table>

<script type="text/javascript">
setTimeout(function() { window.location.reload(); }, 10000);
</script>

