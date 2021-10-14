<?php

class Coinex extends Exchange {

  private $Api = null;

  public function __construct($User, $App, $Credentials = null){
    parent::__construct($User, $App, $this, $Credentials);
    parent::_setExchangeName('coinex');
  }

  public function _getApi(){

    if(!is_null($this->Api)) return $this->Api;

    if(!is_null($this->Credentials)){
      $this->Api = new \ccxt\coinex([
        'apiKey' => App::encrypt_decrypt('decrypt', $this->Credentials["key_coinex"]),
        'secret' => App::encrypt_decrypt('decrypt', $this->Credentials["secret_coinex"])
      ]);
    } else {
      $this->Api = new \ccxt\coinex([
        'apiKey' => App::encrypt_decrypt('decrypt', $this->_isActivated()['key_coinex']),
        'secret' => App::encrypt_decrypt('decrypt', $this->_isActivated()['secret_coinex'])
      ]);
    }

    return $this->Api;

  }

  public function _getName(){ return 'Coinex'; }
  public function _getTable(){ return 'coinex_krypto'; }
  public function _getLogo(){ return 'coinex.png'; }
  public function _isActivated(){ return parent::_isActivated(); }

  public static function _formatPair($from, $to){
    return $from.'/'.$to;
  }

  public function _createOrder($symbol, $type, $side, $price = null, $params = [], $Balance = null, $InternalOrderID = null, $Type = "market", $order_price = null){

    if(!$Balance->_isPractice()){
      if($this->_isActivated() == false) throw new Exception($this->_getExchange()->_getName().' is not enable on your account', 1);

      $priceUnit = parent::_getPriceTrade($symbol);


      $order = $this->_getExchange()->_getApi()->create_order($symbol, $type, $side, $price * $priceUnit, null, $params);
    } else {
      $order = [];
      $order['filled'] = $price;
    }

    parent::_saveOrder($symbol, $type, $side, $order['filled'], $params, $Balance, $order);
  }

  public function _getFormatedBalance(){
    $balance = $this->_getApi()->fetch_balance();
    $res = [];
    foreach ($balance as $key => $value) {
      if($key == 'info' || $key == 'used' || $key == 'free' || $key == 'total') continue;
      $res[$key] = [
        'free' => $value['free'],
        'used' => $value['used']
      ];
    }
    return $res;
  }

  public function _getBalance($fetchall = false){
    $balanceList = $this->_getFormatedBalance();
    uasort($balanceList, array( $this, '_balanceSort' ));
    return $balanceList;
  }

  public function _getOrderBook($symbol = null){
    $v = $this->_getApi()->fetch_open_orders();

  }


}

?>
