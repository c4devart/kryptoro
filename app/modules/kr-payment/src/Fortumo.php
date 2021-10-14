<?php

/**
 * Fortumo class
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
class Fortumo extends MySQL
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
            throw new Exception("Error Fortumo : App need to be given", 1);
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
            throw new Exception("Error Fortumo : App not defined", 1);
        }
        return $this->App;
    }

    /**
     * Test fortumo page callback
     * @param  [type] $args [description]
     * @return [type]       [description]
     */
    public function _testCallback($args){
      if(empty($this->_getApp()->_getFortumoSecretKey())||!$this->_checkFortumoSignature($args, $this->_getApp()->_getFortumoSecretKey())) {
        header("HTTP/1.0 404 Not Found");
        die("<script>window.close();</script>");
      }
    }

    /**
     * Parse Fortumo result
     * @param  Array $result  Formtumo result array
     */
    public function _parseResult($result){

      //error_log(json_encode($result));

      $sender = $result['sender'];
      $amount = $result['amount'];
      $cuid = $result['cuid'];
      $payment_id = $result['payment_id'];
      $test = $result['test'];

      return preg_match("/completed/i", $result['status']);

    }

    /**
     * Check Formtumo signature
     * @param  Array $params_array  Signature list
     * @param  String $secret       Formtumo secret key
     * @return Boolean
     */
    public function _checkFortumoSignature($params_array, $secret){

      if(!isset($params_array['sig'])) return false;

      ksort($params_array);

      $str = '';
      foreach ($params_array as $k=>$v) {
        if($k != 'sig') {
          $str .= "$k=$v";
        }
      }
      $str .= $secret;
      $signature = md5($str);

      return ($params_array['sig'] == $signature);
    }

    /**
     * Check payment fortumo
     * @param  User $user   User logged
     * @param  String $cuid CUID
     * @return Int
     */
    public function _checkPayment($user, $cuid){

      $r = parent::querySqlRequest("SELECT * FROM charges_krypto WHERE id_user=:id_user AND data_payment LIKE :data_payment AND type_payment=:type_payment",
                                  [
                                    'id_user' => $user->_getUserID(),
                                    'data_payment' => '%"cuid":"'.$cuid.'"%',
                                    'type_payment' => 'fortumo'
                                  ]);

      if(count($r) == 0) return 0;
      if(count($r) > 0 && $r[0]['status_charges'] == "1") return 1;
      return 2;

    }
}
