<?php

/**
 * Charge plan selected view
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

  // Load app module
  $App = new App(true);
  $App->_loadModulesControllers();

  // Check if user is logged
  $User = new User();
  if(!$User->_isLogged()) die('User not logged');

  // Init lang object
  $Lang = new Lang($User->_getLang(), $App);

  // Get user charge object
  $Charge = $User->_getCharge($App);

  // Check if plan is given
  if(empty($_POST) || !isset($_POST['plan'])) throw new Exception("Error : Args missing", 1);

  // Check plan validity
  $listPlan = $Charge->_getChargesPlanList();
  if(!array_key_exists($_POST['plan'], $listPlan)) $ChargePlan = $listPlan[array_keys($listPlan)[0]];
  else $ChargePlan = $listPlan[$_POST['plan']];

  // Save plan selected in session
  $_SESSION['kr_plan_selected'] = $_POST['plan'];

  // Get discount plan
  $DiscountPlan = $ChargePlan->_getDiscountPercentage();

  // Check if paypal is available
  $PaypalAvailable = $App->_paypalEnabled();
  if($PaypalAvailable){
    $Paypal = new Paypal($App);
    $PaypalLink = $Paypal->_generateLink($ChargePlan);
  }

  $MollieEnabled = $App->_mollieEnabled();
  if($MollieEnabled){
    $Mollie = new Mollie($App);
    $MolliePayment = $Mollie->_createPayment($User, $ChargePlan);
  }

} catch (Exception $e) {
  die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}



?>
  <section>
    <header>
      <?php echo $App->_getAppTitle().' '.$Lang->tr('will be ready for you !'); ?>
      <div>
        <?php if($Charge->_activeAbo() || $Charge->_isTrial() || $User->_isAdmin()): ?>
          <svg class="lnr lnr-cross"><use xlink:href="#lnr-cross"></use></svg>
        <?php endif; ?>
      </div>
    </header>
    <ul>
      <li>
        <div>
          <span><?php echo $ChargePlan->_getName(); ?></span>
          <div>
            <span><?php echo $ChargePlan->_getPricePerMonth(true).' '.$Charge->_getCurrencySymbol(); ?></span>
            <?php if($DiscountPlan != null): ?>
              <label><?php echo $DiscountPlan.' %'; ?></label>
            <?php endif; ?>
          </div>
          <label><?php echo $Lang->tr('You will be charged').' '.$ChargePlan->_getPrice(true).' '.$Charge->_getCurrencySymbol().' '.$Lang->tr('for').' '.$ChargePlan->_getNumberMonth().' '.($ChargePlan->_getNumberMonth() > 0 ? $Lang->tr('months') : $Lang->tr('month')); ?></label>
        </div>
      </li>
    </ul>
    <section>
      <div>
        <span><?php echo $Lang->tr('Duration'); ?> : <b><?php echo $ChargePlan->_getNumberMonth().' '.($ChargePlan->_getNumberMonth() > 0 ? $Lang->tr('months') : $Lang->tr('month')); ?></b></span>
        <span><?php echo $Lang->tr('Total'); ?> : <b><?php echo $ChargePlan->_getPrice(true).' '.$Charge->_getCurrencySymbol(); ?></b></span>
      </div>
    </section>
    <ul class="kr-ovrley-payment">
      <?php if($PaypalAvailable):

        ?>
        <li>
          <a href="<?php echo $PaypalLink; ?>">
            <?php echo file_get_contents(APP_URL.'/assets/img/icons/payment/paypal.svg'); ?>
          </a>
        </li>
      <?php endif; ?>
      <?php if($App->_creditCardEnabled()): ?>
        <li>
          <a kr-charges-payment="creditcard" kr-charges-selected="<?php echo $ChargePlan->_getPlanID(); ?>">
            <?php echo file_get_contents(APP_URL.'/assets/img/icons/payment/creditcard.svg'); ?>
          </a>
        </li>
      <?php endif; ?>
      <?php if($App->_fortumoEnabled()): ?>
        <li>
          <a class="fortumo-payment-action" fortumodu="<?php echo uniqid(); ?>" fortumod="<?php echo App::encrypt_decrypt('encrypt', $User->_getUserID().'-'.$ChargePlan->_getPlanID()); ?>" href="">
            <img src="https://assets.fortumo.com/fmp/fortumopay_150x50_red.png" alt="Pay with Fortumo">
          </a>
        </li>
      <?php endif; ?>
      <?php if($App->_coingateEnabled()): ?>
      <li>
        <a class="coingate-payment-action" coingatedu="<?php echo uniqid(); ?>" coingaged="<?php echo App::encrypt_decrypt('encrypt', $User->_getUserID().'-'.$ChargePlan->_getPlanID()); ?>" href="">
          <img src="<?php echo APP_URL.'/assets/img/icons/payment/coingate.png'; ?>" alt="Pay with CoinGate">
        </a>
      </li>
      <?php endif; ?>
      <?php if($App->_mollieEnabled()): ?>
        <li>
          <a href="<?php echo $MolliePayment->getPaymentUrl(); ?>">
            <img src="<?php echo APP_URL.'/assets/img/icons/payment/mollie.png'; ?>" alt="Pay with Mollie">
          </a>
        </li>
      <?php endif; ?>
    </ul>
  </section>
