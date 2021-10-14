<?php

class BlockExplorer extends MySQL {

  private $App = null;

  private $Explorer = [];

  public function __construct($App, $User = null){
    $this->App = $App;
    $this->Explorer = [
      'ETH' => new Etherblock($App, $User),
      'BTC' => new BitcoinExplorer($App, $User),
      'LTC' => new LitecoinExplorer($App, $User)
    ];
  }

  public function _getApp(){
    return $this->App;
  }

  private $depositAddress = null;
  public function _getDepositAddress(){
    if(!is_null($this->depositAddress)) return $this->depositAddress;
    $this->depositAddress = [];
    foreach (parent::querySqlRequest("SELECT * FROM block_exp_address_list_krypto") as $key => $value) {
      $this->depositAddress[$value['symbol__block_exp_address_list']] = new DepositAddress($this->_getApp(), $value['symbol__block_exp_address_list'], $value['address__block_exp_address_list'], $value['nb_confirm__block_exp_address_list'], $this);
    }
    return $this->depositAddress;
  }

  public function _getAssociateExplorerSymbol($symbol){
    if(array_key_exists($symbol, $this->Explorer)) return $this->Explorer[$symbol];
  }

  public function _getAllTransaction(){
    $transactionList = [];
    foreach ($this->_getDepositAddress() as $key => $DepAddress) {
      if($DepAddress->_getSymbol() != "ETH") continue;
      $transactionList = array_merge($transactionList, $DepAddress->_getTransactionHistory());
    }
    return $transactionList;
  }

  public function _checkDoneTransaction($transaction_list){

    if(!$this->_getApp()->_getTradingEnableRealAccount()){
      error_log('Fail to fetch address wallet, real account is disabled');
      return true;
    }

    $Addresslist = $this->_getDepositAddress();
    $BalanceUserReal = [];

    foreach ($transaction_list as $key => $transaction) {
      $infosTransaction = parent::querySqlRequest("SELECT * FROM block_exp_tx_krypto WHERE tx_block_exp_tx=:tx_block_exp_tx",
                                  [
                                    'tx_block_exp_tx' => $transaction['hash']
                                  ]);

      if(count($infosTransaction) == 0){
        $v = parent::execSqlRequest("INSERT INTO block_exp_tx_krypto (symbol_block_exp_tx, tx_block_exp_tx, status_block_exp_tx, confirmations_block_exp_tx, data_block_exp_tx, date_block_exp_tx)
                                    VALUES (:symbol_block_exp_tx, :tx_block_exp_tx, :status_block_exp_tx, :confirmations_block_exp_tx, :data_block_exp_tx, :date_block_exp_tx)",
                                    [
                                      'symbol_block_exp_tx' => $transaction['symbol'],
                                      'tx_block_exp_tx' => $transaction['hash'],
                                      'status_block_exp_tx' => 0,
                                      'confirmations_block_exp_tx' => $transaction['confirmations'],
                                      'data_block_exp_tx' => json_encode($transaction),
                                      'date_block_exp_tx' => $transaction['date']
                                    ]);

        $infosTransaction = parent::querySqlRequest("SELECT * FROM block_exp_tx_krypto WHERE tx_block_exp_tx=:tx_block_exp_tx",
                                    [
                                      'tx_block_exp_tx' => $transaction['hash']
                                    ]);

        if(count($infosTransaction) == 0){
          error_log('Fail to fetch last transaction (Fail to add SQL)');
          continue;
        }

        $infosTransaction = $infosTransaction[0];
      } else {
        $infosTransaction = $infosTransaction[0];
        if($infosTransaction['status_block_exp_tx'] == '1') continue;
      }

      if(!array_key_exists($infosTransaction['symbol_block_exp_tx'], $Addresslist)){
        error_log('Fail to fetch deposit address (Wallet not found)');
        continue;
      }

      $DataTransfert = json_decode($infosTransaction['data_block_exp_tx'], true);

      $AddressInfos = $Addresslist[$infosTransaction['symbol_block_exp_tx']];

      if($AddressInfos->_getNbVerification() > $infosTransaction['confirmations_block_exp_tx']) continue;

      $assignedWallet = parent::querySqlRequest("SELECT * FROM user_widthdraw_krypto WHERE value_user_widthdraw LIKE :value_user_widthdraw AND value_user_widthdraw LIKE :value_user_widthdraw_symbol AND type_user_widthdraw=:type_user_widthdraw",
                                               [
                                                 'value_user_widthdraw' => '%"address":"'.$DataTransfert['from'].'"%',
                                                 'value_user_widthdraw_symbol' => '%"cryptocurrency_name":"'.$infosTransaction['symbol_block_exp_tx'].'",%',
                                                 'type_user_widthdraw' => 'cryptocurrencies'
                                               ]);

      if(count($assignedWallet) == 0){
        error_log('Fail to fetch assigned transaction');
        continue;
      }

      $assignedWallet = $assignedWallet[0];

      $UserAssigned = new User($assignedWallet['id_user']);
      $BalanceAssigned = new Balance($UserAssigned, $this->_getApp(), 'real');

      $BalanceAssigned->_addDeposit($DataTransfert['value'], 'direct_deposit', 'Direct deposit ('.$DataTransfert['value'].' '.$DataTransfert['symbol'].')',
                                    $DataTransfert['symbol'], $DataTransfert['hash'], 1);

      $updateTransaction = parent::execSqlRequest("UPDATE block_exp_tx_krypto SET status_block_exp_tx=:status_block_exp_tx WHERE id_block_exp_tx=:id_block_exp_tx",
                                                  [
                                                    'id_block_exp_tx' => $infosTransaction['id_block_exp_tx'],
                                                    'status_block_exp_tx' => '1'
                                                  ]);


    }

  }

  public function _getTransactionUserTime($AddressList, $Time){

    $transactionList = [];
    foreach ($AddressList as $addFrom => $value) {
      $r = parent::querySqlRequest("SELECT * FROM block_exp_tx_krypto WHERE data_block_exp_tx LIKE :data_block_exp_tx AND date_block_exp_tx > :date_block_exp_tx",
                                  [
                                    'data_block_exp_tx' => '%"from":"'.$addFrom.'",%',
                                    'date_block_exp_tx' => $Time
                                  ]);
      $transactionList = array_merge($transactionList, $r);
    }

    return $transactionList;

  }

  public function _saveAddr($addlist){

    $symbolAdded = $this->_getDepositAddress();

    foreach ($addlist as $key => $value) {
      if(array_key_exists($key, $symbolAdded)){
        if(is_null($value['address'])){
          $r = parent::execSqlRequest("DELETE FROM block_exp_address_list_krypto WHERE symbol__block_exp_address_list=:symbol__block_exp_address_list",
                                      [
                                        'symbol__block_exp_address_list' => $key
                                      ]);
        } else {
          $r = parent::querySqlRequest("UPDATE block_exp_address_list_krypto SET address__block_exp_address_list=:address__block_exp_address_list, nb_confirm__block_exp_address_list=:nb_confirm__block_exp_address_list
                                        WHERE symbol__block_exp_address_list=:symbol__block_exp_address_list",
                                        [
                                          'address__block_exp_address_list' => $value['address'],
                                          'nb_confirm__block_exp_address_list' => (strlen($value['confirmations']) == 0 ? '1' : $value['confirmations']),
                                          'symbol__block_exp_address_list' => $key
                                        ]);
        }

      } else {
        $r = parent::execSqlRequest("INSERT INTO block_exp_address_list_krypto (symbol__block_exp_address_list, address__block_exp_address_list, nb_confirm__block_exp_address_list)
                                    VALUES (:symbol__block_exp_address_list, :address__block_exp_address_list, :nb_confirm__block_exp_address_list)",
                                    [
                                      'symbol__block_exp_address_list' => $key,
                                      'address__block_exp_address_list' => $value['address'],
                                      'nb_confirm__block_exp_address_list' => (strlen($value['confirmations']) == 0 ? '1' : $value['confirmations'])
                                    ]);
      }
    }

  }

}

?>
