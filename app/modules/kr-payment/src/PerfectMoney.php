<?php

/**
 * PerfectMoney class
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
class PerfectMoney extends MySQL
{
    /**
     * App object
     * @var App
     */
    private $App = null;

    /**
     * Paypal constructor
     * @param App $App          App object
     * @param String $keycharge Charge key
     */
    public function __construct($App = null)
    {
        if (is_null($App)) {
            throw new Exception("Error PerfectMoney : App need to be given", 1);
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
            throw new Exception("Error PerfectMoney : App not defined", 1);
        }
        return $this->App;
    }

    public function _getListCurrencyAvailable(){
      return ['USD', 'EUR', 'OAU'];
    }

    public function _createDeposit($User, $Amount, $Balance, $currency = 'USD'){

      if(!$this->_getApp()->_getPerfectMoneyEnabled()) throw new Exception("Error : Perfect money is not enabled", 1);

      $refDeposit = $Balance->_generatePaymentReference();

      if(!in_array($currency, $this->_getListCurrencyAvailable())) throw new Exception("Error : Symbol not available", 1);

      $Balance->_addDeposit($Amount, 'perfectmoney', null, $currency, "", 0, $currency, $refDeposit);

      return $refDeposit;


    }

    public function _checkPayment($infos){

      // $signature = $this->_generateOrderSignature($infos);
      //
      // if($signature != $infos['m_sign']) throw new Exception("Error : Worng signature", 1);
      // if($infos['m_status'] != "success") throw new Exception("Error : Payment not paid", 1);
      //
      // $Balance = new Balance(null, $this->_getApp(), null);
      // $Balance->_changeDepositStatus($signature, '1');


    }



}
