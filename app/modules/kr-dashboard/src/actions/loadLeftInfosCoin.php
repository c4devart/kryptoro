<?php

/**
 * Load left coin infos
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
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/Lang/Lang.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/User/User.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoOrder.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoNotification.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoIndicators.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoGraph.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoHisto.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoCoin.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoApi.php";

try {

  $App = new App();

  $User = new User();
  if (!$User->_isLogged()) {
      throw new Exception("Permission denied", 1);
  }

  $Lang = new Lang($User->_getLang(), $App);

  if(empty($_POST) || !isset($_POST['symbol']) || !isset($_POST['currency'])) die("Permission denied");

  $CryptoApi = new CryptoApi(null, (isset($_POST['currency']) ? [$_POST['currency'], null] : null), $App, $_POST['market']);

  $Coin = $CryptoApi->_getCoin($_POST['symbol']);

  $ListDetails = [
    "Open day" => $App->_formatNumber($Coin->_getOpenDayMultiFull(), ($Coin->_getOpenDayMultiFull() > 10 ? 2 : 5)),
    "Market Cap" => $Coin->_formatNumberCommarization($Coin->_getMarketCap()),
    "Volume 24H" => $Coin->_formatNumberCommarization($Coin->_getTotal24VolMultiFull()),
    "Direct Volume" => $Coin->_formatNumberCommarization($Coin->_getDirectVol24())
  ];

} catch (\Exception $e) {
  die($e->getMessage());
}




?>

<header kr-leftinfoisp="<?php echo strtoupper($Coin->_getMarketMultiFull()).':'.$Coin->_getSymbol().$CryptoApi->_getCurrency(); ?>"
      kr-leftinfois-makr="<?php echo strtoupper($Coin->_getMarketMultiFull()); ?>"
      kr-leftinfois-symbol="<?php echo strtoupper($Coin->_getSymbol()); ?>"
      kr-leftinfois-currency="<?php echo strtoupper($CryptoApi->_getCurrency()); ?>"
    >
  <span><?php echo $Lang->tr('Details'); ?></span>
  <button type="button" onclick="hideLeftInfosMoreDetails();" class="btn btn-small btn-green btn-autowidth"><?php echo $Lang->tr('Hide orders book'); ?></button>
</header>
<div class="kr-infoscurrencylf-header" style="<?php if($App->_getHideMarket()) echo 'min-height:13px;'; ?>">
  <h2><?php echo $Coin->_getCoinName(); ?> / <?php echo $CryptoApi->_getCurrencyFullName(); ?></h2>
  <?php if(!$App->_getHideMarket()): ?>
    <span><?php echo $Coin->_getMarketMultiFull(); ?></span>
  <?php endif; ?>
</div>
<div class="kr-infoscurrencylf-price">
  <span class="kr-infoscurrencylf-price-cp"><?php echo $App->_formatNumber($Coin->_getPrice(), ($Coin->_getPrice() > 10 ? 2 : 5)); ?></span>
  <span class="kr-infoscurrencylf-price-evolv <?php echo ($Coin->_getCoin24Change() < 0 ? 'kr-hg-down' : ($Coin->_getCoin24Change() > 0 ? 'kr-hg-up' : '')); ?>"><?php echo $App->_formatNumber($Coin->_getCoin24Change(), 2); ?> (<?php echo $App->_formatNumber($Coin->_getCoin24Evolv(), 2); ?>%)</span>
</div>
<div class="kr-infoscurrencylf-range">
  <div class="kr-infoscurrencylf-range-bar">
    <div style="width:<?php echo $Coin->_getCurrentPercentagePriceLowHigh(); ?>%;">

    </div>
  </div>
  <div class="kr-infoscurrencylf-range-infos">
    <span class="kr-infoscurrencylf-range-infos-low"><?php echo $App->_formatNumber($Coin->_getLow24Hours(), ($Coin->_getLow24Hours() > 10 ? 2 : 5)); ?></span>
    <label><?php echo $Lang->tr('Day range'); ?></label>
    <span class="kr-infoscurrencylf-range-infos-high"><?php echo $App->_formatNumber($Coin->_getHigh24Hours(), ($Coin->_getHigh24Hours() > 10 ? 2 : 5)); ?></span>
  </div>
</div>
<ul>
  <?php
  foreach ($ListDetails as $titleDetails => $valueDetails) {
    ?>
    <li>
      <span><?php echo $Lang->tr($titleDetails); ?></span>
      <div></div>
      <span><?php echo $valueDetails; ?></span>
    </li>
    <?php
  }
  ?>
</ul>

<div class="kr-infoscurrencylf-btn">
  <button type="button" onclick="loadLeftInfosMoreDetails();" class="btn btn-autowidth btn-green btn-small" name="button"><?php echo $Lang->tr('Load orders book'); ?></button>
</div>

<section class="kr-infoscurrencylf-orderbook">
  <?php
  foreach (['asks', 'bids'] as $key => $sideOrderBook) {
  ?>
    <div>
      <header>
        <ul>
          <li><?php echo $Lang->tr('Total'); ?></li>
          <li><?php echo $Lang->tr('Amount'); ?></li>
          <li><?php echo $Lang->tr('Price'); ?></li>
        </ul>
      </header>
      <section kr-orderbook-side="<?php echo $sideOrderBook; ?>">

      </section>
    </div>
  <?php } ?>
</section>
