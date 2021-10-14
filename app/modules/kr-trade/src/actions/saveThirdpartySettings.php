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

    if(empty($_POST) || !isset($_POST['thirdparty_name']) || empty($_POST['thirdparty_name'])) throw new Exception("Permission denied", 1);


    $exchangeName = App::encrypt_decrypt('decrypt', $_POST['thirdparty_name']);

    $Exchange = $Trade->_getExchange($exchangeName);

    if(is_null($Exchange)) throw new Exception("Error: Unable to find exchange", 1);

    $configFieldExchange = $Trade->_getThirdPartyConfig()[$exchangeName];

    if($_POST['thirdpartycfg_type'] == "tradingglobal" && $User->_isAdmin()){

      if($App->_isDemoMode()) throw new Exception("Error : Option not available in demo mode", 1);


      $GlobalTradingConfiguration = $App->_hiddenThirdpartyServiceCfg();
      if(is_null($GlobalTradingConfiguration)) $GlobalTradingConfiguration = [];
      if(!is_null($GlobalTradingConfiguration) && !array_key_exists($Exchange->_getExchangeName(), $GlobalTradingConfiguration)){
        $GlobalTradingConfiguration[$Exchange->_getExchangeName()] = [];
      }

      if(is_null($GlobalTradingConfiguration)) $GlobalTradingConfiguration = [];

      $configListField = array_keys($configFieldExchange);
      foreach (array_keys($configFieldExchange) as $configFieldKey) {
        if(!array_key_exists($configFieldKey, $_POST)){
          $GlobalTradingConfiguration[$Exchange->_getExchangeName()][$configFieldKey] = null;
        } else {
          $GlobalTradingConfiguration[$Exchange->_getExchangeName()][$configFieldKey] = ($configFieldKey == "sandbox" ? $_POST[$configFieldKey] : App::encrypt_decrypt('encrypt', $_POST[$configFieldKey]));
        }
      }


    //
      $TradeExchange = $Trade->_getThirdParty($GlobalTradingConfiguration[$Exchange->_getExchangeName()])[$Exchange->_getExchangeName()];

      try {
        $balance = $TradeExchange->_getBalance();
        $App->_setThirdpartyServiceCfg(json_encode($GlobalTradingConfiguration));
        $App->_cleanCache();
      } catch (\Exception $es) {
        throw new Exception($es->getMessage(), 1);
      }

    } else {
      $requestString = "id_user";
      $updateString = "";
      $requestArgsString = ":id_user";
      $requestArgs = ['id_user' => $User->_getUserID()];

      foreach ($configFieldExchange as $settingsKey => $value) {
        if($settingsKey == "sandbox" && is_null($value)) continue;
        if(!isset($_POST[$settingsKey])) throw new Exception("Error : Wrong format", 1);
        if($settingsKey == "sandbox" && !is_null($value)){
          $requestString .= ", ".$value;
          $requestArgsString .= ", :".$value;
          $requestArgs[$value] = $_POST[$settingsKey];
          if(!empty($updateString)) $updateString .= ", ";
          $updateString .= $value."=:".$value;
        } else {
          if(empty($_POST[$settingsKey])) die(json_encode([
            'error' => 2,
            'msg' => 'empty field'
          ]));
          $requestString .= ", ".$settingsKey;
          $requestArgsString .= ", :".$settingsKey;
          $requestArgs[$settingsKey] = App::encrypt_decrypt('encrypt', $_POST[$settingsKey]);
          if(!empty($updateString)) $updateString .= ", ";
          $updateString .= $settingsKey."=:".$settingsKey;
        }

      }

      $Trade->_saveThirdpartySettings($exchangeName, $requestString, $requestArgsString, $requestArgs, $updateString);

      $TradeCheck = new Trade($User, $App);
      $TradeExchange = $TradeCheck->_getThirdParty()[$exchangeName];
      try {
        $balance = $TradeExchange->_getBalance();
      } catch (\Exception $e) {
        $Exchange = $TradeExchange->_getExchange($exchangeName);
        $Trade->_removeThirdparty($Exchange);
        throw new Exception("Invalid API Credentials", 1);
      }

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
