<?php

/**
 * Remove WatchingList item
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

  // Check args given to page
  if(empty($_POST) || empty($_POST['symb']) || empty($_POST['currency'])) throw new Exception("Error : Args missing", 1);

  // Init CryptoApi object
  $CryptoApi = new CryptoApi(null, null, $App);

  // Inti WatchingList object
  $WatchingList = new WatchingList($CryptoApi, $User);

  // Remove watching list item
  $WatchingList->_removeItem($_POST['symb'], $_POST['currency'], $_POST['market']);

  die(json_encode([
    'error' => 0,
    'msg' => 'Done'
  ]));

} catch (Exception $e) {
  die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}




?>
