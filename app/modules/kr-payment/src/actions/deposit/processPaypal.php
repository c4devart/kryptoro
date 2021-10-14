<?php

/**
 * Process payment paypal action
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

    // Init paypal object
    $Paypal = new Paypal($App);

    $paymentResult = $Paypal->_checkDepositPayment();

    $Balance = new Balance($User, $App);

    $Balance->_validateDeposit($paymentResult->getId(),
                           $Paypal->_getStatus(),
                           $_SESSION['kr_deposit_amount']['amount'],
                          'paypal',
                          json_encode($paymentResult->toArray()),
                          $_SESSION['kr_deposit_amount']['fees']);

    header('Location: '.APP_URL.'/dashboard.php?v='.$paymentResult->getId().'&c=paypal&t='.time());

} catch (Exception $e) {
    header('Location: '.APP_URL.'/dashboard.php?c=paypal&m='.base64_encode($e->getMessage()).'&t='.time());
}
