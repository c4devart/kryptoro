<?php

/**
 * Polipayments class
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
class Polipayments extends MySQL
{
    /**
     * App object
     * @var App
     */
    private $App = null;

    /**
     * Polipayments constructor
     * @param App $App          App object
     * @param String $keycharge Charge key
     */
    public function __construct($App = null)
    {
        if (is_null($App)) {
            throw new Exception("Error Polipayments : App need to be given", 1);
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
            throw new Exception("Error Polipayments : App not defined", 1);
        }
        return $this->App;
    }

    public function _callPolipaymentsApi($ressource = "Transaction/GetTransaction", $postfields = ""){

      if(!$this->_getApp()->_polipaymentsEnabled()) throw new Exception("Error : Polipayments is not enabled", 1);


      $header = ['Authorization: Basic '.base64_encode($this->_getApp()->_getPolipaymentsMarchandCode().':'.$this->_getApp()->_getPolipaymentsAuthCode())];
      if(strlen($postfields) > 1) $header[] = 'Content-Type: application/json';

      $ch = curl_init("https://poliapi.apac.paywithpoli.com/api/v2/".$ressource);
      curl_setopt( $ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
      curl_setopt( $ch, CURLOPT_HTTPHEADER, $header);
      curl_setopt( $ch, CURLOPT_HEADER, 0);
      curl_setopt( $ch, CURLOPT_POST, (strlen($postfields) > 1 ? 1 : 0));
      curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 0);
      curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
      if(strlen($postfields) > 1) curl_setopt( $ch, CURLOPT_POSTFIELDS, $postfields);
      $response = curl_exec( $ch );
      curl_close ($ch);

      $infos = json_decode($response, true);

      if(array_key_exists('Success', $infos) && $infos['Success'] == false){
        if(!$infos['Success']) throw new Exception($infos['ErrorMessage'], 1);
        return $infos;
      } else {
        if(array_key_exists('ErrorCode', $infos) && $infos['ErrorCode'] > 1){
          throw new Exception(json_encode($infos), 1);
        }
      }
      return $infos;
    }

    public function _createDeposit($User, $Amount, $Balance, $currency = 'USD'){
      $refDeposit = $Balance->_generatePaymentReference();
      $infosCall = $this->_callPolipaymentsApi('Transaction/Initiate', '{
            "Amount":"'.$Amount.'",
            "CurrencyCode":"'.$currency.'",
            "MerchantReference":"'.$refDeposit.'",
            "MerchantData": "{\"user_id\":\"'.$User->_getUserID().'\"}",
            "MerchantHomepageURL":"'.APP_URL.'/app/modules/kr-payment/src/actions/deposit/processPolipayments.php",
            "SuccessURL":"'.APP_URL.'/app/modules/kr-payment/src/actions/deposit/processPolipayments.php?d=success",
            "FailureURL":"'.APP_URL.'/app/modules/kr-payment/src/actions/deposit/processPolipayments.php?d=fail",
            "CancellationURL":"'.APP_URL.'/app/modules/kr-payment/src/actions/deposit/processPolipayments.php",
            "NotificationURL":"'.APP_URL.'/app/modules/kr-payment/src/actions/deposit/processPolipayments.php"
        }');

      return $infosCall['NavigateURL'];

    }

    public function _checkPayment($token){

      $infosToken = $this->_callPolipaymentsApi("Transaction/GetTransaction?token=".urlencode($token));

      if($infosToken['TransactionStatusCode'] != "Completed") throw new Exception(json_encode($infosToken), 1);

      $MarchantData = json_decode($infosToken['MerchantData'], true);

      if(!array_key_exists('user_id', $MarchantData)) throw new Exception("Error Polipayments : The user id is not fetchable", 1);

      $User = new User($MarchantData['user_id']);
      $Balance = new Balance($User, $this->_getApp(), 'real');

      $r = parent::querySqlRequest("SELECT * FROM deposit_history_krypto WHERE id_user=:id_user AND payment_type_deposit_history=:payment_type_deposit_history AND payment_status_deposit_history > :payment_status_deposit_history AND payment_data_deposit_history LIKE :payment_data_deposit_history",
                                  [
                                    'id_user' => $User->_getUserID(),
                                    'payment_type_deposit_history' => 'polipayments',
                                    'payment_status_deposit_history' => 0,
                                    'payment_data_deposit_history' => '%'.$infosToken['TransactionID'].'%'
                                  ]);

      if(count($r) > 0) throw new Exception("Error : Polipayment : ".$infosToken['TransactionID']." order already processed", 1);

      $Balance->_addDeposit($infosToken['AmountPaid'], 'polipayments', 'Polipayments deposit ('.$infosToken['AmountPaid'].' '.$infosToken['CurrencyCode'].')', $infosToken['CurrencyCode'], $infosToken['TransactionID']);

    }

    public function _getCurrencyAvailable(){
      return ['AUD', 'NZD'];
    }

    public function _getRetryPaymentURL($token){
      return "https://txn.apac.paywithpoli.com/?Token=".$token;
    }

    public static function _getErrorSentense($error){

      $r = [
        'Cancelled' => 'Your payment has been cancelled',
        'Receipt Unverified' => 'Your payment is not verified yet, you will receive your payment when the payment is complete',
        'Failed' => 'Your payment failed',
        'Timed Out' => 'Your payment has been timed out'
      ];

      if(!array_key_exists($error, $r)) return "Payment return not defined";
      return $r[$error];

    }

    public static function _retryPayment($error){
      $r = [
        'Cancelled' => true,
        'Receipt Unverified' => false,
        'Failed' => true,
        'Timed Out' => true
      ];
      if(!array_key_exists($error, $r)) return false;
      return $r[$error];
    }

}
