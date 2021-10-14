<?php

/**
 * Load order book
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

    if(empty($_POST) || !isset($_POST['order_id']) || empty($_POST['order_id'])) throw new Exception("Permission denied", 1);

    if($App->_hiddenThirdpartyActive()){
      $OrderID = explode('-', App::encrypt_decrypt('decrypt', $_POST['order_id']));
      if(count($OrderID) != 2) throw new Exception("Permission denied", 1);

      $Lang = new Lang($User->_getLang(), $App);

      $CryptoApi = new CryptoApi($User, null, $App);

      $Balance = new Balance($User, $App);

      $CurrentBalance = $Balance->_getCurrentBalance();

      $OrderInfos = $CurrentBalance->_getOrderInfos($OrderID[1]);
      $CoinFrom = $CryptoApi->_getCoin($OrderInfos['symbol_internal_order']);
      $CoinTo = $CryptoApi->_getCoin($OrderInfos['to_internal_order']);
    } else {

      $Trade = new Trade($User, $App);
      $selectedThirdParty = $Trade->_getSelectedThirdparty();

      die();
    }


} catch(Exception $e){
  echo '<script>closeOrderInfos();</script>';
  die($e->getMessage());

}

?>
<header>
  <div>
    <div>
      <img src="<?php echo $CoinFrom->_getIcon(); ?>" alt="">
      <span><?php echo $CoinFrom->_getCoinName(); ?> / <?php echo $CoinTo->_getCoinName(); ?></span>
    </div>
    <svg onclick="closeOrderInfos();" class="lnr lnr-cross"><use xlink:href="#lnr-cross"></use></svg>
  </div>
</header>
<section>
  <?php
  if($OrderInfos['type_internal_order'] == "market"):
  ?>
    <section class="kr-orderinfoside-dt">
      <span><?php echo $Lang->tr($OrderInfos['side_internal_order'].' ORDER DONE'); ?></span>
      <div>
        <svg class="lnr lnr-flag"><use xlink:href="#lnr-flag"></use></svg>
        <span><?php echo date('d/m/Y H:i:s', $OrderInfos['date_internal_order']); ?></span>
      </div>
    </section>
  <?php else: ?>
    <section class="kr-orderinfoside-dt">
      <span><?php echo $Lang->tr($OrderInfos['side_internal_order'].' LIMIT ORDER'); ?></span>
      <?php
      if($OrderInfos['status_internal_order'] == "1"):
      ?>
      <div>
        <svg class="lnr lnr-flag"><use xlink:href="#lnr-flag"></use></svg>
        <span><?php echo date('d/m/Y H:i:s', $OrderInfos['date_internal_order']); ?></span>
      </div>
    <?php else: ?>
      <div>
        <svg class="lnr lnr-clock"><use xlink:href="#lnr-clock"></use></svg>
        <span><?php echo $Lang->tr('Not fullfiled ...'); ?></span>
      </div>
    <?php endif; ?>
    </section>
  <?php endif; ?>
  <?php
  if($OrderInfos['type_internal_order'] == "limit"):
  ?>
    <ul class="kr-orderinfoside-action">
      <li>
        <input type="button" class="btn btn-small btn-autowidth" onclick="_cancelOrder('<?php echo $OrderInfos['id_internal_order']; ?>');closeOrderInfos();" name="" value="Cancel order">
      </li>
    </ul>
  <?php endif; ?>
  <ul class="kr-orderinfoside-cinout">
    <?php if($OrderInfos['side_internal_order'] == "BUY"): ?>
      <li>
        <span>+ <?php echo $App->_formatNumber($OrderInfos['usd_amount_internal_order'] - $OrderInfos['fees_internal_order'], 8); ?></span>
        <label><?php echo $CoinFrom->_getCoinName(); ?></label>
      </li>
      <li>
        <span>- <?php echo rtrim($App->_formatNumber($OrderInfos['amount_internal_order'], 8), "0"); ?></span>
        <label><?php echo $CoinTo->_getCoinName(); ?></label>
      </li>
    <?php else: ?>
      <li>
        <span>+ <?php echo $App->_formatNumber($OrderInfos['usd_amount_internal_order'] - $OrderInfos['fees_internal_order'], 8); ?></span>
        <label><?php echo $CoinTo->_getCoinName(); ?></label>
      </li>
      <li>
        <span>- <?php echo rtrim($App->_formatNumber($OrderInfos['amount_internal_order'], 8), "0"); ?></span>
        <label><?php echo $CoinFrom->_getCoinName(); ?></label>
      </li>
    <?php endif; ?>
  </ul>
  <ul class="kr-orderinfoside-minf">
    <li>
      <span><?php echo $Lang->tr('Order Ref.'); ?></span>
      <div></div>
      <span><?php echo (strlen($OrderInfos['ref_internal_order']) > 0 ? $OrderInfos['ref_internal_order'] : $OrderInfos['id_user'].'-'.$OrderInfos['id_internal_order']); ?></span>
    </li>
    <li>
      <span><?php echo $Lang->tr('Fees'); ?></span>
      <div></div>
      <span><?php echo rtrim($App->_formatNumber($OrderInfos['fees_internal_order'], 12), "0").' '.($OrderInfos['side_internal_order'] == "BUY" ? $OrderInfos['symbol_internal_order'] : $OrderInfos['to_internal_order']); ?></span>
    </li>
    <li>
      <?php if($OrderInfos['side_internal_order'] == "BUY"): ?>
        <span><?php echo $OrderInfos['symbol_internal_order']; ?> <?php echo $Lang->tr('Amount'); ?></span>
        <div></div>
        <span><?php echo rtrim($App->_formatNumber($OrderInfos['usd_amount_internal_order'], 8), "0").' '.$OrderInfos['symbol_internal_order']; ?></span>
      <?php else: ?>
        <span><?php echo $OrderInfos['to_internal_order']; ?> <?php echo $Lang->tr('Amount'); ?></span>
        <div></div>
        <span><?php echo rtrim($App->_formatNumber($OrderInfos['usd_amount_internal_order'], 8), "0").' '.$OrderInfos['to_internal_order']; ?></span>
      <?php endif; ?>
    </li>
    <li>
      <?php if($OrderInfos['side_internal_order'] == "BUY"): ?>
        <span><?php echo $OrderInfos['to_internal_order']; ?> <?php echo $Lang->tr('Amount'); ?></span>
        <div></div>
        <span><?php echo rtrim($App->_formatNumber($OrderInfos['amount_internal_order'], 8), "0").' '.$OrderInfos['to_internal_order']; ?></span>
      <?php else: ?>
        <span><?php echo $OrderInfos['symbol_internal_order']; ?> <?php echo $Lang->tr('Amount'); ?></span>
        <div></div>
        <span><?php echo rtrim($App->_formatNumber($OrderInfos['amount_internal_order'], 8), "0").' '.$OrderInfos['symbol_internal_order']; ?></span>
      <?php endif; ?>
    </li>
  </ul>
  <ul class="kr-orderinfoside-minf">
    <li>
      <span><?php echo $Lang->tr('Ordered price'); ?> (1 <?php echo ($OrderInfos['side_internal_order'] == "BUY" ? $OrderInfos['to_internal_order'] : $OrderInfos['symbol_internal_order']); ?>)</span>
      <div></div>
      <span><?php
      if($OrderInfos['side_internal_order'] == "BUY"){
        $OrderedPrice = (1 / $OrderInfos['amount_internal_order']) * $OrderInfos['usd_amount_internal_order'];
      } else {
        $OrderedPrice = (1 / $OrderInfos['amount_internal_order']) * $OrderInfos['usd_amount_internal_order'];
      }

        echo rtrim($App->_formatNumber($OrderedPrice, ($OrderedPrice > 1 ? 4 : 8)), "0").' '.($OrderInfos['side_internal_order'] == "BUY" ? $OrderInfos['symbol_internal_order'] : $OrderInfos['to_internal_order']);
        ?></span>
    </li>
    <li>
      <span><?php echo $Lang->tr('Current price'); ?> (1 <?php echo ($OrderInfos['side_internal_order'] == "BUY" ? $OrderInfos['to_internal_order'] : $OrderInfos['symbol_internal_order']); ?>)</span>
      <div></div>
      <span><?php
      $CurrentPrice = $Balance->_convertCurrency(1, $OrderInfos['symbol_internal_order'], $OrderInfos['to_internal_order'], strtolower($OrderInfos['thirdparty_internal_order']));
      if($OrderInfos['side_internal_order'] == "BUY") $CurrentPrice = 1 / $CurrentPrice;

      $Evolution = 0;
      if($CurrentPrice > 0) {
        $Evolution = (100 - ($OrderedPrice / $CurrentPrice) * 100);
        if($OrderInfos['side_internal_order'] == "SELL") $Evolution = (100 - ($CurrentPrice / $OrderedPrice) * 100);
      }

      $DiffOrder = $CurrentPrice - $OrderedPrice;
      if($OrderInfos['side_internal_order'] == "SELL"){
        $DiffOrder = $OrderedPrice - $CurrentPrice;
      }

      echo rtrim($App->_formatNumber($CurrentPrice, ($CurrentPrice > 1 ? 4 : 8)), "0").' '.($OrderInfos['side_internal_order'] == "BUY" ? $OrderInfos['symbol_internal_order'] : $OrderInfos['to_internal_order']); ?></span>
    </li>
    <li>
      <span><?php echo $Lang->tr('Evolution'); ?></span>
      <div></div>
      <span style="color:<?php echo ($Evolution > 0 ? '#29c359' : '#e01616'); ?>"><?php echo ($DiffOrder > 0 ? '+' : '').''.rtrim($App->_formatNumber($DiffOrder, ($DiffOrder > 1 ? 2 : ($DiffOrder < -1 ? 2 : 6)))).' '.
                      ($OrderInfos['side_internal_order'] == "BUY" ? $OrderInfos['symbol_internal_order'] : $OrderInfos['to_internal_order']); ?>
          <i>(<?php echo $App->_formatNumber($Evolution, 2); ?>%)</i>
          </span>
    </li>
  </ul>
</section>
