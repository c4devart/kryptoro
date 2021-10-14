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

$App = new App(true);
$App->_loadModulesControllers();
$Paystack = new Paystack($App);
try {

  $Paystack->_callBack();

  die("<script>window.close();</script>");

} catch (Exception $e) {

  error_log(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));

  die("<script>window.close();</script>");

}


?>
