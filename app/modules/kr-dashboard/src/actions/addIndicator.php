<?php

/**
 * Add indicator to graph action
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

    // Check args given
    if (empty($_POST) || empty($_POST['chart']) || empty($_POST['indic']) || empty($_POST['key']) || empty($_POST['title'])) {
        throw new Exception("Error : Empty post", 1);
    }

    // Get container
    $container = $_POST['chart'];

    // List indicator available & check given if available
    $listIndicatorAvailable = CryptoIndicators::_getIndicatorsList();
    if (!array_key_exists($_POST['indic'], $listIndicatorAvailable)) {
        throw new Exception("Error : Invalid indicator", 1);
    }

    // Get infos indicator
    $infosIndicator = CryptoIndicators::_getIndicatorsList()[$_POST['indic']];

    // Load CryptoIndicators object associate to the graph
    $CryptoIndicator = new CryptoIndicators($container);

    // Add new indicator
    $CryptoIndicator->_addIndicator($_POST['indic'], $_POST['key'], $_POST['title']);

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
