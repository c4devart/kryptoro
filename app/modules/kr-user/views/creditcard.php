<?php

/**
 * Credit card user view
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

  // Get charge object
  $Charge = $User->_getCharge($App);

  // Check if plan is given
  if(empty($_POST) || !isset($_POST['plan'])) throw new Exception("Error : Args missing", 1);

  // Check plan validity
  $listPlan = $Charge->_getChargesPlanList();
  if(!array_key_exists($_POST['plan'], $listPlan)) $ChargePlan = $listPlan[array_keys($listPlan)[0]];
  else $ChargePlan = $listPlan[$_POST['plan']];

  // Get discount plan
  $DiscountPlan = $ChargePlan->_getDiscountPercentage();

  // Create credit card object
  $CreditCard = new CreditCard($App, $User);

} catch (Exception $e) {
  die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}


?>
<section style="width:389px;">
  <form action="<?php echo APP_URL; ?>/app/modules/kr-payment/src/actions/processPaymentCard.php" method="post" class="kr-charges-creditcard">
    <header>
      <?php echo file_get_contents(APP_URL.'/assets/img/icons/payment/creditcard.svg'); ?>
    </header>
    <div>
      <div>
        <label><?php echo $Lang->tr('Cardholder name'); ?></label>
        <div>
          <input type="text" name="kr_charges_cardholdername" placeholder="John Doe">
        </div>
      </div>
    </div>
    <div>
      <div>
        <label><?php echo $Lang->tr('Card number'); ?></label>
        <div>
          <input type="text" class="kr-payment-cardnumber" value="" name="kr_charges_cardnumber" placeholder="&middot;&middot;&middot;&middot; &middot;&middot;&middot;&middot; &middot;&middot;&middot;&middot; &middot;&middot;&middot;&middot;">
        </div>
      </div>
    </div>
    <div>
      <div>
        <label><?php echo $Lang->tr('Expiration'); ?></label>
        <div>
          <select class="" name="kr_charges_expirationmonth" placeholder="Month">
            <?php foreach ($CreditCard->_getCreditCardExpiration()['m'] as $monthNum => $monthName) {
              ?>
              <option value="<?php echo $monthNum; ?>"><?php echo ($monthNum < 10 ? '0'.$monthNum : $monthNum).' - '.$monthName; ?></option>
              <?php
            } ?>
          </select>
          <select class="" name="kr_charges_expirationyear" placeholder="Year">
            <?php foreach ($CreditCard->_getCreditCardExpiration()['y'] as $year) {
              ?>
              <option selected value="<?php echo $year; ?>"><?php echo $year; ?></option>
              <?php
            } ?>
          </select>
        </div>
      </div>
      <div class="kr-charges-ccv">
        <label><?php echo $Lang->tr('CCV'); ?></label>
        <div>
          <input type="text" value="" name="kr_charges_ccv" placeholder="&middot;&middot;&middot;">
        </div>
      </div>
    </div>
    <section class="kr-msg kr-msg-error" style="display:none;"></section>
    <section class="kr-payment-act">
      <div>
        <a class="kr-payment-back"><svg class="lnr lnr-chevron-left"><use xlink:href="#lnr-chevron-left"></use></svg> <?php echo $Lang->tr('Back'); ?></a>
      </div>
      <div>
        <input type="hidden" name="kr_charges_plan" value="<?php echo $ChargePlan->_getPlanID(); ?>">
        <input type="submit" class="btn-shadow btn-orange" name="" value="<?php echo $Lang->tr('Validate'); ?>">
      </div>
    </section>
    <footer>
      <?php echo file_get_contents(APP_URL.'/assets/img/icons/payment/stripe.svg'); ?>
    </footer>
  </form>
</section>
