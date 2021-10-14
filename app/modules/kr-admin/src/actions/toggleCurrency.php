<?php

/**
 * Add plan subscription
 *
 * This actions permit to admin to add an plan to krypto
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
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoCoin.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoApi.php";

// Load app modules
$App = new App(true);
$App->_loadModulesControllers();

try {

    // Check loggin & permission
    $User = new User();
    if (!$User->_isLogged()) {
        throw new Exception("Your are not logged");
    }
    if (!$User->_isAdmin()) {
        throw new Exception("Error : Permission denied");
    }

    if($App->_isDemoMode()) throw new Exception("App currently in demo mode", 1);

    if(empty($_POST) || !isset($_POST['symbol']) || empty($_POST['symbol'])) throw new Exception("Error : Invalid args", 1);


    $CryptoApi = new CryptoApi($User, null, $App);
    $CryptoCoin = new CryptoCoin($CryptoApi, $_POST['symbol'], null, $App);

    $CryptoCoin->_toggleActive();

    // Return success message
    die(json_encode([
      'error' => 0,
      'msg' => 'Done',
      'title' => 'Success'
    ]));

} catch (\Exception $e) { // If throw exception, return error message
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}
