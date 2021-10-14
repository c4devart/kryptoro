<?php

/**
 * Change graph action
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
    if (!$User->_isLogged()) {
        throw new Exception("User is not logged", 1);
    }

    // Check args given
    if (empty($_POST) || empty($_POST['symbol'])) {
        throw new Exception("Error : Empty post", 1);
    }

    // Init CryptoApi object
    $CryptoApi = new CryptoApi(null, null, $App);

    // Init dashboard object
    $Dashboard = new Dashboard($CryptoApi, $User);

    // Update graph
    $DashboardTopList = new DashboardTopList($CryptoApi, $User);




    $DashboardTopListItem = new DashboardTopList($CryptoApi, $User, $DashboardTopList->_addItem($_POST['symbol'], $_POST['currency'], $_POST['market']));

    if(isset($_POST['container']) && !empty($_POST['container'])) $DashboardTopListItem->_changeContainer($_POST['container']);

    die(json_encode([
      'error' => 0,
      'item_id' => $DashboardTopListItem->_getItemID(),
      'coin_infos' => [
        'symbol' => $_POST['symbol'],
        'currency' => $_POST['currency'],
        'market' => $_POST['market']
      ]
    ]));

} catch (\Exception $e) {
  die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}
