<?php

class Bitstamp extends Exchange  {

  private $Api = null;

  public function __construct($User, $App, $Credentials = null){
    parent::__construct($User, $App, $this, $Credentials);
    parent::_setExchangeName('bitstamp');
  }

  public function _getApi(){

    if(!is_null($this->Api)) return $this->Api;

    if(!is_null($this->Credentials)){
      $this->Api = new \ccxt\bitstamp([
        'apiKey' => App::encrypt_decrypt('decrypt', $this->Credentials["key_bitstamp"]),
        'secret' => App::encrypt_decrypt('decrypt', $this->Credentials["secret_bitstamp"]),
        'uid' => App::encrypt_decrypt('decrypt', $this->Credentials["uid_bitstamp"])
      ]);
    } else {
      $this->Api = new \ccxt\bitstamp([
        'apiKey' => App::encrypt_decrypt('decrypt', $this->_isActivated()['key_bitstamp']),
        'secret' => App::encrypt_decrypt('decrypt', $this->_isActivated()['secret_bitstamp']),
        'uid' => App::encrypt_decrypt('decrypt', $this->_isActivated()['uid_bitstamp'])
      ]);
    }

    return $this->Api;

  }

  public function _getName(){ return 'Bitstamp'; }
  public function _getTable(){ return 'bitstamp_krypto'; }
  public function _getLogo(){ return 'bitstamp.png'; }
  public function _isActivated(){ return parent::_isActivated(); }



}

?>
