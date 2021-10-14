<?php

/**
 * Remove indicator action
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

// Load app modules
$App = new App(true);
$App->_loadModulesControllers();

try {

    // Check if user is logged
    $User = new User();
    if (!$User->_isLogged()) {
        throw new Exception("User is not logged", 1);
    }

    // Parse args given
    if (empty($_POST) || empty($_POST['chart']) || empty($_POST['indic']) || empty($_POST['key'])) {
        throw new Exception("Error : Empty post", 1);
    }

    // Get container graph
    $container = $_POST['chart'];

    // List indicator available & check if indicator given is available
    $listIndicatorAvailable = CryptoIndicators::_getIndicatorsList();
    if (!array_key_exists($_POST['indic'], $listIndicatorAvailable)) {
        throw new Exception("Error : Invalid indicator", 1);
    }

    // Load crypto indicator for container given
    $CryptoIndicator = new CryptoIndicators($container);

    // Remove indicator
    $CryptoIndicator->_removeIndicator(intVal($_POST['key']) + 1);

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
