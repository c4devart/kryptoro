<?php

class Luno extends Exchange {

  private $Api = null;

  public function __construct($User, $App, $Credentials = null){
    parent::__construct($User, $App, $this, $Credentials);
    parent::_setExchangeName('luno');
  }

  public function _getApi(){

    if(!is_null($this->Api)) return $this->Api;

    if(!is_null($this->Credentials)){
      $this->Api = new \ccxt\luno([
        'apiKey' => App::encrypt_decrypt('decrypt', $this->Credentials["key_luno"]),
        'secret' => App::encrypt_decrypt('decrypt', $this->Credentials["secret_luno"])
      ]);
    } else {
      $this->Api = new \ccxt\luno([
        'apiKey' => App::encrypt_decrypt('decrypt', $this->_isActivated()['key_luno']),
        'secret' => App::encrypt_decrypt('decrypt', $this->_isActivated()['secret_luno'])
      ]);
    }

    return $this->Api;

  }

  public function _getName(){ return 'Luno'; }
  public function _getTable(){ return 'luno_krypto'; }
  public function _getLogo(){ return 'luno.png'; }
  public function _isActivated(){ return parent::_isActivated(); }

  public static function _formatPair($from, $to){
    return $from.'/'.$to;
  }

  public function _createOrder($symbol, $type, $side, $price = null, $params = [], $Balance = null, $InternalOrderID = null, $Type = "market", $order_price = null){

    if(!$Balance->_isPractice()){
      if($this->_isActivated() == false) throw new Exception($this->_getExchange()->_getName().' is not enable on your account', 1);

      if($price < 0.0005) throw new Exception("You need to sell/buy at least : 0.0005 ".explode('/', $symbol)[0], 1);


      $priceUnit = parent::_getPriceTrade($symbol);

      $order = $this->_getExchange()->_getApi()->create_order($symbol, 'market', strtolower($side), $priceUnit * $price, null, $params);
    }
    
    parent::_saveOrder($symbol, $type, $side, $price, $params, $Balance, $order);
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
