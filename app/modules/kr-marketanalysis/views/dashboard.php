<?php

/**
 * Dashboard market analytic view
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
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoGraph.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoHisto.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoCoin.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoApi.php";

// Load app modules
$App = new App(true);
$App->_loadModulesControllers();

// Check if user is logged
$User = new User();
if(!$User->_isLogged()) die("You are not logged");

// Init lang object
$Lang = new Lang($User->_getLang(), $App);

// Init CryptoApi object
$CryptoApi = new CryptoApi($User, null, $App);

?>
<div class="kr-heatmap">
  <nav class="kr-marketnav">
    <ul>
      <li kr-navview="coinlist"><?php echo $Lang->tr('Coin list'); ?></li>
      <li kr-navview="marketlist"><?php echo $Lang->tr('Market list'); ?></li>
      <li kr-navview="dashboard" class="kr-nav-selected"><?php echo $Lang->tr('Heatmap'); ?></li>
    </ul>
  </nav>
  <div class="kr-marketa">

    <?php
    $indicator = ['BTC', 'ETH', 'USD', 'EUR', 'JPY', 'CHF', 'CAD'];
    foreach ($CryptoApi->_getCoinsList(18, true, true) as $symbol) {
      $Coin = $CryptoApi->_getCoin($symbol);
      $marketDataCoin = $Coin->getMarketAnalystic($indicator);
      ?>
      <div class="kr-marketa-currency">
        <ul>
          <?php
          foreach ($marketDataCoin['negative'] as $symbolMarketComparator => $valMarketComparator) {
            ?>
            <li symbol="<?php echo $valMarketComparator['symbol']; ?>" fromsymbol="<?php echo $Coin->_getSymbol(); ?>" class="<?php if($valMarketComparator['color'] != null) echo 'kr-marketa-currency-case-'.$valMarketComparator['color']; ?>">
              <label><?php echo $valMarketComparator['symbol']; ?></label>
              <span><?php echo $App->_formatNumber($valMarketComparator['evolution'], 1); ?>%</span>
            </li>
            <?php
          }
          ?>
        </ul>
        <div><?php echo $Coin->_getSymbol(); ?></div>
        <ul>
          <?php
          foreach ($marketDataCoin['positive'] as $symbolMarketComparator => $valMarketComparator) {

            ?>
            <li symbol="<?php echo $valMarketComparator['symbol']; ?>" fromsymbol="<?php echo $Coin->_getSymbol(); ?>" class="<?php if($valMarketComparator['color'] != null) echo 'kr-marketa-currency-case-'.$valMarketComparator['color']; ?>">
              <label><?php echo $valMarketComparator['symbol']; ?></label>
              <span><?php echo $App->_formatNumber($valMarketComparator['evolution'], 1); ?>%</span>
            </li>
            <?php
          }
          ?>
        </ul>
      </div>
      <?php
    }
    ?>

  </div>
</div>
