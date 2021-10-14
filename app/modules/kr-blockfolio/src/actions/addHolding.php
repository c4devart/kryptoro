<?php

/**
 * Add blockfolio item
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
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoIndicators.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoGraph.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoHisto.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoCoin.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoApi.php";

try {

    // Load app modules
    $App = new App(true);
    $App->_loadModulesControllers();

    // Check if user is logged
    $User = new User();
    if (!$User->_isLogged()) {
        throw new Exception("User is not logged", 1);
    }

    $CryptoApi = new CryptoApi($User, null, $App);

    $BlockFolio = new Blockfolio($User);
    $BlockFolio->_addHolding($_POST['trading_symbol'], $_POST['type_trading'], $_POST['trading_price'], $_POST['quantity'], $_POST['trading_date']);

    var_dump($_POST);


    die(json_encode([
      'error' => 0,
      'msg' => 'Done !'
    ]));

} catch (\Exception $e) {
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}
