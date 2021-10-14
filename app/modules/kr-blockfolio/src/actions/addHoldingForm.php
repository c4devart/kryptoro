<?php

/**
 * Edit indicator action
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
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/Lang/Lang.php";
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
      throw new Exception("User is not logged", 1);
  }

  if(empty($_GET) || !isset($_GET['symbol']) || empty($_GET['symbol'])) throw new Exception("Pair not given", 1);


  $CryptoApi = new CryptoApi($User, null, $App);
  $Coin = new CryptoCoin($CryptoApi, $_GET['symbol']);

  $CoinPrice = $Coin->_getPrice();


} catch (Exception $e) {
  die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}
?>
<section class="kr-block-add-holding">
  <section>
    <header>
      <span>Add transaction</span>
      <div onclick="closeAddTransaction();">
        <svg class="lnr lnr-cross"><use xlink:href="#lnr-cross"></use></svg>
      </div>
    </header>
    <form class="kr-block-add-holding-form" method="post">
      <section class="kr-block-addh-line">
        <div>
          Transaction pair
        </div>
        <div class="kr-block-addh-cellin">
          <?php echo $Coin->_getSymbol(); ?> / USD
        </div>
      </section>
      <section class="kr-block-addh-cp">
          <label>Current price</label>
          <span class="kr-mono"><?php echo $App->_formatNumber($CoinPrice); ?> $</span>
      </section>
      <ul class="kr-block-addh-type">
        <li class="kr-block-addh-type-selected" t="buy">Buy</li>
        <li t="sell">Sell</li>
      </ul>
      <ul class="kr-block-addh-editf">
        <li>
          <span>Trading price</span>
          <input type="text" id="kr-hld-tp" class="kr-hld-changetv" name="trading_price" placeholder="<?php echo $CoinPrice; ?>" value="<?php echo $CoinPrice; ?>">
        </li>
        <li>
          <span>Quantity</span>
          <input type="text" id="kr-hld-qt" class="kr-hld-changetv" name="quantity" placeholder="0.5" value="0.5">
        </li>
        <li>
          <span>Trading date</span>
          <input type="text" name="trading_date" class="datepicker-here" data-language='en' data-position="top left" placeholder="<?php echo date('d/m/Y'); ?>" value="<?php echo date('d/m/Y'); ?>">
        </li>
      </ul>
      <section class="kr-block-addh-cp">
          <label>Total value</label>
          <span class="kr-mono" id="kr-hld-tv"><?php echo $App->_formatNumber($CoinPrice * 0.5); ?> $</span>
      </section>
      <footer>
        <input type="button" onclick="closeAddTransaction();" class="btn btn-small" name="" value="Cancel">
        <input type="hidden" name="trading_symbol" value="<?php echo $Coin->_getSymbol(); ?>">
        <input type="submit" class="btn btn-small btn-orange" name="" value="Add">
      </footer>
    </form>
  </section>
</section>
