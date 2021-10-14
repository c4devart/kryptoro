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

    if(empty($_POST) || !isset($_POST['orderid'])) throw new Exception("Permission denied", 1);

    if($App->_getIdentityEnabled()) $Identity = new Identity($User);

    if($App->_hiddenThirdpartyActive()){

      $Balance = new Balance($User, $App);
      $CurrentBalance = $Balance->_getCurrentBalance();

      if($CurrentBalance->_isPractice() && !$App->_getTradingEnablePracticeAccount()) throw new Exception("Real account is not enable");
      if(!$CurrentBalance->_isPractice() && !$App->_getTradingEnableRealAccount()) throw new Exception("Real account is not enable");

      $CurrentBalance->_cancelOrder($_POST['orderid']);

    }

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
