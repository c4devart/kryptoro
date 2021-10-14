<?php

/**
 * Save thirdparty settings
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

    $Trade = new Trade($User, $App);

    if(empty($_POST) || !isset($_POST['token']) || empty($_POST['token'])) throw new Exception("Permission denied", 1);


    $token = App::encrypt_decrypt('decrypt', $_POST['token']);
    $token = explode('-', $token);
    if(count($token) != 3 || $token[1] != $User->_getUserID()) throw new Exception("Permission denied", 1);


    $Exchange = $Trade->_getExchange($token[0]);
    if(is_null($Exchange)) throw new Exception("Error: Unable to find exchange", 1);

    if($token[2] == 'true'){
      if($App->_isDemoMode()) throw new Exception("Error : Option not available in demo mode", 1);
      $GlobalTradingConfiguration = $App->_hiddenThirdpartyServiceCfg();

      if(!array_key_exists($Exchange->_getExchangeName(), $GlobalTradingConfiguration)) throw new Exception("Error : Exchange not enable", 1);
      unset($GlobalTradingConfiguration[$Exchange->_getExchangeName()]);

      $App->_setThirdpartyServiceCfg(json_encode($GlobalTradingConfiguration));

    } else {
      $Trade->_removeThirdparty($Exchange);
    }




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

?>
