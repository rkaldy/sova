<?php
use Sova\Model\Message;

if (!isset($page)) $page = 1;
?>

<?php if ($page != 1) { ?>
  <a class="pgbutton" href="?page=<?php echo $page-1 ?>">Předchozí</a>
<?php } ?>
<span class="pgbutton">Strana <?php echo $page ?></span>
<?php if ($page * 20 < $totalCount) { ?>
  <a class="pgbutton" href="?page=<?php echo $page+1 ?>">Další</a>
<?php } ?>

<table class="table">
  <thead>
    <tr>
	  <td>Čas</td>
	  <td>Směr</td>
	  <td>Zpráva</td>
	</tr>
  </thead>
  <tbody>
<?php foreach($messages as $msg) { ?>
    <tr class="<?php echo $msg["direction"] == Message::FROM_TEAM ? "from-team" : "to-team" ?>">
	  <td><?php echo $msg["time"] ?></td>
	  <td><b><?php echo $msg["direction"] == Message::FROM_TEAM ? "in" : "out" ?></b></td>
	  <td><?php echo $msg["text"] ?></td>
	</tr>
<?php } ?>
  </tbody>
</table>

