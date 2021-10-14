<?php

/**
 * Process payment credit card action
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

    // Check if user is logged
    $User = new User();
    if (!$User->_isLogged()) {
        throw new Exception("User not logged", 1);
    }

    // Check args given
    $argsNeed = ['type', 'amount'];
    $argsNotFound = [];
    foreach ($argsNeed as $arg) {
        if (empty($_POST) || empty($_POST[$arg])) {
            $argsNotFound[] = $arg;
        }
    }

    if (count($argsNotFound) > 0) {
        die(json_encode([
          'error' => 2,
          'fields' => $argsNotFound
        ]));
    }

    $amount_deposit = $_POST['amount'];
    $currency_deposit = $_POST['currency'];

    if(!is_numeric($amount_deposit)) throw new Exception("Error : Invalid amount", 1);
    if($amount_deposit < $App->_getMinimalDeposit() || $amount_deposit > $App->_getMaximalDeposit()) throw new Exception("Error : Invalid amount", 1);

    $amount_deposit_wfees = $amount_deposit;
    if($App->_getFeesDeposit() > 0){
      $amount_deposit_wfees = $amount_deposit + ($App->_getFeesDeposit() / 100);
    }

    if($_POST['type'] == "paypal"){

      $Paypal = new Paypal($App);
      $PaypalLink = $Paypal->_generateDepositLink($amount_deposit);

      die(json_encode([
        'error' => 0,
        'link' => $PaypalLink
      ]));

    } elseif ($_POST['type'] == "mollie") {


      $MollieEnabled = $App->_mollieEnabled();
      if($MollieEnabled){


        $Mollie = new Mollie($App);
        $MolliePayment = $Mollie->_createDeposit($User, $amount_deposit, $currency_deposit);

        die(json_encode([
          'error' => 0,
          'link' => $MolliePayment->getCheckoutUrl()
        ]));

      } else {
        throw new Exception("Error : Mollie not enabled", 1);

      }

    } else {
      die(json_encode([
        'error' => 1,
        'msg' => 'Wrong payment gateway'
      ]));
    }



} catch (Exception $e) {
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}
