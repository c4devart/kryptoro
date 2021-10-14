<?php

class Banktransfert extends MySQL {

  private $User = null;

  private $App = null;

  public $StatusBank = [
    0 => 'Pending',
    1 => 'Proof received',
    2 => 'Verified',
    3 => 'Canceled'
  ];

  private $BankTransfertSuffixNumber = 6;

  public function __construct($User = null, $App = null){

    $this->User = $User;
    $this->App = $App;

  }

  public function _getUser(){
    if(is_null($this->User)) throw new Exception("Error Bank Transfert : User not defined", 1);
    return $this->User;
  }

  public function _setUser($User = null){
    $this->User = $User;
  }

  public function _getApp(){
    if(is_null($this->App)) throw new Exception("Error Bank Transfert : App is not defined", 1);
    return $this->App;
  }

  public function _NewBankTransfertAllowed(){
    $r = parent::querySqlRequest("SELECT * FROM banktransfert_krypto WHERE id_user=:id_user AND status_banktransfert=:status_banktransfert",
                                [
                                  'id_user' => $this->_getUser()->_getUserID(),
                                  'status_banktransfert' => 0
                                ]);

    return count($r) < intval($this->_getApp()->_getBankMaxTransfert());
  }

  public function _generateNewBankTransferRef(){

    $ranNumber = "";
    for ($i=0; $i < $this->BankTransfertSuffixNumber; $i++) {
      $ranNumber .= rand(0, 9);
    }

    $ref = $this->_getApp()->_getBankTransfertPrefix(). "-".$this->_getUser()->_getUserID()."-".$ranNumber;

    $r = parent::querySqlRequest("SELECT * FROM banktransfert_krypto WHERE uref_banktransfert=:uref_banktransfert",
                                [
                                  'uref_banktransfert' => $ref
                                ]);

    if(count($r) > 0) return $this->_generateNewBankTransferRef();
    return $ref;

  }

  public function _generateNewBankTransfer(){

    if(!$this->_NewBankTransfertAllowed()) throw new Exception("You can only have ".$this->_getApp()->_getBankMaxTransfert()." bank transfert in pending at the same time", 1);

    $ReferenceBankTransfert = $this->_generateNewBankTransferRef();

    $r = parent::execSqlRequest("INSERT INTO banktransfert_krypto (uref_banktransfert, id_user, created_date_banktransfert, status_banktransfert, update_date_banktransfert, amount_banktransfert)
                                VALUES (:uref_banktransfert, :id_user, :created_date_banktransfert, :status_banktransfert, :update_date_banktransfert, :amount_banktransfert)",
                                [
                                  'uref_banktransfert' => $ReferenceBankTransfert,
                                  'id_user' => $this->_getUser()->_getUserID(),
                                  'created_date_banktransfert' => time(),
                                  'status_banktransfert' => 0,
                                  'update_date_banktransfert' => time(),
                                  'amount_banktransfert' => ''
                                ]);

    if(!$r) throw new Exception("Error Bank Transfert : Fail to init new bank transfert", 1);

    return [
      'ref' => $ReferenceBankTransfert,
      'infos' => $this->_getBankTransfertByRef($ReferenceBankTransfert)
    ];

  }

  public function _getBankTransfertByRef($ref){

    $r = parent::querySqlRequest("SELECT * FROM banktransfert_krypto WHERE uref_banktransfert=:uref_banktransfert AND id_user=:id_user",
                                [
                                  'uref_banktransfert' => $ref,
                                  'id_user' => $this->_getUser()->_getUserID()
                                ]);

    if(count($r) == 0) throw new Exception("Error : Fail to get banktransfert", 1);

    return $r[0];
  }

  public function _getListBankTransfert($status = 'ALL', $user = null){

    if(!is_null($user)){
      if($status == 'ALL'){
        return parent::querySqlRequest("SELECT * FROM banktransfert_krypto WHERE id_user=:id_user ORDER BY status_banktransfert, created_date_banktransfert DESC",
                                      [
                                        'id_user' => $user->_getUserID()
                                      ]);
      } else {
        return parent::querySqlRequest("SELECT * FROM banktransfert_krypto WHERE status_banktransfert=:status_banktransfert AND id_user=:id_user ORDER BY status_banktransfert, created_date_banktransfert DESC",
                                      [
                                        'status_banktransfert' => $status,
                                        'id_user' => $user->_getUserID()
                                      ]);
      }
    }

    if($status == 'ALL'){
      return parent::querySqlRequest("SELECT * FROM banktransfert_krypto ORDER BY status_banktransfert, created_date_banktransfert");
    } else {
      return parent::querySqlRequest("SELECT * FROM banktransfert_krypto WHERE status_banktransfert=:status_banktransfert ORDER BY status_banktransfert, created_date_banktransfert",
                                    [
                                      'status_banktransfert' => $status
                                    ]);
    }

  }

  public function _getInfosBankTransfert($id_banktransfert){

    $r = parent::querySqlRequest("SELECT * FROM banktransfert_krypto WHERE id_banktransfert=:id_banktransfert", ['id_banktransfert' => $id_banktransfert]);
    if(count($r) == 0) throw new Exception("Error : Fail to find bank transfert (".$id_banktransfert.")", 1);
    return $r[0];


  }

  public function _validateBankTransfert($id_transfert, $date, $bankref, $amount, $currency, $bankaccount, $wallet_receive, $amount_wallet){
    $infosTransfert = $this->_getInfosBankTransfert($id_transfert);

    $Datetime = new DateTime($date);

    $Balance = new Balance($this->_getUser(), $this->_getApp(), 'real');

    $r = parent::execSqlRequest("UPDATE banktransfert_krypto SET bankref_banktransfert=:bankref_banktransfert,
                                                                 currency_banktransfert=:currency_banktransfert,
                                                                 update_date_banktransfert=:update_date_banktransfert,
                                                                 status_banktransfert=:status_banktransfert,
                                                                 amount_banktransfert=:amount_banktransfert,
                                                                 bankaccount_banktransfert=:bankaccount_banktransfert,
                                                                 wallet_receive_banktransfert=:wallet_receive_banktransfert,
                                                                 amount_wallet_received_banktransfert=:amount_wallet_received_banktransfert,
                                                                 fees_wallet_banktransfert=:fees_wallet_banktransfert
                                                                 WHERE id_banktransfert=:id_banktransfert",
                                                                 [
                                                                   'bankref_banktransfert' => $bankref,
                                                                   'currency_banktransfert' => $currency,
                                                                   'update_date_banktransfert' => $Datetime->getTimestamp(),
                                                                   'status_banktransfert' => 2,
                                                                   'amount_banktransfert' => $amount,
                                                                   'id_banktransfert' => $id_transfert,
                                                                   'bankaccount_banktransfert' => $bankaccount,
                                                                   'wallet_receive_banktransfert' => $wallet_receive,
                                                                   'amount_wallet_received_banktransfert' => $amount_wallet,
                                                                   'fees_wallet_banktransfert' => $amount_wallet * (($this->_getApp()->_getFeesDeposit() + $Balance->_getPaymentGatewayFee('banktransfert')) / 100)
                                                                 ]);

    if(!$r) throw new Exception("Error : Fail to change bank transfert informations", 1);


    $procssed = true;
    $this->_processBankTransfert($id_transfert, false);

    $NotificationCenter = new NotificationCenter(new User($infosTransfert['id_user']));
    $NotificationCenter->_sendNotification('Bank transfert #'.$infosTransfert['uref_banktransfert'], 'Your bank transfert has been validate '.($procssed ? ' and processed' : '').'');


  }

  public function _processBankTransfert($id_transfert, $notification = true){


    $infosTransfert = $this->_getInfosBankTransfert($id_transfert);

    if($infosTransfert['proecessed_banktransfert'] == "1") throw new Exception("Error : Payment already processed", 1);

    $BalanceUser = new Balance(new User($infosTransfert['id_user']), $this->_getApp(), 'real');
    $BalanceUser->_addDeposit($infosTransfert['amount_wallet_received_banktransfert'],
                              'banktransfert',
                              $infosTransfert['uref_banktransfert'].' - Deposit '.$infosTransfert['amount_banktransfert'].' '.$infosTransfert['currency_banktransfert'],
                              $infosTransfert['wallet_receive_banktransfert'],
                              $infosTransfert['uref_banktransfert'], 2, $infosTransfert['wallet_receive_banktransfert']);

    $r = parent::execSqlRequest("UPDATE banktransfert_krypto SET proecessed_banktransfert=:proecessed_banktransfert WHERE id_banktransfert=:id_banktransfert",
                                ['id_banktransfert' => $id_transfert, 'proecessed_banktransfert' => 1]);

    if(!$r) throw new Exception("Error : Fail to change bank transfert as processed", 1);

    if($notification){
      $NotificationCenter = new NotificationCenter(new User($infosTransfert['id_user']));
      $NotificationCenter->_sendNotification('Bank transfert #'.$infosTransfert['uref_banktransfert'], 'Your bank transfert has been processed');
    }
  }

  public function _getListProof($id_banktransfert){

    $r = parent::querySqlRequest("SELECT * FROM banktransfert_proof_krypto WHERE id_banktransfert=:id_banktransfert",
                                [
                                  'id_banktransfert' => $id_banktransfert
                                ]);

    return $r;

  }

  public function _addProof($id_banktransfert, $file){

    if(!file_exists($_SERVER['DOCUMENT_ROOT'].FILE_PATH.'/public/bank-proof')) mkdir($_SERVER['DOCUMENT_ROOT'].FILE_PATH.'/public/bank-proof', 0777);
    if(!file_exists($_SERVER['DOCUMENT_ROOT'].FILE_PATH.'/public/bank-proof/'.App::encrypt_decrypt('encrypt', $id_banktransfert))) mkdir($_SERVER['DOCUMENT_ROOT'].FILE_PATH.'/public/bank-proof/'.App::encrypt_decrypt('encrypt', $id_banktransfert), 0777);

    $fileName = App::encrypt_decrypt('encrypt', uniqid()).'-'.$file['name'];

    if(!App::_getFileExtensionAllowed($file)) throw new Exception("Error : File not accepted", 1);


    move_uploaded_file($file['tmp_name'], $_SERVER['DOCUMENT_ROOT'].FILE_PATH.'/public/bank-proof/'.App::encrypt_decrypt('encrypt', $id_banktransfert).'/'.$fileName);

    $r = parent::execSqlRequest("INSERT INTO banktransfert_proof_krypto (id_banktransfert, id_user, url_banktransfert_proof, date_banktransfert_proof)
                                VALUES (:id_banktransfert, :id_user, :url_banktransfert_proof, :date_banktransfert_proof)",
                                [
                                  'id_banktransfert' => $id_banktransfert,
                                  'id_user' => $this->_getUser()->_getUserID(),
                                  'url_banktransfert_proof' => '/public/bank-proof/'.App::encrypt_decrypt('encrypt', $id_banktransfert).'/'.$fileName,
                                  'date_banktransfert_proof' => time()
                                ]);

    if(!$r) throw new Exception("Error SQL : Fail to add bank proof", 1);

    $this->_updateBankTransfertStatus($id_banktransfert, 1);


  }

  public function _updateBankTransfertStatus($id_banktransfert, $new_status){

    $r = parent::execSqlRequest("UPDATE banktransfert_krypto SET status_banktransfert=:status_banktransfert WHERE id_banktransfert=:id_banktransfert",
                                [
                                  'status_banktransfert' => $new_status,
                                  'id_banktransfert' => $id_banktransfert
                                ]);



    if(!$r) throw new Exception("Error : Fail to update status", 1);
    return true;

  }

  public function _removeProof($id_banktransfert, $id_proof){
    if(!is_numeric($id_proof)) return false;

    $s = parent::querySqlRequest("SELECT * FROM banktransfert_proof_krypto WHERE id_banktransfert=:id_banktransfert AND id_banktransfert_proof=:id_banktransfert_proof AND id_user=:id_user",
                                [
                                  'id_banktransfert' => $id_banktransfert,
                                  'id_banktransfert_proof' => $id_proof,
                                  'id_user' => $this->_getUser()->_getUserID()
                                ]);

    if(count($s) == 0) return false;
    $r = parent::execSqlRequest("DELETE FROM banktransfert_proof_krypto WHERE id_banktransfert=:id_banktransfert AND id_banktransfert_proof=:id_banktransfert_proof AND id_user=:id_user",
                                [
                                  'id_banktransfert' => $id_banktransfert,
                                  'id_banktransfert_proof' => $id_proof,
                                  'id_user' => $this->_getUser()->_getUserID()
                                ]);

    if(!$r) throw new Exception("Error SQL : Fail to remove bank transfert proof", 1);

    // Delete file
    unlink($_SERVER['DOCUMENT_ROOT'].FILE_PATH.$s[0]['url_banktransfert_proof']);


  }

  public function _cancelBankTransfert($id_banktransfert){

    $this->_updateBankTransfertStatus($id_banktransfert, '3');
  }

  public function _assignBankAccount($id_banktransfert, $bank_account){

    $r = parent::execSqlRequest("UPDATE banktransfert_krypto SET bankaccount_banktransfert=:bankaccount_banktransfert WHERE id_banktransfert=:id_banktransfert",
                                [
                                  'bankaccount_banktransfert' => $bank_account,
                                  'id_banktransfert' => $id_banktransfert
                                ]);

    if(!$r) throw new Exception("Error : Fail to update bank account assigned to this bank transfert", 1);


  }

  public function _addNewAccount($bank_name, $bank_currency = null, $bank_iban, $bank_bic, $bank_address, $bank_owner){

    $r = parent::execSqlRequest("INSERT INTO banktransfert_accountavailable_krypto (bank_name__banktransfert_accountavailable,
                                                                                    currency_banktransfert_accountavailable,
                                                                                    iban_banktransfert_accountavailable,
                                                                                    bic_banktransfert_accountavailable,
                                                                                    address_banktransfert_accountavailable,
                                                                                    accountowner_banktransfert_accountavailable) VALUES
                                                                                    (:bank_name__banktransfert_accountavailable,
                                                                                    :currency_banktransfert_accountavailable,
                                                                                    :iban_banktransfert_accountavailable,
                                                                                    :bic_banktransfert_accountavailable,
                                                                                    :address_banktransfert_accountavailable,
                                                                                    :accountowner_banktransfert_accountavailable)",
                                                                                    [
                                                                                      'bank_name__banktransfert_accountavailable' => $bank_name,
                                                                                      'currency_banktransfert_accountavailable' => $bank_currency,
                                                                                      'iban_banktransfert_accountavailable' => $bank_iban,
                                                                                      'bic_banktransfert_accountavailable' => $bank_bic,
                                                                                      'address_banktransfert_accountavailable' => $bank_address,
                                                                                      'accountowner_banktransfert_accountavailable' => $bank_owner
                                                                                    ]);
    if(!$r) throw new Exception("Error SQL : Fail to add bank account", 1);

  }

  public function _deleteAccount($id_account){

    $r = parent::execSqlREquest("DELETE FROM banktransfert_accountavailable_krypto WHERE id_banktransfert_accountavailable=:id_banktransfert_accountavailable",
                                [
                                  'id_banktransfert_accountavailable' => $id_account
                                ]);

    if(!$r) throw new Exception("Error SQL : Fail to delete bank account", 1);


  }

}

?>
