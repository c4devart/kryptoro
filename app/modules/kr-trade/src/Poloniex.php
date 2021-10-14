<?php

class Poloniex extends Exchange {

  private $Api = null;

  public function __construct($User, $App, $Credentials = null){
    parent::__construct($User, $App, $this, $Credentials);
    parent::_setExchangeName('poloniex');
  }

  public function _getName(){ return 'Poloniex'; }
  public function _getTable(){ return 'poloniex_krypto'; }
  public function _getLogo(){ return 'poloniex.png'; }
  public function _isActivated(){ return parent::_isActivated(); }

  public function _getApi(){

    if(!is_null($this->Api)) return $this->Api;

    if(!is_null($this->Credentials)){
      $this->Api = new \ccxt\poloniex([
        'apiKey' => App::encrypt_decrypt('decrypt', $this->Credentials["key_poloniex"]),
        'secret' => App::encrypt_decrypt('decrypt', $this->Credentials["secret_poloniex"])
      ]);
    } else {
      $this->Api = new \ccxt\poloniex([
        'apiKey' => App::encrypt_decrypt('decrypt', $this->_isActivated()['key_poloniex']),
        'secret' => App::encrypt_decrypt('decrypt', $this->_isActivated()['secret_poloniex'])
      ]);
    }

    return $this->Api;

  }

  public function _createOrder($symbol, $type, $side, $price = null, $params = [], $Balance = null, $InternalOrderID = null, $Type = "market", $order_price = null){

    if(!$Balance->_isPractice()){
      if($this->_isActivated() == false) throw new Exception($this->_getExchange()->_getName().' is not enable on your account', 1);

      $priceUnit = parent::_getPriceTrade($symbol);

      if($price * $priceUnit <= 0.0001) throw new Exception("You need to trade at least 0.0001 ".explode('/', $symbol)[1], 1);

      $order = $this->_getExchange()->_getApi()->create_order($symbol, 'limit', $side, $price, $priceUnit + ($priceUnit * 0.05), $params);
    } else {
      $order = [];
      $order['amount'] = $price;
    }

    parent::_saveOrder($symbol, $type, $side, $order['amount'], $params, $Balance, $order);
  }

  public function _getBalance($fetchall = false){
    $balanceList = $this->_getFormatedBalance();
    //error_log(json_encode($balanceList));
    $balanceListRes = [];
    foreach ($balanceList as $key => $value) {
      if($key == "info" || $key == "total" || $key == "used" || $key == "free") continue;
      //error_log(json_encode($value));
      if($value['free'] > 0 || $value['used'] > 0 || $fetchall){
        $balanceListRes[$key] = $value;
      }
    }

    if(count($balanceListRes) == 0){
      $listAvailable = ['USD', 'BTC', 'EUR', 'LTC', 'ETH'];
      foreach ($listAvailable as $cur) {
        if(array_key_exists($cur, $balanceList)){
          $balanceListRes[$cur] = $balanceList[$cur];
        }
      }
    }
    uasort($balanceListRes, array( $this, '_balanceSort' ));
    return $balanceListRes;
  }


}

?>
