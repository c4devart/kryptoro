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
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoApi.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoOrder.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoNotification.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoIndicators.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoGraph.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoHisto.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoCoin.php";


// Load app modules
$App = new App(true);
$App->_loadModulesControllers();

try {

    // Check if user is logged
    $User = new User();
    if (!$User->_isLogged()) {
        throw new Exception("Error : User is not logged", 1);
    }

    $BookList = [];
    $Balance = new Balance($User, $App);
    if(!$App->_hiddenThirdpartyActive()){

      $Trade = new Trade($User, $App);

      $Exchange = $Trade->_getExchange($_GET['market']);

      if(!is_null($Exchange)){
        if($Exchange->_isActivated() === false){

        } else {
          $BookListNF = $Exchange->_getOrderBook($Exchange::_formatPair($_GET['symbol'], $_GET['currency']));
          foreach ($BookListNF as $key => $value) {


            $BookList[$value['id']] = [
              'id' => $value['id'],
              'id_encrypted' => App::encrypt_decrypt('encrypt', time().'-'.$value['id']),
              'symbol' => $value['symbol'],
              'currency' => $value['currency'],
              'date' => (strlen($value['time']) > 10 ? $value['time'] / 1000 : $value['time']),
              'exchange' => strtoupper($Exchange->_getExchangeName()),
              'fees' => $value['fees'],
              'side' => strtoupper($value['side']),
              'usd_amount' => $value['total'],
              'amount' => $value['size'],
              'evolv' => '-'
            ];
          }
        }
      } else {
        $BookList = [];
      }





    } else {

      $CryptoApi = new CryptoApi($User, null, $App);

      $CurrentBalance = $Balance->_getCurrentBalance();

      $BookListNF = $CurrentBalance->_getOrderHistory(null, $_GET['symbol'], $_GET['currency']);
      foreach ($BookListNF as $key => $value) {

        if($value['amount_internal_order'] == 0) $value['amount_internal_order'] = 1;

        if($value['amount_internal_order'] > 0){
          if($value['side_internal_order'] == "BUY"){
            $OrderedPrice = (1 / $value['amount_internal_order']) * $value['usd_amount_internal_order'];
          } else {
            $OrderedPrice = (1 / $value['amount_internal_order']) * $value['usd_amount_internal_order'];
          }
        } else {
          $OrderedPrice = 1;
        }


        $CurrentPrice = $Balance->_convertCurrency(1, $value['symbol_internal_order'], $value['to_internal_order'], $value["thirdparty_internal_order"]);
        if($CurrentPrice == 0) $CurrentPrice = 1;
        if($value['side_internal_order'] == "BUY") $CurrentPrice = 1 / $CurrentPrice;

        $Evolution = 0;
        if($CurrentPrice > 0) {
          $Evolution = (100 - ($OrderedPrice / $CurrentPrice) * 100);
          if($value['side_internal_order'] == "SELL") $Evolution = (100 - ($CurrentPrice / $OrderedPrice) * 100);
        }

        $DiffOrder = $CurrentPrice - $OrderedPrice;
        if($value['side_internal_order'] == "SELL"){
          $DiffOrder = $OrderedPrice - $CurrentPrice;
        }

        if($DiffOrder <= 0.000001 && $DiffOrder >= -0.000001) $Evolution = 0;

        $BookList[$value['id_internal_order']] = [
          'id' => $value['id_internal_order'],
          'id_encrypted' => App::encrypt_decrypt('encrypt', time().'-'.$value['id_internal_order']),
          'symbol' => $value['symbol_internal_order'],
          'currency' => $value['to_internal_order'],
          'date' => $value['date_internal_order'],
          'exchange' => $value['thirdparty_internal_order'],
          'fees' => $value['fees_internal_order'],
          'side' => $value['side_internal_order'],
          'type' => $value['type_internal_order'],
          'status' => $value['status_internal_order'],
          'usd_amount' => $value['usd_amount_internal_order'],
          'amount' => $value['amount_internal_order'],
          'evolv' => ($DiffOrder > 0 ? '+' : '').''.rtrim($App->_formatNumber($DiffOrder, ($DiffOrder > 1 ? 2 : ($DiffOrder < -1 ? 2 : 6)))).' '.
                    ($value['side_internal_order'] == "SELL" ? $value['to_internal_order'] : $value['symbol_internal_order']).' ('.$App->_formatNumber($Evolution, 2).'%)'
        ];
      }
    }


    die(json_encode([
      'error' => 0,
      'orders' => $BookList,
      'pair' => $_GET['symbol'].'/'.$_GET['currency'],
      'market' => $_GET['market'],
      'native' => ($App->_hiddenThirdpartyActive() ? 1 : 0),
      'show_market' => ($App->_getHideMarket() ? 1 : 0)
    ]));

} catch (\Exception $e) {
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}

?>
