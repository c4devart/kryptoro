<?php
session_start();

require "../../../config/config.settings.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/vendor/autoload.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/MySQL/MySQL.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/App.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/AppModule.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/User/User.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/Lang/Lang.php";

$App = new App(true);
$App->_loadModulesControllers();

$User = new User();
if(!$User->_isLogged()) $Lang = new Lang($User->_getLang(), $App);
else $Lang = new Lang(null, $App);

?>
<section class="kr-contact-zone kr-ov-nblr">
  <section>
    <div class="kr-contact-zone-image">

    </div>
    <div>
      <header>
        <div onclick="_closeContactPopup();">
          <svg class="lnr lnr-cross"><use xlink:href="#lnr-cross"></use></svg>
        </div>
      </header>
      <h2><?php echo $Lang->tr('Support'); ?></h2>
      <ul>
        <li>
          <span><?php echo $Lang->tr('Email'); ?></span>
          <span><a href="mailto:<?php echo $App->_getSupportEmail(); ?>"><?php echo $App->_getSupportEmail(); ?></a></span>
        </li>
        <?php if(!is_null($App->_getSupportPhone()) && strlen($App->_getSupportPhone()) > 1): ?>
        <li>
          <span><?php echo $Lang->tr('Phone'); ?></span>
          <span><?php echo $App->_getSupportPhone(); ?></span>
        </li>
        <?php endif; ?>
        <?php if(!is_null($App->_getSupportAddress()) && strlen($App->_getSupportAddress()) > 1): ?>
          <li>
            <span><?php echo $Lang->tr('Address'); ?></span>
            <span><?php echo $App->_getSupportAddress(); ?></span>
          </li>
        <?php endif; ?>
      </ul>
      <?php if(strlen($App->_getDPOPhone()) > 1 || strlen($App->_getDPOEmail()) > 1) ?>
      <h2><?php echo $Lang->tr('Contact DPO'); ?></h2>
      <ul>
        <?php if(!is_null($App->_getDPOEmail()) && strlen($App->_getDPOEmail()) > 1): ?>
          <li>
            <span><?php echo $Lang->tr('Email'); ?></span>
            <span><a href="mailto:<?php echo $App->_getDPOEmail(); ?>"><?php echo $App->_getDPOEmail(); ?></a></span>
          </li>
        <?php endif; ?>
        <?php if(!is_null($App->_getDPOPhone()) && strlen($App->_getDPOPhone()) > 1): ?>
          <li>
            <span><?php echo $Lang->tr('Phone'); ?></span>
            <span><?php echo $App->_getDPOPhone(); ?></span>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </section>
</section>
