<table id="progress">
<?php for ($row = 0; $row < count($progress); $row += 4) { ?>
  <tr>
  <?php for ($col = 0; $col < 4; $col++) { ?>
    <td>
    <?php 
      if ($row + $col < count($progress)) {
        $cipher = $progress[$row + $col];
    ?>
      <div class="locname"><?php echo $cipher["name"] ?> (<?php echo count($cipher["teams"]) ?>)</div>
      <table>
      <?php foreach ($cipher["teams"] as $team) {  ?>
        <tr<?php 
          if ($team["recent"]) echo ' class="recent"'; 
          else if ($team["hint_type"] == 1) echo ' class="withhint"';
          else if ($team["hint_type"] == 2) echo ' class="withhint_absolute"';
        ?>>
          <th><?php echo $team["name"] ?></th>
          <td><?php echo $team["time"] ?></td>
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

