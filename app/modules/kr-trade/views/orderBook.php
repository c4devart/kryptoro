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

    $Lang = new Lang($User->_getLang(), $App);

} catch(Exception $e){
  die($e->getMessage());
}

if(!$App->_hiddenThirdpartyActive()):

  function order_book($a, $b){
    return $a['time'] > $b['time'];
  }

  $Trade = new Trade($User, $App);
  $selectedThirdParty = $Trade->_getSelectedThirdparty();
  //$balanceList = $selectedThirdParty->_getBalance(true);
  $BookList = [];
  $CryptoApi = new CryptoApi($User, null, $App);
  foreach ($Trade->_getListOrderSymbol($selectedThirdParty->_getExchangeName()) as $key => $value) {
    $BookList = array_merge($selectedThirdParty->_getOrderBook($value), $BookList);
  }
  usort($BookList, "order_book");
  if(count($BookList) == 0){
    ?>
    <section><?php echo $Lang->tr('No order to show'); ?></section>
    <?php
  } else {
  echo '<ul class="kr-bookorder-native">';
  foreach (array_reverse($BookList) as $OrderDetails) {
    $OrderCoin = $CryptoApi->_getCoin($OrderDetails['symbol']);
    $OrderID = App::encrypt_decrypt('encrypt', time().'-'.$OrderDetails['id']);
    ?>
    <li>
      <div>
        <span><?php echo date('H:i', $OrderDetails['time'] / 1000); ?></span>
        <span><?php echo date('d M', $OrderDetails['time'] / 1000); ?></span>
      </div>
      <div>
        <div>
          <span><?php echo $OrderDetails['market']; ?></span>
        </div>
        <?php
        if($OrderDetails['side'] == "BUY"):
        ?>
          <span><i>&#9662;</i> <?php echo $App->_formatNumber($OrderDetails['total'], 5).' '.$OrderDetails['currency']; ?></span>
        <?php else: ?>
          <span style="color:#2bab3f;font-weight:bold;opacity:1;"><i>&#9652;</i> <?php echo $App->_formatNumber($OrderDetails['total'], 5).' '.$OrderDetails['symbol']; ?></span>
        <?php endif; ?>
      </div>
      <div>
        <?php

        if($OrderDetails['side'] == "BUY"):
        ?>
          <span style="color:#2bab3f;font-weight:bold;opacity:1;"><i>&#9652;</i> <?php echo $App->_formatNumber($OrderDetails['size'], 5); ?></span>
          <span><?php echo $OrderDetails['symbol']; ?></span>
        <?php else: ?>
          <span><i>&#9662;</i> <?php echo $App->_formatNumber($OrderDetails['size'], 5); ?></span>
          <span><?php echo $OrderDetails['currency']; ?></span>
        <?php endif; ?>
      </div>
    </li>

    <?php
  }

  echo '</ul>';
}

else:
  $CryptoApi = new CryptoApi($User, null, $App);

  $Balance = new Balance($User, $App);
  $CurrentBalance = $Balance->_getCurrentBalance();
?>
<div class="kr-orderbookside-resum">

</div>
<?php
$BookList = $CurrentBalance->_getOrderHistory();
if(count($BookList) == 0):
?>
  <section><?php echo $Lang->tr('No order to show'); ?></section>
<?php else:

  ?>
<ul class="kr-bookorder-native">
  <?php
  foreach (array_reverse($BookList) as $OrderDetails) {
    $OrderCoin = $CryptoApi->_getCoin($OrderDetails['symbol_internal_order']);
    $OrderID = App::encrypt_decrypt('encrypt', time().'-'.$OrderDetails['id_internal_order']);
  ?>
  <li kr-bookorder-if="<?php echo $OrderID; ?>" onclick="showOrderInfos('<?php echo $OrderID; ?>')">
    <div>
      <span><?php echo date('H:i', $OrderDetails['date_internal_order']); ?></span>
      <span><?php echo date('d M', $OrderDetails['date_internal_order']); ?></span>
    </div>
    <div>
      <div>
        <?php
        if($OrderDetails['type_internal_order'] == "limit" && $OrderDetails['status_internal_order'] == "0"):
        ?>
        <svg class="lnr lnr-clock" style="width:13px; height:13px;fill:#fff; margin-right:5px;"><use xlink:href="#lnr-clock"></use></svg>
        <?php endif; ?>
        <span><?php echo $OrderDetails['symbol_internal_order'].'/'.$OrderDetails['to_internal_order']; ?></span>
      </div>
      <?php
      if($OrderDetails['side_internal_order'] == "BUY"):
      ?>
        <span><i>&#9662;</i> <?php echo $App->_formatNumber($OrderDetails['amount_internal_order'], 5).' '.$OrderDetails['to_internal_order']; ?></span>
      <?php else: ?>
        <span style="color:#2bab3f;font-weight:bold;opacity:1;"><i>&#9652;</i> <?php echo $App->_formatNumber($OrderDetails['usd_amount_internal_order'] - $OrderDetails['fees_internal_order'], 5).' '.$OrderDetails['to_internal_order']; ?></span>
      <?php endif; ?>
    </div>
    <div>
      <?php
      if($OrderDetails['side_internal_order'] == "BUY"):
      ?>
        <span style="color:#2bab3f;font-weight:bold;opacity:1;"><i>&#9652;</i> <?php echo $App->_formatNumber($OrderDetails['usd_amount_internal_order'] - $OrderDetails['fees_internal_order'], 5); ?></span>
        <span><?php echo $OrderDetails['symbol_internal_order']; ?></span>
      <?php else: ?>
        <span><i>&#9662;</i> <?php echo $App->_formatNumber($OrderDetails['amount_internal_order'], 5); ?></span>
        <span><?php echo $OrderDetails['symbol_internal_order']; ?></span>
      <?php endif; ?>
    </div>
  </li>
  <?php
}
  ?>
</ul>
<?php endif; ?>
<?php endif; ?>
