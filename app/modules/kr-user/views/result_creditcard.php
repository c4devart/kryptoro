<?php

/**
 * Credit card result view
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

  // Check if args is given
  if(empty($_POST) || !isset($_POST['k'])) throw new Exception("Error : Args missing", 1);

  // Load stripe object
  \Stripe\Stripe::setApiKey($App->_getPrivateStripeKey());

  // Init credit card object
  $CreditCard = new CreditCard($App, $User, $_POST['k']);

} catch (Exception $e) {
  die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}

?>
<section class="kr_payment_result <?php if($CreditCard->_getStatus() == 0) 'kr_payment_result_fail'; ?>" style="width:389px;">
  <header style="justify-content:center;">
    <img src="<?php echo APP_URL.$App->_getLogoBlackPath(); ?>" alt="<?php echo $App->_getAppTitle(); ?>">
  </header>
  <img src="<?php echo APP_URL; ?>/app/modules/kr-user/statics/img/success.svg" alt="">
  <h3 style="text-align:center;"><?php echo ($CreditCard->_getStatus() == '1' ? $Lang->tr('You are now premium !') : $Lang->tr('Your payment failed !')); ?></h3>
  <div class="kr_payment_result_support">
    <label><?php echo $Lang->tr('You can contact the support'); ?></label>
    <span><?php echo $App->_getSupportEmail(); ?></span>
  </div>
  <div class="kr_payment_result_txt"><?php echo $App->_getPaymentResultDone(); ?></div>
  <footer>
    <div></div>
    <div>
      <?php if($CreditCard->_getStatus() == 0): ?>
        <a class="btn-shadow btn-orange"><?php echo $Lang->tr('Retry'); ?></a>
      <?php else: ?>
        <a class="btn-shadow btn-orange" onclick="closeEditIndicator();"><?php echo $Lang->tr("Let's go !"); ?></a>
      <?php endif; ?>
    </div>
  </footer>
</section>
