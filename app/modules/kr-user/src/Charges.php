<?php

/**
 * Charges class
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
class Charges extends MySQL
{
    /**
     * User object
     * @var User
     */
    private $User = null;

    /**
     * App object
     * @var App
     */
    private $App = null;

    /**
     * Charge contructor
     * @param User $User   Current user object
     * @param App $App     App object
     */
    public function __construct($User = null, $App = null)
    {
        // Check args given
        if (is_null($App)) {
            throw new Exception("Error Charge : You need to specify App object", 1);
        }
        $this->App = $App;
        $this->User = $User;
    }

    /**
     * Get app object
     * @return App Return app object
     */
    private function _getApp()
    {
        if (is_null($this->App)) {
            throw new Exception("Error Charge : App is null", 1);
        }
        return $this->App;
    }

    /**
     * Get user object
     * @return User Return user object
     */
    private function _getUser()
    {
        if (is_null($this->User)) {
            throw new Exception("Error Charge : User is null", 1);
        }
        return $this->User;
    }

    /**
     * Get if user is currently in trial version
     * @return Boolean
     */
    public function _isTrial()
    {
        if(!$this->_getApp()->_freetrialEnabled()) return false;
        if ((time() - $this->_getUser()->_getCreatedDate()) < ($this->_getApp()->_getChargeTrialDay() * 86400)) {
            return true;
        }
        return false;
    }

    /**
     * Get number days left in user trial version
     * @return Int Number trial days left
     */
    public function _getTrialNumberDay()
    {
        if(!$this->_getApp()->_freetrialEnabled()) return 0;
        if (!$this->_isTrial()) {
            return 0;
        }
        return ceil((($this->_getUser()->_getCreatedDate() + ($this->_getApp()->_getChargeTrialDay() * 86400)) - time()) / 86400);
    }

    /**
     * Get timestamp when trial end
     * @return String End timestamp
     */
    public function _getTimestampTrialEnd(){
      return ($this->_getUser()->_getCreatedDate() + ($this->_getApp()->_getChargeTrialDay() * 86400));
    }

    /**
     * Get list charges plan available
     * @return Array ChargesPlan array
     */
    public function _getChargesPlanList()
    {
        $listPlan = [];
        // Fetch plan & create Charge plan object
        foreach (parent::querySqlRequest("SELECT * FROM plan_krypto ORDER BY price_plan", []) as $key => $planData) {
            $listPlan[$planData['id_plan']] = new ChargesPlan($planData['id_plan']);
        }
        return $listPlan;
    }

    /**
     * Get charge currency
     * @return String Charge currency (ex : USD)
     */
    public function _getCurrency()
    {
        return $this->_getApp()->_getChargeCurrency();
    }

    /**
     * Get charge currency symbol
     * @return String Charge currency (ex : $)
     */
    public function _getCurrencySymbol()
    {
        return $this->_getApp()->_getChargeCurrencySymbol();
    }

    /**
     * Parse charges features text
     * @return Array Features list
     */
    public function _parseChargeText()
    {
        $res = [];
        foreach (explode('<br>', $this->_getApp()->_getChargeText()) as $text) {
            $res[] = str_replace(['[b]', '[/b]'], ['<b>', '</b>'], $text);
        }
        return $res;
    }

    /**
     * Validate charge request
     * @param  String $keycharge   Charge uniq key
     * @param  String $status      Charge status
     * @param  ChargePlan $plan    Charge plan object
     * @param  String $typepayment Type payment (credit card, payment ...)
     * @param  String $datapayment Timestamp payment
     */
    public function _validateCharge($keycharge, $status, $plan, $typepayment, $datapayment)
    {
        // Save in charges log
        $r = parent::execSqlRequest("INSERT INTO charges_krypto (id_user, key_charges, date_charges, status_charges, ndays_charges, type_payment, data_payment)
                                    VALUES (:id_user, :key_charges, :date_charges, :status_charges, :ndays_charges, :type_payment, :data_payment)",
                                    [
                                      'id_user' => $this->_getUser()->_getUserID(),
                                      'key_charges' => $keycharge,
                                      'date_charges' => time(),
                                      'status_charges' => $status,
                                      'ndays_charges' => $plan->_getDuration(),
                                      'type_payment' => $typepayment,
                                      'data_payment' => $datapayment
                                    ]);
        // Check insert status
        if (!$r) {
            throw new Exception("Error SQL : Fail to add your payment, contact support", 1);
        }
        return true;
    }

    /**
     * Check if user have currenly an subscription
     * @return Boolean
     */
    public function _activeAbo()
    {
        // Get user charge list
        $listChargeActive = parent::querySqlRequest("SELECT * FROM charges_krypto WHERE id_user=:id_user AND status_charges=:status_charge", ['id_user' => $this->_getUser()->_getUserID(), 'status_charge' => 1]);
        if (count($listChargeActive) == 0) {
            return false;
        }
        // Check if user charge is already available
        foreach ($listChargeActive as $dataCharge) {
            $timeActive = intval($dataCharge['date_charges']) + (intval($dataCharge['ndays_charges']) * 86400);
            if ($timeActive > time()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get subscription day left
     * @return Int subscription days left
     */
    public function _getTimeRes()
    {
        // Get user charge list (only available)
        $listChargeActive = parent::querySqlRequest("SELECT * FROM charges_krypto WHERE id_user=:id_user AND status_charges=1", ['id_user' => $this->_getUser()->_getUserID()]);
        if (count($listChargeActive) == 0) {
            return 0;
        }
        // Fetch result & calculate time left
        foreach ($listChargeActive as $dataCharge) {
            $timeActive = intval($dataCharge['date_charges']) + (intval($dataCharge['ndays_charges']) * 86400);
            return ceil(($timeActive - time()) / 86400);
        }
        return 0;
    }

    /**
     * Get ending date (timestamp) subscribtion
     * @return String Ending date timestamp
     */
    public function _getTimestampChargeEnd(){
      // Get user charge list (only available)
      $listChargeActive = parent::querySqlRequest("SELECT * FROM charges_krypto WHERE id_user=:id_user AND status_charges=1", ['id_user' => $this->_getUser()->_getUserID()]);
      if (count($listChargeActive) == 0) {
          return null;
      }
      // Fetch result & calculate time left
      foreach ($listChargeActive as $dataCharge) {
          return intval($dataCharge['date_charges']) + (intval($dataCharge['ndays_charges']) * 86400);
      }
      return null;
    }

    /**
     * Check payment result code
     * @return [type] [description]
     */
    public function _checkPaymentResult()
    {
        if (empty($_GET) || empty($_GET['c']) || empty($_GET['t']) || empty($_GET['k'])) {
            return false;
        }
        if (!is_numeric($_GET['t']) || (time() - intval($_GET['t']) > 5)) {
            return false;
        }

        // Get list payment available
        $listPaymentAvailable = ['creditcard', 'paypal', 'mollie'];
        if (!in_array($_GET['c'], $listPaymentAvailable)) {
            return false;
        }

        if($_GET['c'] == "mollie"){
          $dataPayment = parent::querySqlRequest("SELECT * FROM charges_krypto WHERE data_payment LIKE :data_payment AND id_user=:id_user AND type_payment=:type_payment",
                                              [
                                                'data_payment' => '%"cid":"'.$_GET['k'].'"%',
                                                'id_user' => $this->_getUser()->_getUserID(),
                                                'type_payment' => $_GET['c']
                                              ]);
        } else {
          // Fetch charge
          $dataPayment = parent::querySqlRequest("SELECT * FROM charges_krypto WHERE md5(key_charges)=:key_charges AND id_user=:id_user AND type_payment=:type_payment",
                                              [
                                                'key_charges' => $_GET['k'],
                                                'id_user' => $this->_getUser()->_getUserID(),
                                                'type_payment' => $_GET['c']
                                              ]);
        }

        if (count($dataPayment) == 0) {
            return false;
        }
        $dataPayment = $dataPayment[0];

        echo '<script type="text/javascript">$(document).ready(function(){ showChargePopup("result_'.$_GET['c'].'", {k:"'.$dataPayment['key_charges'].'"}); });</script>';
    }

    /**
     * Create new plan
     * @param  String $name     Plan name
     * @param  Int $price       Plan price
     * @param  Int $duration    Plan duration in days
     */
    public function _createPlan($name, $price, $duration)
    {

        // Check args given
        if (!is_numeric($price)) {
            throw new Exception("Your price is not numeric", 1);
        }
        if (intval($price) <= 0) {
            throw new Exception("The price need to be more than 0", 1);
        }

        if (!is_numeric($duration)) {
            throw new Exception("Your duration is not numeric", 1);
        }
        if (intval($duration) < 1) {
            throw new Exception("The duration need to be equal to 1 or more", 1);
        }

        if (strlen($name) > 20) {
            throw new Exception("Your name plan can be more than 20 caracters", 1);
        }

        // Insert new plan
        $r = parent::execSqlRequest("INSERT INTO plan_krypto (name_plan, price_plan, ndays_plan) VALUES (:name_plan, :price_plan, :ndays_plan)",
                                [
                                  'name_plan' => $name,
                                  'price_plan' => round($price * 100, 0),
                                  'ndays_plan' => $duration
                                ]);

        // Check insert status
        if (!$r) {
            throw new Exception("Error SQL : Fail to create plan", 1);
        }
        return true;
    }

    /**
     * Remove plan
     * @param  Int $planid    Plan ID
     */
    public function _removePlan($planid){

      $r = parent::execSqlRequest("DELETE FROM plan_krypto WHERE id_plan=:id_plan",
                                  [
                                    'id_plan' => $planid
                                  ]);

      if (!$r) {
          throw new Exception("Error SQL : Fail to delete plan", 1);
      }

    }
}

?>
