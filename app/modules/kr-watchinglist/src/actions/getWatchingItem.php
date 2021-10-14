<?php

/**
 * WatchingList item view
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */

session_start();

require "../../../../../config/config.settings.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/vendor/autoload.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/MySQL/MySQL.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/User/User.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/App.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/AppModule.php";
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
  if(!$User->_isLogged()) throw new Exception("User are not logged", 1);

  // Check args
  if(empty($_GET) || empty($_GET['symb'])) throw new Exception("Error : Args missing", 1);

  // Init CryptoApi object
  $CryptoApi = new CryptoApi(null, [$_GET['currency'], $_GET['currency']], $App, (isset($_GET['market']) ? $_GET['market'] : 'CCCAGG'));

  // Get coin data
  $Coin = $CryptoApi->_getCoin($_GET['symb']);


  // If item need to be added --> add
  if(!empty($_GET['t']) && $_GET['t'] == "add"){
    // Init watching list
    $WatchingList = new WatchingList($CryptoApi, $User);
    $WatchingList->_addItem($Coin->_getSymbol(), $_GET['currency'], (isset($_GET['market']) ? $_GET['market'] : 'CCCAGG'));
  }

} catch (Exception $e) { // If error detected, show error
  die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}

?>
<li kr-watchinglistpair="<?php echo $Coin->_getMarket().':'.$Coin->_getSymbol().'/'.$CryptoApi->_getCurrency(); ?>" class="<?php //echo ($i == 2 ? 'kr-wtchl-lst-selected' : ''); ?>">
  <div>
    <span><?php echo $Coin->_getSymbol().'/'.$CryptoApi->_getCurrency(); ?></span>
  </div>
  <div>
    <span class="kr-watchinglistpair-price"><?php echo $App->_formatNumber($Coin->_getPrice(), ($Coin->_getPrice() > 10 ? 2 : 5)); ?></span>
  </div>
  <div>
    <span class="kr-watchinglistpair-evolv"><?php echo $App->_formatNumber($Coin->_getCoin24Evolv(), 2); ?>%</span>
  </div>
  <div class="kr-wtchl-lst-remove">
    <svg class="lnr lnr-cross"><use xlink:href="#lnr-cross"></use></svg>
  </div>
</li>
