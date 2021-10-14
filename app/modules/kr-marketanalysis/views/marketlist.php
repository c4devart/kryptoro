<?php

/**
 * Coin list market analytic view
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

$Trade = new Trade($User, $App);

?>
<div class="kr-marketcoinlist">

  <nav class="kr-marketnav">
    <ul>
      <li kr-navview="coinlist"><?php echo $Lang->tr('Coin list'); ?></li>
      <li kr-navview="marketlist" class="kr-nav-selected"><?php echo $Lang->tr('Market list'); ?></li>
      <li kr-navview="dashboard"><?php echo $Lang->tr('Heatmap'); ?></li>
    </ul>
    <form class="kr-search-market" action="" method="post">
      <input type="text" name="kr-search-value" placeholder="Search exchange / pair / symbol ..." value="<?php echo (!isset($_POST['search']) || empty($_POST['search']) ? '' : $_POST['search']); ?>">
    </form>
  </nav>

  <div class="kr-marketlist" kr-currency-mm="<?php echo $CryptoApi->_getCurrency(); ?>" kr-currency-mm-symb="<?php echo $CryptoApi->_getCurrencySymbol(); ?>">
    <div class="kr-marketlist-header">
      <?php if(!$App->_getHideMarket()): ?>
        <div class="kr-marketlist-n"><span><?php echo $Lang->tr('Market'); ?></span></div>
      <?php endif; ?>
      <div class="kr-marketlist-n"><span><?php echo $Lang->tr('Pair'); ?></span></div>
      <div class="kr-marketlist-cellnumber kr-mono"><span><?php echo $Lang->tr('Price'); ?></span></div>
      <div class="kr-marketlist-cellnumber kr-mono kr-marketlist-cellnumber-f2"><span><?php echo $Lang->tr('Direct Vol. 24H'); ?></span></div>
      <div class="kr-marketlist-cellnumber kr-mono kr-marketlist-cellnumber-f3"><span><?php echo $Lang->tr('Total Vol. 24H'); ?></span></div>
      <div class="kr-marketlist-cellnumber kr-mono kr-marketlist-cellnumber-f2"><span><?php echo $Lang->tr('Market Cap'); ?></span></div>
      <div class="kr-marketlist-cellnumber kr-mono"><span><?php echo $Lang->tr('Chg. 24H'); ?></span></div>
      <div class="kr-marketlist-cellnumber kr-mono kr-marketlist-cellnumber-f1"><span><?php echo $Lang->tr('24h High/Low'); ?></span></div>
    </div>
    <?php
    foreach ($Trade->_getMarketTradeAvailable($CryptoApi, 60, (!isset($_POST['search']) || empty($_POST['search']) ? null : $_POST['search'])) as $Market) {
      $Coin = $Market['coin'];
      $CryptoApi = $Market['cryptoapi'];
      if(is_null($Coin)) continue;

      $Exchange = $Trade->_getExchange($Market['market']['name_thirdparty_crypto']);
      if(is_null($Exchange)) continue;

      $icon = $Coin->_getIcon();

      ?>
      <div class="kr-marketlist-item" kr-symbol-mm="<?php echo $Coin->_getSymbol(); ?>" kr-symbol-tt="<?php echo $CryptoApi->_getCurrencySymbol(); ?>" kr-symbol-market="<?php echo $Market['market']['name_thirdparty_crypto']; ?>">
        <?php if(!$App->_getHideMarket()): ?>
          <div class="kr-marketlist-n">
            <div class="kr-marketlist-n-nn">
              <label class="kr-mono"><?php echo $Exchange->_getName(); ?></label>
            </div>
          </div>
        <?php endif; ?>
        <div class="kr-marketlist-n">
          <div class="kr-marketlist-n-nn">
            <label class="kr-mono"><?php echo $Coin->_getSymbol(); ?>/<?php echo $Market['market']['to_thirdparty_crypto']; ?></label>
          </div>
        </div>
        <div class="kr-marketlist-cellnumber kr-mono">
          <span kr-mm-c="PRICE" kr-mm-cp="<?php echo $Coin->_getPrice(); ?>"><?php echo $CryptoApi->_getCurrencySymbol().' '.($Coin->_getPrice() > 1 ? number_format($Coin->_getPrice(), 2, ',', ' ') : number_format($Coin->_getPrice(), 6, ',', ' ')) ; ?></span>
        </div>
        <div class="kr-marketlist-cellnumber kr-mono kr-marketlist-cellnumber-f2">
          <span kr-mm-c="VOLUME24HOURTO"><?php echo $CryptoApi->_getCurrencySymbol().' '.$Coin->_formatNumberCommarization($Coin->_getDirectVol24()); ?></span>
        </div>
        <div class="kr-marketlist-cellnumber kr-mono kr-marketlist-cellnumber-f3">
          <span><?php echo $CryptoApi->_getCurrencySymbol().' '.$Coin->_formatNumberCommarization($Coin->_getTotalVol24()); ?></span>
        </div>
        <div class="kr-marketlist-cellnumber kr-mono kr-marketlist-cellnumber-f2">
          <span><?php echo $CryptoApi->_getCurrencySymbol().' '.$Coin->_formatNumberCommarization($Coin->_getMarketCap()); ?></span>
        </div>
        <div class="kr-marketlist-cellnumber kr-mono">
          <span class="<?php echo ($Coin->_getCoin24Evolv() < 0 ? 'kr-marketlist-cellnumber-negativ' : 'kr-marketlist-cellnumber-positiv'); ?>" kr-mm-c="CHANGE24HOURPCT"><?php echo round($Coin->_getCoin24Evolv(), 2); ?>%</span>
        </div>
        <div class="kr-marketlist-cellffhl kr-mono kr-marketlist-cellnumber-f1">
          <div class="kr-marketlist-ffhl">
            <div class="kr-marketlist-ffhl-progr">
              <div style="width:<?php echo $Coin->_getCurrentPercentagePriceLowHigh(); ?>%;"></div>
            </div>
            <div class="kr-marketlist-ffhl-mm">
              <span><?php echo $Coin->_getLow24Hours(); ?></span>
              <span><?php echo $Coin->_getHigh24Hours(); ?></span>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
  </div>

</div>
