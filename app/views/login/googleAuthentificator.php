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

try {
  if(empty($_POST) || !isset($_POST['user']) || !isset($_POST['pwd'])) throw new Exception("Error", 1);

} catch (\Exception $e) {
  die('Access denied');
}


?>
<section class="kr-login-tfs">
  <section>
    <div style="display:none;" class="sk-folding-cube sk-folding-cube-orange"> <div class="sk-cube1 sk-cube"></div> <div class="sk-cube2 sk-cube"></div> <div class="sk-cube4 sk-cube"></div> <div class="sk-cube3 sk-cube"></div></div>
    <img src="assets/img/login/authenticator.png" alt="">
    <form class="kr-login-authentificator-act" action="<?php echo APP_URL; ?>/app/modules/kr-user/src/actions/login.php" method="post">
      <span>Write your Google Authenticator code just below</span>
      <input type="hidden" name="kr_usr_email" value="<?php echo $_POST['user']; ?>">
      <input type="hidden" name="kr_usr_pwd" value="<?php echo $_POST['pwd']; ?>">
      <input id="google_tfs_inpt" type="text" pattern="[0-9]{6}" placeholder="******" maxlength="6" name="kr_login_code" value="">
      <input type="submit" class="btn btn-shadow btn-black" name="" value="Enter">
    </form>
  </section>
</section>
