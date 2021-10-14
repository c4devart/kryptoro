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
    if (!$User->_isLogged()) {
        throw new Exception("Error : User is not logged", 1);
    }


    $thirdPartyChoosen = null;
    $Trade = new Trade($User, $App);
    $CurrentBalance = null;

    if(empty($_POST) || !isset($_POST['from'])) throw new Exception("Permission denied", 1);

    if($App->_getIdentityEnabled()) $Identity = new Identity($User);

    if($App->_hiddenThirdpartyActive()){

      $Balance = new Balance($User, $App);
      $CurrentBalance = $Balance->_getCurrentBalance();

      if($CurrentBalance->_isPractice() && !$App->_getTradingEnablePracticeAccount()) throw new Exception("Real account is not enable");
      if(!$CurrentBalance->_isPractice() && !$App->_getTradingEnableRealAccount()) throw new Exception("Real account is not enable");

      if($CurrentBalance->_getBalanceType() == "real" && $App->_getIdentityEnabled() && $App->_getIdentityTradeBlocked() && !$Identity->_identityVerified()){
        die(json_encode([
          'error' => 9,
          'msg' => 'Identity not verified'
        ]));
      }

      $ListBalance = $CurrentBalance->_getBalanceListResum();
      if(strtolower($_POST['side']) == "sell") $CurrentBalanceValue = $ListBalance[$_POST['from']];
      if(strtolower($_POST['side']) == "buy") $CurrentBalanceValue = $ListBalance[$_POST['to']];


      // if(strtoupper($_POST['to']) == "USD" && $CurrentBalance->_getBalanceValue() < $_POST['amount']) throw new Exception("Insufficient funds", 1);
      // if(strtoupper($_POST['to']) != "USD" && $CurrentBalance->_getAmountCrypto($_POST['from']) < $_POST['amount']) throw new Exception("Insufficient funds (".$CurrentBalance->_getAmountCrypto($_POST['from'])." ".$_POST['from'].")", 1);

      $CryptoApi = new CryptoApi($User, [$_POST['to'], $_POST['to']]);
      $Coin = new CryptoCoin($CryptoApi, $_POST['from']);

      $thirdPartyChoosen = $Trade->_getThirdParty($App->_hiddenThirdpartyServiceCfg()[strtolower($_POST['thirdparty'])])[strtolower($_POST['thirdparty'])];

      if(strtolower($_POST['side']) == "sell" && $CurrentBalanceValue < $_POST['amount']) throw new Exception("Insufficient funds (".$CurrentBalanceValue." ".(strtolower($_POST['side']) == "sell" ? $_POST['from'] : $_POST['to']).")", 1);
      if(strtolower($_POST['side']) == "buy"){
        $PriceInfos = $thirdPartyChoosen->_getPriceTrade($thirdPartyChoosen::_formatPair($_POST['from'], $_POST['to']), 1);
        if($CurrentBalanceValue < (floatval($PriceInfos) * floatval($_POST['amount']))) throw new Exception("Insufficient funds (".$CurrentBalanceValue." ".(strtolower($_POST['side']) == "sell" ? $_POST['from'] : $_POST['to']).")", 1);
      }



    } else {

      $listThirdPartyAvailable = $Trade->_thirdparySymbolTrading($_POST['from'], $_POST['to']);

      if(!isset($_POST['to'])) throw new Exception("Permission denied", 1);

      foreach ($listThirdPartyAvailable as $key => $thirdparty) {
        if($thirdparty->_getExchangeName() == $_POST['thirdparty']){
          $thirdPartyChoosen = $thirdparty;
          break;
        }
      }

    }


    if(is_null($thirdPartyChoosen)) throw new Exception("Error : Thirdparty not available", 1);

    try {

      if(!$App->_hiddenThirdpartyActive()){
        if(!$thirdPartyChoosen->_isActivated()) die(json_encode([
          'error' => 3,
          'thirdparty' => $thirdPartyChoosen->_getExchangeName()
        ]));
      }

      if($_POST['type'] == "market" || $App->_hiddenThirdpartyActive()){

        if(array_key_exists('amount_limit', $_POST) && $_POST['type_super'] == "limit"){
          $_POST['amount'] = $_POST['amount_limit'];
        }

        $result = $thirdPartyChoosen->_createOrder($thirdPartyChoosen::_formatPair($_POST['from'], $_POST['to']),
                                                    $_POST['type'], $_POST['side'], $_POST['amount'], [],
                                                    $CurrentBalance, null, $_POST['type_super'], $_POST['order_price']);
      } else {
        $result = $thirdPartyChoosen->_createOrderLimit($thirdPartyChoosen::_formatPair($_POST['from'], $_POST['to']), $_POST['amount_limit'], $_POST['price_limit'], $_POST['side']);
      }
    } catch (\Exception $e) {
      die(json_encode([
        'error' => 2,
        'msg' => $e->getMessage()
      ]));
    }

    // if($_POST['type'] == "market"){
    //   $CryptoApi = new CryptoApi(null, null, $App);
    //
    //   // Init coin associate to the graph
    //   $Coin = new CryptoCoin($CryptoApi, $_POST['from'], null, $App);
    //
    //   $CryptoOrder = new CryptoOrder($Coin);
    //   $CryptoOrder->_createOrder($User, $_POST['date'], $_POST['side'], $_POST['amount'], $_POST['to']);
    // }

    die(json_encode([
      'error' => 0,
      'msg' => 'Success !'
    ]));



} catch (\Exception $e) {
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}

?>
