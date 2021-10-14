<?php

/**
 * Charge list plan
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

$UserLogged = $User;
$adminView = false;
if(($User->_isAdmin() || $User->_isManager()) && isset($_SESSION['kr_account_view_user']) && !empty($_SESSION['kr_account_view_user']) && $_SESSION['kr_account_view_user'] != $User->_getUserID()){
  $User = new User($_SESSION['kr_account_view_user']);
  $adminView = true;
}

if(($User->_isAdmin() || $User->_isManager()) && !$UserLogged->_isAdmin()){
  $User = $UserLogged;
  $adminView = false;
}

// Init CryptoApi object
$CryptoApi = new CryptoApi(null, null, $App);

// Init Dashboard object
$Dashboard = new Dashboard($CryptoApi, $User);

$Charge = $User->_getCharge($App);

?>
<form class="kr-user-update" action="<?php echo APP_URL; ?>/app/modules/kr-user/src/actions/updateUserprofile.php" method="post">

  <?php if($adminView): ?>
    <div class="kr-user-f-l">
      <?php if($UserLogged->_isAdmin()): ?>
        <div>
          <label><?php echo $Lang->tr('User level'); ?></label>
          <select class="" name="kr-user-adminlevel">
            <option <?php echo (!$User->_isAdmin() && !$User->_isManager() ? 'selected="selected"' : ''); ?> value="0">Standard user</option>
            <option <?php echo ($User->_isManager() && !$User->_isAdmin() ? 'selected="selected"' : ''); ?> value="2">Manager</option>
            <option <?php echo ($User->_isAdmin() ? 'selected="selected"' : ''); ?> value="1">Admin user</option>
          </select>
        </div>
      <?php endif; ?>
      <div>
        <label><?php echo $Lang->tr('Status'); ?></label>
        <select class="" name="kr-user-userstatus">
          <option <?php echo ($User->_isActive() ? 'selected="selected"' : ''); ?> value="1">Active</option>
          <option <?php echo (!$User->_isActive() ? 'selected="selected"' : ''); ?> value="0">Desactive</option>
        </select>
      </div>
    </div>
    <div class="kr-user-f-l">
      <div>
        <label><?php echo $Lang->tr('Subscription'); ?></label>
        <select class="" name="kr-user-adminuserpremium">
          <option <?php if($Charge->_activeAbo()) echo 'disabled="disabled"';
                        if($Charge->_isTrial() || !$Charge->_activeAbo()) echo 'selected="selected"'; ?> value="free">Free trial</option>
          <option <?php if($Charge->_activeAbo()) echo 'selected="selected"'; ?> value="premium">Premium</option>
        </select>
      </div>
      <div>
        <label><?php echo $Lang->tr('Expire in'); ?></label>
        <?php
        $date = $Charge->_getTimestampTrialEnd();
        if($Charge->_activeAbo()) $date = $Charge->_getTimestampChargeEnd();
        ?>
        <input type="datetime-local" value="<?php echo str_replace(' ', 'T', date('Y-m-d H:i', $date)); ?>" name="kr-user-adminuserpremiumexpiration">
      </div>
    </div>
  <?php endif; ?>

  <div class="kr-user-f-l">
    <div>
      <label><?php echo $Lang->tr('Your name'); ?></label>
      <input type="text" name="kr-user-name" placeholder="<?php echo $Lang->tr('Your name'); ?>" value="<?php echo $User->_getName(); ?>">
    </div>
    <div>
      <label><?php echo $Lang->tr('Your e-mail address'); ?></label>
      <input type="text" <?php if($User->_getOauth() != 'standard') echo 'disabled'; ?> placeholder="<?php echo $Lang->tr('Your e-mail address'); ?>" name="kr-user-email" value="<?php echo $User->_getEmail(); ?>">
    </div>
  </div>
  <div class="kr-user-f-l">
    <div>
      <label><?php echo $Lang->tr('Language'); ?></label>
      <select class="" name="kr-user-language">
        <?php
        $LngUser = $Lang->getLang();
        if($adminView) $LngUser = $User->_getLang();
        foreach ($Lang->getListLanguage($_SERVER['DOCUMENT_ROOT'].FILE_PATH.'/') as $filename => $language) {
          echo '<option value="'.$filename.'" '.($LngUser == $filename ? 'selected="selected"' : '').'>'.$language.'</option>';
        }
        ?>
      </select>
    </div>
    <div>
      <label><?php echo $Lang->tr('Currency'); ?></label>
      <select class="" name="kr-user-currency">
        <?php
        foreach ($Dashboard->_getListCurrency(500) as $dataCurrency) {
          ?>
          <option <?php if($CryptoApi->_getCurrency() == $dataCurrency['code_iso_currency']) echo 'selected'; ?> value="<?php echo $dataCurrency['code_iso_currency']; ?>"><?php echo $dataCurrency['name_currency'].' ('.$dataCurrency['code_iso_currency'].')'; ?></option>
          <?php
        }
        ?>
      </select>
    </div>
  </div>
  <?php if($App->_allowSwitchChart()): ?>
    <div class="kr-user-f-l">
      <div>
        <label><?php echo $Lang->tr('Type chart'); ?></label>
        <select class="" name="kr-user-typechart">
          <option value="default">Default</option>
          <option <?php echo ($User->_tradingviewChartLibraryUse() ? 'selected' : ''); ?> value="tradingview">Tradingview</option>
        </select>
      </div>
      <div>

      </div>
    </div>
  <?php endif; ?>

  <?php if($User->_getOauth() == "standard"): ?>
    <div class="kr-user-f-l">
      <div>
        <label><?php echo $Lang->tr('Change your password'); ?></label>
        <input type="password" name="kr-user-pwd" value="">
      </div>
      <div>
        <label><?php echo $Lang->tr('Repeat your password'); ?></label>
        <input type="password" name="kr-user-pwd-repeat" value="">
      </div>
    </div>
  <?php endif; ?>

  <?php if($App->_referalEnabled()): ?>
    <div class="kr-user-f-l">
      <div>
        <label><?php echo $Lang->tr('Referal link'); ?></label>
        <input type="text" readonly value="<?php echo APP_URL; ?>/?ref=<?php echo $User->_getReferalUrl(); ?>">
      </div>
    </div>
  <?php endif; ?>

  <footer>
    <a class="kr-user-back"><svg class="lnr lnr-chevron-left"><use xlink:href="#lnr-chevron-left"></use></svg> <?php echo $Lang->tr('Back'); ?></a>
    <input type="hidden" name="kr_prof_u" value="<?php echo $User->_getUserID(true); ?>">
    <input type="submit" class="btn-shadow btn-orange" name="" value="<?php echo $Lang->tr('Validate'); ?>">
  </footer>
</form>
