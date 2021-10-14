<?php

/**
 * Change payment settings
 *
 * This actions permit to admin to change payment settings in Krypto
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

// Load app modules
$App = new App(true);
$App->_loadModulesControllers();

try {

    // Check loggin & permission
    $User = new User();
    if (!$User->_isLogged()) {
        throw new Exception("Your are not logged", 1);
    }
    if (!$User->_isAdmin()) {
        throw new Exception("Error : Permission denied", 1);
    }

    if($App->_isDemoMode()) throw new Exception("App currently in demo mode", 1);

    // Check data available
    if (empty($_POST)) {
        throw new Exception("Error : Args not valid", 1);
    }

    // Check if stripe key was changed
    if ($_POST['kr-adm-stripekey'] != '*********************' && !empty($_POST['kr-adm-stripekey'])) {

        // Check stripe key
        \Stripe\Stripe::setApiKey($_POST['kr-adm-stripekey']);
        \Stripe\Balance::retrieve();
    }

    // Save payment in Krypto configuration
    $App->_savePayment([
      'creditcard_enabled' => isset($_POST['kr-adm-chk-creditcard']) && $_POST['kr-adm-chk-creditcard'] == "on", // Credit card enable
      "paypal_enabled" => isset($_POST['kr-adm-chk-enablepaypal']) && $_POST['kr-adm-chk-enablepaypal'] == "on", // Paypal payment enable

      // Save Stripe encrypted private key
      "stripe_privatekey" => ($_POST['kr-adm-stripekey'] != '*********************' ? App::encrypt_decrypt('encrypt', $_POST['kr-adm-stripekey']) : $_POST['kr-adm-stripekey']),

      // Save paypal encrypted client id
      "paypal_clientid" => ($_POST['kr-adm-paypalclientid'] != '*********************' ? App::encrypt_decrypt('encrypt', $_POST['kr-adm-paypalclientid']) : $_POST['kr-adm-paypalclientid']),

      // Save paypal encrypted secret
      "paypal_secret" => ($_POST['kr-adm-paypalclientsecret'] != '*********************' ? App::encrypt_decrypt('encrypt', $_POST['kr-adm-paypalclientsecret']) : $_POST['kr-adm-paypalclientsecret']),

      "payment_success" => $_POST['kr-adm-paymentdoneresult'],
      "paypal_live" => isset($_POST['kr-adm-chk-enablepaypallive']) && $_POST['kr-adm-chk-enablepaypallive'] == "on", // Paypal live mode
      "fortumo_service" => ($_POST['kr-adm-fortumoservicekey'] != '*********************' ? App::encrypt_decrypt('encrypt', $_POST['kr-adm-fortumoservicekey']) : $_POST['kr-adm-fortumoservicekey']),
      "fortumo_secret" => ($_POST['kr-adm-fortumosecretkey'] != '*********************' ? App::encrypt_decrypt('encrypt', $_POST['kr-adm-fortumosecretkey']) : $_POST['kr-adm-fortumosecretkey']),
      "fortumo_enabled" => isset($_POST['kr-adm-chk-enablefortumo']) && $_POST['kr-adm-chk-enablefortumo'] == "on",
      "coingate_enabled" => isset($_POST['kr-adm-chk-enablecoingate']) && $_POST['kr-adm-chk-enablecoingate'] == "on",
      "coingate_live_mode" => isset($_POST['kr-adm-chk-coingatelivemode']) && $_POST['kr-adm-chk-coingatelivemode'] == "on",
      "coingate_authtoken" => ($_POST['kr-adm-coingateauthtoken'] != '*********************' ? App::encrypt_decrypt('encrypt', $_POST['kr-adm-coingateauthtoken']) : $_POST['kr-adm-coingateauthtoken']),
      "coingate_paymentconvertion" => $_POST['kr-adm-coingatereceivedcurrency'],
      "mollie_enabled" => isset($_POST['kr-adm-chk-enablemollie']) && $_POST['kr-adm-chk-enablemollie'] == "on",
      "mollie_key" => ($_POST['kr-adm-molliekey'] != '*********************' ? App::encrypt_decrypt('encrypt', $_POST['kr-adm-molliekey']) : $_POST['kr-adm-molliekey']),

      "banktransfert_proof_enable" => isset($_POST['kr-adm-chk-enablebanktransfertproof']) && $_POST['kr-adm-chk-enablebanktransfertproof'] == "on",
      "banktransfert_proof_max" => $_POST['kr-adm-enablebanktransfertproofmax'],
      "banktransfert_max" => $_POST['kr-adm-enablebanktransfertmax'],
      "raveflutterwave_enabled" => isset($_POST['kr-adm-chk-enableraveflutterwave']) && $_POST['kr-adm-chk-enableraveflutterwave'] == "on",
      "raveflutterwave_sandbox" => isset($_POST['kr-adm-chk-sandboxraveflutterwave']) && $_POST['kr-adm-chk-sandboxraveflutterwave'] == "on",
      "raveflutterwave_public_key" => ($_POST['kr-adm-raveflutterwavepublickey'] != '*********************' ? App::encrypt_decrypt('encrypt', $_POST['kr-adm-raveflutterwavepublickey']) : $_POST['kr-adm-raveflutterwavepublickey']),
      "raveflutterwave_secret_key" => ($_POST['kr-adm-raveflutterwavesecretkey'] != '*********************' ? App::encrypt_decrypt('encrypt', $_POST['kr-adm-raveflutterwavesecretkey']) : $_POST['kr-adm-raveflutterwavesecretkey']),
      "raveflutterwave_title" => $_POST['kr-adm-raveflutterwavetitle'],
      "raveflutterwave_prefix" => $_POST['kr-adm-raveflutterwaveprefix'],

      "coinbasecommerce_enabled" => isset($_POST['kr-adm-chk-enablecoinbasecommerce']) && $_POST['kr-adm-chk-enablecoinbasecommerce'] == "on",
      "coinbasecommerce_apikey" => ($_POST['kr-adm-coinbasecommerceapikey'] != '*********************' ? App::encrypt_decrypt('encrypt', $_POST['kr-adm-coinbasecommerceapikey']) : $_POST['kr-adm-coinbasecommerceapikey']),
      "coinbasecommerce_paymentitle" => $_POST['kr-adm-coinbasecommercepaymentitle'],

      "coinpayments_enabled" => isset($_POST['kr-adm-chk-enablecoinpayments']) && $_POST['kr-adm-chk-enablecoinpayments'] == "on",
      "coinpayments_publickey" => ($_POST['kr-adm-coinpaymentspublickey'] != '*********************' ? App::encrypt_decrypt('encrypt', $_POST['kr-adm-coinpaymentspublickey']) : $_POST['kr-adm-coinpaymentspublickey']),
      "coinpayments_privatekey" => ($_POST['kr-adm-coinpaymentsprivatekey'] != '*********************' ? App::encrypt_decrypt('encrypt', $_POST['kr-adm-coinpaymentsprivatekey']) : $_POST['kr-adm-coinpaymentsprivatekey']),
      "coinpayments_marchant_id" => ($_POST['kr-adm-coinpaymentsmarchandid'] != '*********************' ? App::encrypt_decrypt('encrypt', $_POST['kr-adm-coinpaymentsmarchandid']) : $_POST['kr-adm-coinpaymentsmarchandid']),
      "coinpayment_ipn_secret" => ($_POST['kr-adm-coinpaymentsipnsecret'] != '*********************' ? App::encrypt_decrypt('encrypt', $_POST['kr-adm-coinpaymentsipnsecret']) : $_POST['kr-adm-coinpaymentsipnsecret']),

      "fortumo_payment_fees" => $_POST['kr-adm-fortumofees'],
      "coingate_payment_fees" => $_POST['kr-adm-coingatefees'],
      "coinpayment_payment_fees" => $_POST['kr-adm-coinpaymentfees'],
      "raveflutterwave_payment_fees" => $_POST['kr-adm-raveflutterwavefees'],
      "coinbasecommerce_payment_fees" => $_POST['kr-adm-coinbasecommercefees'],
      "mollie_payment_fees" => $_POST['kr-adm-molliefees'],
      "payeer_payment_fees" => $_POST['kr-adm-payeerfees'],
      "banktransfert_payment_fees" => $_POST['kr-adm-banktransfertfees'],
      "payeer_enable" => isset($_POST['kr-adm-chk-enablepayeer']) && $_POST['kr-adm-chk-enablepayeer'] == "on",
      "payeer_shopid" => ($_POST['kr-adm-payeershopid'] != '*********************' ? App::encrypt_decrypt('encrypt', $_POST['kr-adm-payeershopid']) : $_POST['kr-adm-payeershopid']),
      "payeer_apikey" => ($_POST['kr-adm-payeerapikey'] != '*********************' ? App::encrypt_decrypt('encrypt', $_POST['kr-adm-payeerapikey']) : $_POST['kr-adm-payeerapikey']),
      // "paygoal_fees" => $_POST['kr-adm-paygolfees'],
      "banktransfert_enable" => isset($_POST['kr-adm-chk-enablebanktransfert']) && $_POST['kr-adm-chk-enablebanktransfert'] == "on",
      "payment_approve_needed" => isset($_POST['kr-adm-chk-paymentneedapproved']) && $_POST['kr-adm-chk-paymentneedapproved'] == "on",

      "polipayments_enable" => isset($_POST['kr-adm-chk-enablepolipayments']) && $_POST['kr-adm-chk-enablepolipayments'] == "on",
      "polipayments_authcode" => ($_POST['kr-adm-polipaymentsauthcode'] != '*********************' ? App::encrypt_decrypt('encrypt', $_POST['kr-adm-polipaymentsauthcode']) : $_POST['kr-adm-polipaymentsauthcode']),
      "polipayments_marchandcode" => ($_POST['kr-adm-polipaymentsmarchandcode'] != '*********************' ? App::encrypt_decrypt('encrypt', $_POST['kr-adm-polipaymentsmarchandcode']) : $_POST['kr-adm-polipaymentsmarchandcode']),
      "polipayments_payment_fees" => $_POST['kr-adm-polipaymentsfees'],

      "paystack_enable" => isset($_POST['kr-adm-chk-enablepaystack']) && $_POST['kr-adm-chk-enablepaystack'] == "on",
      "paystack_publickey" => ($_POST['kr-adm-paystackpublickey'] != '*********************' ? App::encrypt_decrypt('encrypt', $_POST['kr-adm-paystackpublickey']) : $_POST['kr-adm-paystackpublickey']),
      "paystack_privatekey" => ($_POST['kr-adm-paystackprivatekey'] != '*********************' ? App::encrypt_decrypt('encrypt', $_POST['kr-adm-paystackprivatekey']) : $_POST['kr-adm-paystackprivatekey']),
      "paystack_payment_fees" => $_POST['kr-adm-paystackfees']
    ]);

    // Return success message
    die(json_encode([
      'error' => 0,
      'msg' => 'Done',
      'title' => 'Success'
    ]));

} catch (\Exception $e) { // If throw exception, return error message
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}
