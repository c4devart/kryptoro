<?php

/**
 * CoinGate class
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
class CoinGate extends MySQL
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
            throw new Exception("Error CoinGate : App need to be given", 1);
        }
        $this->App = $App;
        $this->_initCoinGateConfig();
    }
    /**
     * Get app object
     * @return App App object
     */
    private function _getApp()
    {
        if (is_null($this->App)) {
            throw new Exception("Error CoinGate : App not defined", 1);
        }
        return $this->App;
    }

    private function _getCoinGateEnv(){
      if($this->_getApp()->_coingateLiveMode()) return "live";
      return "sandbox";
    }

    private function _getCoinGateConnexionData(){
      return [
        'environment'   => $this->_getCoinGateEnv(),
        'auth_token'    => $this->_getApp()->_getCoinGateAuthToken()
      ];

    }

    public function _initCoinGateConfig(){

      if(!$this->_getApp()->_coingateEnabled()) throw new Exception("Error : Coin gate not enabled", 1);
      error_log(json_encode($this->_getCoinGateConnexionData()));
      $testConnection = \CoinGate\CoinGate::testConnection($this->_getCoinGateConnexionData());

      if(!$testConnection) throw new Exception("Error : Wrong Coin Gate API key", 1);

      \CoinGate\CoinGate::config($this->_getCoinGateConnexionData());


    }

    public function _createOrder($User, $cuid){

      $infosPlan = explode('-', $cuid);
      if(count($infosPlan) != 2) throw new Exception("Error : Wrong args", 1);
      $infosUserPlan = explode('-', App::encrypt_decrypt('decrypt', $infosPlan[0]));
      if(count($infosUserPlan) != 2) throw new Exception("Error : Wrong args", 1);

      if($infosUserPlan[0] != $User->_getUserID()) throw new Exception("Error : Permission denied", 1);

      $ChargePlan = new ChargesPlan($infosUserPlan[1]);


     return \CoinGate\Merchant\Order::create(array(
            'order_id'          => $cuid,
            'price_amount'      => $ChargePlan->_getPrice() / 100,
            'price_currency'    => 'USD',
            'receive_currency'  => $this->_getApp()->_getCoingateConvertionTo(),
            'callback_url'      => APP_URL.'/app/modules/kr-payment/src/actions/processCoinGate.php',
            'cancel_url'        => APP_URL.'/app/modules/kr-payment/src/actions/processCoinGate.php?r='.$cuid,
            'success_url'       => APP_URL.'/app/modules/kr-payment/src/actions/processCoinGate.php?r='.$cuid,
            'title'             => 'Order - '.$infosPlan[1],
            'description'       => $ChargePlan->_getName().' ('.$ChargePlan->_getNumberMonth().' month'.($ChargePlan->_getNumberMonth() > 1 ? 's' : '').')'
        ));

    }

    public function _createDeposit($User, $Amount, $Balance, $currency = 'USD'){
      $cuid = App::encrypt_decrypt('encrypt', $User->_getUserID().'-'.uniqid().'-'.($Amount * 100));
      $fees = ($Amount * ($this->_getApp()->_getFeesDeposit() / 100));

      try {
        $InfoCoin = $Balance->_getInfosMoney($currency);
      } catch (\Exception $e) {
        $InfoCoin = $Balance->_getInfoCryptoCurrency($currency);
      }

      $WalletListAvailable = $Balance->_getBalanceListResum();

      $BalanceReceivedSymbol = $this->_getApp()->_getDepositSymbolNotExistConvert();
      if(array_key_exists($currency, $WalletListAvailable)) $BalanceReceivedSymbol = $currency;

      $convertRatio = 1;
      if($BalanceReceivedSymbol != $currency){
          $response = \CoinGate\Coingate::request('/rates/merchant/'.$currency.'/'.$BalanceReceivedSymbol, 'GET');
          $convertRatio = number_format($response, 20, '.', '');
      }


      $Balance->_addDeposit($Amount, 'coingate', 'Coingate payment', $currency, $cuid, 0, $BalanceReceivedSymbol);

      return \CoinGate\Merchant\Order::create(array(
             'order_id'          => $cuid,
             'price_amount'      => ($fees + $Amount),
             'price_currency'    => $currency,
             'receive_currency'  => $this->_getApp()->_getCoingateConvertionTo(),
             'callback_url'      => APP_URL.'/app/modules/kr-payment/src/actions/deposit/processCoinGate.php',
             'cancel_url'        => APP_URL.'/app/modules/kr-payment/src/actions/deposit/processCoinGate.php?r='.$cuid,
             'success_url'       => APP_URL.'/app/modules/kr-payment/src/actions/deposit/processCoinGate.php?r='.$cuid,
             'title'             => 'Deposit - '.$User->_getUserID().' - '.$User->_getEmail().' - '.$this->_getApp()->_formatNumber($Amount, 2).' '.$InfoCoin['symbol_currency'].' (+'.$fees.' '.$InfoCoin['symbol_currency'].' fees)',
             'description'       => 'Deposit - '.$User->_getUserID().' - '.$User->_getEmail().' - '.$this->_getApp()->_formatNumber($Amount, 2).' '.$InfoCoin['symbol_currency'].' (+'.$fees.' '.$InfoCoin['symbol_currency'].' fees)'
         ));
    }

    public function _parseResult($args){

      $infosPlan = explode('-', $args['order_id']);
      if(count($infosPlan) != 2) throw new Exception("Error : Wrong args", 1);
      $infosUserPlan = explode('-', App::encrypt_decrypt('decrypt', $infosPlan[0]));
      if(count($infosUserPlan) != 2) throw new Exception("Error : Wrong args", 1);

      $ChargePlan = new ChargesPlan($infosUserPlan[1]);

      $order = \CoinGate\Merchant\Order::find($args['id']);

      if(!$order) throw new Exception("Error : Fail to get order", 1);

      if($order->order_id != $args['order_id']) throw new Exception("Error : Fail to get order", 1);

      $status = 1;
      if($order->status != "confirming" && $order->status != "paid"){
        $status = 0;
      }

      return [
        'plan' => $ChargePlan,
        'user' => $infosUserPlan[0],
        'status' => $status
      ];
    }

    public function _parseResultDeposit($args){
      $orderID = $args['order_id'];
      $infosDeposit = explode('-', App::encrypt_decrypt('decrypt', $orderID));
      //error_log($infosDeposit[0]);
      if(count($infosDeposit) != 3) throw new Exception("Permission denied", 1);

      $order = \CoinGate\Merchant\Order::find($args['id']);

      if(!$order) throw new Exception("Error : Fail to get order", 1);

      if($order->order_id != $orderID) throw new Exception("Error : Fail to get order", 1);

      $status = 1;
      if($order->status != "confirming" && $order->status != "paid"){
        $status = 0;
      }

      return [
        'user' => new User(intval($infosDeposit[0])),
        'order_id' => $orderID,
        'status' => $status
      ];

    }

    /**
     * Check payment CoinGate
     * @param  User $user   User logged
     * @param  String $cuid CUID
     * @return Int
     */
    public function _checkPayment($user, $cuid){

      $r = parent::querySqlRequest("SELECT * FROM charges_krypto WHERE id_user=:id_user AND data_payment LIKE :data_payment AND type_payment=:type_payment",
                                  [
                                    'id_user' => $user->_getUserID(),
                                    'data_payment' => '%"order_id":"'.$cuid.'"%',
                                    'type_payment' => 'coingate'
                                  ]);

      if(count($r) == 0) return 0;
      if(count($r) > 0 && $r[0]['status_charges'] == "1") return 1;
      return 2;

    }

    public function _checkDeposit($User, $time){
      if(!is_numeric($time)) throw new Exception("Error : Wrong format", 1);
      $r = parent::querySqlRequest('SELECT * FROM deposit_history_krypto WHERE payment_type_deposit_history=:payment_type_deposit_history AND date_deposit_history > :date_deposit_history AND id_user=:id_user ORDER BY id_deposit_history DESC LIMIT 1',
                                  [
                                    'id_user' => $User->_getUserID(),
                                    'payment_type_deposit_history' => 'coingate',
                                    'date_deposit_history' => $time
                                  ]);
      if(count($r) == 0) return 2;
      if($r[0]['payment_status_deposit_history'] == "1") return 1;
      return 0;
    }

    public static function _getListCurrenciesConvertAvailable(){

      return ['USD', 'EUR', 'BTC', 'LTC', 'ETH', 'BCH'];

    }
}
