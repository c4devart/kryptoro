<?php

class Holding extends MySQL {

  private $User = null;
  private $HoldingID = null;
  private $HoldingData = null;

  public function __construct($User, $HoldingID = null){
    $this->User = $User;
    $this->HoldingID = $HoldingID;
    if(!is_null($this->HoldingID)) $this->_loadHoldingData();
  }

  public function _getUser(){ return $this->User; }

  public function _getHoldingID(){ return $this->HoldingID; }

  public function _loadHoldingData(){
    $r = parent::querySqlRequest("SELECT * FROM holding_krypto WHERE id_holding=:id_holding AND id_user=:id_user",
                                  [
                                    'id_holding' => $this->_getHoldingID(),
                                    'id_user' => $this->_getUser()->_getUserID()
                                  ]);
    if(count($r) == 0) throw new Exception("Error : Fail to load holding (".$this->_getHoldingID().")", 1);
    $this->HoldingData = $r[0];

  }

  private function _getHoldingDataByKey($key){

    if(!array_key_exists($key, $this->HoldingData)) throw new Exception("Error : Holding data not exist for key = ".$key, 1);
    if(empty($this->HoldingData[$key]) || strlen($this->HoldingData[$key]) == 0) return null;
    return $this->HoldingData[$key];
  }

  public function _getDate(){
    $d = new DateTime();
    $d->setTimestamp($this->_getHoldingDataByKey('date_holding'));
    return $d;
  }

  public function _getQuantity(){
    return floatval($this->_getHoldingDataByKey('value_holding'));
  }

  public function _getPriceUnit(){
    return floatval($this->_getHoldingDataByKey('price_holding'));
  }

  public function _getType(){
    return $this->_getHoldingDataByKey('type_holding');
  }

  public function _getListHolding($symbol){
    $r = [];
    foreach (parent::querySqlRequest("SELECT * FROM holding_krypto WHERE symbol_holding=:symbol_holding AND id_user=:id_user",
                                      ['symbol_holding' => $symbol, 'id_user' => $this->_getUser()->_getUserID()]) as $key => $holding) {
      $r[] = new Holding($this->_getUser(), $holding['id_holding']);
    }
    return $r;
  }

  public function _getHoldingSize($symbol){
    $size = 0.0;
    foreach ($this->_getListHolding($symbol) as $HoldingItem) {
      if($HoldingItem->_getType() != "buy") $size -= $HoldingItem->_getQuantity();
      else $size += $HoldingItem->_getQuantity();
    }
    return $size;
  }

  public function _getProfit($symbol, $totalwallet){

    $totalBuy = 0;
    foreach ($this->_getListHolding($symbol) as $HoldingItem) {
      if($HoldingItem->_getType() != "buy") $totalBuy -= $HoldingItem->_getQuantity() * $HoldingItem->_getPriceUnit();
      else $totalBuy += $HoldingItem->_getQuantity() * $HoldingItem->_getPriceUnit();

    }

    return $totalwallet - $totalBuy;

  }

  public function _getHoldingBuyValue($symbol){
    $totalBuy = 0;
    foreach ($this->_getListHolding($symbol) as $HoldingItem) {
      if($HoldingItem->_getType() != "buy") $totalBuy -= $HoldingItem->_getQuantity() * $HoldingItem->_getPriceUnit();
      else $totalBuy += $HoldingItem->_getQuantity() * $HoldingItem->_getPriceUnit();
    }
    return $totalBuy;
  }

}

?>
