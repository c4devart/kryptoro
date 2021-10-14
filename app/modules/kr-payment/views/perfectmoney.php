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

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoApi.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoCoin.php";

try {

  // Load app module
  $App = new App(true);
  $App->_loadModulesControllers();

  // Check if user is logged
  $User = new User();
  if(!$User->_isLogged()) die('User not logged');

  if(empty($_GET) || !isset($_GET['m']) && !isset($_GET['t'])) throw new Exception("Error : Wrong args", 1);
  $amount = floatval($_GET['m']);
  $Balance = new Balance($User, $App, 'real');

  $PerfectMoney = new PerfectMoney($App);
  $DepositRef = $PerfectMoney->_createDeposit($User, $amount, $Balance, $_GET['cr']);

  var_dump($DepositRef);

} catch (Exception $e) {
  die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
} finally {
  ?>

  <?php
}

?>
<form action="https://perfectmoney.is/api/step1.asp" method="POST">
<input type="hidden" name="PAYEE_ACCOUNT" value="<?php echo $App->_getPerfectMoneyPayeeAccount(); ?>">
<input type="hidden" name="PAYEE_NAME" value="<?php echo $App->_getPerfectMoneyPayeeName(); ?>">
<input type="hidden" name="PAYMENT_ID" value="<?php echo $DepositRef; ?>">
<input type="hidden" name="PAYMENT_AMOUNT" value="<?php echo $amount; ?>">
<input type="hidden" name="PAYMENT_UNITS" value="<?php echo $_GET['cr']; ?>">
<input type="hidden" name="STATUS_URL" value="<?php echo APP_URL; ?>/app/modules/kr-payment/src/actions/deposit/processPerfectMoney.php">
<input type="hidden" name="PAYMENT_URL" value="https://krypto.dev.ovrley.com/app/modules/kr-payment/src/actions/test.php">
<input type="hidden" name="PAYMENT_URL_METHOD" value="LINK">
<input type="hidden" name="NOPAYMENT_URL" value="https://krypto.dev.ovrley.com/app/modules/kr-payment/src/actions/test.php">
<input type="hidden" name="NOPAYMENT_URL_METHOD" value="LINK">
<input type="hidden" name="SUGGESTED_MEMO" value="">
<input type="hidden" name="BAGGAGE_FIELDS" value="">
<input type="submit" name="PAYMENT_METHOD" value="Pay Now!">
</form>
