<?php
use Sova\Model\Message;

if (!isset($page)) $page = 1;
?>

<p>
<?php if ($page != 1) { ?>
  <a class="pgbutton" href="?page=<?php echo $page-1 ?>">Předchozí</a>
<?php } ?>
<span class="pgbutton"><?php echo "Strana $page / ".intdiv($totalCount+14, 15) ?></span>
<?php if ($page * 15 < $totalCount) { ?>
  <a class="pgbutton" href="?page=<?php echo $page+1 ?>">Další</a>
<?php } ?>
</p>

<?php foreach($messages as $msg) { ?>
  <div class="message <?php echo $msg["direction"] == Message::FROM_TEAM ? "from-team" : "to-team"; if ($msg["async"] == 1) { echo " async"; } ?>">
	<span class="dir"><?php echo $msg["direction"] == Message::FROM_TEAM ? "😎" : "🦉" ?></span>&nbsp;
	<span class="time"><?php echo $msg["time"] ?></span><br>
    <span class="text"><?php echo $msg["text"]; ?></span>
  </div>
<?php } ?>
