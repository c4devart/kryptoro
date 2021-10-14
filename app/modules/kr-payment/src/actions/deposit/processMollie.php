<?php

/**
 * Process payment Fortumo
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */

session_start();

require "../../../../../../config/config.settings.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/vendor/autoload.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/MySQL/MySQL.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/App.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/AppModule.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/User/User.php";

try {

    // Load app modules
    $App = new App(true);
    $App->_loadModulesControllers();

    $Mollie = new Mollie($App);
    $paymentCheck = $Mollie->_checkPayment($_POST["id"]);

    $statusPayment = 0;
    if($paymentCheck) {
      $statusPayment = 1;
    } else {
      error_log('Mollie payment : Order ('.$_POST['id'].') not valid');
    }

    $User = new User($paymentCheck['user_id']);

    $Balance = new Balance($User, $App, 'real');
    error_log(json_encode($paymentCheck));
    $Balance->_addDeposit($paymentCheck['amount'],
                          'mollie',
                          'Mollie deposit',
                          $paymentCheck['currency'],
                          json_encode($paymentCheck),
                          $statusPayment);


} catch (Exception $e) {
  error_log(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}


?>
