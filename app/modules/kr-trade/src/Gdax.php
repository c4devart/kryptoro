<?php

class Gdax extends Exchange {

  private $AuthValue = null;
  private $Api = null;

  public function __construct($User, $App, $Credentials = null){
    $this->Credentials = $Credentials;
    parent::__construct($User, $App, $this, $Credentials);
    parent::_setExchangeName('gdax');
  }

  public function _getName(){ return 'Gdax'; }
  public function _getTable(){ return 'gdax_krypto'; }
  public function _getLogo(){ return 'gdax.svg'; }
  public function _isActivated(){ return parent::_isActivated(); }

  public function _getExchangeReal() { return "Coinbase"; }

  public function _getApi(){

    if(!is_null($this->Api)) return $this->Api;

    if(!is_null($this->Credentials)){
      $this->Api = new \ccxt\gdax([
        'apiKey' => App::encrypt_decrypt('decrypt', $this->Credentials["key_gdax"]),
        'password' => App::encrypt_decrypt('decrypt', $this->Credentials["pass_gdax"]),
        'secret' => App::encrypt_decrypt('decrypt', $this->Credentials["secret_gdax"])
      ]);

      if($this->Credentials["sandbox"] == 0) $this->Api->urls['api'] = 'https://api-public.sandbox.gdax.com';
    } else {
      $this->Api = new \ccxt\gdax([
        'apiKey' => App::encrypt_decrypt('decrypt', $this->_isActivated()['key_gdax']),
        'password' => App::encrypt_decrypt('decrypt', $this->_isActivated()['pass_gdax']),
        'secret' => App::encrypt_decrypt('decrypt', $this->_isActivated()['secret_gdax'])
      ]);
      if($this->_isActivated()['live_gdax'] == 0) $this->Api->urls['api'] = 'https://api-public.sandbox.gdax.com';
    }

    return $this->Api;

  }

  public static function _formatPair($from, $to){
    return $from.'/'.$to;
  }

  public function _getListAccount(){ return []; }

  public function _getBalance($fetchall = false){
    $balanceList = $this->_getFormatedBalance();
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

  public function _generateSign($time){

    $activation = $this->_isActivated();
    if(!$activation) throw new Exception("Error: GDAX not enabled", 1);

    return base64_encode(hash_hmac('sha256', $time.'GET/users/self/verify', base64_decode(App::encrypt_decrypt('decrypt', $activation['secret_gdax'])), true));

  }

  public function _getCredentials(){
    $activation = $this->_isActivated();
    if(!$activation) throw new Exception("Error : GDAX not enabled", 1);

    return [
      'pass' => App::encrypt_decrypt('decrypt', $activation['pass_gdax']),
      'apikey' => App::encrypt_decrypt('decrypt', $activation['key_gdax'])
    ];

  }

  public function _getOrderBook($symbol = null){
    $orderList = [];
    if(is_null($symbol)){
      foreach ($this->_getOrderSymbol() as $symbolOrdered) {
        foreach ($this->_getApi()->fetch_my_trades($symbolOrdered) as $orderInfos) {
          $symbolInfos = explode('-', $orderInfos['info']['product_id']);
          $DateTime = new DateTime($orderInfos['info']['created_at']);

          $orderList[] = [
            'id' => $orderInfos['id'],
            'market' => $orderInfos['info']['product_id'],
            'market_price_buyed' => $orderInfos['info']['price'],
            'symbol' => $symbolInfos[0],
            'date' => $this->_formatTradingDate($DateTime->getTimestamp()),
            'time' => $DateTime->getTimestamp(),
            'type' => strtolower($orderInfos['info']['side']),
            'size' => $orderInfos['info']['size'],
            'total' => $orderInfos['info']['price'] * $orderInfos['info']['size'],
            'total_currency' => $symbolInfos[1],
            'fees' => $orderInfos['info']['fee']
          ];
        }
      }
    } else {
      foreach ($this->_getApi()->fetch_my_trades($symbol) as $orderInfos) {
        $symbolInfos = explode('-', $orderInfos['info']['product_id']);
        $DateTime = new DateTime($orderInfos['info']['created_at']);
        $orderList[] = [
          'id' => $orderInfos['id'],
          'market' => $orderInfos['info']['product_id'],
          'market_price_buyed' => $orderInfos['info']['price'],
          'symbol' => $symbolInfos[0],
          'date' => $this->_formatTradingDate($DateTime->getTimestamp()),
          'time' => $DateTime->getTimestamp(),
          'type' => strtolower($orderInfos['info']['side']),
          'size' => $orderInfos['info']['size'],
          'total' => $orderInfos['info']['price'] * $orderInfos['info']['size'],
          'total_currency' => $symbolInfos[1],
          'fees' => $orderInfos['info']['fee']
        ];
      }
    }
    usort($orderList, array($this, '_sortOrderBook'));
    return $orderList;

  }


}

?>
