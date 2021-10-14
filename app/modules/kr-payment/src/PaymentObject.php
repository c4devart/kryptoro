<?php

class PaymentObject extends MySQL {

  private $App = null;

  public function __construct($App){

    $this->App = $App;

  }

  public function _getApp(){
    if(is_null($this->App)) throw new Exception("Error : App not given", 1);
    return $this->App;
  }

  protected function _getPaymentSettings($PaymentName){
    $r = parent::querySqlRequest("SELECT * FROM paygateway_krypto WHERE name_paygateway=:name_paygateway", ['name_paygateway' => $PaymentName]);
    if(count($r) == 0) throw new Exception("Error : Payment gateway not enabled (".$PaymentName.")", 1);
    $returnSettings = [];
    foreach ($r as $key => $value) {
      $returnSettings[$value['arg_paygateway']] = $value['val_paygateway'];
    }
    return $returnSettings;
  }

}

?>
