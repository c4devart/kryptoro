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

$Lang = new Lang(null, $App);
$GoogleOauth = new GoogleOauth($User);

$resetPwdAction = false;
if(isset($_GET) && isset($_GET['token'])){
  if($User->_parseToken($App, $_GET['token']) !== false) $resetPwdAction = true;
}

?>
<div class="kr-loading-fnc">
  <div> <div class="sk-folding-cube sk-folding-cube-orange"> <div class="sk-cube1 sk-cube"></div> <div class="sk-cube2 sk-cube"></div> <div class="sk-cube4 sk-cube"></div> <div class="sk-cube3 sk-cube"></div> </div> </div>
</div>
<header>
    <img src="<?php echo APP_URL.$App->_getLogoBlackPath(); ?>" alt="">
</header>
<section class="kr-login-act" act="<?php echo APP_URL; ?>/app/modules/kr-user/src/actions/resetPassword.php">
  <section class="kr-login-field">
    <?php
    if(!$resetPwdAction):
    ?>
      <input type="text" name="kr_usr_email" placeholder="<?php echo $Lang->tr('Your e-mail address'); ?>" value="">
      <div class="kr-i-msg-f-kr_usr_email"><span></span></div>
    <?php else: ?>
      <input type="password" name="kr_usr_pwdr" placeholder="<?php echo $Lang->tr('Your new password'); ?>" value="">
      <div class="kr-i-msg-f-kr_usr_pwdr"><span></span></div>
      <input type="password" name="kr_usr_pwdr_rep" placeholder="<?php echo $Lang->tr('Repeat your password'); ?>" value="">
      <div class="kr-i-msg-f-kr_usr_pwdr_rep"><span></span></div>
      <input type="hidden" name="kr_usr_pwdr_token" value="<?php echo $_GET['token']; ?>">
    <?php endif; ?>
    <footer>
      <a class="kr-gologin-view"><?php echo $Lang->tr('Back to login'); ?></a>
      <input type="submit" class="btn-shadow" name="" value="<?php echo strtoupper($Lang->tr('Next')); ?>">
    </footer>
  </section>
</section>
<footer>

</footer>
