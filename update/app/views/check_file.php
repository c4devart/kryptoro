<?php
$Install = new Install();

$allValid = true;

$FileCheck = $Install->_getFileCheck();
?>
<section>
  <ul>
    <?php foreach ($FileCheck['missing'] as $key => $value) { ?>
      <li>
        <span><?php echo $value; ?></span>
        <span style="color:red;">Missing</span>
      </li>
    <?php } ?>
    <?php foreach ($FileCheck['done'] as $key => $value) { ?>
      <li>
        <span><?php echo $value; ?></span>
        <span style="color:green;">Ok</span>
      </li>
    <?php } ?>
  </ul>
</section>
<footer>
  <div>
    <?php if($Install->_getBack() != null): ?>
      <a href="<?php echo $Install->_getBack(); ?>" class="btn-shadow btn-grey">BACK</a>
    <?php endif; ?>
  </div>
  <div>
    <?php if($Install->_getRefresh() != null): ?>
      <a href="<?php echo $Install->_getRefresh(); ?>" class="btn-shadow btn-grey">REFRESH</a>
    <?php endif; ?>
    <?php if($Install->_getForward() != null): ?>
      <input type="submit" class="btn-shadow <?php echo ($FileCheck['valid'] ? 'btn-orange' : 'btn-grey'); ?>" value="NEXT">
    <?php endif; ?>
  </div>
</footer>
