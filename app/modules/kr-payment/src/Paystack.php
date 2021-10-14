<?php

/**
 * Paystack class
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
class Paystack extends MySQL
{
    /**
     * App object
     * @var App
     */
    private $App = null;

    /**
     * Paystack constructor
     * @param App $App          App object
     * @param String $keycharge Charge key
     */
    public function __construct($App = null)
    {
        if (is_null($App)) {
            throw new Exception("Error Paystack : App need to be given", 1);
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
            throw new Exception("Error Paystack : App not defined", 1);
        }
        return $this->App;
    }

    private $PaystackObj = null;
    private function _getPaystackObject(){
      if(!is_null($this->PaystackObj)) return $this->PaystackObj;
      $this->PaystackObj = new Yabacon\Paystack($this->_getApp()->_getPaystackPrivateKey());
      return $this->PaystackObj;
    }

    public function _createDeposit($User, $Amount, $Balance, $currency = 'USD'){
      $refDeposit = $Balance->_generatePaymentReference();


      try
      {
        $tranx = $this->_getPaystackObject()->transaction->initialize([
          'amount'=>$Amount * 100,       // in kobo
          'email'=> $User->_getEmail(),         // unique to customers
          'reference'=> $refDeposit, // unique to transactions
        ]);


        $Balance->_addDeposit($Amount, 'paystack', 'Paystack deposit ('.($Amount / 100).' '.$currency.')', $currency, $refDeposit, 0, null, $refDeposit);

        return $tranx->data->authorization_url;
      } catch(\Yabacon\Paystack\Exception\ApiException $e){
        throw new Exception($e->getResponseObject(), 1);
      }
      return false;
    }

    public function _callBack(){
      $event = Yabacon\Paystack\Event::capture();

      $my_keys = [
                  'live'=>$this->_getApp()->_getPaystackPrivateKey(),
                  'test'=>$this->_getApp()->_getPaystackPrivateKey(),
                ];

      $owner = $event->discoverOwner($my_keys);
      if(!$owner){
          throw new Exception("Error : Permission denied, wrong keys", 1);
          die();
      }


      $BalanceEmpty = new Balance(null, $this->_getApp());
      $InfosPayment = $BalanceEmpty->_getDepositInfosByRef($event->obj->data->reference);
      $User = new User($InfosPayment['id_user']);
      $Balance = new Balance($User, $this->_getApp(), 'real');

      $r = parent::querySqlRequest("SELECT * FROM deposit_history_krypto WHERE id_user=:id_user AND payment_type_deposit_history=:payment_type_deposit_history AND payment_status_deposit_history > :payment_status_deposit_history AND payment_data_deposit_history LIKE :payment_data_deposit_history",
                                  [
                                    'id_user' => $User->_getUserID(),
                                    'payment_type_deposit_history' => 'paystack',
                                    'payment_status_deposit_history' => 0,
                                    'payment_data_deposit_history' => '%'.$event->obj->data->reference.'%'
                                  ]);

      if(count($r) > 0) throw new Exception("Error : Paystack : ".$event->obj->data->reference." order already processed", 1);

      switch($event->obj->event){
          // charge.success
          case 'charge.success':
              if('success' === $event->obj->data->status){
                $Balance->_validDeposit($event->obj->data->reference, 'paystack');
              }
              break;
      }
    }

    public function _checkPayment($token){


    }

    public function _getCurrencyAvailable(){
      return ['NGN'];
    }

    public function _getRetryPaymentURL($token){

    }

}
