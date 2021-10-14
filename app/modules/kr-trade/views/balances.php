<?php

session_start();

require "../../../../config/config.settings.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/vendor/autoload.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/MySQL/MySQL.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/User/User.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/Lang/Lang.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/App.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/AppModule.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoIndicators.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoGraph.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoHisto.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoCoin.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoApi.php";

$App = new App(true);
$App->_loadModulesControllers();

$User = new User();
if(!$User->_isLogged()) die('Error : User not logged');

$Lang = new Lang($User->_getLang(), $App);

try {
  // Init CryptoApi object
  $CryptoApi = new CryptoApi($User, ['BTC', 'BTC'], $App);

  $Balance = new Balance($User, $App);
  $BitcoinBalanceEstimation = 0;
  $BalanceEstimationSymbol = $Balance->_getEstimationSymbol();
  $BalanceEstimation = 0;
  if($App->_hiddenThirdpartyActive()){

    if(!$App->_hiddenThirdpartyNotConfigured()) throw new Exception("You must activate at least 1 exchange (Admin -> Trading)", 1);


    $Balance = $Balance->_getCurrentBalance();

    $BitcoinBalanceEstimation = $Balance->_getEstimationBalance('BTC');
    $BalanceEstimationSymbol = $Balance->_getEstimationSymbol();
    $BalanceEstimation = $Balance->_getEstimationBalance();
  } else {
    $Trade = new Trade($User, $App);
    $listThirdParty = $Trade->_getThirdPartyListAvailable();

    $selectedThirdParty = $listThirdParty[0];

    $BalanceList = $selectedThirdParty->_getBalance(true);

    $BitcoinBalanceEstimation = $selectedThirdParty->_getBalanceEstimation('BTC', $Balance);
    $BalanceEstimation = $selectedThirdParty->_getBalanceEstimation($Balance->_getEstimationSymbol(true), $Balance);
  }

  if($App->_getIdentityEnabled()) $Identity = new Identity($User);


} catch (\Exception $e) {
  die("<span style='color:#f4f6f9;'>".$e->getMessage()."</span>");
}

?>

<section class="kr-balance-view">
  <header>
    <h2><?php echo $Lang->tr('Balances'); ?></h2>
    <div>
      <div>
        <input type="text" class="kr-balances-view-search" name="" value="" placeholder="<?php echo $Lang->tr('Currency'); ?> ...">
        <div class="kr-balances-view-tggsmall" title="<?php echo $Lang->tr('Balances valued less than 0.001 BTC'); ?>">
          <label><?php echo $Lang->tr('Hide small balance'); ?></label>
        </div>
      </div>
      <span><?php echo $Lang->tr('Estimate value'); ?> : <b><?php echo $App->_formatNumber($BitcoinBalanceEstimation, 8); ?> BTC / <?php echo $App->_formatNumber($BalanceEstimation, 2).' '.$BalanceEstimationSymbol; ?> </b></span>
    </div>
  </header>

  <div class="kr-marketlist">
    <div class="kr-marketlist-header">
      <div class="kr-marketlist-n"></div>
      <div class="kr-mono"><span><?php echo $Lang->tr('Name'); ?></span></div>
      <?php if($App->_hiddenThirdpartyActive()): ?>
        <div class="kr-mono"><span><?php echo $Lang->tr('Total'); ?></span></div>
      <?php else: ?>
        <div class="kr-mono"><span><?php echo $Lang->tr('Total Free'); ?></span></div>
        <div class="kr-mono"><span><?php echo $Lang->tr('Total Used'); ?></span></div>
      <?php endif; ?>
      <div class="kr-mono"><span><?php echo $Lang->tr('Value in').' BTC'; ?></span></div>
      <div class="kr-mono"><span><?php echo $Lang->tr('Value in').' '.$Balance->_getEstimationSymbol(true); ?></span></div>
      <?php if($App->_hiddenThirdpartyActive()): ?>
        <div style="flex:0.6;" class="kr-marketlist-cellnumber kr-mono"></div>
      <?php endif; ?>
    </div>

    <?php
    if($App->_hiddenThirdpartyActive()){

      $DepositSymbolAllowed = array_values($Balance->_getDepositListAvailable());
      $DepositSymbolAllowed = array_merge($DepositSymbolAllowed, $App->_getCoinGateCryptoCurrencyDepositAllowed());

      foreach ($Balance->_getBalanceListResum() as $key => $value) {
        $NameCoin = "NULL";
        $CurrencySymbol = $key;
        $DecimalShown = 8;
        if($Balance->_symbolIsMoney($key)){
          $InfosCurrency = $Balance->_getInfosMoney($key);
          $NameCoin = $InfosCurrency['name_currency'];
          $CurrencySymbol = $InfosCurrency['symbol_currency'];
          $DecimalShown = 2;
          $SymbolConvertPrice = 0;
          $SymbolConvertPriceIndicatif = 0;
          if($value > 0){
            $SymbolConvertPrice = $Balance->_convertCurrency($value, $key, 'BTC');
            $SymbolConvertPriceIndicatif = $Balance->_convertCurrency($SymbolConvertPrice, 'BTC', $Balance->_getEstimationSymbol(true));
          }

        } else {
          try {
            $Coin = $CryptoApi->_getCoin($key);
            $NameCoin = $Coin->_getCoinName();
            $SymbolConvertPrice = 0;
            if($value > 0) $SymbolConvertPrice = $Coin->_convertTo('BTC', $value);

            $SymbolConvertPriceIndicatif = 0;
            if($value > 0) $SymbolConvertPriceIndicatif = $Coin->_convertTo($Balance->_getEstimationSymbol(true), $SymbolConvertPrice, 'BTC');

          } catch (\Exception $e) {
            continue;
          }
        }


        ?>
        <div class="kr-marketlist-item kr-balanceitem-cv" kr-balance-item-value-currency="<?php echo $key; ?>" kr-balance-item-value="<?php echo $SymbolConvertPrice; ?>">
          <div class="kr-marketlist-n">
            <div class="kr-marketlist-n-nn">
              <label class="kr-mono"><?php echo $key; ?></label>
            </div>
          </div>
          <div class="kr-mono">
            <span><?php echo $NameCoin; ?></span>
          </div>
          <div class="kr-mono">
            <span><?php echo $App->_formatNumber($value, $DecimalShown).' '.$CurrencySymbol; ?></span>
          </div>
          <div class="kr-mono">
            <span><?php echo $App->_formatNumber($SymbolConvertPrice, 8); ?> BTC</span>
          </div>
          <div class="kr-mono">
            <span><?php echo $App->_formatNumber($SymbolConvertPriceIndicatif, 2); ?></span>
          </div>
          <div class="kr-marketlist-cellnumber kr-mono" style="flex:0.6;">
            <?php if(in_array($key, $DepositSymbolAllowed)): ?>
              <button type="button" style="margin-right:8px;" onclick="_loadCreditForm('depositRealBalance', {symbol:'<?php echo $key; ?>','type':'symbol'});" class="btn btn-xssmall btn-autowidth btn-green" name="button"><?php echo $Lang->tr('Deposit'); ?></button>
            <?php endif; ?>
            <?php if($Balance->_getType() == "real"): ?>
              <?php if($App->_getIdentityEnabled()): ?>
                <?php if($App->_getIdentityWithdrawBlocked()): ?>
                  <?php if($Identity->_identityVerified()): ?>
                    <button type="button" class="btn btn-xssmall btn-autowidth" onclick="_askWidthdraw('<?php echo $key; ?>');" name="button"><?php echo $Lang->tr('Withdraw'); ?></button>
                  <?php else: ?>
                    <a onclick="_showIdentityWizard();return false;" class="btn btn-grey btn-autowidth btn-small"><?php echo $Lang->tr('Widthdraw'); ?></a>
                  <?php endif; ?>
                <?php else: ?>
                  <button type="button" class="btn btn-xssmall btn-autowidth" onclick="_askWidthdraw('<?php echo $key; ?>');" name="button"><?php echo $Lang->tr('Withdraw'); ?></button>
                <?php endif; ?>
              <?php else: ?>
                <button type="button" class="btn btn-xssmall btn-autowidth" onclick="_askWidthdraw('<?php echo $key; ?>');" name="button"><?php echo $Lang->tr('Withdraw'); ?></button>
              <?php endif; ?>
            <?php endif; ?>

          </div>
        </div>
        <?php
      }
    } else {

      foreach ($BalanceList as $key => $value) {
        $infosValue = $value;
        $value = $value['free'] + $value['used'];
        $NameCoin = "NULL";
        $CurrencySymbol = $key;
        $DecimalShown = 8;
        if($Balance->_symbolIsMoney($key)){
          $InfosCurrency = $Balance->_getInfosMoney($key);
          $NameCoin = $InfosCurrency['name_currency'];
          $CurrencySymbol = $InfosCurrency['symbol_currency'];
          $DecimalShown = 2;
          $SymbolConvertPrice = 0;
          $SymbolConvertPriceIndicatif = 0;
          if($value > 0){
            $SymbolConvertPrice = $Balance->_convertCurrency($value, $key, 'BTC');
            $SymbolConvertPriceIndicatif = $Balance->_convertCurrency($SymbolConvertPrice, 'BTC', $Balance->_getEstimationSymbol(true));
          }

        } else {
          try {
            $Coin = $CryptoApi->_getCoin($key);
            $NameCoin = $Coin->_getCoinName();
            $SymbolConvertPrice = 0;
            if($value > 0) $SymbolConvertPrice = $Coin->_convertTo('BTC', $value);

            $SymbolConvertPriceIndicatif = 0;
            if($value > 0) $SymbolConvertPriceIndicatif = $Coin->_convertTo($Balance->_getEstimationSymbol(true), $SymbolConvertPrice, 'BTC');
          } catch (\Exception $e) {
            continue;
          }
        }
        ?>
        <div class="kr-marketlist-item kr-balanceitem-cv" kr-balance-item-value-currency="<?php echo $key; ?>" kr-balance-item-value="<?php echo $SymbolConvertPrice; ?>">
          <div class="kr-marketlist-n">
            <div class="kr-marketlist-n-nn">
              <label class="kr-mono"><?php echo $key; ?></label>
            </div>
          </div>
          <div class="kr-mono">
            <span><?php echo $NameCoin; ?></span>
          </div>
          <div class="kr-mono">
            <span><?php echo $App->_formatNumber($infosValue['free'], $DecimalShown).' '.$CurrencySymbol; ?></span>
          </div>
          <div class="kr-mono">
            <span><?php echo $App->_formatNumber($infosValue['used'], $DecimalShown).' '.$CurrencySymbol; ?></span>
          </div>
          <div class="kr-mono">
            <span><?php echo $App->_formatNumber($SymbolConvertPrice, 8); ?> BTC</span>
          </div>
          <div class="kr-mono">
            <span><?php echo $App->_formatNumber($SymbolConvertPriceIndicatif, 2); ?></span>
          </div>
        </div>
        <?php
      }
    }
    ?>
  </div>
</section>
