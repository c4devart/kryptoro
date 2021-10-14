<?php

class Yobit extends Exchange {

  private $Api = null;

  public function __construct($User, $App, $Credentials = null){
    parent::__construct($User, $App, $this, $Credentials);
    parent::_setExchangeName('yobit');
  }

  public function _getApi(){

    if(!is_null($this->Api)) return $this->Api;

    if(!is_null($this->Credentials)){
      $this->Api = new \ccxt\yobit([
        'apiKey' => App::encrypt_decrypt('decrypt', $this->Credentials["key_yobit"]),
        'secret' => App::encrypt_decrypt('decrypt', $this->Credentials["secret_yobit"])
      ]);
    } else {
      $this->Api = new \ccxt\yobit([
        'apiKey' => App::encrypt_decrypt('decrypt', $this->_isActivated()['key_yobit']),
        'secret' => App::encrypt_decrypt('decrypt', $this->_isActivated()['secret_yobit'])
      ]);
    }

    return $this->Api;

  }

  public function _getName(){ return 'Yobit'; }
  public function _getTable(){ return 'yobit_krypto'; }
  public function _getLogo(){ return 'yobit.png'; }
  public function _isActivated(){ return parent::_isActivated(); }

  public static function _formatPair($from, $to){
    return $from.'/'.$to;
  }

  public function _createOrder($symbol, $type, $side, $price = null, $params = [], $Balance = null, $InternalOrderID = null, $Type = "market", $order_price = null){

    if(!$Balance->_isPractice()){
      if($this->_isActivated() == false) throw new Exception($this->_getExchange()->_getName().' is not enable on your account', 1);

      $priceUnit = parent::_getPriceTrade($symbol);

      if($price * $priceUnit < 0.0001) throw new Exception("You need to trade at least 0.0001 ".explode('/', $symbol)[1], 1);

      $order = $this->_getExchange()->_getApi()->create_order($symbol, 'limit', $side, $price, $priceUnit + ($priceUnit * 0.05), $params);
    } else {
      $order = [];
      $order['amount'] = $price;
    }

    parent::_saveOrder($symbol, $type, $side, $order['amount'], $params, $Balance, $order);
  }

  public function _getFormatedBalance(){
    $balance = $this->_getApi()->fetch_balance();
    $res = [];
    foreach ($balance as $key => $value) {
      if($key == 'info' || $key == 'used' || $key == 'free' || $key == 'total') continue;
      $res[$key] = [
        'free' => $value['free'],
        'total' => $value['used']
      ];
    }
    return $res;
  }

  public function _getBalance($fetchall = false){
    $balanceList = [];
    $balanceWallet = $this->_getApi()->fetch_balance();
    foreach ($this->_getApi()->fetch_markets() as $key => $value) {
      if(array_key_exists($value['base'], $balanceWallet)) {
        $balanceList[$value['base']] = ['free' => $balanceWallet[$value['base']]['free'], 'used' => $balanceWallet[$value['base']]['used']];
      } else {
        $balanceList[$value['base']] = [ 'free' => 0, 'used' => 0 ];
      }
    }
    uasort($balanceList, array( $this, '_balanceSort' ));
    return $balanceList;
  }

  public function _getOrderBook($symbol = null){
    $v = $this->_getApi()->fetch_open_orders();
  }


}

?>
