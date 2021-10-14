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

    if($App->_hiddenThirdpartyActive()) throw new Exception("Permission denied", 1);

    if(empty($_POST) || !isset($_POST['exchange']) || empty($_POST['exchange'])) throw new Exception("Error : Permissions denied", 1);

    $Trade = new Trade($User, $App);

    $Exchange = $Trade->_getExchange(App::encrypt_decrypt('decrypt', $_POST['exchange']));
    if(!$Exchange->_isActivated()) throw new Exception("Exchange not activated", 1);

    die(json_encode([
      'error' => 0,
      'balance' => $Trade->_changeFirstExchange($Exchange)
    ]));


} catch (\Exception $e) {
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}

?>
