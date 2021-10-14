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

    if($App->_hiddenThirdpartyActive()) throw new Exception("Permission denied", 1);

    $Trade = new Trade($User, $App);
    $listThirdParty = $Trade->_getThirdPartyListAvailable();
    $selectedThirdParty = $listThirdParty[0];
    $balanceList = $selectedThirdParty->_getBalance(true);

} catch (\Exception $e) {
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}

if(count($balanceList) == 0){ ?>

  <section class="kr-balance-trhistory-empty">
    <span>No balance available</span>
  </section>

<?php
} else {
?>
<section class="kr-balance-trhistory-histo kr-balance-blist">
  <?php foreach ($balanceList as $symbol => $balanceItem) {
  ?>
  <div>
    <div>
      <span><?php echo $symbol; ?></span>
    </div>
    <div class="kr-balance-blist-nmb">
      <label>Available</label>
      <span><?php echo $App->_formatNumber($balanceItem['free'], ($balanceItem['free'] > 10 ? 2 : 5)).' '.$symbol; ?></span>
    </div>
    <div class="kr-balance-blist-nmb">
      <label>Locked</label>
      <span><?php echo $App->_formatNumber($balanceItem['used'], ($balanceItem['used'] > 10 ? 2 : 5)).' '.$symbol; ?></span>
    </div>
  </div>
  <?php
  } ?>
</section>
<?php } ?>
