<?php

/**
 * Coinpayments class
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
class Coinpayments extends MySQL
{
    /**
     * App object
     * @var App
     */
    private $App = null;

    private $Coinpayments = null;

    /**
     * RaveFlutterwave constructor
     * @param App $App          App object
     */
    public function __construct($App = null)
    {
        if (is_null($App)) {
            throw new Exception("Error Coinpayments : App need to be given", 1);
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
            throw new Exception("Error Coinpayments : App not defined", 1);
        }
        return $this->App;
    }

    public function _init(){
      if(!$this->_getApp()->_coinpaymentsEnabled()) throw new Exception("Error Coinbase Commerce is not enabled", 1);
      $this->Coinpayments = new BertW\Client($this->_getApp()->_getCoinpaymentsPublicKey(), $this->_getApp()->_getCoinpaymentsPrivateKey());
    }

    public function _getCoinbaseCommerce(){
      if(is_null($this->Coinpayments)) $this->_init();
      return $this->Coinpayments;
    }

    private $CurrentAvailable = null;
    public function _getCurrencyAvailableOld(){
      if(!is_null($this->CurrentAvailable)) return $this->CurrentAvailable;
      $listRate = $this->_getCoinbaseCommerce()->rates();
      return array_keys($listRate['result']);
      $listCurrency = [];
      foreach ($listRate['result'] as $currencySymbol => $value) {
        //if(in_array('payments', $value['capabilities'])){
          $listCurrency[] = $currencySymbol;
        //}
      }
      $this->CurrentAvailable =  array_values($listCurrency);
      return $this->CurrentAvailable;
    }

    private $CurrencyConvertAvailable = null;
    public function _getCurrencyAvailable(){
      if(!is_null($this->CurrencyConvertAvailable)) return $this->CurrencyConvertAvailable;
      $listRate = $this->_getCoinbaseCommerce()->rates(1, 1);
      $listCurrency = [];
      foreach ($listRate['result'] as $currencySymbol => $value) {
        if($value['accepted'] == 1 && $value['status'] == "online") $listCurrency[] = $currencySymbol;
      }
      $this->CurrencyConvertAvailable = array_values($listCurrency);
      return $this->CurrencyConvertAvailable;
    }


    public function _getCurrencyConvertAvailable(){
      if(!is_null($this->CurrencyConvertAvailable)) return $this->CurrencyConvertAvailable;
      $listRate = $this->_getCoinbaseCommerce()->rates(1, 1);
      $listCurrency = [];
      foreach ($listRate['result'] as $currencySymbol => $value) {
        if($value['accepted'] == 1 && $value['status'] == "online") $listCurrency[] = $currencySymbol;
      }
      $this->CurrencyConvertAvailable = array_values($listCurrency);
      return $this->CurrencyConvertAvailable;
    }

    public function _createNewPayment($User, $Amount, $BalanceUser, $Currency, $ToCurrency = null){

      $Currency = (in_array($Currency, $this->_getCurrencyAvailable()) ? $Currency : $this->_getCurrencyAvailable()[0]);
      $ToCurrency = (!is_null($ToCurrency) && in_array($ToCurrency, $this->_getCurrencyConvertAvailable()) ? $ToCurrency : $this->_getCurrencyConvertAvailable()[0]);

      $DepositReference = $BalanceUser->_addDeposit($Amount, 'coinpayments', 'Payment Coinpayments '.$Amount.' '.$Currency, $Currency, "-not-filled-yet-".App::encrypt_decrypt('encrypt', time().rand(0, 9999999).time()), 0);

      $TransactionInfos = $this->_getCoinbaseCommerce()->createTransaction([
           'amount' => $Amount,
           'currency1' => $Currency,
           'currency2' => $ToCurrency,
           'buyer_email' => $User->_getEmail(),
           'buyer_name' => APP_URL.'/app/modules/kr-payment/src/actions/deposit/processCoinpayment.php',
           'item_name' => $DepositReference,
           'buyer_name' => $User->_getName()]);

      $this->_checkPaymentIntegrity($TransactionInfos);

      $BalanceUser->_updateDepositPaymentData($DepositReference, $TransactionInfos['result']['txn_id']);

      return $TransactionInfos['result']['status_url'];

    }

    public function _checkPaymentIntegrity($payment_infos){

      if(count($payment_infos) != 2 ||
        !array_key_exists('error', $payment_infos) ||
        !array_key_exists('result', $payment_infos) ||
        !array_key_exists('amount', $payment_infos['result']) ||
        !array_key_exists('txn_id', $payment_infos['result']) ||
        !array_key_exists('status_url', $payment_infos['result'])) throw new Exception("Error Coinpayments : Format incorect", 1);

      if(strtolower($payment_infos['error']) != "ok") throw new Exception("Error Coinpayments : Payment error : ".$payment_infos['error'], 1);

      return true;
    }



}
