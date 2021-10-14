<?php

class Hitbtc extends Exchange {

  private $Api = null;

  public function __construct($User, $App, $Credentials = null){
    parent::__construct($User, $App, $this, $Credentials);
    parent::_setExchangeName('hitbtc2');
  }

  public function _getOtherName(){ return 'hitbtc'; }

  public function _getName(){ return 'Hitbtc'; }
  public function _getTable(){ return 'hitbtc2_krypto'; }
  public function _getLogo(){ return 'hitbtc.png'; }
  public function _isActivated(){ return parent::_isActivated(); }

  public function _getApi(){

    if(!is_null($this->Api)) return $this->Api;

    if(!is_null($this->Credentials)){
      $this->Api = new \ccxt\hitbtc2([
        'apiKey' => App::encrypt_decrypt('decrypt', $this->Credentials["key_hitbtc2"]),
        'secret' => App::encrypt_decrypt('decrypt', $this->Credentials["secret_hitbtc2"])
      ]);
    } else {
      $this->Api = new \ccxt\hitbtc2([
        'apiKey' => App::encrypt_decrypt('decrypt', $this->_isActivated()['key_hitbtc2']),
        'secret' => App::encrypt_decrypt('decrypt', $this->_isActivated()['secret_hitbtc2'])
      ]);
    }

    return $this->Api;

  }

  public function _getFormatedBalance(){
    if($this->_isActivated() == false) throw new Exception($this->_getExchange()->_getName().' is not enable on your account', 1);
    $balance = $this->_getApi()->fetch_balance();
    $res = [];
    foreach ($balance as $key => $value) {
      if($key == 'info' || $key == 'used' || $key == 'free' || $key == 'total') continue;
      $res[$key] = [
        'free' => $value['free'],
        'used' => $value['locked']
      ];
    }
    return $res;
  }


}

?>
