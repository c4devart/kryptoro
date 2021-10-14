<?php

/**
 * Charge plan selected view
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
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/Lang/Lang.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoApi.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoCoin.php";

try {

  // Load app module
  $App = new App(true);
  $App->_loadModulesControllers();


  // Check if user is logged
  $User = new User();
  if(!$User->_isLogged()) die('User not logged');


  if(empty($_GET) || !isset($_GET['g']) && !isset($_GET['t'])) throw new Exception("Error : Wrong args", 1);

  $CoinGate = new CoinGate($App);

  if(isset($_GET['t']) && $_GET['t'] == "deposit" && isset($_GET['m'])){
    $amount = floatval($_GET['m']);
    $Balance = new Balance($User, $App, 'real');
    $order = $CoinGate->_createDeposit($User, $amount, $Balance, $_GET['cr']);

  } else {
    $order = $CoinGate->_createOrder($User, $_GET['g']);
  }

  header('Location: '.$order->payment_url);

} catch (Exception $e) {
  die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
} finally {
  ?>
  <div style="width:100%;box-sizing: border-box;padding:20px;background:red;color:#fff;text-align:center;">
    CoinGate authentification failed, check your credentials
  </div>
  <?php
}

?>
