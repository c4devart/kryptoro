<?php
$Install = new Install();

$allValid = true;

?>
<section><center>
<ul>
  <?php foreach ($Install->_getListPageCalled() as $key => $value) { ?>
    <li kr-sync-link="<?php echo APP_URL.'/'.$key; ?>"><i>Waiting ...</i> <?php echo $value; ?></li>
  <?php } ?>
</ul>
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
      <input type="submit" style="display:none;" class="btn-newup btn-shadow <?php echo (true ? 'btn-orange' : 'btn-grey'); ?>" value="NEXT">
    <?php endif; ?>
  </div>
</footer>
<script type="text/javascript">
  $(document).ready(function(){
    let ac = $('[kr-sync-link]').length;
    let ad = 0;
    $('[kr-sync-link]').each(function(){
      let link = $(this).attr('kr-sync-link');
      let elementLink = $(this);
      $.get(link).done(function(){
        elementLink.find('i').html('Done ! ');
        elementLink.css('color', 'green');
        ad++;
        if(ad == ac){
          $('.btn-newup').show();
        }
      }).fail(function(){
        elementLink.find('i').html('Fail ! ');
        elementLink.css('color', 'red');
        alert('Fail to load : ' + link);
      });
    });
  });
</script>
