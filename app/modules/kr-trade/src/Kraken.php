<?php

class Kraken extends Exchange {

  private $AuthValue = null;
  private $Api = null;

  public function __construct($User, $App, $Credentials = null){
    parent::__construct($User, $App, $this, $Credentials);
    parent::_setExchangeName('kraken');
  }

  public function _getName(){ return 'Kraken'; }
  public function _getTable(){ return 'kraken_krypto'; }
  public function _getLogo(){ return 'kraken.png'; }
  public function _isActivated(){ return parent::_isActivated(); }

  public function _getApi(){

    if(!is_null($this->Api)) return $this->Api;

    if(!is_null($this->Credentials)){
      $this->Api = new \ccxt\kraken([
        'apiKey' => App::encrypt_decrypt('decrypt', $this->Credentials["key_kraken"]),
        'secret' => App::encrypt_decrypt('decrypt', $this->Credentials["private_kraken"])
      ]);
    } else {
      $this->Api = new \ccxt\kraken([
        'apiKey' => App::encrypt_decrypt('decrypt', $this->_isActivated()['key_kraken']),
        'secret' => App::encrypt_decrypt('decrypt', $this->_isActivated()['private_kraken'])
      ]);
    }

    return $this->Api;

  }

  public static function _formatPair($from, $to){
    return $from.'/'.$to;
  }

  public function _createOrder($symbol, $type, $side, $price = null, $params = [], $Balance = null, $InternalOrderID = null, $Type = "market", $order_price = null){
    if(!$Balance->_isPractice()){
        if($this->_isActivated() == false) throw new Exception($this->_getExchange()->_getName().' is not enable on your account', 1);

        $priceUnit = parent::_getPriceTrade($symbol);

        $order = $this->_getExchange()->_getApi()->create_order($symbol, $type, $side, $price, null, $params);
    } else {
      $order = [];
      $order['amount'] = $price;
    }

    parent::_saveOrder($symbol, $type, $side, $order['amount'], $params, $Balance, $order);
  }

  public function _getFormatedBalance(){
    if($this->_isActivated() == false) throw new Exception($this->_getExchange()->_getName().' is not enable on your account', 1);
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

}

?>
