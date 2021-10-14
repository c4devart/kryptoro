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

    if(!empty($_POST)){

      if(!isset($_POST['order_id']) || !isset($_POST['status']) || !isset($_POST['created_at'])) throw new Exception("Wrong arguments", 1);

      $CoinGate = new CoinGate($App);
      $resultParsed = $CoinGate->_parseResultDeposit($_POST);

      $User = $resultParsed['user'];
      $Balance = new Balance($User, $App, 'real');
      if($resultParsed['status'] == 1){
        $Balance->_validDeposit($resultParsed['order_id']);
      }

    } else {

      die("<script>window.close();</script>");
    }

} catch (Exception $e) {
    error_log($e->getMessage());
    die('Error : '.$e->getMessage());
}
