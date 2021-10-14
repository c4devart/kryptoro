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

    $Balance = new Balance($User, $App, 'real');
    $TransactionsHistory = $Balance->_getTransactionsHistory();

} catch (\Exception $e) {
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}

?>

<?php if(count($TransactionsHistory) == 0): ?>
<section class="kr-balance-trhistory-empty">
  <span><?php echo $Lang->tr('Transactions history is empty.'); ?></span>
</section>
<?php else: ?>
<section class="kr-balance-trhistory-histo">
  <?php foreach ($TransactionsHistory as $dataHisto) {

    $Decimal = 8;
    if($Balance->_symbolIsMoney($dataHisto['currency'])) $Decimal = 2;

  ?>
  <div>
    <div>
      <span><?php echo $dataHisto['type_histo']; ?></span>
    </div>
    <div>
      <span><?php echo date('d/m/Y H:i:s', $dataHisto['date_histo']); ?></span>
    </div>
    <div>
      <?php echo $dataHisto['description_histo']; ?>
    </div>
    <div>
      <?php if($dataHisto['type_histo'] == "deposit"): ?>
        <span class="kr-balance-trhistory-histo-dep">+<?php echo $App->_formatNumber($dataHisto['amount_histo'], $Decimal).' '.$dataHisto['currency']; ?></span>
      <?php else: ?>
        <span>-<?php echo $App->_formatNumber($dataHisto['amount_histo'], $Decimal).' '.$dataHisto['currency']; ?></span>
      <?php endif; ?>
    </div>
  </div>
  <?php
  } ?>
</section>
<?php endif; ?>
