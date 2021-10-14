<?php

/**
 * Load chart data
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

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoOrder.php";
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
    if (!$User->_isLogged() && (!isset($_GET['key']) || $_GET['key'] != '123')) {
        throw new Exception("Error : User is not logged", 1);
    }

    if (!empty($_GET['type']) && $_GET['type'] != "load"){
      if(date('s') > 10) die(json_encode([
        'error' => 3,
        'msg' => 'Update not needed'
      ]));
    }

    if($_GET['market'] == "GDAX") $_GET['market'] = "COINBASE";

    // Init CryptoApi object
    $CryptoApi = new CryptoApi(null, (isset($_GET['currency']) ? [$_GET['currency'], '$'] : null), $App, $_GET['market']);

    // Get coin associate to the graph
    $Coin = $CryptoApi->_getCoin($_GET['coin']);

    // Init graph
    $CryptoGraph = new CryptoGraph($Coin->_getHistoMin(1440));


    $listCandles = null;
    $listNotification = null;
    $listOrder = [];

    if (empty($_GET['type']) || $_GET['type'] == "load") { // Load all graph

        // List graph per hours
        $CryptoGraphHours = new CryptoGraph($Coin->_getHistoHour());
        $listCandles = array_merge($CryptoGraphHours->_getCandles(), $CryptoGraph->_getCandles());

        // Get list graph per days
        $CryptoGraphDays = new CryptoGraph($Coin->_getHistoDay());
        $listCandles = array_merge($CryptoGraphDays->_getCandles(), $listCandles);

        //$listCandles = CryptoGraph::_compressCandle($listCandles, 1);

        $listCandles = array_values($listCandles);

        if($User->_isLogged()){
          // Get crypto notification list
          $CryptoNotification = new CryptoNotification($Coin->_getSymbol(), $CryptoApi->_getCurrency(), $_GET['market'], $User);
          $listNotification = $CryptoNotification->_getListCryptoNotifications();
          $CryptoOrder = new CryptoOrder($Coin);
          $listOrder = $CryptoOrder->_getOrderList($User, $CryptoApi->_getCurrency());
        }

    } else { // Only update graph (only last 5 data will be sent)
        $listCandles = array_values(array_slice($CryptoGraph->_getCandles(), -5));
    }

    $internalOrderList = [];
    if($App->_hiddenThirdpartyActive() && false){

      $Trade = new Trade($User, $App);
      $TraderUser = [];
      foreach ($Trade->_getInternalOrderList($Coin->_getSymbol()) as $key => $orderData) {
        if(!array_key_exists($orderData['id_user'], $TraderUser)) $TraderUser[$orderData['id_user']] = new User($orderData['id_user']);

        $internalOrderList[] = [
          'name' => $TraderUser[$orderData['id_user']]->_getName(),
          'picture' => $TraderUser[$orderData['id_user']]->_getPicture(),
          'type' => $orderData['side_internal_order'],
          'me' => $orderData['id_user'] == $User->_getUserID(),
          'amount' => $orderData['usd_amount_internal_order'],
          'date' => date('d/m/Y H:i', $orderData['date_internal_order']).':00',
          'order_id' => $orderData['id_internal_order']
        ];
      }

    }



    echo json_encode([
      'error' => 0,
      'candles' => $listCandles,
      'current_price' => $Coin->_getPrice(),
      'notification_list' => $listNotification,
      'order_list' => $listOrder,
      'internal_order' => $internalOrderList,
      'currency' => $CryptoApi->_getCurrency()
    ]);

} catch (\Exception $e) {
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}
