<?php

/**
 * Load data balance
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
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/Lang/Lang.php";
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

    $Lang = new Lang($User->_getLang(), $App);

    $CryptoApi = new CryptoApi($User, null, $App);

    if($App->_hiddenThirdpartyActive()){

      $thirdPartyChoosen = null;
      $Trade = new Trade($User, $App);

      $Balance = new Balance($User, $App);
      $CurrentBalance = $Balance->_getCurrentBalance();

      $BalanceReturned = [];

      foreach ($Balance->_getBalanceList() as $BalanceItem) {
        if($App->_getBalanceEstimationShown()){
          $BalanceReturned[] = [
            'enc_id' => $BalanceItem->_getBalanceID(true),
            'balance' => $BalanceItem->_getEstimationBalance() * 100,
            'balance_investment' => $BalanceItem->_getBalanceInvestisment(),
          ];
        } else {
          $BalanceResum = $BalanceItem->_getBalanceListResum();
          $BalanceResumFirstSymbol = array_keys($BalanceResum)[0];
          $ReturnedValue = floatval($BalanceResum[$BalanceResumFirstSymbol]);
          $BalanceReturned[] = [
            'enc_id' => $BalanceItem->_getBalanceID(true),
            'balance' => $ReturnedValue * 100,
            'balance_investment' => $ReturnedValue,
          ];
        }


      }



      if($App->_getBalanceEstimationShown()){
        $ReturnedSymbol = "";
        $ReturnedValue = $CurrentBalance->_getEstimationBalance();
      } else {
        $BalanceResum = $CurrentBalance->_getBalanceListResum();
        $BalanceResumFirstSymbol = array_keys($BalanceResum)[0];
        $ReturnedValue = floatval($BalanceResum[$BalanceResumFirstSymbol]);
      }

      die(json_encode([
        'error' => 0,
        'balance' => $BalanceReturned,
        'type' => 'native',
        'balances' => array_slice($CurrentBalance->_getBalanceListResum(), 0, 12),
        'show_more' => true,
        'current_balance' => [
          'title' => $Lang->tr($CurrentBalance->_getBalanceType().' account'),
          'available' => $ReturnedValue,
          'total' => $ReturnedValue
        ]
      ]));

    } else {

      $Trade = new Trade($User, $App);
      $listThirdParty = $Trade->_getThirdPartyListAvailable();
      if(count($listThirdParty) > 0){
        $selectedThirdParty = $Trade->_getSelectedThirdparty();
        $balanceList = $selectedThirdParty->_getBalance(true);
        //error_log(json_encode($balanceList));
        $balanceSelectedSymbol = null;
        $balanceSelectedAmount = null;
        foreach ($balanceList as $key => $value) {
          if(!is_null($balanceSelectedSymbol)) continue;
          $balanceSelectedSymbol = $key;
          $balanceSelectedAmount = $value['free'];
        }

        $balanceListFormated = [];
        foreach (array_slice($balanceList, 0, 12) as $symbol => $infosBalance) {
          $balanceListFormated[] = [
            'symbol' => $symbol,
            'amount' => $infosBalance['free']
          ];
        }

        die(json_encode([
          'error' => 0,
          'type' => 'external',
          'exchange_title' => $selectedThirdParty->_getName(),
          'exchange_name' => $selectedThirdParty->_getExchangeName(),
          'first_balance' => $balanceSelectedAmount,
          'first_balance_symbol' => $balanceSelectedSymbol,
          'show_more' => count($balanceList) > 12,
          'balances' => $balanceListFormated
        ]));

      } else {
        die(json_encode([
          'error' => 0,
          'type' => 'none'
        ]));
      }

    }


} catch (\Exception $e) {
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}

?>
