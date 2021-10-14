<?php

class Etherblock extends MySQL {

  private $type = "ether";

  private $App = null;


  public function __construct($App, $User = null){
    $this->App = $App;
  }

  public function _getApp(){
    return $this->App;
  }

  private function _getApiKey(){
    return "451886GWK6728IW8YQVKCFYEEHCSFRI2EI";
  }

  public function _call($args){

    $argsString = "";
    $n = 0;
    foreach ($args as $key => $value) {
      $argsString .= ($n == 0 ? "?" : "&").$key."=".$value;
      $n++;
    }
    $ch =  curl_init("http://api.etherscan.io/api".$argsString);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_ENCODING,  '');
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));

    $s = json_decode(curl_exec($ch), true);

    curl_close($ch);

    if(array_key_exists('status', $s) && $s['status'] != "1") throw new Exception("Error Etherblock : ".$s['message'], 1);

    if(array_key_exists('result', $s)) return $s['result'];
    return $s;
    //https://api.etherscan.io/api?module=logs&action=getLogs&fromBlock=0&toBlock=latest&address=0x33990122638b9132ca29c723bdf037f1a891a70c

  }

  public function _getHistoryTransaction($address = null, $symbol = null){
    $transactionList = $this->_call([
      'module' => 'account',
      'action' => 'txlist',
      'startblock' => 0,
      'endblock' => '99999999',
      "sort" => "desc",
      'address' => $address,
      'apiKey' => $this->_getApiKey()
    ]);

    $TransactionFormated = [];
    foreach ($transactionList as $key => $value) {
      if($value['to'] != $address) continue;
      $TransactionFormated[$value['hash']] = [
        'date' => $value['timeStamp'],
        'hash' => $value['hash'],
        'from' => $value['from'],
        'to' => $value['to'],
        'value' => $value['value'] / 1000000000000000000,
        'symbol' => $symbol,
        'confirmations' => $value['confirmations']
      ];
    }

    return $TransactionFormated;

  }

  public function _getTransactionInfos($tx){

    $transactionInfos = $this->_call([
      'module' => 'proxy',
      'action' => 'eth_getTransactionByHash',
      'txhash' => $tx,
      'apiKey' => $this->_getApiKey()
    ]);
    $transactionInfos['sub_infos'] = $this->_getTransactionInfosSub($transactionInfos['to'], hexdec($transactionInfos['blockNumber']));
    return $transactionInfos;
    return $this->_hexArrayToDecimal($transactionInfos);

  }

  public function _getBlockInfos($block){

    //https://api.etherscan.io/api?module=proxy&action=eth_getBlockByNumber&tag=0x10d4f&boolean=true&apikey=YourApiKeyToken
    $transactionInfos = $this->_call([
      'module' => 'proxy',
      'action' => 'eth_getBlockByNumber',
      "boolean" => "true",
      'tag' => $block,
      'apiKey' => $this->_getApiKey()
    ]);

    return $this->_hexArrayToDecimal($transactionInfos);

  }

  public function _hexArrayToDecimal($arr){
    $res = [];
    foreach ($arr as $key => $value) {
      if(is_array($value)){
        $res[$key] = $this->_hexArrayToDecimal($value);
      } else {
        $res[$key] = number_format(hexdec($value), 10, '.', '');
      }

    }
    return $res;
  }

  public function _getTransactionInfosSub($address, $block){

    $transactionInfos = $this->_call([
      'module' => 'account',
      'action' => 'txlist',
      "address" => $address,
      'startblock' => $block,
      'endblock' => $block,
      'sort' => 'desc',
      'apiKey' => $this->_getApiKey()
    ]);

    if(count($transactionInfos) == 0) return [];
    return $transactionInfos[0];

  }


}

?>
