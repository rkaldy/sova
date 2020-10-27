<p><?php if (isset($response)) echo $response; ?></p>

<p>Aktuálně máte <?php echo $hintCount ?> nevyužitých nápověd.</p>

<h3>Zadejte číslo šifry, ke které chcete nápovědu</h3>

<form method="POST" action="applyhint">
  <p>
    <input type="text" name="cipher" class="focused">
    <input type="submit" value="Odeslat">
  </p>
</form>
