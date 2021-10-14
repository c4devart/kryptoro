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

?>
<div class="kr-marketcoinlist">

  <nav class="kr-marketnav">
    <ul>
      <li kr-navview="coinlist" class="kr-nav-selected"><?php echo $Lang->tr('Coin list'); ?></li>
      <li kr-navview="marketlist"><?php echo $Lang->tr('Market list'); ?></li>
      <li kr-navview="dashboard"><?php echo $Lang->tr('Heatmap'); ?></li>
    </ul>
    <form class="kr-search-coin" action="" method="post">
      <input type="text" name="kr-search-value" placeholder="Search coin ..." value="<?php echo (!isset($_POST['search']) || empty($_POST['search']) ? '' : $_POST['search']); ?>">
    </form>
  </nav>

  <div class="kr-marketlist" kr-currency-mm="<?php echo $CryptoApi->_getCurrency(); ?>" kr-currency-mm-symb="<?php echo $CryptoApi->_getCurrencySymbol(); ?>">
    <div class="kr-marketlist-header">
      <div class="kr-marketlist-n"></div>
      <div class="kr-marketlist-cellnumber kr-mono"><span><?php echo $Lang->tr('Price'); ?></span></div>
      <div class="kr-marketlist-cellnumber kr-mono kr-marketlist-cellnumber-f2"><span><?php echo $Lang->tr('Direct Vol. 24H'); ?></span></div>
      <div class="kr-marketlist-cellnumber kr-mono kr-marketlist-cellnumber-f3"><span><?php echo $Lang->tr('Total Vol. 24H'); ?></span></div>
      <div class="kr-marketlist-cellnumber kr-mono kr-marketlist-cellnumber-f2"><span><?php echo $Lang->tr('Market Cap'); ?></span></div>
      <div class="kr-marketlist-cellnumber kr-mono"><span><?php echo $Lang->tr('Chg. 24H'); ?></span></div>
      <div class="kr-marketlist-cellnumber kr-mono kr-marketlist-cellnumber-f1"><span><?php echo $Lang->tr('24h High/Low'); ?></span></div>
    </div>
    <?php

    foreach ($CryptoApi->_getCoinsList(30, true, false, (!isset($_POST['search']) || empty($_POST['search']) ? null : $_POST['search'])) as $Coin) {

      $icon = $Coin->_getIcon();

      ?>
      <div kr-symbol-mm="<?php echo $Coin->_getSymbol(); ?>" onclick="return false;">
        <div class="kr-marketlist-n">
          <div class="kr-marketlist-n-nn">
            <label class="kr-mono"><?php echo $Coin->_getCoinName(); ?></label>
          </div>
        </div>
        <div class="kr-marketlist-cellnumber kr-mono">
          <span kr-mm-c="PRICE" kr-mm-cp="<?php echo $Coin->_getPrice(); ?>"><?php echo $App->_formatNumber($Coin->_getPrice(), ($Coin->_getPrice() > 10 ? 2 : 5)); ?></span>
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
