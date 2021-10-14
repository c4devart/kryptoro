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

$adminView = false;
if($User->_isAdmin() && isset($_SESSION['kr_account_view_user']) && !empty($_SESSION['kr_account_view_user']) && $_SESSION['kr_account_view_user'] != $User->_getUserID()){
  $User = new User($_SESSION['kr_account_view_user']);
  $adminView = true;
}

// Get charge user
$Charge = $User->_getCharge($App);

// Check if user was not currently subscribe
if(!$Charge->_activeAbo()):
?>
<section class="kr-msg <?php echo ($Charge->_getTrialNumberDay() <= 5 ? 'kr-msg-warning': ''); ?>" style="display:flex;"><span><?php echo $Lang->tr('Trial version'); ?>, <b><?php echo $Charge->_getTrialNumberDay().' '.$Lang->tr('day').($Charge->_getTrialNumberDay() > 1 ? 's' : '').' '.$Lang->tr('left'); ?></b></span></section>
<div class="kr-subs-user">
  <h2><?php echo $Lang->tr('Premium wait you !'); ?></h2>
  <div>
    <ul>
      <?php
      foreach ($Charge->_parseChargeText() as $feature) {
        echo '<li><svg class="lnr lnr-checkmark-circle"><use xlink:href="#lnr-checkmark-circle"></use></svg> <span>'.$Lang->tr(trim($feature)).'</span></li>';
      }
      ?>
    </ul>
    <div>
      <a class="btn-shadow btn-orange" onclick="showChargePopup('plan', {}); "><?php echo $Lang->tr("Let's go !"); ?></a>
    </div>
  </div>
</div>
<?php else: // User was subscribe ?>
<section class="kr-premium-details">
  <div class="kr-premium-details-icn">
    <svg class="lnr lnr-diamond"><use xlink:href="#lnr-diamond"></use></svg>
  </div>
  <div class="kr-premium-details-dl">
    <h5><?php echo $Lang->tr('You are premium !'); ?></h5>
    <span><?php echo $Charge->_getTimeRes(); ?> <?php echo $Lang->tr('day').($Charge->_getTimeRes() > 1 ? 's' : '').' '.$Lang->tr('left'); ?></span>
  </div>
</section>

<?php endif; ?>
