<?php
$Install = new Install();

$allValid = true;

$r = $Install->_processSql();
?>
<section><center>
<h3>DATABASE UPDATED !</h3>
<p>You can click on next ;)</p>
</center>
</section>
<footer>
  <div>
    <?php if($Install->_getBack() != null): ?>
      <a href="<?php echo $Install->_getBack(); ?>" class="btn-shadow btn-grey">BACK</a>
    <?php endif; ?>
  </div>
  <div>
    <?php if($Install->_getRefresh() != null && false): ?>
      <a href="<?php echo $Install->_getRefresh(); ?>" class="btn-shadow btn-grey">REFRESH</a>
    <?php endif; ?>
    <?php if($Install->_getForward() != null): ?>
      <input type="submit" class="btn-shadow <?php echo (true ? 'btn-orange' : 'btn-grey'); ?>" value="NEXT">
    <?php endif; ?>
  </div>
</footer>
