<?php

/**
 * Formtumo result view
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

try {

  // Load app modules
  $App = new App(true);
  $App->_loadModulesControllers();

  // Check if user is logged
  $User = new User();
  if(!$User->_isLogged()) die('User not logged');

  // Init lang object
  $Lang = new Lang($User->_getLang(), $App);

  // Init charge object
  $Charge = new Charges($User, $App);

  // Check args given
  if(empty($_POST) || !isset($_POST['k'])) throw new Exception("Error : Args missing", 1);

  $Mollie = new Mollie($App);

  $titlePopup = 'You are now premium !';
  if(isset($_POST['t']) && $_POST['t'] == "deposit"){
    $ValidPayment = $Mollie->_checkDepositUser($_POST['k'], $User);
    $titlePopup = 'Deposit success !';
  } else {
    $ValidPayment = $Mollie->_checkPaymentUser($_POST['k'], $User);
  }


} catch (Exception $e) {
  die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}

?>
<section class="kr_payment_result <?php if($ValidPayment == false) 'kr_payment_result_fail'; ?>" style="width:389px;">
  <header style="justify-content:center;">
    <img src="<?php echo APP_URL.$App->_getLogoBlackPath(); ?>" alt="<?php echo $App->_getAppTitle(); ?>">
  </header>
  <?php if($ValidPayment): ?>
    <img src="<?php echo APP_URL; ?>/app/modules/kr-user/statics/img/success.svg" alt="">
  <?php else: ?>
    <img src="<?php echo APP_URL; ?>/app/modules/kr-user/statics/img/fail.svg" alt="">
  <?php endif; ?>
  <h3 style="text-align:center;<?php if(!$ValidPayment) echo 'color:red;'; ?>"><?php echo ($ValidPayment ? $Lang->tr($titlePopup) : $Lang->tr('Your payment failed !')); ?></h3>
  <div class="kr_payment_result_support">
    <label><?php echo $Lang->tr('You can contact the support'); ?></label>
    <span><?php echo $App->_getSupportEmail(); ?></span>
  </div>
  <?php if($ValidPayment): ?>
    <?php if(!isset($_POST['t']) && $_POST['t'] != "deposit"): ?>
      <div class="kr_payment_result_txt"><?php echo $App->_getPaymentResultDone(); ?></div>
    <?php endif; ?>
  <?php endif; ?>
  <footer>
    <div></div>
    <div>
      <?php if($ValidPayment == false): ?>
        <a class="btn-shadow btn-orange" onclick="showChargePopup('plan');"><?php echo $Lang->tr('Retry'); ?></a>
      <?php else: ?>
        <a class="btn-shadow btn-orange" href="<?php echo APP_URL; ?>/dashboard.php"><?php echo $Lang->tr("Let's go !"); ?></a>
      <?php endif; ?>
    </div>
  </footer>
</section>
