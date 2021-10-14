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

    if(!empty($_POST)){

      if(!isset($_POST['order_id']) || !isset($_POST['status']) || !isset($_POST['created_at'])) throw new Exception("Wrong arguments", 1);

      if($_POST['status'] == "pending") die('Order in pending : '.$_POST['order_id']);

      $CoinGate = new CoinGate($App);
      $resultParsed = $CoinGate->_parseResult($_POST);

      $User = new User($resultParsed['user']);

      $Charge = $User->_getCharge($App);

      $Charge->_validateCharge($_POST['id'],
                               $resultParsed['status'],
                               $resultParsed['plan'],
                              'coingate',
                              json_encode($_POST));

    } else {
      die("<script>window.close();</script>");
    }

} catch (Exception $e) {
    die('Error : '.$e->getMessage());
}
