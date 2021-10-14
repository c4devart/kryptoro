<?php

/**
 * Get coin list action
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

// Check if user is logged
$User = new User();
if (!$User->_isLogged()) {
    throw new Exception("User is not logged", 1);
}

$CryptoApi = new CryptoApi(null, null, $App);

$listCoinFound = [];

// If coin list need is currency
if (!empty($_GET['t']) && $_GET['t'] == "currency") {
    $Dashboard = new Dashboard($CryptoApi, $User);

    foreach ($Dashboard->_getListCurrency(30, (!empty($_GET) && !empty($_GET['q']) ? htmlentities($_GET['q']) : null)) as $dataCurrency) {
      $listCoinFound[$dataCurrency['code_iso_currency']] = [
        'symbol' => $dataCurrency['code_iso_currency'],
        'name' => $dataCurrency['name_currency'],
        'currency' => $CryptoApi->_getCurrency(),
        'icon' => ''
      ];
    }
} else { // Fetch cryptocurrency list
    foreach ($CryptoApi->_getCoinsList(30, true, false, (!empty($_GET) && !empty($_GET['q']) ? htmlentities($_GET['q']) : null), (isset($_GET['s']) ? $_GET['s'] : 0)) as $Coin) {
        $icon = @file_get_contents($Coin->_getIcon());

        $listCoinFound[$Coin->_getSymbol()] = [
          'symbol' => $Coin->_getSymbol(),
          'name' => $Coin->_getCoinName(),
          'currency' => $CryptoApi->_getCurrency(),
          'source' => $Coin->_getCoinSource(),
          'icon' => ($icon != false ? $icon : '')
        ];
    }
}

die(json_encode($listCoinFound));

?>
