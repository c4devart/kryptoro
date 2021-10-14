<?php

class Statistics extends MySQL {

  private $startingDate = null;
  private $endingDate = null;

  public function __construct($startingDate = null, $endingDate = null){
    if(is_null($startingDate)){
      $startingDate = new DateTime('now');
      $startingDate->sub(new DateInterval('P29D'));
    }
    if(is_null($endingDate)) $endingDate = new DateTime('now');
    $startingDate->setTime(0,0,0);
    $endingDate->setTime(23,59,59);
    $this->startingDate = $startingDate;
    $this->endingDate = $endingDate;
  }

  public function _getStartingDate(){
    return $this->startingDate;
  }

  public function _getEndingDate(){
    return $this->endingDate;
  }

  private $ListDateCache = null;

  public function _generateListDate(){

    if(!is_null($this->ListDateCache)) return $this->ListDateCache;

    $list = [];
    $period = new DatePeriod(
         $this->_getStartingDate(),
         new DateInterval('P1D'),
         $this->_getEndingDate()
    );

    foreach ($period as $key => $value) {
      $list[] = $value->format('d/m/Y');
    }

    $this->ListDateCache = $list;

    return $list;

  }

  private $ListUserCache = null;

  public function _getListUser(){

    if(!is_null($this->ListUserCache)) return $this->ListUserCache;

    $r = parent::querySqlRequest("SELECT id_user, created_date_user FROM user_krypto WHERE created_date_user > :created_date_user_start AND created_date_user < :created_date_user_end",
                                [
                                  'created_date_user_start' => $this->_getStartingDate()->getTimestamp(),
                                  'created_date_user_end' => $this->_getEndingDate()->getTimestamp()
                                ]);

    $res = [];
    foreach ($r as $key => $value) {
      $DateTime = new DateTime();
      $DateTime->setTimestamp($value['created_date_user']);
      if(array_key_exists($DateTime->format('d/m/Y'), $res)) $res[$DateTime->format('d/m/Y')] = $res[$DateTime->format('d/m/Y')] + 1;
      else  $res[$DateTime->format('d/m/Y')] = 1;
    }

    $this->ListUserCache = $res;

    return $res;

  }

  private $ListDepositCache = null;

  public function _getListDeposit(){

    if(!is_null($this->ListDepositCache)) return $this->ListDepositCache;

    $r = parent::querySqlRequest("SELECT id_deposit_history, date_deposit_history FROM deposit_history_krypto WHERE date_deposit_history > :date_deposit_history_start AND date_deposit_history < :date_deposit_history_end",
                                [
                                  'date_deposit_history_start' => $this->_getStartingDate()->getTimestamp(),
                                  'date_deposit_history_end' => $this->_getEndingDate()->getTimestamp()
                                ]);

    $res = [];
    foreach ($r as $key => $value) {
      $DateTime = new DateTime();
      $DateTime->setTimestamp($value['date_deposit_history']);
      if(array_key_exists($DateTime->format('d/m/Y'), $res)) $res[$DateTime->format('d/m/Y')] = $res[$DateTime->format('d/m/Y')] + 1;
      else  $res[$DateTime->format('d/m/Y')] = 1;
    }

    $this->ListDepositCache = $res;

    return $res;

  }

  private $ListWithdrawCache = null;

  public function _getListWidthdraw(){

    if(!is_null($this->ListWithdrawCache)) return $this->ListWithdrawCache;

    $r = parent::querySqlRequest("SELECT id_widthdraw_history, date_widthdraw_history FROM widthdraw_history_krypto WHERE date_widthdraw_history > :date_deposit_history_start AND date_widthdraw_history < :date_deposit_history_end",
                                [
                                  'date_deposit_history_start' => $this->_getStartingDate()->getTimestamp(),
                                  'date_deposit_history_end' => $this->_getEndingDate()->getTimestamp()
                                ]);

    $res = [];
    foreach ($r as $key => $value) {
      $DateTime = new DateTime();
      $DateTime->setTimestamp($value['date_widthdraw_history']);
      if(array_key_exists($DateTime->format('d/m/Y'), $res)) $res[$DateTime->format('d/m/Y')] = $res[$DateTime->format('d/m/Y')] + 1;
      else  $res[$DateTime->format('d/m/Y')] = 1;
    }

    $this->ListWithdrawCache = $res;

    return $res;

  }

  private $ListIdentityCache = null;

  public function _getListIdentity(){

    if(!is_null($this->ListIdentityCache)) return $this->ListIdentityCache;

    $r = parent::querySqlRequest("SELECT id_identity, date_processed_identity FROM identity_krypto WHERE date_processed_identity > :date_deposit_history_start AND date_processed_identity < :date_deposit_history_end",
                                [
                                  'date_deposit_history_start' => $this->_getStartingDate()->getTimestamp(),
                                  'date_deposit_history_end' => $this->_getEndingDate()->getTimestamp()
                                ]);

    $res = [];
    foreach ($r as $key => $value) {
      $DateTime = new DateTime();
      $DateTime->setTimestamp($value['date_processed_identity']);
      if(array_key_exists($DateTime->format('d/m/Y'), $res)) $res[$DateTime->format('d/m/Y')] = $res[$DateTime->format('d/m/Y')] + 1;
      else  $res[$DateTime->format('d/m/Y')] = 1;
    }

    $this->ListIdentityCache = $res;

    return $res;

  }

  private $ListOrderCache = null;

  public function _getListOrderPassed(){

    if(!is_null($this->ListOrderCache)) return $this->ListOrderCache;

    $r = parent::querySqlRequest("SELECT id_internal_order, date_internal_order FROM internal_order_krypto WHERE date_internal_order > :date_deposit_history_start AND date_internal_order < :date_deposit_history_end",
                                [
                                  'date_deposit_history_start' => $this->_getStartingDate()->getTimestamp(),
                                  'date_deposit_history_end' => $this->_getEndingDate()->getTimestamp()
                                ]);

    $res = [];
    foreach ($r as $key => $value) {
      $DateTime = new DateTime();
      $DateTime->setTimestamp($value['date_internal_order']);
      if(array_key_exists($DateTime->format('d/m/Y'), $res)) $res[$DateTime->format('d/m/Y')] = $res[$DateTime->format('d/m/Y')] + 1;
      else  $res[$DateTime->format('d/m/Y')] = 1;
    }

    $this->ListOrderCache = $res;

    return $res;

  }

  private $ListSubscriptionCache = null;

  public function _getListSubscription(){

    if(!is_null($this->ListSubscriptionCache)) return $this->ListSubscriptionCache;

    $r = parent::querySqlRequest("SELECT id_charges, date_charges FROM charges_krypto WHERE date_charges > :date_deposit_history_start AND date_charges < :date_deposit_history_end",
                                [
                                  'date_deposit_history_start' => $this->_getStartingDate()->getTimestamp(),
                                  'date_deposit_history_end' => $this->_getEndingDate()->getTimestamp()
                                ]);

    $res = [];
    foreach ($r as $key => $value) {
      $DateTime = new DateTime();
      $DateTime->setTimestamp($value['date_charges']);
      if(array_key_exists($DateTime->format('d/m/Y'), $res)) $res[$DateTime->format('d/m/Y')] = $res[$DateTime->format('d/m/Y')] + 1;
      else  $res[$DateTime->format('d/m/Y')] = 1;
    }

    $this->ListSubscriptionCache = $res;

    return $res;

  }


  public function _generateDataSet($set){

    $res = [];
    foreach ($this->_generateListDate() as $date) {
      if(array_key_exists($date, $set)) $res[$date] = $set[$date];
      else $res[$date] = 0;
    }
    return $res;

  }

  public function _getSumDateSet($set){

    return array_sum($set);

  }

  public function _getFeesList($App){

    $currency = [];
    $balanceUser = [];

    $r = parent::querySqlRequest("SELECT * FROM deposit_history_krypto WHERE date_deposit_history > :date_deposit_history_start AND date_deposit_history < :date_deposit_history_end",
                                [
                                  'date_deposit_history_start' => $this->_getStartingDate()->getTimestamp(),
                                  'date_deposit_history_end' => $this->_getEndingDate()->getTimestamp()
                                ]);

    foreach ($r as $key => $value) {
      try {
        if(!array_key_exists($value['id_user'], $balanceUser)) $balanceUser[$value['id_user']] = new Balance(new User($value['id_user']), $App, 'real');
        $BalanceUser = $balanceUser[$value['id_user']];
        if($BalanceUser->_getBalanceID() != $value['balance_deposit_history']) continue;
        $currency = $this->_addFeesList($currency, $value['currency_deposit_history'], $value['amount_deposit_history'], 0, 0, $value['fees_deposit_history']);
      } catch (\Exception $e) {

      }
    }

    $r = parent::querySqlRequest("SELECT * FROM internal_order_krypto WHERE date_internal_order > :date_deposit_history_start AND date_internal_order < :date_deposit_history_end",
                                [
                                  'date_deposit_history_start' => $this->_getStartingDate()->getTimestamp(),
                                  'date_deposit_history_end' => $this->_getEndingDate()->getTimestamp()
                                ]);

    foreach ($r as $key => $value) {
      try {
        if(!array_key_exists($value['id_user'], $balanceUser)) $balanceUser[$value['id_user']] = new Balance(new User($value['id_user']), $App, 'real');
        $BalanceUser = $balanceUser[$value['id_user']];
        if($BalanceUser->_getBalanceID() != $value['id_balance']) continue;
        $currency = $this->_addFeesList($currency, $value['to_internal_order'], 0, $value['amount_internal_order'], 0, $value['fees_internal_order']);
      } catch (\Exception $e) {

      }

    }

    $r = parent::querySqlRequest("SELECT * FROM widthdraw_history_krypto WHERE date_widthdraw_history > :date_deposit_history_start AND date_widthdraw_history < :date_deposit_history_end",
                                [
                                  'date_deposit_history_start' => $this->_getStartingDate()->getTimestamp(),
                                  'date_deposit_history_end' => $this->_getEndingDate()->getTimestamp()
                                ]);

    foreach ($r as $key => $value) {
      try {
        if(!array_key_exists($value['id_user'], $balanceUser)) $balanceUser[$value['id_user']] = new Balance(new User($value['id_user']), $App, 'real');
        $BalanceUser = $balanceUser[$value['id_user']];
        if($BalanceUser->_getBalanceID() != $value['id_balance']) continue;
        $currency = $this->_addFeesList($currency, $value['symbol_widthdraw_history'], 0, 0, $value['amount_widthdraw_history'], $value['fees_widthdraw_history']);
      } catch (\Exception $e) {

      }

    }

    return $currency;

  }

  public function _addFeesList($tabl, $symbol, $total_deposit = 0, $total_trade = 0, $totalwithdraw = 0, $totalfees = 0){
    if(!array_key_exists($symbol, $tabl)){
      $tabl[$symbol] = [
        'total_trade' => floatval($total_trade),
        'total_deposit' => floatval($total_deposit),
        'total_withdraw' =>  floatval($totalwithdraw),
        'fees' => floatval($totalfees)
      ];
    } else {
      $tabl[$symbol]['total_trade'] += floatval($total_trade);
      $tabl[$symbol]['total_deposit'] += floatval($total_deposit);
      $tabl[$symbol]['total_withdraw'] += floatval($totalwithdraw);
      $tabl[$symbol]['fees'] += floatval($totalfees);
    }
    return $tabl;
  }

}

?>
