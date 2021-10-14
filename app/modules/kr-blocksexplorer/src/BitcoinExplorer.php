<?php

class BitcoinExplorer extends MySQL {

  private $type = "bitcoin";

  private $App = null;


  public function __construct($App, $User = null){
    $this->App = $App;
  }

  public function _getApp(){
    return $this->App;
  }

  private function _getApiKey(){

  }

  public function _call($args){
    $ch =  curl_init("https://blockchain.info/".join('/', $args));
    var_dump("https://blockchain.info/".join('/', $args));
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

    if(is_null($s)) throw new Exception("Error : Fail to fetch etherblock", 1);

    return $s;


  }

  public function _getHistoryTransaction($address){
    $transactionList = $this->_call(['rawaddr', $address])['txs'];
    $receiveTransaction = [];
    foreach ($transactionList as $key => $value) {
      foreach ($value['out'] as $keyVInput => $valueVInput) {
        if($valueVInput['addr'] == $address){
          $receiveTransaction[] = $value;
          break;
        }
      }
    }

    foreach ($receiveTransaction as $key => $value) {
      // code...
    }

    var_dump($receiveTransaction);
  }

  private $CurrentBlockHeight = null;
  public function _getBlockHeight(){
    if(!is_null($this->CurrentBlockHeight)) return $this->CurrentBlockHeight;
    $this->CurrentBlockHeight = $this->_call(['q', 'getblockcount']);
    return $this->CurrentBlockHeight;
  }

  public function _getNumberConfirmation($block){
    return $this->_getBlockHeight() - $block + 1;
  }

  public function _getTransactionInfos($tx){

    $transactionInfos = $this->_call(['rawtx', $tx]);
    $transactionInfos['confirmation'] = $this->_getNumberConfirmation($transactionInfos['block_height']);
    return $transactionInfos;
    //https://blockchain.info/rawtx/

  }

  public function _hexArrayToDecimal($arr){

  }


}

?>
