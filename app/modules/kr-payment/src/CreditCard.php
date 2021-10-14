<?php

/**
 * CreditCard class
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
class CreditCard extends MySQL
{
    /**
     * App object
     * @var App
     */
    private $App = null;

    /**
     * User object
     * @var User
     */
    private $User = null;

    /**
     * Credit card data
     * @var Array
     */
    private $cardData = [];

    /**
     * Plan
     * @var ChargesPlan
     */
    private $plan = null;

    private $type = null;

    /**
     * Credit card token
     * @var String
     */
    private $token = null;

    /**
     * Credit card charge
     * @var String
     */
    private $charge = null;

    /**
     * Credit card constructor
     * @param App $App          App object
     * @param User $User        User object
     * @param String $keycharge Key charge
     */
    public function __construct($App = null, $User = null, $keycharge = null)
    {
        if (is_null($App)) {
            throw new Exception("Error Credit card : App need to be given in args", 1);
        }
        if (is_null($User)) {
            throw new Exception("Error Credit card : User need to be given in args", 1);
        }
        $this->App = $App;
        $this->User = $User;

        // If key charge given -> load charge
        if (!is_null($keycharge)) {
            $this->_loadCharge($keycharge);
        }
    }

    /**
     * Get app object
     * @return App App object
     */
    private function _getApp()
    {
        if (is_null($this->App)) {
            throw new Exception("Error Credit Card : App is null", 1);
        }
        return $this->App;
    }

    /**
     * Get user object
     * @return User User object
     */
    private function _getUser()
    {
        if (is_null($this->User)) {
            throw new Exception("Error Credit Card : User is null", 1);
        }
        return $this->User;
    }

    /**
     * Get credit card expiration
     * @return Array Card expiration
     */
    public function _getCreditCardExpiration()
    {
        $year = [];
        for ($i=intval(date('Y')); $i < intval(date('Y') + 10); $i++) {
            $year[$i] = $i;
        }

        $month = [];

        for ($i=1; $i <= 12; $i++) {
            $month[$i] = $this->_getApp()->_getMonthName()[$i - 1];
        }

        return ['y' => $year, 'm' => $month];
    }

    /**
     * Init credit card payment
     * @param  String $cardholder      Card holder
     * @param  String $number          Card number
     * @param  String $month_exp       Month expiration
     * @param  String $year_exp        Year expiration
     * @param  String $ccv             CCV
     * @param  ChargesPlan $plan       Charge plan object
     */
    public function _initCreditCardPayment($cardholder, $number, $month_exp, $year_exp, $ccv, $plan, $type = 'charge')
    {
        $this->cardData = [
          'cardholder' => $cardholder,
          'number' => $number,
          'month_exp' => $month_exp,
          'year_exp' => $year_exp,
          'ccv' => $ccv
        ];
        $this->plan = $plan;
        $this->type = $type;
    }


    /**
     * Get credit card data by key
     * @param  String $key Data key needed
     * @return String      Data associate to the key
     */
    private function _getCardDataKey($key)
    {
        if (!array_key_exists($key, $this->cardData)) {
            throw new Exception("Error : Creditcard data not exist for key = ".$key, 1);
        }
        if (empty($this->cardData[$key]) || strlen($this->cardData[$key]) == 0) {
            return null;
        }
        return $this->cardData[$key];
    }

    /**
     * Get card holder name
     * @return String Card holder name
     */
    private function _getCardHolder()
    {
        return $this->_getCardDataKey('cardholder');
    }

    /**
     * Get credit card number
     * @return String Credit card number
     */
    private function _getCardNumber()
    {
        return $this->_getCardDataKey('number');
    }

    /**
     * Get credit card month expiration
     * @return String Credit card month expiration
     */
    private function _getCardMonthExp()
    {
        return $this->_getCardDataKey('month_exp');
    }

    /**
     * Get credit card year expiration
     * @return String Credit card year expiration
     */
    private function _getCardYearExp()
    {
        return $this->_getCardDataKey('year_exp');
    }

    /**
     * Get credit card CCV code
     * @return String CCV code
     */
    private function _getCardCCV()
    {
        return $this->_getCardDataKey('ccv');
    }

    /**
     * Get plan
     * @return ChargesPlan Charge plan associate
     */
    private function _getPlan()
    {
        if (is_null($this->plan)) {
            throw new Exception("Error Credit card charge : Fail to get plan", 1);
        }
        return $this->plan;
    }

    /**
     * Get token credit card
     * @return String Credit card token
     */
    private function _getToken()
    {
        if (is_null($this->token)) {
            throw new Exception("Error Credit card : Fail to get token", 1);
        }
        return $this->token;
    }

    /**
     * Get payment charge id
     * @return String Charge payment id
     */
    private function _getCharge()
    {
        if (is_null($this->charge)) {
            throw new Exception("Error Credit card : Fail to get charge", 1);
        }
        return $this->charge;
    }

    /**
     * Make payment process
     * @return String Charge payment id
     */
    public function _processPayment()
    {
        $this->_createToken();
        $this->_chargePayment();
        return $this->_getCharge();
    }

    /**
     * Create new credit card token
     */
    private function _createToken()
    {
        $this->token = \Stripe\Token::create(array(
          "card" => array(
            "number" => $this->_getCardNumber(),
            "exp_month" => $this->_getCardMonthExp(),
            "exp_year" => $this->_getCardYearExp(),
            "cvc" => $this->_getCardCCV(),
            'name' => $this->_getCardHolder()
          )
        ));
    }

    public function _getType(){
      return $this->type;
    }

    /**
     * Charche payment
     */
    private function _chargePayment()
    {

        $description = null;
        $amount = null;
        if($this->_getType() == 'charge'){
          $amount = $this->_getPlan()->_getPrice();
          $description = "Charge for ".$this->_getCardHolder().', amount : '.$this->_getPlan()->_getPrice(true).$this->_getApp()->_getChargeCurrencySymbol().', plan : '.$this->_getPlan()->_getName();
        } else if($this->_getType() == 'deposit'){
          $amount = $this->_getPlan() * 100;
          $description = "Deposit for ".$this->_getCardHolder().', amount : '.$this->_getPlan().$this->_getApp()->_getChargeCurrencySymbol().', account number : '.$this->_getUser()->_getUserID();
        }

        $this->charge = \Stripe\Charge::create(array(
          "amount" => $amount,
          "currency" => strtolower($this->_getApp()->_getChargeCurrency()),
          "source" => $this->_getToken()->id,
          "description" => $description,
          "metadata" => [
            'user_email' => $this->_getUser()->_getEmail(),
            'user_id' => $this->_getUser()->_getUserID()
          ],
          "receipt_email" => $this->_getUser()->_getEmail()
        ));
    }

    /**
     * Get credit card payment status
     * @return Int Payment status (0 = fail , 1 = done)
     */
    public function _getStatus()
    {
        if ($this->_getCharge()->status != 'failed') {
            return 1;
        }
        return 0;
    }

    /**
     * Load charge
     * @param  String $keycharge Charge key
     * @return Charge            Charge loaded
     */
    private function _loadCharge($keycharge)
    {
        $this->charge = \Stripe\Charge::retrieve($keycharge);
    }
}
