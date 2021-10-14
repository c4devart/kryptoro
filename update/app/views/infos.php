<?php
$Install = new Install();
?>
<section>
  <h1>Warning !</h1>
  <p style="text-align: justify">This update will turn your website in maintenance mode. For disable the maintenance mode after the update, you need to go on your <b>admin interface and general settings</b>, then you can turn 'Off' the maintenance mode and allow the signup.</p>
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
