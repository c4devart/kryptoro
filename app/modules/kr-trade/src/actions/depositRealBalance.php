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

    $Lang = new Lang($User->_getLang(), $App);

    if(!$App->_hiddenThirdpartyActive()) throw new Exception("Permission denied", 1);
    if(is_null($App->_hiddenThirdpartyServiceCfg()) || count($App->_hiddenThirdpartyServiceCfg()) == 0) throw new Exception("You must activate at least 1 exchange (Admin -> Trading)", 1);



    $Balance = new Balance($User, $App, 'real');

    $BalanceList = $Balance->_getBalanceListResum();
    $symbolFetched = array_keys($BalanceList)[0];
    if(!isset($_POST['symbol']) || empty($_POST['symbol'])) $_POST['symbol'] = "BTC";
    $typeFetched = 'symbol';
    if(!empty($_POST) && isset($_POST['symbol']) && (array_key_exists($_POST['symbol'], $BalanceList) || in_array($_POST['symbol'], $App->_getListCurrencyDepositAvailable()))) $symbolFetched = $_POST['symbol'];
    if(!empty($_POST) && isset($_POST['type']) && $_POST['type'] == "bank_transfert") {
      $typeFetched = $_POST['type'];
      $symbolFetched = "null";
    }

    $Widthdraw = new Widthdraw($User);

    $IsRealMoney = $Balance->_symbolIsMoney($symbolFetched);

    $PaymentMethodList = $Widthdraw->_getWidthdrawMethod(($IsRealMoney ? 'currency' : 'crypto'));

    $BalanceListDeposit = $Balance->_getDepositListAvailable();


} catch (\Exception $e) {
  echo '<b style="color:#fff;">'.$e->getMessage().'</b>';
  die();
}

?>
<div class="spinner" style="display:none;"></div>
<section class="kr-balance-credit-drel-cont" kr-bssymbol="<?php echo $symbolFetched; ?>">

  <?php
  $navSymbolDone = [];
  ?>
  <nav>
    <ul>
      <?php if($App->_getBankTransfertEnable()): ?>
        <li class="svg-icon-deposit-balance <?php if($typeFetched == 'bank_transfert') echo 'kr-balance-widthdraw-selected'; ?>" onclick="_loadCreditForm('depositRealBalance', {type:'bank_transfert'});">
          <label><?php echo $Lang->tr('Bank transfert'); ?></label>
        </li>
      <?php endif; ?>
      <?php
      if(count($BalanceListDeposit) > 0){
        foreach ($BalanceListDeposit as $keyDepositSymbol => $symbolDepositSymbol) {
          $navSymbolDone[] = $symbolDepositSymbol;
          ?>
          <li class="<?php if($symbolFetched == $symbolDepositSymbol) echo 'kr-balance-widthdraw-selected'; ?>" onclick="_loadCreditForm('depositRealBalance', {symbol:'<?php echo $symbolDepositSymbol; ?>'});">
            <label><?php echo $symbolDepositSymbol; ?></label>
          </li>
          <?php
        }
      }

      if($App->_getBlockonomicsEnabled() && false){

        foreach ($App->_getListBlockonomicsCurrencyAllowed() as $blocoSymbol) {
          if(!array_key_exists($blocoSymbol, $BalanceList) || in_array($blocoSymbol, $navSymbolDone)) continue;
          $navSymbolDone[] = $blocoSymbol;
          ?>
          <li class="<?php if($symbolFetched == $blocoSymbol) echo 'kr-balance-widthdraw-selected'; ?>" onclick="_loadCreditForm('depositRealBalance', {symbol:'<?php echo $blocoSymbol; ?>','type':'symbol'});">
            <label><?php echo $blocoSymbol; ?></label>
          </li>
          <?php
        }

      }

      if($App->_coingateEnabled()){

        foreach ($App->_getCoinGateCryptoCurrencyDepositAllowed() as $coinGateSymbol) {
          if(!array_key_exists($coinGateSymbol, $BalanceList) || in_array($coinGateSymbol, $navSymbolDone)) continue;
          $navSymbolDone[] = $coinGateSymbol;
          ?>
          <li class="<?php if($symbolFetched == $coinGateSymbol) echo 'kr-balance-widthdraw-selected'; ?>" onclick="_loadCreditForm('depositRealBalance', {symbol:'<?php echo $coinGateSymbol; ?>','type':'symbol'});">
            <label><?php echo $coinGateSymbol; ?></label>
          </li>
          <?php
        }

      }

      if($App->_coinpaymentsEnabled()){

        $Coinpayment = new Coinpayments($App);

        foreach ($Coinpayment->_getCurrencyAvailable() as $coinGateSymbol) {
          if(!array_key_exists($coinGateSymbol, $BalanceList) || in_array($coinGateSymbol, $navSymbolDone)) continue;
          $navSymbolDone[] = $coinGateSymbol;
          ?>
          <li class="<?php if($symbolFetched == $coinGateSymbol) echo 'kr-balance-widthdraw-selected'; ?>" onclick="_loadCreditForm('depositRealBalance', {symbol:'<?php echo $coinGateSymbol; ?>','type':'symbol'});">
            <label><?php echo $coinGateSymbol; ?></label>
          </li>
          <?php
        }

      }

      ?>

    </ul>
  </nav>
  <section>
    <?php if($typeFetched == "symbol" && ($App->_coinpaymentsEnabled() || $App->_coingateEnabled()
                                          || $App->_polipaymentsEnabled() || $App->_paystackEnabled()
                                          || $App->_mollieEnabled() || $App->_getPayeerEnabled() || $App->_raveflutterwaveEnabled()
                                          || $App->_coinbasecommerceEnabled())):

      //if($IsRealMoney || !in_array($symbolFetched, $App->_getListBlockonomicsCurrencyAllowed()) || !$App->_getBlockonomicsEnabled()):
      if(true):

      if(!$App->_paymentIsEnabled()){
        ?>
        <div style="color:#f4f6f9;">
          <b><?php echo $Lang->tr('You must activate at least 1 payment system in this list :'); ?></b>
          <ul style="margin-left:17px; margin-top:5px;">
            <?php
            foreach ($App->_getPaymentListAvailableTrading() as $key => $value) {
              echo '<li style="list-style:square;margin-bottom:5px;">'.$value.'</li>';
            }
            ?>
          </ul>
        </div>
        <?php
      } else {

      $precision = 2;
      if($IsRealMoney){
        $InfosCurrency = $Balance->_getInfosMoney($symbolFetched);
      }
      else {
        $InfosCurrency = $Balance->_getInfoCryptoCurrency($symbolFetched);
        $precision = 5;
      }

      $MinimalDeposit = $App->_getMinimalDeposit() * floatval($InfosCurrency['usd_rate_currency']);

      ?>
      <h3><?php echo $Lang->tr('Deposit amount'); ?></h3>
      <div class="kr-balance-range-content kr-balance-range-content-deposit">
        <input type="text" class="kr-balance-range-inp-deposit" name="" value="<?php echo round($MinimalDeposit, ($IsRealMoney ? 2 : 5)); ?>">
        <div>
          <div class="kr-balance-range" kr-chosamount-precision="<?php echo $precision; ?>">
            <input type="text" id="kr-credit-chosamount" kr-chosamount-step="<?php echo ($MinimalDeposit < 1 ? 0.001 : 1); ?>" kr-chosamount-symbol="<?php echo $InfosCurrency['symbol_currency']; ?>" kr-chosamount-max="<?php echo round($App->_getMaximalDeposit() * floatval($InfosCurrency['usd_rate_currency']), 2); ?>" kr-chosamount-min="<?php echo round($MinimalDeposit, ($MinimalDeposit < 1 ? 3 : 2)); ?>" name="kr-credit-chosamount" value="" />
          </div>
        </div>
      </div>
      <div class="kr-credit-feescalc">
        <div kr-credit-calcfees="amount">
          <label><?php echo $Lang->tr('Amount'); ?></label>
          <span><i><?php echo $App->_formatNumber($App->_getMinimalDeposit() * floatval($InfosCurrency['usd_rate_currency']), $precision); ?></i> <?php echo $InfosCurrency['symbol_currency']; ?></span>
        </div>
        <div kr-credit-calcfees="fees" kr-credit-calcfees-am="<?php echo $App->_getFeesDeposit(); ?>">
          <label><?php echo $Lang->tr('% Fees'); ?> (<?php echo $App->_formatNumber($App->_getFeesDeposit(), 2); ?> %)</label>
          <span><i><?php echo $App->_formatNumber(($App->_getMinimalDeposit() * floatval($InfosCurrency['usd_rate_currency'])) * ($App->_getFeesDeposit() / 100), $precision); ?></i> <?php echo $InfosCurrency['symbol_currency']; ?></span>
        </div>
        <div kr-credit-calcfees="total">
          <label><?php echo $Lang->tr('Total'); ?></label>
          <input type="hidden" kr-charges-payment-vamdepo="cvmps" name="" value="<?php echo $MinimalDeposit; ?>">
          <span><i><?php echo $App->_formatNumber(($App->_getMinimalDeposit() * floatval($InfosCurrency['usd_rate_currency'])) + (($App->_getMinimalDeposit() * floatval($InfosCurrency['usd_rate_currency'])) * ($App->_getFeesDeposit() / 100)), $precision); ?></i> <?php echo $InfosCurrency['symbol_currency']; ?></span>
        </div>
      </div>
      <ul>

        <?php
        if($App->_getDirectDepositEnable() && false):
          $BlockExplorer = new BlockExplorer($App, null);
          if(array_key_exists($symbolFetched, $BlockExplorer->_getDepositAddress())):
          ?>
          <li kr-charges-payment="directdeposit" kr-cng-lt="<?php echo time() - 2; ?>">
            <a>
              <img src="<?php echo APP_URL.'/assets/img/icons/payment/qrcode.svg'; ?>" alt="">
            </a>
            <?php if($Balance->_getPaymentGatewayFee('directdeposit') > 0): ?>
              <label>+ <?php echo $Balance->_getPaymentGatewayFee('directdeposit').' '.$Lang->tr('% Fees'); ?></label>
            <?php endif; ?>
          </li>
        <?php endif;
        endif; ?>
        <?php if($App->_coingateEnabled()): ?>
        <li kr-charges-payment="coingate" kr-cng-lt="<?php echo time() - 2; ?>">
          <a>
            <img src="<?php echo APP_URL.'/assets/img/icons/payment/coingate.png'; ?>" alt="">
          </a>
          <?php if($Balance->_getPaymentGatewayFee('coingate') > 0): ?>
          <label>+ <?php echo $Balance->_getPaymentGatewayFee('coingate').' '.$Lang->tr('% Fees'); ?></label>
          <?php endif; ?>
        </li>
        <?php endif; ?>
        <?php if($App->_mollieEnabled() && in_array($symbolFetched, Mollie::_getCurrencyAvailable())): ?>
          <li kr-charges-payment="mollie">
            <a>
              <img src="<?php echo APP_URL.'/assets/img/icons/payment/mollie.png'; ?>" alt="">
            </a>
            <?php if($Balance->_getPaymentGatewayFee('mollie') > 0): ?>
            <label>+ <?php echo $Balance->_getPaymentGatewayFee('mollie').' '.$Lang->tr('% Fees'); ?></label>
            <?php endif; ?>
          </li>
        <?php endif; ?>
        <?php if($App->_getPayeerEnabled()):
          $Payeer = new Payeer($App);
          if(array_key_exists($symbolFetched, $Payeer->_getListCurrencyAvailable())){
            ?>
              <li kr-charges-payment="payeer" kr-cng-lt="<?php echo time() - 2; ?>">
                <a>
                  <img src="<?php echo APP_URL.'/assets/img/icons/payment/payeer.png'; ?>" alt="">
                </a>
                <?php if($Balance->_getPaymentGatewayFee('payeer') > 0): ?>
                  <label>+ <?php echo $Balance->_getPaymentGatewayFee('payeer').' '.$Lang->tr('% Fees'); ?></label>
                <?php endif; ?>
              </li>
            <?php
          }
        endif; ?>

        <?php if($App->_coinbasecommerceEnabled()):
          $CoinbaseCommerce = new CoinbaseCommerce($App);
          if(in_array($symbolFetched, $CoinbaseCommerce->_getCurrencyAvailable())){
            ?>
              <li kr-charges-payment="coinbasecommerce" kr-cng-lt="<?php echo time() - 2; ?>">
                <a>
                  <img src="<?php echo APP_URL.'/assets/img/icons/payment/coinbasecommerce.svg'; ?>" alt="">
                </a>
                <?php if($Balance->_getPaymentGatewayFee('coinbasecommerce') > 0): ?>
                <label>+ <?php echo $Balance->_getPaymentGatewayFee('coinbasecommerce').' '.$Lang->tr('% Fees'); ?></label>
                <?php endif; ?>
              </li>
            <?php
          }
        endif; ?>

        <?php if($App->_raveflutterwaveEnabled()):
          $RaveFlutterwave = new RaveFlutterwave($App);
          if(in_array($symbolFetched, $RaveFlutterwave->_getCurrencyAvailable())){
            ?>
              <li kr-charges-payment="raveflutterwave" kr-cng-lt="<?php echo time() - 2; ?>">
                <a>
                  <img src="<?php echo APP_URL.'/assets/img/icons/payment/raveflutterwave.svg'; ?>" alt="">
                </a>
                <?php if($Balance->_getPaymentGatewayFee('raveflutterwave') > 0): ?>
                <label>+ <?php echo $Balance->_getPaymentGatewayFee('raveflutterwave').' '.$Lang->tr('% Fees'); ?></label>
                <?php endif; ?>
              </li>
            <?php
          }
        endif; ?>

        <?php if($App->_coinpaymentsEnabled()):
          $Coinpayments = new Coinpayments($App);
          if(in_array($symbolFetched, $Coinpayments->_getCurrencyAvailable())){
            ?>
              <li kr-charges-payment="coinpayments" kr-cng-lt="<?php echo time() - 2; ?>">
                <a>
                  <img src="<?php echo APP_URL.'/assets/img/icons/payment/coinpayments.png'; ?>" alt="">
                </a>
                <?php if($Balance->_getPaymentGatewayFee('coinpayments') > 0): ?>
                <label>+ <?php echo $Balance->_getPaymentGatewayFee('coinpayments').' '.$Lang->tr('% Fees'); ?></label>
                <?php endif; ?>
              </li>
            <?php

          }

        endif; ?>

        <?php if($App->_polipaymentsEnabled()):
          $Polipayments = new Polipayments($App);
          if(in_array($symbolFetched, $Polipayments->_getCurrencyAvailable())){
            ?>
              <li kr-charges-payment="polipayments" kr-cng-lt="<?php echo time() - 2; ?>">
                <a>
                  <img src="<?php echo APP_URL.'/assets/img/icons/payment/polipayments.png'; ?>" alt="">
                </a>
                <?php if($Balance->_getPaymentGatewayFee('polipayments') > 0): ?>
                <label>+ <?php echo $Balance->_getPaymentGatewayFee('polipayments').' '.$Lang->tr('% Fees'); ?></label>
                <?php endif; ?>
              </li>
            <?php

          }

        endif; ?>

        <?php if($App->_paystackEnabled()):
          $Paystack = new Paystack($App);
          if(in_array($symbolFetched, $Paystack->_getCurrencyAvailable())){
            ?>
              <li kr-charges-payment="paystack" kr-cng-lt="<?php echo time() - 2; ?>">
                <a>
                  <img src="<?php echo APP_URL.'/assets/img/icons/payment/paystack.svg'; ?>" alt="">
                </a>
                <?php if($Balance->_getPaymentGatewayFee('paystack') > 0): ?>
                <label>+ <?php echo $Balance->_getPaymentGatewayFee('paystack').' '.$Lang->tr('% Fees'); ?></label>
                <?php endif; ?>
              </li>
            <?php

          }

        endif; ?>
      </ul>
    <?php } ?>
    <?php else:

      $Blockonomics = new Blockonomics($App);
      $error_block = false;
      try {
        $AddressDeposit = $Blockonomics->_generateNewPaymentAddress($User);
      } catch (\Exception $e) {
        $error_block = $e->getMessage();
      }

      if($error_block === false):
      ?>
        <div class="kr-credit-cryptocc">
          <h2><span><?php echo strtoupper($symbolFetched); ?></span> <?php echo $Lang->tr('Deposit'); ?></h2>
          <div class="kr-credit-cryptocc-qrcode">
            <img src="https://krypto.dev.ovrley.com/public/qrcode/<?php echo $AddressDeposit ?>.png" alt="">
          </div>
          <div class="kr-credit-cryptocc-addrinp">
            <input type="text" readonly name="" id="kr-deposit-addrinp" value="<?php echo $AddressDeposit; ?>">
            <div data-clipboard-target="#kr-deposit-addrinp">
              <svg class="lnr lnr-file-empty"><use xlink:href="#lnr-file-empty"></use></svg>
            </div>
          </div>
        </div>
      <?php else: ?>
        <span style="color:#f4f6f9;"><?php echo $error_block; ?></span>
      <?php endif; ?>

    <?php endif; ?>
  </section>
<?php elseif($typeFetched == "bank_transfert" || (!$App->_coinpaymentsEnabled() && !$App->_coingateEnabled())):

  $BankTransfert = new Banktransfert($User, $App);

  ?>
    <div class="kr-banktransfert-action">
      <button type="button" class="btn btn-small btn-autowidth btn-orange create-n-banktransfert" name="button"><?php echo $Lang->tr('Create new bank transfert'); ?></button>
    </div>
    <ul class="kr-deposit-banktransfert-l">
      <?php foreach ($BankTransfert->_getListBankTransfert('ALL', $User) as $key => $value) { ?>
      <li class="kr-deposit-banktransfert-item" bankid="<?php echo App::encrypt_decrypt('encrypt', time().'-'.$value['id_banktransfert']); ?>">
        <div class="kr-deposit-banktransfert-l-mi">
          <div class="kr-deposit-banktransfert-l-mi-<?php echo $value['proecessed_banktransfert']; ?>"></div>
          <label><?php echo $value['uref_banktransfert']; ?></label>
        </div>
        <div class="kr-deposit-banktransfert-l-dt">
          <span><?php echo date('d/m/Y H:i', $value['created_date_banktransfert']); ?></span>
        </div>
        <div class="kr-deposit-banktransfert-l-st kr-deposit-banktransfert-l-st-<?php echo $value['status_banktransfert']; ?>">
          <span class="kr-transfert-tag-<?php echo $value['status_banktransfert']; ?>"><?php echo $Lang->tr($BankTransfert->StatusBank[$value['status_banktransfert']]); ?></span>
        </div>
        <div class="kr-deposit-banktransfert-l-dtl">
          <span><?php echo $Lang->tr('Details'); ?></span>
        </div>
      </li>
      <?php } ?>
    </ul>

<?php endif; ?>
</section>
