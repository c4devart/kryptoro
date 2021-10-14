<?php

class DepositAddress extends MySQL {

  private $Symbol = null;
  private $App = null;
  private $Address = null;
  private $VerificationsNb = null;

  private $BlockExplorer = null;

  public function __construct($App, $symbol, $address, $verification_count = null, $BlockExplorer = null){

    $this->App = $App;
    $this->Symbol = $symbol;
    $this->Address = $address;
    $this->VerificationsNb = $verification_count;

    if(!is_null($BlockExplorer)){
      $this->BlockExplorer = new BlockExplorer($App, null);
    } else {
      $this->BlockExplorer = $BlockExplorer;
    }

  }

  public function _getSymbol(){
    return $this->Symbol;
  }

  public function _getNbVerification(){
    return $this->VerificationsNb;
  }

  public function _getApp(){
    return $this->App;
  }

  public function _getAddress(){
    return $this->Address;
  }

  public function _getBlockExplorer(){
    return $this->BlockExplorer;
  }

  public function _getLinkedBlockExplorer(){
    return $this->_getBlockExplorer()->_getAssociateExplorerSymbol($this->_getSymbol());
  }

  public function _getTransactionHistory(){
    if(is_null($this->_getLinkedBlockExplorer())) return [];
    var_dump($this->_getAddress());
    return $this->_getLinkedBlockExplorer()->_getHistoryTransaction($this->_getAddress(), $this->_getSymbol());
  }

}

?>
