<?php

/**
 * Mollie class
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
class Mollie extends MySQL
{
    /**
     * App object
     * @var App
     */
    private $App = null;

    /**
     * Mollie object
     * @var Mollie
     */
    private $Mollie = null;

    /**
     * Paypal constructor
     * @param App $App          App object
     * @param String $keycharge Charge key
     */
    public function __construct($App = null)
    {
        if (is_null($App)) {
            throw new Exception("Error Mollie : App need to be given", 1);
        }
        $this->App = $App;

        if(!$this->_getApp()->_mollieEnabled() || empty($this->_getApp()->_getMollieKey())) throw new Exception("Error : Mollie not enabled", 1);

        $this->_initMollie();

    }

    /**
     * Get app object
     * @return App App object
     */
    private function _getApp()
    {
        if (is_null($this->App)) {
            throw new Exception("Error Fortumo : App not defined", 1);
        }
        return $this->App;
    }

    /**
     * Init mollie payment
     */
    private function _initMollie(){

      $this->Mollie = new \Mollie\Api\MollieApiClient();

      $this->Mollie->setApiKey($this->_getApp()->_getMollieKey());

    }

    public static function _getCurrencyAvailable(){
      return ['AUD', 'BGN', 'CAD', 'BRL',
              'HRK', 'CZK', 'DKK', 'HKD',
              'HUF', 'ISK', 'ILS', 'JPY',
              'NOK', 'PLN', 'GBP', 'RON',
              'SEK', 'CHF', 'USD', 'EUR',
              'CAD', 'ISK', 'MXN', 'MYR',
              'NZD', 'PHP', 'RUB', 'SGD',
              'THB', 'TWD'];
    }

    /**
     * Get Mollie object
     * @return Mollie
     */
    private function _getMollieObj(){
      if(is_null($this->Mollie)) throw new Exception("Error : Mollie not init", 1);
      return $this->Mollie;
    }

    public function _createPayment($User, $ChargePlan){

      $ChargeID = $User->_getUserID().'-'.$ChargePlan->_getPlanID().'-'.uniqid();

      return $this->_getMollieObj()->payments->create(array(
          "amount"      => round($ChargePlan->_getPrice() / 100, 2),
          "description" => $ChargePlan->_getName(),
          "redirectUrl" => APP_URL.'/dashboard.php?k='.App::encrypt_decrypt('encrypt', $ChargeID).'&c=mollie&t='.(time() + 100000),
          "webhookUrl"  => APP_URL.'/app/modules/kr-payment/src/actions/processMollie.php',
          "metadata" => [
            "cid" => App::encrypt_decrypt('encrypt', $ChargeID)
          ]
      ));
    }

    public function _createDeposit($User, $amount, $currency_deposit){

      $amount_deposit_wfees = $amount;
      if($this->_getApp()->_getFeesDeposit() > 0){
        $amount_deposit_wfees = $amount + ($amount * ($this->_getApp()->_getFeesDeposit() / 100));
      }

      $ChargeID = $User->_getUserID().'-'.base64_encode($amount).'-'.($this->_getApp()->_getFeesDeposit() > 0 ? base64_encode(($amount * ($this->_getApp()->_getFeesDeposit() / 100))) : base64_encode('0'));

      return $this->_getMollieObj()->payments->create(array(
          "amount" => [
            "value" => number_format($amount, 2, '.', ''),
            "currency" => $currency_deposit
          ],
          "description" => $User->_getUserID().' - Deposit '.$this->_getApp()->_formatNumber($amount, 2).' '.$currency_deposit.' (+'.$this->_getApp()->_formatNumber(($amount * ($this->_getApp()->_getFeesDeposit() / 100)), 2).' '.$currency_deposit.' fees)',
          "redirectUrl" => APP_URL.'/dashboard.php?v='.App::encrypt_decrypt('encrypt', $ChargeID).'&c=mollie&t='.(time() + 100000),
          "webhookUrl"  => APP_URL.'/app/modules/kr-payment/src/actions/deposit/processMollie.php',
          "metadata" => [
            "cid" => App::encrypt_decrypt('encrypt', $ChargeID)
          ]
      ));
    }

    /**
     * Check payment mollie
     * @param  String Patyment id
     */
    public function _checkPayment($orderid){

      $payment  = $this->_getMollieObj()->payments->get($orderid);

      //error_log(App::encrypt_decrypt('decrypt', $payment->metadata->cid));

      if(!$payment->isPaid()) return false;

      $dataPayment = explode('-', App::encrypt_decrypt('decrypt', $payment->metadata->cid));
      if(count($dataPayment) != 3) throw new Exception("Error Mollie : Invalid CID", 1);
      error_log(json_encode($payment));


      return [
        'cid' => $payment->metadata->cid,
        'payment_data' => $payment,
        'order_id' => $orderid,
        'user_id' => $dataPayment[0],
        'plan' => $dataPayment[1],
        'uniq' => $dataPayment[2],
        'amount' => $payment->amount->value,
        "currency" => $payment->amount->currency
      ];

    }

    public function _checkPaymentUser($orderid, $user){

      $r = parent::querySqlRequest("SELECT * FROM charges_krypto WHERE id_user=:id_user AND key_charges=:key_charges",
                                    [
                                      'id_user' => $user->_getUserID(),
                                      'key_charges' => $orderid
                                    ]);

      if(count($r) == 0) return false;
      if(count($r) > 0 && $r[0]['status_charges'] == "1") return true;
      return false;

    }

    public function _checkDepositUser($orderid, $user){
      $r = parent::querySqlRequest("SELECT * FROM deposit_history_krypto WHERE id_user=:id_user AND payment_data_deposit_history LIKE :payment_data_deposit_history",
                                    [
                                      'id_user' => $user->_getUserID(),
                                      'payment_data_deposit_history' => '%'.$orderid.'%'
                                    ]);

      if(count($r) == 0) return false;
      if(count($r) > 0 && $r[0]['payment_status_deposit_history'] == "1") return true;
      return false;
    }
}
