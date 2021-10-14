<?php

/**
 * Process payment Fortumo
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


    $Fortumo = new Fortumo($App);
    $Fortumo->_testCallback($_GET);

    if($Fortumo->_parseResult($_GET)){

      $dataCUID = explode('-', $_GET['cuid']);
      if(count($dataCUID) != 2) throw new Exception("Error : Fail parse result data", 1);
      $dataOrder = explode('-', App::encrypt_decrypt('decrypt', $dataCUID[0]));
      if(count($dataOrder) != 2) throw new Exception("Error : Fail parse result data", 1);

      $User = new User($dataOrder[0]);
      $Charge = $User->_getCharge($App);

      $Charge->_validateCharge($_GET['payment_id'],
                               1,
                               new ChargesPlan($dataOrder[1]),
                               'fortumo',
                               json_encode($_GET));


    } else {

    }


} catch (Exception $e) {
  error_log(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}


?>
