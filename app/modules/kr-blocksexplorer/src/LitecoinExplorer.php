<?php

class LitecoinExplorer extends MySQL {

  private $type = "litcoin";

  private $App = null;

  private $Api = null;


  public function __construct($App, $User = null){
    $this->App = $App;
    $this->Api = new ChainSo($this->_getApp(), 'LTC');
  }

  public function _getApp(){
    return $this->App;
  }

  private function _getApi(){
    return $this->Api;
  }

  public function _getNumberConfirmation($tx){
    return $this->_getApi()->_call('get_confidence', ['2ae79b79b82b545b43cde08d8a22950b5e7b8da7c619aef14f49b0e9fda1f248'])['confirmations'];
  }

  public function _getTransactionInfos($tx){

    $transactionInfos = $this->_getApi()->_call('get_tx', [$tx]);

    return $transactionInfos;
    //https://blockchain.info/rawtx/

  }

  public function _getHistoryTransaction($address){
    return [];
  }


}

?>
