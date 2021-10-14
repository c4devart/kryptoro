<?php

/**
 * Process payment paypal action
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

    // Get user charge
    $Charge = $User->_getCharge($App);

    // Init paypal object
    $Paypal = new Paypal($App);

    // Get list plan available & check current plan is available
    $listPlan = $Charge->_getChargesPlanList();
    if (!array_key_exists($_SESSION['kr_plan_selected'], $listPlan)) {
        throw new Exception("Error Invalid plan", 1);
    } else {
        $ChargePlan = $listPlan[$_SESSION['kr_plan_selected']];
    }

    // Check paypal payment
    $dataPayment = $Paypal->_checkPayment($ChargePlan);

    // Valid charge
    $Charge->_validateCharge(
      $dataPayment->getId(),
                           $Paypal->_getStatus(),
                           $ChargePlan,
                          'paypal',
                          '{}'
  );

    // Redirect user
    header('Location: '.APP_URL.'/dashboard.php?c=paypal&k='.md5($dataPayment->getId()).'&t='.time());
} catch (Exception $e) {
    header('Location: '.APP_URL.'/dashboard.php?c=paypal&m='.base64_encode($e->getMessage()).'&t='.time());
}
