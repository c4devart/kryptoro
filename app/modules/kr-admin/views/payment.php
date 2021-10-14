<?php

/**
 * Admin payment settings
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

// Load app modules
$App = new App(true);
$App->_loadModulesControllers();

// Check loggin & permission
$User = new User();
if(!$User->_isLogged()) throw new Exception("User are not logged", 1);
if(!$User->_isAdmin()) throw new Exception("Permission denied", 1);

// Init language object
$Lang = new Lang($User->_getLang(), $App);

// Init admin object
$Admin = new Admin();
?>
<form class="kr-admin kr-adm-post-evs" action="<?php echo APP_URL; ?>/app/modules/kr-admin/src/actions/savePayment.php" method="post">
  <nav class="kr-admin-nav">
    <ul>
      <?php foreach ($Admin->_getListSection() as $key => $section) { // Get list admin section
        echo '<li type="module" kr-module="admin" kr-view="'.strtolower(str_replace(' ', '', $section)).'" '.($section == 'Payment' ? 'class="kr-admin-nav-selected"' : '').'>'.$Lang->tr($section).'</li>';
      } ?>
    </ul>
  </nav>
  <div class="kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Payment success'); ?></label>
      </div>
      <div>
        <input type="text" name="kr-adm-paymentdoneresult" value="<?php echo $App->_getPaymentResultDone(); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Payment reference pattern'); ?></label><br/>
        <span>$ : Random number (0-9)</span><br/>
        <span>* : Random Letter (A-Z)</span>
      </div>
      <div>
        <input type="text" name="kr-adm-paymentpattern" placeholder="Your payment reference pattern (ex : KRYP-$**$-$$$$)" value="<?php echo $App->_paymentReferencePattern(); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Payment need to be approved'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-paymentneedapproved" <?php echo ($App->_getPaymentApproveNeeded() ? 'checked' : ''); ?> name="kr-adm-chk-paymentneedapproved">
            <label for="kr-adm-chk-paymentneedapproved"></label>
        </div>
      </div>
    </div>
  </div>
  <div class="kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Enable credit card'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-creditcard" <?php echo ($App->_creditCardEnabled() ? 'checked' : ''); ?> name="kr-adm-chk-creditcard">
            <label for="kr-adm-chk-creditcard"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Stipe private key'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your Stripe API Key'); ?>" name="kr-adm-stripekey" value="<?php echo ($App->_getPrivateStripeKey() != '' ? '*********************' : ''); ?>">
        <span>Stripe dashboard > API > Secret key (click on reveal key token)</span>
      </div>
    </div>
  </div>
  <div class="kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Enable Paypal'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-enablepaypal" <?php echo ($App->_paypalEnabled() ? 'checked' : ''); ?> name="kr-adm-chk-enablepaypal">
            <label for="kr-adm-chk-enablepaypal"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Enable Paypal Live mode'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-enablepaypallive" <?php echo ($App->_paypalLiveModeEnabled() ? 'checked' : ''); ?> name="kr-adm-chk-enablepaypallive">
            <label for="kr-adm-chk-enablepaypallive"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Paypal client ID'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your paypal client ID'); ?>" name="kr-adm-paypalclientid" value="<?php echo ($App->_getPaypalClientID() != '' ? '*********************' : ''); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Paypal Client Secret'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your paypal client Secret'); ?>" name="kr-adm-paypalclientsecret" value="<?php echo ($App->_getPaypalClientSecret() != '' ? '*********************' : ''); ?>">
      </div>
    </div>
  </div>
  <?php if(false): ?>
    <div class="kr-admin-line kr-admin-line-cls">
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Enable Blockonomics'); ?></label><br/>
          <span>Blockonomics allow user to have a Bitcoin address</span>
        </div>
        <div>
          <div class="ckbx-style-14">
              <input type="checkbox" id="kr-adm-chk-enableblocknomics" <?php echo ($App->_getBlockonomicsEnabled() ? 'checked' : ''); ?> name="kr-adm-chk-enableblocknomics">
              <label for="kr-adm-chk-enableblocknomics"></label>
          </div>
        </div>
      </div>
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Blockonomics Secret key'); ?></label>
        </div>
        <div>
          <input type="text" placeholder="<?php echo $Lang->tr('Your blockonomics secret key'); ?>" name="kr-adm-blockonomicssecretkey" value="<?php echo ($App->_getBlockonomicsApiKey() != '' ? '*********************' : ''); ?>">
        </div>
      </div>
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Blockonomics payment Fees (in %)'); ?></label>
        </div>
        <div>
          <input type="text" placeholder="<?php echo $Lang->tr('Your blockonomics payment fees (in %)'); ?>" name="kr-adm-blockonomicsfees" value="<?php echo $App->_getBlockonomicsPaymentFees(); ?>">
        </div>
      </div>
    </div>
  <?php endif; ?>
  <div class="kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Enable Fortumo'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-enablefortumo" <?php echo ($App->_fortumoEnabled() ? 'checked' : ''); ?> name="kr-adm-chk-enablefortumo">
            <label for="kr-adm-chk-enablefortumo"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Fortumo Secret key'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your fortumo secret key'); ?>" name="kr-adm-fortumosecretkey" value="<?php echo ($App->_getFortumoSecretKey() != '' ? '*********************' : ''); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Fortumo Service key'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your fortumo service key'); ?>" name="kr-adm-fortumoservicekey" value="<?php echo ($App->_getFortumoServiceKey() != '' ? '*********************' : ''); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Fortumo payment Fees (in %)'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your fortumo payment fees (in %)'); ?>" name="kr-adm-fortumofees" value="<?php echo $App->_getFortumoPaymentFees(); ?>">
      </div>
    </div>
  </div>
  <div class="kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Enable Coingate'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-enablecoingate" <?php echo ($App->_coingateEnabled() ? 'checked' : ''); ?> name="kr-adm-chk-enablecoingate">
            <label for="kr-adm-chk-enablecoingate"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Enable CoinGate live mode'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-coingatelivemode" <?php echo ($App->_coingateLiveMode() ? 'checked' : ''); ?> name="kr-adm-chk-coingatelivemode">
            <label for="kr-adm-chk-coingatelivemode"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('CoinGate Auth Token'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your CoinGate Auth Token'); ?>" name="kr-adm-coingateauthtoken" value="<?php echo ($App->_getCoinGateAuthToken() != '' ? '*********************' : ''); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('CoinGate Receive Currency'); ?></label>
      </div>
      <div>
        <select name="kr-adm-coingatereceivedcurrency">
          <?php
          foreach (CoinGate::_getListCurrenciesConvertAvailable() as $key => $value) {
            echo '<option '.($App->_getCoingateConvertionTo() == $value ? 'selected' : '').' value="'.$value.'">'.$value.'</option>';
          }
          ?>
        </select>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('CoinGate payment Fees (in %)'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your coingate payment fees (in %)'); ?>" name="kr-adm-coingatefees" value="<?php echo $App->_getCoingatePaymentFees(); ?>">
      </div>
    </div>
  </div>
  <div class="kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Enable Coinpayments'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-enablecoinpayments" <?php echo ($App->_coinpaymentsEnabled() ? 'checked' : ''); ?> name="kr-adm-chk-enablecoinpayments">
            <label for="kr-adm-chk-enablecoinpayments"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Coinpayments Public Key'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your Coinpayments Public Key'); ?>" name="kr-adm-coinpaymentspublickey" value="<?php echo ($App->_getCoinpaymentsPublicKey() != '' ? '*********************' : ''); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Coinpayments Private Key'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your Coinpayments Private Key'); ?>" name="kr-adm-coinpaymentsprivatekey" value="<?php echo ($App->_getCoinpaymentsPrivateKey() != '' ? '*********************' : ''); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Coinpayments Marchand ID'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your Coinpayments Marchand ID'); ?>" name="kr-adm-coinpaymentsmarchandid" value="<?php echo ($App->_getCoinpaymentsMarchandID() != '' ? '*********************' : ''); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Coinpayments IPN Secret'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your Coinpayments IPN Secret'); ?>" name="kr-adm-coinpaymentsipnsecret" value="<?php echo ($App->_getCoinpaymentsIPNSecret() != '' ? '*********************' : ''); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Coinpayments payment Fees (in %)'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your coinpayment payment fees (in %)'); ?>" name="kr-adm-coinpaymentfees" value="<?php echo $App->_getCoinpaymentPaymentFees(); ?>">
      </div>
    </div>
  </div>
  <div class="kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Enable Rave Flutterwave payment'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-enableraveflutterwave" <?php echo ($App->_raveflutterwaveEnabled() ? 'checked' : ''); ?> name="kr-adm-chk-enableraveflutterwave">
            <label for="kr-adm-chk-enableraveflutterwave"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Sandbox mode Rave Flutterwave'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-sandboxraveflutterwave" <?php echo ($App->_raveflutterwaveSandboxMode() ? 'checked' : ''); ?> name="kr-adm-chk-sandboxraveflutterwave">
            <label for="kr-adm-chk-sandboxraveflutterwave"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Rave Flutterwave Public Key'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your Rave Flutterwave Public Key'); ?>" name="kr-adm-raveflutterwavepublickey" value="<?php echo ($App->_getRaveflutterwavePublicKey() != '' ? '*********************' : ''); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Rave Flutterwave Secret Key'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your Rave Flutterwave Secret Key'); ?>" name="kr-adm-raveflutterwavesecretkey" value="<?php echo ($App->_getRaveflutterwaveSecretKey() != '' ? '*********************' : ''); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Rave Flutterwave Payment Title'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your Rave Flutterwave Payment title'); ?>" name="kr-adm-raveflutterwavetitle" value="<?php echo ($App->_getRaveflutterwaveTitle() != '' ? $App->_getRaveflutterwaveTitle() : $App->_getAppTitle()); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Rave Flutterwave Payment Prefix'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your Rave Flutterwave Payment prefix (ex : KRYP)'); ?>" name="kr-adm-raveflutterwaveprefix" value="<?php echo ($App->_getRaveflutterwavePrefix() != '' ? $App->_getRaveflutterwavePrefix() : ''); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Rave Flutterwave payment Fees (in %)'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your rave flutterwave payment fees (in %)'); ?>" name="kr-adm-raveflutterwavefees" value="<?php echo $App->_getRaveflutterwavePaymentFees(); ?>">
      </div>
    </div>
  </div>
  <?php if(false): ?>
  <div class="kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Enable Perfect Money payment'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-enableperfectmoney" <?php echo ($App->_getPerfectMoneyEnabled() ? 'checked' : ''); ?> name="kr-adm-chk-enableperfectmoney">
            <label for="kr-adm-chk-enableperfectmoney"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Perfect Money Payee Account'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your Perfect Money Payee Account'); ?>" name="kr-adm-perfectmoneypayeeaccount" value="<?php echo ($App->_getRaveflutterwaveTitle() != '' ? $App->_getRaveflutterwaveTitle() : $App->_getAppTitle()); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Rave Flutterwave Payment Prefix'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your Rave Flutterwave Payment prefix (ex : KRYP)'); ?>" name="kr-adm-raveflutterwaveprefix" value="<?php echo ($App->_getRaveflutterwavePrefix() != '' ? $App->_getRaveflutterwavePrefix() : ''); ?>">
      </div>
    </div>
  </div>
<?php endif; ?>
  <div class="kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Enable Coinbase Commerce payment'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-enablecoinbasecommerce" <?php echo ($App->_coinbasecommerceEnabled() ? 'checked' : ''); ?> name="kr-adm-chk-enablecoinbasecommerce">
            <label for="kr-adm-chk-enablecoinbasecommerce"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Coinbase commerce API Key'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your Coinbase commerce API Key'); ?>" name="kr-adm-coinbasecommerceapikey" value="<?php echo ($App->_getCoinbaseCommerceAPIKey() != '' ? '*********************' : ''); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Coinbase commerce Payment Title'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your Coinbase commerce Payment title'); ?>" name="kr-adm-coinbasecommercepaymentitle" value="<?php echo ($App->_getCoinbaseCommercePaymentTitle() != '' ? $App->_getCoinbaseCommercePaymentTitle() : $App->_getAppTitle()); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Coinbase commerce payment Fees (in %)'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your coinbase commerce payment fees (in %)'); ?>" name="kr-adm-coinbasecommercefees" value="<?php echo $App->_getCoinbaseCommercePaymentFees(); ?>">
      </div>
    </div>
  </div>
  <div class="kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Enable Mollie'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-enablemollie" <?php echo ($App->_mollieEnabled() ? 'checked' : ''); ?> name="kr-adm-chk-enablemollie">
            <label for="kr-adm-chk-enablemollie"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Mollie Key'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your Mollie Key'); ?>" name="kr-adm-molliekey" value="<?php echo ($App->_getMollieKey() != '' ? '*********************' : ''); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Mollie payment Fees (in %)'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your mollie payment fees (in %)'); ?>" name="kr-adm-molliefees" value="<?php echo $App->_getMolliePaymentFees(); ?>">
      </div>
    </div>
  </div>
  <div class="kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Enable Payeer'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-enablepayeer" <?php echo ($App->_getPayeerEnabled() ? 'checked' : ''); ?> name="kr-adm-chk-enablepayeer">
            <label for="kr-adm-chk-enablepayeer"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Payeer Shop ID'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your Payeer Shop ID'); ?>" name="kr-adm-payeershopid" value="<?php echo ($App->_getPayeerShopID() != '' ? '*********************' : ''); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Payeer API Key'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your Payeer API Key'); ?>" name="kr-adm-payeerapikey" value="<?php echo ($App->_getPayeerAPIKey() != '' ? '*********************' : ''); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Payeer payment Fees (in %)'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your payeer payment fees (in %)'); ?>" name="kr-adm-payeerfees" value="<?php echo $App->_getPayeerPaymentFees(); ?>">
      </div>
    </div>
  </div>
  <?php if(false): ?>
    <div class="kr-admin-line kr-admin-line-cls">
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Enable Paygoal'); ?></label>
        </div>
        <div>
          <div class="ckbx-style-14">
              <input type="checkbox" id="kr-adm-chk-enablepaygol" <?php echo ($App->_getPaygolEnabled() ? 'checked' : ''); ?> name="kr-adm-chk-enablepaygol">
              <label for="kr-adm-chk-enablepaygol"></label>
          </div>
        </div>
      </div>
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Paygol service ID'); ?></label>
        </div>
        <div>
          <input type="text" placeholder="<?php echo $Lang->tr('Your paygol service ID'); ?>" name="kr-adm-paygolserviceid" value="<?php echo ($App->_getPaygolServiceID() != '' ? '*********************' : ''); ?>">
        </div>
      </div>
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Paygol secret'); ?></label>
        </div>
        <div>
          <input type="text" placeholder="<?php echo $Lang->tr('Your paygol secret'); ?>" name="kr-adm-paygolsecret" value="<?php echo ($App->_getPaygolSecret() != '' ? '*********************' : ''); ?>">
        </div>
      </div>
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Paygol fees'); ?></label>
        </div>
        <div>
          <input type="text" placeholder="<?php echo $Lang->tr('Your paygol fees (ex 10 for 10%)'); ?>" name="kr-adm-paygolfees" value="<?php echo $App->_getPaygolFees(); ?>">
        </div>
      </div>
    </div>
  <?php endif; ?>
  <div class="kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Enable Polipayments'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-enablepolipayments" <?php echo ($App->_polipaymentsEnabled() ? 'checked' : ''); ?> name="kr-adm-chk-enablepolipayments">
            <label for="kr-adm-chk-enablepolipayments"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Polipayments marchand code'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your polipayments marchand code'); ?>" name="kr-adm-polipaymentsmarchandcode" value="<?php echo ($App->_getPolipaymentsMarchandCode() != '' ? '*********************' : ''); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Polipayments authentication code'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your polipayments authentication code'); ?>" name="kr-adm-polipaymentsauthcode" value="<?php echo ($App->_getPolipaymentsAuthCode() != '' ? '*********************' : ''); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Polipayments fees'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your polipayments fees (ex 10 for 10%)'); ?>" name="kr-adm-polipaymentsfees" value="<?php echo $App->_getPolipaymentsFees(); ?>">
      </div>
    </div>
  </div>
  <div class="kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Enable PayStack'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-enablepaystack" <?php echo ($App->_paystackEnabled() ? 'checked' : ''); ?> name="kr-adm-chk-enablepaystack">
            <label for="kr-adm-chk-enablepaystack"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Paystack public key'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your PayStack public key'); ?>" name="kr-adm-paystackpublickey" value="<?php echo ($App->_getPaystackPublicKey() != '' ? '*********************' : ''); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Paystack private key'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your PayStack private key'); ?>" name="kr-adm-paystackprivatekey" value="<?php echo ($App->_getPaystackPrivateKey() != '' ? '*********************' : ''); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('PayStack fees'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your paystack fees (ex 10 for 10%)'); ?>" name="kr-adm-paystackfees" value="<?php echo $App->_getPaystackFees(); ?>">
      </div>
    </div>
  </div>
  <div class="kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Enable Bank transfert'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-enablebanktransfert" <?php echo ($App->_getBankTransfertEnable() ? 'checked' : ''); ?> name="kr-adm-chk-enablebanktransfert">
            <label for="kr-adm-chk-enablebanktransfert"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Bank transfert maximum at the same time'); ?></label>
      </div>
      <div>
        <select name="kr-adm-enablebanktransfertmax">
          <?php
          for ($i=1; $i <= 90; $i++) {
            echo '<option '.($i == $App->_getBankMaxTransfert() ? 'selected' : '').' value="'.$i.'">'.$i.'</option>';
          }
          ?>
        </select>
      </div>
    </div>

    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Bank transfert proof enabled'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-enablebanktransfertproof" <?php echo ($App->_getBankTransfertProofEnable() ? 'checked' : ''); ?> name="kr-adm-chk-enablebanktransfertproof">
            <label for="kr-adm-chk-enablebanktransfertproof"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Bank transfert maximum proof'); ?></label>
      </div>
      <div>
        <select name="kr-adm-enablebanktransfertproofmax">
          <?php
          for ($i=1; $i <= 20; $i++) {
            echo '<option '.($i == $App->_getBankTransfertProofMax() ? 'selected' : '').' value="'.$i.'">'.$i.'</option>';
          }
          ?>
        </select>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Bank transfert payment Fees (in %)'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your bank transfert payment fees (in %)'); ?>" name="kr-adm-banktransfertfees" value="<?php echo $App->_getBankTransfertPaymentFees(); ?>">
      </div>
    </div>
  </div>
  <div class="kr-admin-action">
    <input type="submit" class="btn btn-orange" name="" value="<?php echo $Lang->tr('Save'); ?>">
  </div>
</form>
