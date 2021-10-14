<?php

/**
 * Check notification CRON script
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */

session_start();

require "../../../../config/config.settings.php";
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

$listSymbolFetched = [];

// Init CryptoNotification object as global
$CryptoNotification = new CryptoNotification(null, null, null, "global");
foreach ($CryptoNotification->_admFetchAllNotifications() as $Notification) {
  // Init user object for notification
  try {
    $User = new User($Notification->_getAttribuateUserNotification());
    // Init CryptoApi
    $CryptoApi = new CryptoApi($User, [$Notification->_getCurrency(), $Notification->_getCurrency()], $App, $Notification->_getMarket());
    // Set notification user
    $Notification->_setUser($User);

    // Check symbol cache
    if(!array_key_exists($Notification->_getSymbol(), $listSymbolFetched)){
      // Fetch coin data
      $Coin = new CryptoCoin($CryptoApi, $Notification->_getSymbol());
      // Save in cache
      $listSymbolFetched[$Notification->_getSymbol()]['coin'] = $Coin;
      $listSymbolFetched[$Notification->_getSymbol()]['price'] = $Coin->_getPrice();
    }

    // Check if notification need
    if($Notification->_notificationNeeded($listSymbolFetched[$Notification->_getSymbol()]['price'])){
      // Send notification
      $Notification->_sendNotification($listSymbolFetched[$Notification->_getSymbol()]['coin'], $listSymbolFetched[$Notification->_getSymbol()]['price']);
    }
  } catch (\Exception $e) {
    error_log('Check Notification error : '.$e->getMessage());
    continue;
  }


}

$App->_saveCronStatus('app/src/CryptoApi/actions/CheckNotification.php');



?>
