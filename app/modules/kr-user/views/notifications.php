<?php

/**
 * Notification account view
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */

session_start();

require "../../../../config/config.settings.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/vendor/autoload.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/MySQL/MySQL.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/App.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/AppModule.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/User/User.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/Lang/Lang.php";

// Load app module
$App = new App(true);
$App->_loadModulesControllers();

// Check if user is logged
$User = new User();
if(!$User->_isLogged()) die('User not logged');

// Init lang object
$Lang = new Lang($User->_getLang(), $App);

$adminView = false;
if($User->_isAdmin() && isset($_SESSION['kr_account_view_user']) && !empty($_SESSION['kr_account_view_user']) && $_SESSION['kr_account_view_user'] != $User->_getUserID()){
  $User = new User($_SESSION['kr_account_view_user']);
  $adminView = true;
}

// Check if PushBullet is not init
if(is_null($User->_getPushbulletKey())):
?>
<section class="kr-msg kr-msg-error"></section>
<div class="kr-user-notif-setup">

  <div>
    <h2><?php echo $Lang->tr('Mobile, browser, windows notifications'); ?></h2>
    <ul>
      <li><?php echo $Lang->tr('Create price alert'); ?></li>
    </ul>
    <form class="kr-user-notifi-steup-pb" method="post">
      <input type="text" name="pushbullet_key" placeholder="<?php echo $Lang->tr('Your PushBullet Access Token'); ?>" value="">
      <input type="hidden" name="kr_prof_u" value="<?php echo $User->_getUserID(true); ?>">
      <input type="submit" class="btn-shadow btn-orange" name="" value="<?php echo $Lang->tr('Save'); ?>">
    </form>
  </div>
  <div class="kr-user-notif-setuppicture" style="background-image:url('<?php echo APP_URL; ?>/app/modules/kr-user/statics/img/notification_mockup.png');">

  </div>

</div>
<div class="kr-user-notif-instruction">
  <h2><?php echo $Lang->tr('I need help !'); ?></h2>
  <ul>
    <li>1. <?php echo $Lang->tr('Download PushBullet app on your phone (available for Android & iOS)'); ?></li>
    <li>2. <?php echo $Lang->tr('Create an account on it'); ?></li>
    <li>3. <?php echo $Lang->tr('Connect with your new account here'); ?> : <a href="https://www.pushbullet.com/signin" target=_bank>https://www.pushbullet.com/signin</a></li>
    <li>4. <?php echo $Lang->tr('Go on <b>Settings</b> > <b>Account</b> and create an <b>Access token</b>'); ?></li>
    <li>5. <?php echo $Lang->tr('Copy the key and paste it on the fill and click on save.'); ?></li>
    <li>6. <?php echo $Lang->tr('An notification will be sent to check if connexion is successful.'); ?></li>
  </ul>
</div>
<?php else: // Init pushbullet is already init ?>
<section class="kr-msg" style="display:flex;"><?php echo $Lang->tr("Notification's configuration complete."); ?></section>
<div class="kr-user-notif-setup">

  <div>
    <form class="kr-user-notifi-steup-rmv-pb" method="post">
      <input type="text" name="pushbullet_key" disabled placeholder="<?php echo $Lang->tr('Your PushBullet Access Token'); ?>" value="<?php echo substr($User->_getPushbulletKey(), 0, 10).'############'; ?>">
      <input type="hidden" name="kr_prof_u" value="<?php echo $User->_getUserID(true); ?>">
      <input type="submit" class="btn-shadow btn-grey" name="" value="<?php echo $Lang->tr('Remove token'); ?>">
    </form>
  </div>
  <div class="kr-user-notif-setuppicture" style="background-image:url('<?php echo APP_URL; ?>/app/modules/kr-user/statics/img/notification_mockup.png');">

  </div>

</div>
<?php endif; ?>
