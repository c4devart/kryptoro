<?php

/**
 * RaveFlutterwave class
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
class RaveFlutterwave extends MySQL
{
    /**
     * App object
     * @var App
     */
    private $App = null;

    private $RavePayment = null;

    /**
     * RaveFlutterwave constructor
     * @param App $App          App object
     */
    public function __construct($App = null)
    {
        if (is_null($App)) {
            throw new Exception("Error RaveFlutterwave : App need to be given", 1);
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
            throw new Exception("Error RaveFlutterwave : App not defined", 1);
        }
        return $this->App;
    }

    public function _init(){
      if(!$this->_getApp()->_raveflutterwaveEnabled()) throw new Exception("Error Rave Flutterwave not enabled", 1);

      $this->RavePayment = new Rave\Payment\RavePayment($this->_getApp()->_getRaveflutterwavePublicKey(),
                                                        $this->_getApp()->_getRaveflutterwaveSecretKey(), (strlen($this->_getApp()->_getRaveflutterwavePrefix()) > 0 ? $this->_getApp()->_getRaveflutterwavePrefix() : "KRYP"),
                                                        ($this->_getApp()->_raveflutterwaveSandboxMode() ? 'staging' : 'live'));
      $this->RavePayment->eventHandler(new RaveFlutterwaveHandler($this));
    }

    public function _getRave(){
      if(is_null($this->RavePayment)) $this->_init();
      return $this->RavePayment;
    }

    public function _getCurrencyAvailable(){
      return ['NGN', 'GHS', 'KES', 'UGX', 'TZS', 'ZAR', 'USD', 'GBP', 'EUR'];
    }

    public function _createNewPayment($User, $Amount, $BalanceUser, $Currency){

      $BalanceUser = new Balance($User, $this->_getApp(), 'real');
      $Currency = (in_array($Currency, $this->_getCurrencyAvailable()) ? $Currency : $this->_getCurrencyAvailable()[0]);
      $this->_getRave()->setAmount($Amount);
      $this->_getRave()->setCurrency($Currency);
      $this->_getRave()->setEmail($User->_getEmail());
      $this->_getRave()->setFirstname($User->_getName());
      $this->_getRave()->setLogo(APP_URL.$this->_getApp()->_getLogoBlackPath());
      $this->_getRave()->setRedirectUrl(APP_URL.'/app/modules/kr-payment/src/actions/deposit/processRave.php');
      $this->_getRave()->createReferenceNumber();

      $DepositReference = $BalanceUser->_addDeposit($Amount, 'raveflutterwave', 'Payment Rave '.$Amount.' '.$Currency, $Currency, $this->_getRave()->getReferenceNumber(), 0);
      $this->_getRave()->setMetaData(['id_user' => $User->_getUserID()]);
      $this->_getRave()->setMetaData(['payment_ref' => $DepositReference]);
      $this->_getRave()->setDescription($DepositReference);
      $this->_getRave()->setTitle(strlen($this->_getApp()->_getRaveflutterwaveTitle()) > 0 ? $this->_getApp()->_getRaveflutterwaveTitle() : $this->_getApp()->_getAppTitle());
      $this->_getRave()->initialize();

    }

    public function _parseCallback($post, $get){
      if(!isset($post['resp']) || empty($post['resp'])) throw new \Exception("Permission denied", 1);
      $infosPayment = json_decode($post['resp'], true);
      $PaymentObject = $this->_getRave()->requeryTransaction($get['txref']);
    }

    public function _processPayment($paymentdata){


      if($paymentdata->status != "successful") throw new Exception("Payment refused", 1);

      $infosPayment = parent::querySqlRequest("SELECT * FROM deposit_history_krypto WHERE payment_data_deposit_history=:payment_data_deposit_history AND payment_status_deposit_history=:payment_status_deposit_history",
                                              [
                                                'payment_status_deposit_history' => '0',
                                                'payment_data_deposit_history' => $paymentdata->txref
                                              ]);

      if(count($infosPayment) == 0) throw new Exception("Error : Payment not found", 1);
      $infosPayment = $infosPayment[0];

      $UserPayment = new User($infosPayment['id_user']);
      $BalanceUser = new Balance($UserPayment, $this->_getApp(), 'real');

      $BalanceUser->_changeDepositStatus($paymentdata->txref, '1');
    }

    public function _setPaymentFail($paymentdata){

      $infosPayment = parent::querySqlRequest("SELECT * FROM deposit_history_krypto WHERE payment_data_deposit_history=:payment_data_deposit_history AND payment_status_deposit_history=:payment_status_deposit_history",
                                              [
                                                'payment_status_deposit_history' => '0',
                                                'payment_data_deposit_history' => $paymentdata->txref
                                              ]);

      if(count($infosPayment) == 0) throw new Exception("Error : Payment not found", 1);
      $infosPayment = $infosPayment[0];

      $UserPayment = new User($infosPayment['id_user']);
      $BalanceUser = new Balance($UserPayment, $this->_getApp(), 'real');
      $BalanceUser->_changeDepositStatus($paymentdata->txref, '-1');
    }


}
