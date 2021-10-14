<?php

/**
 * Get WatchingList item
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */

session_start();

require "../../../../../config/config.settings.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/vendor/autoload.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/MySQL/MySQL.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/User/User.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/App.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/AppModule.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoIndicators.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoGraph.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoHisto.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoCoin.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoApi.php";

// Load app modules
$App = new App(true);
$App->_loadModulesControllers();

try {

  // Check if user is logged
  $User = new User();
  if(!$User->_isLogged()) throw new Exception("User are not logged", 1);

  // Init CryptoApi object
  $CryptoApi = new CryptoApi(null, null, $App);

  // Init WatchingList object
  $WatchingList = new WatchingList($CryptoApi, $User);

  // Show in json list item
  die(json_encode([
    'error' => 0,
    'item' => $WatchingList->_getListCoins()
  ]));

} catch (Exception $e) { // If error throw, show error
  die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}

?>
