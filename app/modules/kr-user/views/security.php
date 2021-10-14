<?php

/**
 * Subscription view
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
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoApi.php";

// Load app modules
$App = new App(true);
$App->_loadModulesControllers();

// Check if user is logged
$User = new User();
if(!$User->_isLogged()) die('User not logged');

// Init lang object
$Lang = new Lang($User->_getLang(), $App);

if(!$User->_googleTwoFactorEnable($User->_getUserID())):

  $tfsData = $User->_generateGoogleTwoFactor($App);

?>

  <section class="kr-user-security kr-user-security-setup">
    <section>
      <h3>Secure your account with<br/><i>Google Authenticator</i></h3>
      <ul>
        <li><a class="btn btn-black btn-autowidth" href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=en">Download on Android</a></li>
        <li><a class="btn btn-black btn-autowidth" href="https://itunes.apple.com/us/app/google-authenticator/id388497605?mt=8">Download on iOS</a></li>
        <li></li>
      </ul>
      <h4>Scan the QRcode, enter your code and validate</h4>
    </section>
    <div>
      <img src='<?php echo $tfsData['qrcode']; ?>'/>
      <form class="kr-gogoletfs-check" action="<?php echo APP_URL; ?>/app/modules/kr-user/src/actions/validateGoogleTFS.php" method="post">
        <input type="text" pattern="[0-9]{6}" placeholder="******" maxlength="6" name="google_tfs_code" value="">
        <span>Wrong code</span>
        <input type="submit" name="" class="btn btn-shadow btn-autowidth" value="Validate">
      </form>
    </div>
  </section>

<?php else: ?>

  <section class="kr-msg" style="display:flex;"><?php echo $Lang->tr("Google Authentification is enable !"); ?></section>

  <form class="kr-gogletfs-remove" action="<?php echo APP_URL; ?>/app/modules/kr-user/src/actions/removeGoogleTFS.php" method="post">
    <input type="hidden" name="kr-user-id-c" value="<?php echo $User->_getUserID(true); ?>">
    <input type="submit" class="btn btn-shadow btn-autowidth" value="Remove Google Authenticator">
  </form>


<?php endif; ?>

  <h3 class="kr-login-user-history">Login history</h3>

  <table class="kr-login-user-history-table">
    <?php
    foreach ($User->_getHistoryLoginUser() as $UserLoginDetails) {
    ?>
    <tr>
      <td>
        <img src="<?php echo APP_URL; ?>/assets/img/icons/country/<?php echo strtolower($UserLoginDetails['country_code_user_login_history']); ?>.png" alt="">
        <span><?php echo $UserLoginDetails['location_user_login_history']; ?></span>
      </td>
      <td>
        <span><?php echo $UserLoginDetails['ip_user_login_history']; ?></span>
      </td>
      <td><?php echo date('d/m/Y H:i:s', $UserLoginDetails['date_user_login_history']); ?></td>
    </tr>
    <?php } ?>
  </table>
