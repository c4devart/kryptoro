<?php

class ChainSo extends MySQL {


  private $App = null;

  private $Symbol = null;


  public function __construct($App, $Symbol = "LTC"){
    $this->App = $App;
    $this->Symbol = $Symbol;
  }

  public function _getApp(){
    return $this->App;
  }

  public function _getSymbol(){
    return $this->Symbol;
  }

  private function _getApiKey(){
    //https://chain.so/api/v2/get_confidence/LTC/2ae79b79b82b545b43cde08d8a22950b5e7b8da7c619aef14f49b0e9fda1f248
  }

  public function _call($service, $args){
    $ch =  curl_init("https://chain.so/api/v2/".$service."/".$this->_getSymbol()."/".join('/', $args));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_ENCODING,  '');
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));

    $s = json_decode(curl_exec($ch), true);

    curl_close($ch);

    if(is_null($s)) throw new Exception("Error : Fail to fetch ChainSo", 1);

    if(array_key_exists('status', $s) && $s['status'] != "success") throw new Exception($s['data']['API'], 1);

    return $s['data'];

  }

  public function _getHistoryTransaction($address){
    return [];
  }

}

?>
