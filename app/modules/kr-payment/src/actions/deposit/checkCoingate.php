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

    // Check if user is logged
    $User = new User();
    if (!$User->_isLogged()) {
        throw new Exception("User not logged", 1);
    }

    if(empty($_GET) || !isset($_GET['t']) || empty($_GET['t']) || $_GET['t'] > time()) throw new Exception("Permission denied", 1);


    $CoinGate = new CoinGate($App);
    $result = $CoinGate->_checkDeposit($User, $_GET['t']);
    die(json_encode([
      'error' => 0,
      'status' => $result
    ]));

} catch (Exception $e) {
    die([
      'error' => 1,
      'msg' => $e->getMessage()
    ]);
}
