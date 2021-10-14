<?php

class Blockonomics extends MySQL {

  private $App = null;

  public function __construct($App = null){
    $this->App = $App;
  }

  public function _getApp(){
    if(is_null($this->App)) throw new Exception("Error Blockonomics : App is null", 1);
    return $this->App;
  }

  public function _getApiCall($api_access = "new_address", $data = ''){
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => 'https://www.blockonomics.co/api/'.$api_access,
        CURLOPT_HTTPHEADER => array(
          'Authorization: Bearer '.$this->_getApp()->_getBlockonomicsApiKey(),
        ),
    ));

    if(!is_null($data)) curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

    $resp = curl_exec($curl);
    curl_close($curl);

    return json_decode($resp);
  }



  public function _generateNewPaymentAddress($User){


    $infosWalletCurrent = parent::querySqlRequest("SELECT * FROM blockonomics_address_krypto WHERE id_user=:id_user", ['id_user' => $User->_getUserID()]);

    if(count($infosWalletCurrent) > 0 && $this->_getWalletExist($infosWalletCurrent[0]['address_blockonomics_trs'])) return $infosWalletCurrent[0]['address_blockonomics_trs'];

    $object = $this->_getApiCall("new_address");

    if(is_null($object) || !property_exists($object, 'address')){
      if(property_exists($object, 'status') && $object->status == 500){
        if(property_exists($object, 'message')) throw new Exception("Blockonomics error : ".$object->message, 1);
      }
      throw new Exception("Error : Fail to generate new address", 1);
    }


    if(count($infosWalletCurrent) > 0){
      $r = parent::execSqlRequest("DELETE FROM blockonomics_address_krypto WHERE id_user=:id_user", ['id_user' => $User->_getUserID()]);
    }

    $r = parent::execSqlRequest("INSERT INTO blockonomics_address_krypto (id_user, address_blockonomics_trs, date_blockonomics_trs)
                                  VALUES (:id_user, :address_blockonomics_trs, :date_blockonomics_trs)",
                                  [
                                    'id_user' => $User->_getUserID(),
                                    'address_blockonomics_trs' => $object->address,
                                    'date_blockonomics_trs' => time()
                                  ]);

    if(!$r){
      error_log("Error : Fail to create blockonomics insertion in SQL");
      throw new Exception("Error : Fail to create blockonomics insertion in SQL", 1);

    }

    return $object->address;

  }

  public function _generateQrcodePicture($address){

    if(file_exists($_SERVER['DOCUMENT_ROOT'].FILE_PATH.'/public/qrcode/'.$address.'.png')) return $address;

    if(!file_exists($_SERVER['DOCUMENT_ROOT'].FILE_PATH.'/public/qrcode')) mkdir($_SERVER['DOCUMENT_ROOT'].FILE_PATH.'/public/qrcode', 0777);

    \PHPQRCode\QRcode::png("bitcoin:".$address, $_SERVER['DOCUMENT_ROOT'].FILE_PATH.'/public/qrcode/'.$address.'.png', "L", 15, 0);

    return $address;

  }

  public function _getWalletExist($walletAddr){
    $details = $this->_getApiCall('balance', '{"addr":"'.$walletAddr.'"}');
    if(property_exists($details, 'status')) return false;
    return true;
  }

  public function _statusStrToInt($status){
    if(strtoupper($status) == "CONFIRMED") return 2;
    if(strtoupper($status) == "PARTIALLY CONFIRMED") return 1;
    return 0;
  }

  public function _getUserByAddress($address){
    $r = parent::querySqlRequest("SELECT * FROM blockonomics_address_krypto WHERE address_blockonomics_trs=:address_blockonomics_trs",
                                [
                                  'address_blockonomics_trs' => $address
                                ]);
    if(count($r) == 0) throw new Exception("Error : Fail to get user (address : ".$address.")", 1);
    return new User($r[0]['id_user']);
  }


  public function _calcAmountPayment($PaymentDetail){
    $amount = 0;
    return $this->_convertSatoshiToStandard($PaymentDetail->vin[0]->value);
    foreach ($PaymentDetail->vout as $key => $value) {
      $amount += $value->value;
    }
    return $amount;
  }

  public function _validPayment($txtid, $addr){
    $PaymentDetail = $this->_getTransactionDetails($txtid, $addr);
    $User = $this->_getUserByAddress($PaymentDetail->vout[0]->address);
    $this->_setTransaction($User, $txtid, $addr, $this->_statusStrToInt($PaymentDetail->status));
    if($this->_statusStrToInt($PaymentDetail->status) == 2) {



      $fees = $this->_calcAmountPayment($PaymentDetail) * ($this->_getApp()->_getFeesDeposit() / 100);

      $Balance = new Balance($User, $this->_getApp(), 'real');

      if($Balance->_depositAlreadyDone($txtid)) throw new Exception("Error : Process already done", 1);

      $Balance->_addDeposit($this->_calcAmountPayment($PaymentDetail), 'blockonomics', 'Deposit '.$this->_calcAmountPayment($PaymentDetail).' BTC ('.number_format($fees, 8).' BTC Fees)', 'BTC', $txtid);

    }
  }

  public function _setTransaction($User, $txtid, $addr, $status = 0){

    $r = parent::querySqlRequest("SELECT * FROM blockonomics_transactions_krypto WHERE address_blockonomics_transactions=:address_blockonomics_transactions AND txid_blockonomics_transactions=:txid_blockonomics_transactions",
                                [
                                  'address_blockonomics_transactions' => $addr,
                                  'txid_blockonomics_transactions' => $txtid
                                ]);

    if(count($r) == 0){
      $r = parent::execSqlRequest("INSERT INTO blockonomics_transactions_krypto (address_blockonomics_transactions, id_user, txid_blockonomics_transactions, date_blockonomics_transactions, status_blockonomics_transactions)
                                  VALUES (:address_blockonomics_transactions, :id_user, :txid_blockonomics_transactions, :date_blockonomics_transactions, :status_blockonomics_transactions)",
                                  [
                                    'address_blockonomics_transactions' => $addr,
                                    'id_user' => $User->_getUserID(),
                                    'txid_blockonomics_transactions' => $txtid,
                                    'date_blockonomics_transactions' => time(),
                                    'status_blockonomics_transactions' => $status
                                  ]);
    } else {

      $r = parent::execSqlRequest("UPDATE blockonomics_transactions_krypto SET status_blockonomics_transactions=:status_blockonomics_transactions WHERE address_blockonomics_transactions=:address_blockonomics_transactions AND txid_blockonomics_transactions=:txid_blockonomics_transactions AND id_user=:id_user",
                                  [
                                    'address_blockonomics_transactions' => $addr,
                                    'id_user' => $User->_getUserID(),
                                    'txid_blockonomics_transactions' => $txtid,
                                    'status_blockonomics_transactions' => $status
                                  ]);

    }

    if(!$r){
      error_log("Error : Fail to update / insert transaction (Blockonomics)");
      throw new Exception("Error : Fail to update / insert transaction (Blockonomics)", 1);
    }

    return true;

  }

  public function _getTransactionDetails($txtid, $addr = null){

    //if(is_null($addr)) $addr = $this->_generateNewPaymentAddress($User);

    $details = $this->_getApiCall('tx_detail?txid='.$txtid);

    if(property_exists($details, 'status') && $details->status == 500) throw new Exception("Error : Payment not found", 1);

    return $details;

  }

  public function _convertSatoshiToStandard($value){
    return $value / 100000000;
  }


}

?>
