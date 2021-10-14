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
<form name="pg_frm" method="post" action="https://www.paygol.com/pay" >
   <input type="hidden" name="pg_serviceid" value="">
   <input type="hidden" name="pg_currency" value="EUR">
   <input type="hidden" name="pg_name" value="DEMO">
   <input type="hidden" name="pg_custom" value="">
   <input type="hidden" name="pg_price" value="1">
   <input type="hidden" name="pg_return_url" value="">
   <input type="hidden" name="pg_cancel_url" value="">
   <input type="image" name="pg_button" src="https://www.paygol.com/pay-now/images/payment-button.png" border="0" alt="Make payments with Paygol: the easiest way!" title="Make payments with Paygol: the easiest way!" >
</form>
