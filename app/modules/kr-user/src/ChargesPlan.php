<?php

/**
 * Charges Plan class
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
class ChargesPlan extends MySQL
{

    /**
     * Plan ID
     * @var Int
     */
    private $PlanID = null;

    /**
     * Plan data
     * @var Array
     */
    private $PlanData = null;

    /**
     * ChargesPlan constructor
     * @param Int $PlanID Plan ID
     */
    public function __construct($PlanID = null)
    {
        // Check plan need to be loaded
        if (!is_null($PlanID)) {
            $this->PlanID = $PlanID;
            // Load plan data
            $this->_loadPlanData();
        }
    }

    /**
     * Get plan ID
     * @return Int Plan ID
     */
    public function _getPlanID()
    {
        if (is_null($this->PlanID)) {
            throw new Exception("Error Charges Plan : Plan ID is null", 1);
        }
        return $this->PlanID;
    }

    /**
     * Load plan data
     */
    private function _loadPlanData()
    {
        $r = parent::querySqlRequest("SELECT * FROM plan_krypto WHERE id_plan=:id_plan", ['id_plan' => $this->_getPlanID()]);
        if (count($r) == 0) {
            throw new Exception("Error Charges Plan : Fail to load plan (".$this->_getPlanID().")", 1);
        }
        $this->PlanData = $r[0];
    }

    /**
     * Get plan data by key
     * @param  String $key Key needed
     * @return String      Value data associate to the key
     */
    private function _getPlanDataByKey($key)
    {
        if (!array_key_exists($key, $this->PlanData)) {
            throw new Exception("Error : Charge plan data not exist for key = ".$key, 1);
        }
        if (empty($this->PlanData[$key]) || strlen($this->PlanData[$key]) == 0) {
            return null;
        }

        // Return data associate to the key
        return $this->PlanData[$key];
    }

    /**
     * Get name plan
     * @return String Name plan
     */
    public function _getName()
    {
        return $this->_getPlanDataByKey('name_plan');
    }

    /**
     * Get plan duration (in days)
     * @return Int  Plan duration in days
     */
    public function _getDuration()
    {
        return intval($this->_getPlanDataByKey('ndays_plan'));
    }

    /**
     * Get number month plan duration
     * @return Int  Plan duration in month
     */
    public function _getNumberMonth()
    {
        return ceil($this->_getDuration() / 31);
    }

    /**
     * Get plan price
     * @param  Boolean $formated Price need to be formated
     * @return Int               Price plan
     */
    public function _getPrice($formated = false)
    {
        if (!$formated) {
            return intval($this->_getPlanDataByKey('price_plan'));
        }

        $App = new App();

        // Format plan price
        return $App->_formatNumber($this->_getPrice() / 100, 2);
    }

    /**
     * Get price plan per month
     * @param  Boolean $formated Price per month need to be formated
     * @return Int               Price plan per month
     */
    public function _getPricePerMonth($formated = false)
    {
        if (!$formated) {
            return intval($this->_getPrice()) / $this->_getNumberMonth();
        }

        $App = new App();

        // Format plan price
        return $App->_formatNumber(($this->_getPrice() / 100) / $this->_getNumberMonth(), 2);
    }

    /**
     * Get discount percentage plan (compared to the 1st plan)
     * @param  Boolean $formated Discount plan need to be formated
     * @return Int               Discount plan
     */
    public function _getDiscountPercentage($formated = false)
    {
        // Get first plan
        $r = parent::querySqlRequest("SELECT * FROM plan_krypto ORDER BY price_plan LIMIT 1", []);
        if (count($r) == 0) {
            return null;
        }

        // Check if current plan is the 1st
        if ($r[0]['id_plan'] == $this->_getPlanID()) {
            return null;
        }

        // Load cheap plan
        $CheapPlan = new ChargesPlan($r[0]['id_plan']);

        if (!$formated) {
            return round(($this->_getPricePerMonth() - $CheapPlan->_getPricePerMonth()) / $CheapPlan->_getPricePerMonth() * 100);
        }

        $App = new App();

        // Format discount
        return $App->_formatNumber($this->_getDiscountPercentage(), 0);
    }
}

?>
