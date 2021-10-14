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

$Trade = new Trade($User, $App);

?>
<section class="kr-account-config-exchange">
  <?php
  foreach ($Trade->_getThirdParty() as $key => $Exchange) {
    ?>
    <div onclick="_showThirdpartySetup('<?php echo $Exchange->_getExchangeName(); ?>', 'nativetradingcfg');" class="<?php if($Exchange->_isActivated() != false) echo 'kr-account-config-exchange-enable'; ?>">
      <div>
        <svg class="lnr lnr-checkmark-circle"><use xlink:href="#lnr-checkmark-circle"></use></svg>
      </div>
      <img src="<?php echo APP_URL; ?>/assets/img/icons/trade/<?php echo $Exchange->_getLogo(); ?>" alt="<?php echo $Exchange->_getName(); ?>">
    </div>
    <?php
  }
  ?>
</section>
