<?php

/**
 * Main account view
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

// Init language object
$Lang = new Lang($User->_getLang(), $App);
$UserLogged = $User;
$adminView = false;
if(($User->_isAdmin() || $User->_isManager()) && isset($_GET['adm_acc_user'])){
  $User = new User($_GET['adm_acc_user']);
  $adminView = true;
}

if(($User->_isAdmin() || $User->_isManager()) && !$UserLogged->_isAdmin()){
  $User = $UserLogged;
  $adminView = false;
}

$_SESSION['kr_account_view_user'] = $User->_getUserID();



?>
<header>
  <div class="kr-user-md">
    <div class="kr-user-pic kr-user-pic-s kr-user-pic-s-u <?php if(!is_null($User->_getPicture())) echo 'kr-user-pic-exist' ?>" style="background-image:url('<?php echo $User->_getPicture(); ?>')">
      <svg class="lnr lnr-camera">
        <use xlink:href="#lnr-camera"></use>
      </svg>
      <div class="kr-user-pic-loading">

      </div>
    </div>
    <div class="kr-user-infos">
      <h3><?php echo $User->_getName(); ?></h3>
      <span><?php echo $User->_getEmail(); ?></span>
    </div>
  </div>
  <section class="kr-user-pic-s kr-user-pic-s-u" style="background-image:url('<?php echo $User->_getPicture(); ?>')">

  </section>
</header>
<ul class="kr-user-nav">
  <li class="kr-user-nav-selected" kr-user-v="profile"><?php echo $Lang->tr('Profile'); ?></li>
  <?php if($User->_accessAllowedFeature($App, 'notifications_phone')): ?>
    <li kr-user-v="notifications"><?php echo $Lang->tr('Notification'); ?></li>
  <?php endif; ?>
  <?php if($App->_subscriptionEnabled()): ?>
    <li kr-user-v="subscription"><?php echo $Lang->tr('Subscription'); ?></li>
  <?php endif; ?>
  <?php if($User->_accessAllowedFeature($App, 'googleauthenticator')): ?>
    <li kr-user-v="security"><?php echo $Lang->tr('Security'); ?></li>
  <?php endif; ?>
  <?php if($User->_accessAllowedFeature($App, 'tradinglive') && !$App->_hiddenThirdpartyActive()): ?>
    <li kr-user-v="exchanges"><?php echo $Lang->tr('Exchanges'); ?></li>
  <?php endif; ?>
  <?php if($User->_accessAllowedFeature($App, 'tradinglive') && $App->_hiddenThirdpartyActive()): ?>
    <li kr-user-v="widthdraw"><?php echo $Lang->tr('Widthdraw / Wallets'); ?></li>
  <?php endif; ?>
  <?php if(!$adminView): ?>
    <li kr-user-v="logout"><?php echo $Lang->tr('Logout'); ?></li>
  <?php endif; ?>
</ul>
<section class="kr-user-content">
  <div class="kr-user-loading"><div class="sk-folding-cube sk-folding-cube-orange"> <div class="sk-cube1 sk-cube"></div> <div class="sk-cube2 sk-cube"></div> <div class="sk-cube4 sk-cube"></div> <div class="sk-cube3 sk-cube"></div> </div></div>
</section>
