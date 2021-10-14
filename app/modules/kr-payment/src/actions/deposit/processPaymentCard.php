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
    $argsNeed = ['kr_charges_cardholdername', 'kr_charges_cardnumber', 'kr_charges_expirationmonth', 'kr_charges_expirationyear', 'kr_charges_ccv', 'kr_charge_amount'];
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

    $amount_deposit = App::encrypt_decrypt('decrypt', $_POST['kr_charge_amount']);

    if(!is_numeric($amount_deposit)) throw new Exception("Error : Invalid amount", 1);
    if($amount_deposit < $App->_getMinimalDeposit() || $amount_deposit > $App->_getMaximalDeposit()) throw new Exception("Error : Invalid amount", 1);

    $amount_deposit_wfees = $amount_deposit;
    if($App->_getFeesDeposit() > 0){
      $amount_deposit_wfees = $amount_deposit + ($App->_getFeesDeposit() / 100);
    }

    \Stripe\Stripe::setApiKey($App->_getPrivateStripeKey());
    $CreditCard = new CreditCard($App, $User);

    $CreditCard->_initCreditCardPayment($_POST['kr_charges_cardholdername'],
                                   $_POST['kr_charges_cardnumber'],
                                   $_POST['kr_charges_expirationmonth'],
                                   $_POST['kr_charges_expirationyear'],
                                   $_POST['kr_charges_ccv'],
                                   $amount_deposit_wfees, 'deposit');

    $dataPayment = $CreditCard->_processPayment();

    $Balance = new Balance($User, $App);

    $Balance->_validateDeposit($dataPayment->id,
                           $CreditCard->_getStatus(),
                           $amount_deposit,
                          'creditcard',
                          json_encode($dataPayment),
                          $amount_deposit_wfees - $amount_deposit);

    // Get charge user
    die(json_encode([
        'error' => 0,
        'charge_id' => $dataPayment->id,
        'type' => 'creditcard',
        'time' => time(),
        'key' => md5($dataPayment->id)
      ]));


} catch (Exception $e) {
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}
