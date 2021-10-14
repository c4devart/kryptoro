<?php
$Install = new Install();
?>
<section>
  <h1>Welcome to the <i>V4.1</i></h1>
  <p>Welcome to the new version of <b>Krypto</b>, after couple of months of work, this update is finaly here !
    We hope this update will be a breakthough in the Krypto's life. If you have any bugs, or something else, please contact us at : hello@ovrley.com</p>
  <div class="signature">
    <span>Léo DUMONTIER</span>
    <b>CEO, OVRLEY</b>
    <img src="assets/img/signature.png" alt="Léo Dumontier Signature">
  </div>
</section>
<footer>
  <div>
    <?php if($Install->_getBack() != null): ?>
      <a href="<?php echo $Install->_getBack(); ?>" class="btn-shadow btn-grey">BACK</a>
    <?php endif; ?>
  </div>
  <div>
    <?php if($Install->_getForward() != null): ?>
      <input type="submit" class="btn-shadow btn-orange" value="UPDATE NOW">
    <?php endif; ?>
  </div>
</footer>
