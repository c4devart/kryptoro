<?php

/**
 * Process payment credit card action
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
    $argsNeed = ['kr_charges_cardholdername', 'kr_charges_cardnumber', 'kr_charges_expirationmonth', 'kr_charges_expirationyear', 'kr_charges_ccv', 'kr_charges_plan'];
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

    // Get charge user
    $Charge = $User->_getCharge($App);

    // Get list plan available & check plan validity
    $listPlan = $Charge->_getChargesPlanList();
    $ChargePlan = null;
    if (!array_key_exists($_POST['kr_charges_plan'], $listPlan)) {
        throw new Exception("Error : Fail to featch plan", 1);
    } else {
        $ChargePlan = $listPlan[$_POST['kr_charges_plan']];
    }

    // Define stripe auth
    \Stripe\Stripe::setApiKey($App->_getPrivateStripeKey());

    // Init credit card object
    $CreditCard = new CreditCard($App, $User);

    // Init credit card payment
    $CreditCard->_initCreditCardPayment($_POST['kr_charges_cardholdername'],
                                   $_POST['kr_charges_cardnumber'],
                                   $_POST['kr_charges_expirationmonth'],
                                   $_POST['kr_charges_expirationyear'],
                                   $_POST['kr_charges_ccv'],
                                   $ChargePlan);

    // Make payment process
    $dataPayment = $CreditCard->_processPayment();

    // Validate charge returned
    $Charge->_validateCharge($dataPayment->id,
                           $CreditCard->_getStatus(),
                           $ChargePlan,
                          'creditcard',
                          json_encode($dataPayment));

    // Return payment data
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
