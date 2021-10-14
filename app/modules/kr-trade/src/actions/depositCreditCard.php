<?php

/**
 * Credit card deposit
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */

session_start();

require "../../../../../config/config.settings.php";
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

  if(empty($_POST) || !isset($_POST['amount']) || empty($_POST['amount'])) throw new Exception("Permission denied", 1);

  // Init lang object
  $Lang = new Lang($User->_getLang(), $App);

  if(!$App->_hiddenThirdpartyActive()) throw new Exception("Permission denied", 1);

  // Create credit card object
  $CreditCard = new CreditCard($App, $User);

} catch (Exception $e) {
  die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}


?>
<div class="spinner" style="display:none;"></div>
<section>
  <form action="<?php echo APP_URL; ?>/app/modules/kr-payment/src/actions/deposit/processPaymentCard.php" method="post" class="kr-charges-creditcard kr-deposit-creditcard">
    <header>
      <img src="<?php echo APP_URL.'/assets/img/icons/payment/creditcard.svg'; ?>" alt="">
    </header>
    <div>
      <div>
        <label><?php echo $Lang->tr('Cardholder name'); ?></label>
        <div>
          <input type="text" name="kr_charges_cardholdername" value="Leo Dumontier" placeholder="John Doe">
        </div>
      </div>
    </div>
    <div>
      <div>
        <label><?php echo $Lang->tr('Card number'); ?></label>
        <div>
          <input type="text" class="kr-payment-cardnumber" value="4242424242424242" name="kr_charges_cardnumber" placeholder="&middot;&middot;&middot;&middot; &middot;&middot;&middot;&middot; &middot;&middot;&middot;&middot; &middot;&middot;&middot;&middot;">
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
        <a class="kr-payment-back" onclick="_loadCreditForm('depositChooseBalance');"><svg class="lnr lnr-chevron-left"><use xlink:href="#lnr-chevron-left"></use></svg> <?php echo $Lang->tr('Back'); ?></a>
      </div>
      <div>
        <input type="hidden" name="kr_charge_amount" value="<?php echo App::encrypt_decrypt('encrypt', $_POST['amount']); ?>">
        <input type="submit" class="btn btn-green" name="" value="<?php echo $Lang->tr('Validate'); ?>">
      </div>
    </section>
    <footer>
      <?php echo file_get_contents(APP_URL.'/assets/img/icons/payment/stripe.svg'); ?>
    </footer>
  </form>
</section>
