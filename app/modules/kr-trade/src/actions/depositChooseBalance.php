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

    $Balance = new Balance($User, $App);
    $Balance = $Balance->_getCurrentBalance();
    $SymbolAvailable = $Balance->_getBalanceListResum();

    $Lang = new Lang($User->_getLang(), $App);

    $InfosCurrency = $Balance->_getInfosMoney(strtoupper($User->_getCurrency()));

    if($App->_getIdentityEnabled()) $Identity = new Identity($User);

} catch (\Exception $e) {
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}

?>

<section class="kr-balance-credit-cblance">
  <?php foreach ($Balance->_getBalanceList() as $BalanceItem) {
    $BalanceValue = null;
    if($App->_getBalanceEstimationShown()){
      $EstimatedValueBalance = $BalanceItem->_getEstimationBalance();
      $EstimatedValueSymbol = $BalanceItem->_getEstimationSymbol();
    }

    ?>
    <section class="kr-balance-credit-choose-<?php echo $BalanceItem->_getBalanceType(); ?>">
      <img src="<?php echo APP_URL; ?>/app/modules/kr-trade/statics/img/<?php echo $BalanceItem->_getBalanceType(); ?>.svg" alt="">
      <h3><?php echo $Lang->tr($BalanceItem->_getBalanceType().' account'); ?></h3>
      <span class="kr-balance-credit-b-ammc"><?php echo $App->_formatNumber($EstimatedValueBalance, 2).' '.$EstimatedValueSymbol; ?></span>
      <div kr-balance-credit="cmd"
            <?php if($BalanceItem->_getBalanceType() == "real" && $App->_getIdentityEnabled() && $App->_getIdentityDepositBlocked() && !$Identity->_identityVerified()): ?>
              onclick="_closeCreditForm();_showIdentityWizard();return false;"
            <?php endif; ?>
            kr-balance-idc="<?php echo $BalanceItem->_getBalanceID(true); ?>" kr-balance-type="<?php echo $BalanceItem->_getBalanceType(); ?>" class="btn btn-big btn-autowidth btn-<?php echo ($BalanceItem->_getBalanceType() == "practice" ? 'orange' : 'green'); ?> <?php echo ($BalanceItem->_limitReached() ? 'kr-balance-credit-dibl' : ''); ?>">
        <?php if($BalanceItem->_getBalanceType() == "real"): ?>
          <span><?php echo $Lang->tr('Add real funds'); ?></span>
          <span><?php echo $Lang->tr('Minimal deposit'); ?> : <?php echo $App->_formatNumber($App->_getMinimalDeposit() * $InfosCurrency['usd_rate_currency'], 2).' '.$InfosCurrency['symbol_currency']; ?></span>
        <?php else: ?>
          <span><?php echo $Lang->tr('Fill up'); ?> <?php echo $App->_formatNumber($App->_getMaximalFreeDeposit() * $InfosCurrency['usd_rate_currency'], 2).' '.$InfosCurrency['symbol_currency']; ?></span>
          <span><?php echo $Lang->tr("It's free"); ?></span>
        <?php endif; ?>
      </div>
      <?php if($BalanceItem->_getBalanceType() == "practice"):
        if(is_null($SymbolAvailable) || count($SymbolAvailable) == 0){
          ?>
          <label> You need to active at least 1 exchange</label>
          <?php
        } else {
          if(is_null($App->_getFreeDepositSymbol()) || strlen($App->_getFreeDepositSymbol()) == 0){
            ?>
            <label style="text-align:center;">You need to select the free currency given (Admin -> Trading)</label>
            <?php
          } else {

          $ConvertInfos = $Balance->_convertCurrency($App->_getMaximalFreeDeposit(), 'USD', $App->_getFreeDepositSymbol());
        ?>
        <label><?php echo $Lang->tr('You will receive'); ?> <?php echo $App->_formatNumber($ConvertInfos, (App::_getNumberDecimal($ConvertInfos) > 8 ? 8 : (App::_getNumberDecimal($ConvertInfos) < 2 ? 2 : App::_getNumberDecimal($ConvertInfos)))).' '.$App->_getFreeDepositSymbol(); ?></label>
          <?php
        }
        }

      endif; ?>
    </section>
  <?php } ?>
</section>
