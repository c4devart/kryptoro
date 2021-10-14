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
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/Lang/Lang.php";
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

    $Lang = new Lang($User->_getLang(), $App);

    if(!$App->_hiddenThirdpartyActive()) throw new Exception("Permission denied", 1);

    foreach (['kr_widthdraw_symbol', 'kr_widthdraw_amount', 'kr_widthdraw_method'] as $fieldWidth) {
      if(empty($_POST) || !isset($_POST[$fieldWidth]) || empty($_POST[$fieldWidth])) throw new Exception("Error : Permissions denied", 1);
    }

    if($User->_googleTwoFactorEnable($User->_getUserID())){
      if(!$User->_checkGoogleTFS($_POST['kr_widthdraw_gauth'])) throw new Exception($Lang->tr("Google Authenticator code is invalid"), 1);
    }

    $Balance = new Balance($User, $App, 'real');
    $BalanceList = $Balance->_getBalanceListResum();
    if(!array_key_exists($_POST['kr_widthdraw_symbol'], $BalanceList)) throw new Exception("Error : Symbol not available", 1);
    if($BalanceList[$_POST['kr_widthdraw_symbol']] < $_POST['kr_widthdraw_amount']) throw new Exception("Error : Balance is too small", 1);

    $IsRealMoney = $Balance->_symbolIsMoney($_POST['kr_widthdraw_symbol']);

    $Withdraw = new Widthdraw($User);
    $InfosWithdrawMethod = $Withdraw->_getInformationWithdrawMethod(App::encrypt_decrypt('decrypt', $_POST['kr_widthdraw_method']));
    if(!$IsRealMoney && $InfosWithdrawMethod['type_user_widthdraw'] == "banktransfert" && $_POST['kr_withdraw_agreement_completed'] == "0"){
      die(json_encode([
        'error' => 2,
        'msg' => 'Bank withdraw need to approve contract'
      ]));
    }

    $Balance->_askWidthdraw($_POST['kr_widthdraw_symbol'], $_POST['kr_widthdraw_amount'], App::encrypt_decrypt('decrypt', $_POST['kr_widthdraw_method']));

    die(json_encode([
      'error' => 0,
      'msg' => $Lang->tr('You will receive a widthdraw confirmation by email')
    ]));


} catch (\Exception $e) {
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}

?>
