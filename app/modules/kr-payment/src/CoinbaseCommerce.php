<?php

/**
 * CoinbaseCommerce class
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
class CoinbaseCommerce extends MySQL
{
    /**
     * App object
     * @var App
     */
    private $App = null;

    private $CoinbaseCommerce = null;

    /**
     * RaveFlutterwave constructor
     * @param App $App          App object
     */
    public function __construct($App = null)
    {
        if (is_null($App)) {
            throw new Exception("Error CoinbaseCommerce : App need to be given", 1);
        }
        $this->App = $App;

    }
    /**
     * Get app object
     * @return App App object
     */
    private function _getApp()
    {
        if (is_null($this->App)) {
            throw new Exception("Error CoinbaseCommerce : App not defined", 1);
        }
        return $this->App;
    }

    public function _init(){
      if(!$this->_getApp()->_coinbasecommerceEnabled()) throw new Exception("Error Coinbase Commerce is not enabled", 1);
      $this->CoinbaseCommerce = new \WPDMPP\Coinbase\Commerce\Client();
      $this->CoinbaseCommerce->setApiKey($this->_getApp()->_getCoinbaseCommerceAPIKey());
    }

    public function _getCoinbaseCommerce(){
      if(is_null($this->CoinbaseCommerce)) $this->_init();
      return $this->CoinbaseCommerce;
    }

    public function _getCurrencyAvailable(){
      return ['USD', 'EUR', 'JPY', 'GBP', 'AUD', 'CAD', 'CHF', 'CNY', 'SEK',
              'NZD', 'MXN', 'SGD', 'HKD', 'NOK', 'KRW', 'TRY', 'RUB', 'INR',
              'BRL', 'ZAR', 'AED', 'AFN', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS',
              'AWG', 'AZN', 'BAM', 'BBD', 'BDT', 'BGN', 'BHD', 'BIF', 'BMD',
              'BND', 'BOB', 'BSD', 'BTN', 'BWP', 'BYN', 'BZD', 'CDF', 'CLF',
              'CLP', 'COP', 'CRC', 'CUC', 'CUP', 'CVE', 'CZK', 'DJF', 'DKK',
              'DOP', 'DZD', 'EGP', 'ERN', 'ETB', 'FJD', 'FKP', 'GEL', 'GGP',
              'GHS', 'GIP', 'GMD', 'GNF', 'GTQ', 'GYD', 'HNL', 'HRK', 'HTG',
              'HUF', 'IDR', 'ILS', 'IMP', 'IQD', 'IRR', 'ISK', 'JEP', 'JMD',
              'JOD', 'KES', 'KGS', 'KHR', 'KMF', 'KPW', 'KWD', 'KYD', 'KZT',
              'LAK', 'LBP', 'LKR', 'LRD', 'LSL', 'LYD', 'MAD', 'MDL', 'MGA',
              'MKD', 'MMK', 'MNT', 'MOP', 'MRO', 'MUR', 'MVR', 'MWK', 'MYR',
              'MZN', 'NAD', 'NGN', 'NIO', 'NPR', 'OMR', 'PAB', 'PEN', 'PGK',
              'PHP', 'PKR', 'PLN', 'PYG', 'QAR', 'RON', 'RSD', 'RWF', 'SAR',
              'SBD', 'SCR', 'SDG', 'SHP', 'SLL', 'SOS', 'SRD', 'SSP', 'STD',
              'SVC', 'SYP', 'SZL', 'THB', 'TJS', 'TMT', 'TND', 'TOP', 'TTD',
              'TWD', 'TZS', 'UAH', 'UGX', 'UYU', 'UZS', 'VEF', 'VND', 'VUV',
              'WST', 'XAF', 'XAG', 'XAU', 'XCD', 'XDR', 'XOF', 'XPD', 'XPF',
              'XPT', 'YER', 'ZMW', 'ZWL'];
    }

    public function _createNewPayment($User, $Amount, $BalanceUser, $Currency){

      $Currency = (in_array($Currency, $this->_getCurrencyAvailable()) ? $Currency : $this->_getCurrencyAvailable()[0]);

      $charge = new \WPDMPP\Coinbase\Commerce\Model\Charge();

      $money = new \WPDMPP\Coinbase\Commerce\Model\Money();
      $money->SetAmount($Amount);
      $money->SetCurrency($Currency);

      $DepositReference = $BalanceUser->_addDeposit($Amount, 'coinbasecommerce', 'Payment Coinbase Commerce '.$Amount.' '.$Currency, $Currency, "-not-filled-yet-".App::encrypt_decrypt('encrypt', time().rand(0, 9999999).time()), 0);

      $charge->setName($this->_getApp()->_getCoinbaseCommercePaymentTitle());
      $charge->setDescription($DepositReference.' - '.$this->_getApp()->_formatNumber($Amount, 2).' '.$Currency.' - Deposit');
      $charge->setPricingType('fixed_price');
      $charge->setLocalPrice($money);
      $charge->addMetadata('id_user', $User->_getUserID());


      $charge->addMetadata('deposit_reference', $DepositReference);

      $charge->setRedirectUrl(APP_URL.'/app/modules/kr-payment/src/actions/deposit/processCoinbaseCommerce.php');

      $response = $this->_getCoinbaseCommerce()->createCharge($charge);

      $responseDecoded = json_decode($response);
      $BalanceUser->_updateDepositPaymentData($DepositReference, $responseDecoded->data->id);

      return $responseDecoded->data->hosted_url;

    }

    public function _parseWebhook($payload){
      $requestValid = $this->_validateRequest($payload);

      if($payload['event']['type'] == 'charge:confirmed') {

        $this->_confirmTransaction($payload);
      }
      elseif($payload['event']['type'] == 'charge:created') {
        
      }
      elseif($payload['event']['type'] == 'charge:failed') {

      }

    }

    public function _confirmTransaction($payload){

      if(!array_key_exists('event', $payload) ||
         !array_key_exists('id', $payload['event']) ||
         !array_key_exists('data', $payload['event']) ||
         !array_key_exists('deposit_reference', $payload['event']['data']['metadata']) ||
         !array_key_exists('id_user', $payload['event']['data']['metadata'])) throw new Exception("Error Coinbase Commerce : Invalid payment", 1);

      $idPayment = $payload['event']['id'];
      $paymentRef = $payload['event']['data']['metadata']['deposit_reference'];
      $idUser = $payload['event']['data']['metadata']['id_user'];

      $UserPayment = new User($idUser);

      $Balance = new Balance($UserPayment, $this->_getApp(), 'real');
      $Balance->_changeDepositStatus($idPayment, '1');

    }

    public function _validateRequest($payload){
      $hash = hash_hmac("sha256", file_get_contents('php://input'), $this->_getApp()->_getCoinbaseCommerceAPIKey());
      $testMode = true;
      if(!array_key_exists('HTTP_X_CC_WEBHOOK_SIGNATURE', $_SERVER) && !$testMode) {
          throw new Exception("Webhook signature not included in the headers of the request.", 1);

      }
      elseif (array_key_exists('HTTP_X_CC_WEBHOOK_SIGNATURE', $_SERVER) && $hash !== $_SERVER['HTTP_X_CC_WEBHOOK_SIGNATURE'] && !$testMode) {
          throw new Exception("The webhook signature of the request does not match the one generated by the server.", 1);
      }
      elseif(!array_key_exists('event', $payload)) {
          throw new Exception("Request needs to contain 'event' value.", 1);

      }
      return true;
    }


}
