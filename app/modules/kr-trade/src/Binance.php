<?php

class Binance extends Exchange  {

  private $Api = null;

  public function __construct($User, $App, $Credentials = null){
    parent::__construct($User, $App, $this, $Credentials);
    parent::_setExchangeName('binance');
  }

  public function _getName(){ return 'Binance'; }
  public function _getTable(){ return 'binance_krypto'; }
  public function _getLogo(){ return 'binance.png'; }
  public function _isActivated(){ return parent::_isActivated(); }

  public function _getApi(){

    if(!is_null($this->Api)) return $this->Api;

    if(!is_null($this->Credentials)){
      $this->Api = new \ccxt\binance([
        'apiKey' => App::encrypt_decrypt('decrypt', $this->Credentials["key_binance"]),
        'secret' => App::encrypt_decrypt('decrypt', $this->Credentials["secret_binance"])
      ]);
    } else {
      $this->Api = new \ccxt\binance([
        'apiKey' => App::encrypt_decrypt('decrypt', $this->_isActivated()['key_binance']),
        'secret' => App::encrypt_decrypt('decrypt', $this->_isActivated()['secret_binance'])
      ]);
    }

    return $this->Api;

  }

  public function _getFormatedBalance(){
    $balance = $this->_getApi()->fetch_balance();
    $res = [];

    foreach ($balance['info']['balances'] as $key => $value) {
      $res[$value['asset']] = [
        'free' => $value['free'],
        'used' => $value['locked']
      ];
    }

    return $res;
  }

  public function _getBalance($fetchall = false){
    $balanceList = $this->_getFormatedBalance();
    return $balanceList;
    $balanceListRes = [];
    foreach ($balanceList['info'] as $key => $value) {
      if($value['available'] > 0 || $value['hold'] > 0 || $fetchall){
        $balanceListRes[$value['currency']] = [
          'free' => $value['available'],
          'used' => $value['hold']
        ];
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

  public static function _formatPair($from, $to){
    return $from.'/'.$to;
  }



  public function _getOrderBook($symbol = null){
    $orderList = [];
    if(is_null($symbol)){
      foreach ($this->_getOrderSymbol() as $symbolOrdered) {
        foreach ($this->_getApi()->fetch_my_trades($symbolOrdered) as $orderInfos) {
          $symbolInfos = $this->_infosPair($orderInfos['symbol']);

          $orderList[] = [
            'id' => $orderInfos['id'],
            'market' => $orderInfos['symbol'],
            'market_price_buyed' => $orderInfos['price'],
            'symbol' => $symbolInfos['symbol'],
            'currency' => $symbolInfos['currency'],
            'date' => $this->_formatTradingDate($orderInfos['timestamp']),
            'time' => $orderInfos['timestamp'],
            'type' => strtolower($orderInfos['type']),
            'size' => $orderInfos['amount'],
            'total' => $orderInfos['cost'],
            'total_currency' => $symbolInfos[1],
            'fees' => $orderInfos['fee']['cost']
          ];
        }
      }
    } else {
      foreach ($this->_getApi()->fetch_my_trades($symbol) as $orderInfos) {
        $symbolInfos = $this->_infosPair($orderInfos['symbol']);

        $orderList[] = [
          'id' => $orderInfos['id'],
          'market' => $orderInfos['symbol'],
          'market_price_buyed' => $orderInfos['price'],
          'symbol' => $symbolInfos['symbol'],
          'currency' => $symbolInfos['currency'],
          'date' => $this->_formatTradingDate($orderInfos['timestamp']),
          'time' => $orderInfos['timestamp'],
          'type' => strtolower($orderInfos['type']),
          'size' => $orderInfos['amount'],
          'side' => strtoupper($orderInfos['side']),
          'total' => $orderInfos['cost'],
          'total_currency' => $symbolInfos['currency'],
          'fees' => $orderInfos['fee']['cost']
        ];
      }
    }

    usort($orderList, array($this, '_sortOrderBook'));
    return $orderList;

  }




}

?>
