<?php

/**
 * Add graph notification
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
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoNotification.php";
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
    if (!$User->_isLogged()) {
        throw new Exception("Error : User is not logged", 1);
    }

    // Init CryptoApi object
    $CryptoApi = new CryptoApi(null, [$_POST['currency'], $_POST['currency']], $App, $_POST['market']);

    // Get CryptoCoin associate to the graph
    $CrypoCoin = new CryptoCoin($CryptoApi, $_POST['symb'], null, $_POST['market']);

    // Init CryptoNotification graph
    $CryptoNotification = new CryptoNotification($_POST['symb'], $_POST['currency'], $_POST['market'], $User);

    // Create new notification
    $CryptoNotification->_createNotification(($_POST['value'] > 10 ? round($_POST['value'], 2) : $_POST['value']), $_POST['currency'], $_POST['market'], $CrypoCoin->_getPrice());

    die(json_encode([
      'error' => 0,
      'msg' => 'Done'
    ]));

} catch (\Exception $e) {
  die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}
