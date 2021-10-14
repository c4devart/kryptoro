<?php

class Balance extends MySQL {

  private $User = null;
  private $App = null;
  private $Type = null;
  private $BalanceTypeList = ['practice', 'real'];
  private $BalanceList = null;

  private $BalanceLimit = [
    'practice' => 10000,
    'real' => 500000000000
  ];

  private $BalanceData = null;

  private $CurrentBalance = null;

  public function __construct($User, $App, $Type = null, $IDBalance = null){

    $this->User = $User;
    $this->App = $App;
    $this->Type = $Type;
    $this->BalanceLimit['practice'] = $App->_getMaximalFreeDeposit();
    if(!is_null($User) && $this->_getApp()->_hiddenThirdpartyActive()) $this->_checkBalanceUser();
    if(!is_null($Type) && in_array($Type, $this->BalanceTypeList)){
      if(is_null($IDBalance)) $this->_loadBalance();
    }

    if(!is_null($IDBalance)){
      $this->_loadBalanceByID($IDBalance);
    }

  }

  public function _getUser(){ return $this->User; }
  public function _getApp(){ return $this->App; }

  public function _getType(){ return $this->Type; }

  public function _isPractice(){ return $this->_getType() == 'practice'; }

  public function _checkBalanceUser(){
    foreach ($this->BalanceTypeList as $type) {
      $r = parent::querySqlRequest("SELECT * FROM balance_krypto WHERE id_user=:id_user AND type_balance=:type_balance",
                                   [
                                     'id_user' => $this->_getUser()->_getUserID(),
                                     'type_balance' => $type
                                   ]);
      if(count($r) == 0){
        $r = parent::execSqlRequest("INSERT INTO balance_krypto (id_user, type_balance, created_balance)
                                     VALUES (:id_user, :type_balance, :created_balance)",
                                     [
                                       'id_user' => $this->_getUser()->_getUserID(),
                                       'type_balance' => $type,
                                       'created_balance' => time()
                                     ]);
        if(!$r) throw new Exception("Error SQL : Fail to create user balance", 1);

        if($type == 'practice'){
          $Balance = new Balance($this->_getUser(), $this->_getApp(), 'practice');
          $ValueAdd = $Balance->_convertCurrency($this->_getApp()->_getMaximalFreeDeposit(), 'USD', $this->_getApp()->_getFreeDepositSymbol());
          $Balance->_addDeposit($ValueAdd, 'Initial',
                                'Practice account deposit', $this->_getApp()->_getFreeDepositSymbol(), "", 2, $this->_getApp()->_getFreeDepositSymbol());
        }

      }



    }
  }

  public function _getCurrentBalance(){
    if(!is_null($this->CurrentBalance)) return $this->CurrentBalance;
    $r = parent::querySqlRequest("SELECT * FROM balance_krypto WHERE id_user=:id_user AND active_balance=:active_balance",
                                [
                                  'id_user' => $this->_getUser()->_getUserID(),
                                  'active_balance' => "1"
                                ]);
    if(count($r) == 0) $this->CurrentBalance = new Balance($this->_getUser(), $this->_getApp(), 'practice');
    else $this->CurrentBalance = new Balance($this->_getUser(), $this->_getApp(), $r[0]['type_balance']);



    return $this->CurrentBalance;
  }

  public function _getBalanceList(){
    if(!is_null($this->BalanceList)) return $this->BalanceList;
    foreach ($this->BalanceTypeList as $type) {
      if($type == 'real' && !$this->_getApp()->_getTradingEnableRealAccount()) continue;
      if($type == 'practice' && !$this->_getApp()->_getTradingEnablePracticeAccount()) continue;
      $this->BalanceList[] = new Balance($this->_getUser(), $this->_getApp(), $type);
    }
    return $this->BalanceList;
  }

  public function _getRealBalance(){
    return new Balance($this->_getUser(), $this->_getApp(), 'real');
  }

  private function _loadBalance(){
    $r = parent::querySqlRequest("SELECT * FROM balance_krypto WHERE id_user=:id_user AND type_balance=:type_balance",
                                [
                                  'id_user' => $this->_getUser()->_getUserID(),
                                  'type_balance' => $this->_getType()
                                ]);
    if(count($r) == 0) throw new Exception("Error : Fail to get balance (".$this->_getType().")", 1);

    $this->BalanceData = $r[0];
  }

  public function _loadBalanceByID($id){
    $r = parent::querySqlRequest("SELECT * FROM balance_krypto WHERE id_user=:id_user AND id_balance=:id_balance",
                                [
                                  'id_user' => $this->_getUser()->_getUserID(),
                                  'id_balance' => $id
                                ]);
    if(count($r) == 0) throw new Exception("Error : Fail to get balance by ID (".$id.")", 1);

    $this->BalanceData = $r[0];
  }

  public function _getBalanceKeyData($k){
    // Check if coin data is loaded
    if(is_null($this->BalanceData)) $this->_loadBalance();

    // Check if key is founded
    if(!array_key_exists($k, $this->BalanceData)) throw new Exception("Error : ".$k." not exist in Balance (".$this->_getType().")", 1);

    // Return associate value
    return $this->BalanceData[$k];
  }

  public function _getBalanceID($encrypted = false){
    if($encrypted) return App::encrypt_decrypt('encrypt', $this->_getBalanceKeyData('id_balance'));
    return $this->_getBalanceKeyData('id_balance');
  }

  public function _getBalanceType(){
    return $this->_getBalanceKeyData('type_balance');
  }

  public function _depositAlreadyDone($datapayment){
    $r = parent::querySqlRequest("SELECT * FROM deposit_history_krypto WHERE payment_data_deposit_history LIKE :payment_data_deposit_history AND balance_deposit_history=:balance_deposit_history AND id_user=:id_user",
                                [
                                  'balance_deposit_history' => $this->_getBalanceID(),
                                  'id_user' => $this->_getUser()->_getUserID(),
                                  'payment_data_deposit_history' => '%'.$datapayment.'%'
                                ]);
    return count($r) > 0;
  }

  public function _getDepositInfosByRef($datapayment){
    $r = parent::querySqlRequest("SELECT * FROM deposit_history_krypto WHERE payment_data_deposit_history LIKE :payment_data_deposit_history",
                                [
                                  'payment_data_deposit_history' => '%'.$datapayment.'%'
                                ]);

    if(count($r) == 0) throw new Exception('Fail to receive payment : '.$datapayment);
    return $r[0];
  }

  public function _generateWithdrawReference(){
    function randLetter()
    {
        $a_z = str_split("ABCDEFGHIJKLMNOPQRSTUVWXYZ");
        shuffle($a_z);
        return $a_z[0];
    }

    $pattern = $this->_getApp()->_getWidthdrawPattern();
    $resultArray = str_split($this->_getApp()->_getWidthdrawPattern());
    foreach ($resultArray as $key => $value) {
      if($value == '$') $resultArray[$key] = rand(0, 9);
      if($value == '*')  $resultArray[$key] = randLetter();
    }

    $ref = join('', $resultArray);
    $r = parent::querySqlRequest("SELECT * FROM widthdraw_history_krypto WHERE ref_widthdraw_history=:ref_widthdraw_history",
                                [
                                  'ref_widthdraw_history' => $ref
                                ]);
    if(count($r) > 0) return $this->_generateWithdrawReference();
    return $ref;
  }

  public function _generatePaymentReference(){

    function randLetter()
    {
        $a_z = str_split("ABCDEFGHIJKLMNOPQRSTUVWXYZ");
        shuffle($a_z);
        return $a_z[0];
    }

    $pattern = $this->_getApp()->_paymentReferencePattern();
    $resultArray = str_split($this->_getApp()->_paymentReferencePattern());
    foreach ($resultArray as $key => $value) {
      if($value == '$') $resultArray[$key] = rand(0, 9);
      if($value == '*')  $resultArray[$key] = randLetter();
    }

    $ref = join('', $resultArray);
    $r = parent::querySqlRequest("SELECT * FROM deposit_history_krypto WHERE ref_deposit_history=:ref_deposit_history",
                                [
                                  'ref_deposit_history' => $ref
                                ]);
    if(count($r) > 0) return $this->_generatePaymentReference();
    return $ref;
  }

  public function _getPaymentGatewayFee($paymentGateway = null){
    if(is_null($paymentGateway)) return 0;
    if($paymentGateway == "coingate") return $this->_getApp()->_getCoingatePaymentFees();
    if($paymentGateway == "blockonomics") return $this->_getApp()->_getBlockonomicsPaymentFees();
    if($paymentGateway == "coinbasecommerce") return $this->_getApp()->_getCoinbaseCommercePaymentFees();
    if($paymentGateway == "coinpayments") return $this->_getApp()->_getCoinpaymentPaymentFees();
    if($paymentGateway == "payeer") return $this->_getApp()->_getPayeerPaymentFees();
    if($paymentGateway == "mollie") return $this->_getApp()->_getMolliePaymentFees();
    if($paymentGateway == "raveflutterwave") return $this->_getApp()->_getRaveflutterwavePaymentFees();
    if($paymentGateway == "banktransfert") return $this->_getApp()->_getBankTransfertPaymentFees();
    if($paymentGateway == "paystack") return $this->_getApp()->_getPaystackFees();
    if($paymentGateway == "polipayments") return $this->_getApp()->_getPolipaymentsFees();
  }

  public function _addDeposit($amount, $payment_type = 'referal', $description = null, $currency = 'USD', $datapayment = "", $payment_status = 1, $wallet_target = null, $payment_reference = null){

    $fees = 0;
    if($payment_type != "referal" && $payment_type != 'Initial' && $payment_type != 'Manager_update'){
      $fees = $amount * (($this->_getApp()->_getFeesDeposit() + $this->_getPaymentGatewayFee($payment_type)) / 100);
      $amount -= $fees;
    }

    if(is_null($wallet_target)){
      $BalanceList = $this->_getBalanceListResum();
      if(array_key_exists($currency, $BalanceList)) {
        $wallet_target = $currency;
      }
      else {
        if(!array_key_exists($this->_getApp()->_getDepositSymbolNotExistConvert(), $BalanceList)){
          throw new Exception("Wallet receive can't be given. Please contact admin");
        } else {
          $wallet_target = $this->_getApp()->_getDepositSymbolNotExistConvert();
        }
      }
    }

    if(is_null($payment_reference)) $payment_reference = $this->_generatePaymentReference();

    $r = parent::execSqlRequest("INSERT INTO deposit_history_krypto (id_user, amount_deposit_history, date_deposit_history, balance_deposit_history, payment_status_deposit_history, payment_type_deposit_history, description_deposit_history, currency_deposit_history, fees_deposit_history, payment_data_deposit_history, wallet_deposit_history, ref_deposit_history) VALUES
                                 (:id_user, :amount_deposit_history, :date_deposit_history, :balance_deposit_history, :payment_status_deposit_history, :payment_type_deposit_history, :description_deposit_history, :currency_deposit_history, :fees_deposit_history, :payment_data_deposit_history, :wallet_deposit_history, :ref_deposit_history)",
                                 [
                                   'id_user' => $this->_getUser()->_getUserID(),
                                   'amount_deposit_history' => number_format(floatval($amount), 8).'',
                                   'date_deposit_history' => time(),
                                   'balance_deposit_history' => $this->_getBalanceID(),
                                   'payment_status_deposit_history' => $payment_status,
                                   'payment_type_deposit_history' => $payment_type,
                                   'description_deposit_history' => (!is_null($description) ? $description : 'Deposit '.rtrim($amount, '0').' '.$currency.' ('.rtrim($fees, '0').' '.$currency.' fees)'),
                                   'currency_deposit_history' => $currency,
                                   'fees_deposit_history' => number_format($fees, 8),
                                   'payment_data_deposit_history' => $datapayment,
                                   'wallet_deposit_history' => $wallet_target,
                                   'ref_deposit_history' => $payment_reference
                                 ]);

    if(!$r) throw new Exception("Error SQL : Fail to add deposit in database", 1);

    if(!$this->_isPractice() && $this->_getApp()->_referalEnabled()){
      $r = parent::querySqlRequest("SELECT * FROM deposit_history_krypto WHERE id_user=:id_user AND balance_deposit_history=:balance_deposit_history AND payment_status_deposit_history='1'",
                                  [
                                    'id_user' => $this->_getUser()->_getUserID(),
                                    'balance_deposit_history' => $this->_getBalanceID()
                                  ]);

      if(count($r) == 1 || count($r) == 0){

        $AssociateReferal = $this->_getUser()->_getAssociateReferall();

        if(!is_null($AssociateReferal)){

          $BalanceOther = new Balance($AssociateReferal, $this->_getApp(), 'real');
          $NGiven = $this->_convertCurrency($this->_getApp()->_getReferalWinAmount(), 'USD', $currency);
          $BalanceOther->_addDeposit($NGiven, 'Referal', 'Referal commission ('.$this->_getUser()->_getEmail().')', $currency);

        }

      }
    }



    return $payment_reference;

  }

  public function _changeDepositStatus($datapayment, $new_status = 1){
    $infosPayment = parent::querySqlRequest("SELECT * FROM deposit_history_krypto WHERE payment_data_deposit_history LIKE :payment_data_deposit_history",
                                            [
                                              'payment_data_deposit_history' => '%'.$datapayment.'%',
                                            ]);
    if(count($infosPayment) == 0) throw new Exception("Error : Fail to get payment", 1);
    $infosPayment = $infosPayment[0];
    if($infosPayment['payment_status_deposit_history'] != "0") throw new Exception("Error : Unable to change payment status", 1);

    $r = parent::execSqlRequest("UPDATE deposit_history_krypto SET payment_status_deposit_history=:payment_status_deposit_history WHERE payment_data_deposit_history LIKE :payment_data_deposit_history",
                                [
                                  'payment_data_deposit_history' => '%'.$datapayment.'%',
                                  'payment_status_deposit_history' => $new_status
                                ]);
    if(!$r) throw new Exception("Error : Fail to change status depost", 1);
    return true;
  }

  public function _updateDepositPaymentData($deposit_ref, $datapayment){

    $infosPayment = parent::querySqlRequest("SELECT * FROM deposit_history_krypto WHERE ref_deposit_history=:ref_deposit_history AND id_user=:id_user",
                                            [
                                              'ref_deposit_history' => $deposit_ref,
                                              'id_user' => $this->_getUser()->_getUserID()
                                            ]);

    if(count($infosPayment) == 0) throw new Exception("Error : Fail to update deposit payment data (payment not found)", 1);

    $r = parent::execSqlRequest("UPDATE deposit_history_krypto SET payment_data_deposit_history=:payment_data_deposit_history WHERE id_deposit_history=:id_deposit_history",
                                [
                                  'id_deposit_history' => $infosPayment[0]['id_deposit_history'],
                                  'payment_data_deposit_history' => $datapayment
                                ]);

    if(!$r) throw new Exception("Error SQL : Fail to update deposit payment data", 1);


  }

  public function _getDepositHistory($lastDepositF = false){
    if($lastDepositF){
      return parent::querySqlRequest("SELECT * FROM deposit_history_krypto WHERE balance_deposit_history=:balance_deposit_history AND id_user=:id_user ORDER BY date_deposit_history DESC",
                                     [
                                       'balance_deposit_history' => $this->_getBalanceID(),
                                       'id_user' => $this->_getUser()->_getUserID()
                                     ]);

    }
    return parent::querySqlRequest("SELECT * FROM deposit_history_krypto WHERE balance_deposit_history=:balance_deposit_history AND id_user=:id_user",
                                   [
                                     'balance_deposit_history' => $this->_getBalanceID(),
                                     'id_user' => $this->_getUser()->_getUserID()
                                   ]);

  }



  public function _getWidthdrawHistory($onlyapproved = false, $all = false){

    if($all) return parent::querySqlRequest("SELECT * FROM widthdraw_history_krypto WHERE id_balance=:id_balance AND id_user=:id_user",
                                  [
                                    'id_balance' => $this->_getBalanceID(),
                                    'id_user' => $this->_getUser()->_getUserID()
                                  ]);

    if($onlyapproved) return parent::querySqlRequest("SELECT * FROM widthdraw_history_krypto WHERE id_balance=:id_balance AND id_user=:id_user AND status_widthdraw_history != :status_widthdraw_history",
                                  [
                                    'id_balance' => $this->_getBalanceID(),
                                    'id_user' => $this->_getUser()->_getUserID(),
                                    'status_widthdraw_history' => 0
                                  ]);

    return parent::querySqlRequest("SELECT * FROM widthdraw_history_krypto WHERE id_balance=:id_balance AND id_user=:id_user AND (:status_widthdraw_history != :status_widthdraw_history OR :date_widthdraw_history < date_widthdraw_history)",
                                  [
                                    'id_balance' => $this->_getBalanceID(),
                                    'id_user' => $this->_getUser()->_getUserID(),
                                    'status_widthdraw_history' => '0',
                                    'date_widthdraw_history' => time() - 3600
                                  ]);


  }

  public function _getOrderHistory($side = null, $symbol = null, $currency = null){
    if(!is_null($side)){
      if(!is_null($symbol)){
        return parent::querySqlRequest("SELECT * FROM internal_order_krypto WHERE id_balance=:id_balance AND id_user=:id_user AND side_internal_order=:side_internal_order AND symbol_internal_order=:symbol_internal_order",
                                      [
                                        'id_user' => $this->_getUser()->_getUserID(),
                                        'id_balance' => $this->_getBalanceID(),
                                        'side_internal_order' => $side,
                                        'symbol_internal_order' => $symbol
                                      ]);
      } else {
        return parent::querySqlRequest("SELECT * FROM internal_order_krypto WHERE id_balance=:id_balance AND id_user=:id_user AND side_internal_order=:side_internal_order",
                                      [
                                        'id_user' => $this->_getUser()->_getUserID(),
                                        'id_balance' => $this->_getBalanceID(),
                                        'side_internal_order' => $side
                                      ]);
      }

    }

    if(!is_null($symbol) && is_null($currency)){
      return parent::querySqlRequest("SELECT * FROM internal_order_krypto WHERE id_balance=:id_balance AND id_user=:id_user AND symbol_internal_order=:symbol_internal_order",
                                    [
                                      'id_user' => $this->_getUser()->_getUserID(),
                                      'id_balance' => $this->_getBalanceID(),
                                      'symbol_internal_order' => $symbol
                                    ]);
    }

    if(!is_null($symbol) && !is_null($currency)){
      return parent::querySqlRequest("SELECT * FROM internal_order_krypto WHERE id_balance=:id_balance AND id_user=:id_user AND symbol_internal_order=:symbol_internal_order AND to_internal_order=:to_internal_order",
                                    [
                                      'id_user' => $this->_getUser()->_getUserID(),
                                      'id_balance' => $this->_getBalanceID(),
                                      'symbol_internal_order' => $symbol,
                                      'to_internal_order' => $currency
                                    ]);

    }



    return parent::querySqlRequest("SELECT * FROM internal_order_krypto WHERE id_balance=:id_balance AND id_user=:id_user",
                                  [
                                    'id_user' => $this->_getUser()->_getUserID(),
                                    'id_balance' => $this->_getBalanceID()
                                  ]);
  }

  public function _getOrderInfos($order_id = null){
    if(is_null($order_id)) throw new Exception("Error : You need to specify order ID", 1);
    $r = parent::querySqlRequest("SELECT * FROM internal_order_krypto WHERE id_balance=:id_balance AND id_user=:id_user AND id_internal_order=:id_internal_order",
                                [
                                  'id_balance' => $this->_getBalanceID(),
                                  'id_user' => $this->_getUser()->_getUserID(),
                                  'id_internal_order' => $order_id
                                ]);
    if(count($r) == 0) throw new Exception("Error : Order not found", 1);
    return $r[0];
  }

  public function _getBalanceValue(){
    $total = 0;
    foreach ($this->_getDepositHistory() as $infosDeposit) {
      $total += floatval($infosDeposit['amount_deposit_history']);
    }

    foreach ($this->_getOrderHistory() as $infosOrder) {
      if($infosOrder['side_internal_order'] == "BUY") $total -= (floatval($infosOrder['usd_amount_internal_order']) + floatval($infosOrder['fees_internal_order']));
      else $total += (floatval($infosOrder['usd_amount_internal_order']) - floatval($infosOrder['fees_internal_order']));
    }

    foreach ($this->_getWidthdrawHistory() as $infosOrder) {
      $total -= floatval($infosOrder['amount_widthdraw_history']);
    }

    return $total;
  }

  private $BalanceinvestissementCache = [];
  public function _getBalanceInvestisment($symbol = null){
    if(array_key_exists($symbol, $this->BalanceinvestissementCache)) return $this->BalanceinvestissementCache;
    $total = 0;
    foreach ($this->_getOrderHistory(null, $symbol) as $infosOrder) {
      if($infosOrder['side_internal_order'] == "BUY") $total += floatval($infosOrder['usd_amount_internal_order']);
      else $total -= floatval($infosOrder['usd_amount_internal_order']);
    }
    $this->BalanceinvestissementCache[$total] = $total;
    return $total;
  }

  public function _getBalanceEvolution($CryptoApi, $Symbol = null){
    $total = 0;
    $CoinPrice = [];
    $CoinValue = [];
    foreach ($this->_getOrderHistory(null, $Symbol) as $infosOrder) {
      if(!array_key_exists($infosOrder['symbol_internal_order'], $CoinValue)){
        $CoinValue[$infosOrder['symbol_internal_order']]['usd'] = 0;
        $CoinValue[$infosOrder['symbol_internal_order']]['amount'] = 0;
      }

      if($infosOrder['side_internal_order'] == "BUY") {
        $CoinValue[$infosOrder['symbol_internal_order']]['usd'] += floatval($infosOrder['usd_amount_internal_order']);
        $CoinValue[$infosOrder['symbol_internal_order']]['amount'] += floatval($infosOrder['amount_internal_order']);
      }
      else {
        $CoinValue[$infosOrder['symbol_internal_order']]['usd'] -= floatval($infosOrder['usd_amount_internal_order']);
        $CoinValue[$infosOrder['symbol_internal_order']]['amount'] -= floatval($infosOrder['amount_internal_order']);
      }

    }

    $totalContain = 0;
    foreach ($CoinValue as $SymbolFetched => $ValueOrdered) {
      if(!array_key_exists($SymbolFetched, $CoinPrice)){
        $Coin = new CryptoCoin($CryptoApi, $SymbolFetched);
        $CoinPrice[$SymbolFetched] = $Coin->_getPrice();
      }

      $Price = $CoinPrice[$SymbolFetched];
      $totalContain += floatval($ValueOrdered['amount']);
      $total += ($ValueOrdered['amount'] * $Price);

    }

    return [
      'total' => $total,
      'contain' => $totalContain,
      'evolv' => ($this->_getBalanceInvestisment($Symbol) == 0 ? '0' : ($total - $this->_getBalanceInvestisment($Symbol)) / $this->_getBalanceInvestisment($Symbol) * 100)
    ];
  }

  public function _getBalanceTotal($CryptoApi){
    return $this->_getBalanceValue() + ($this->_getBalanceEvolution($CryptoApi)['total'] - $this->_getBalanceInvestisment()) + $this->_getBalanceInvestisment();
  }

  public function _generateOrderReference(){

    function randLetter()
    {
        $a_z = str_split("ABCDEFGHIJKLMNOPQRSTUVWXYZ");
        shuffle($a_z);
        return $a_z[0];
    }

    $pattern = $this->_getApp()->_hiddenTradingOrderPatternReference();
    $resultArray = str_split($this->_getApp()->_hiddenTradingOrderPatternReference());
    foreach ($resultArray as $key => $value) {
      if($value == '$') $resultArray[$key] = rand(0, 9);
      if($value == '*')  $resultArray[$key] = randLetter();
    }

    $ref = join('', $resultArray);
    $r = parent::querySqlRequest("SELECT * FROM internal_order_krypto WHERE ref_internal_order=:ref_internal_order",
                                [
                                  'ref_internal_order' => $ref
                                ]);
    if(count($r) > 0) return $this->_generateOrderReference();
    return $ref;
  }

  public function _saveOrder($exchange, $amount, $usd_total, $side, $symbol, $order, $to, $type = "market", $ordered_price = null){
    $fees = floatval($usd_total) * ($this->_getApp()->_hiddenThirdpartyTradingFee() / 100);

    if(count($order) == 0 || !array_key_exists('id', $order)) $order['id'] = uniqid();

    $r = parent::execSqlRequest("INSERT INTO internal_order_krypto (id_user, date_internal_order, id_balance, thirdparty_internal_order, amount_internal_order,
                                                                  usd_amount_internal_order, symbol_internal_order, fees_internal_order, order_key_internal_order,
                                                                  side_internal_order, to_internal_order, ref_internal_order, type_internal_order, status_internal_order, ordered_price_internal_order)
                                  VALUES (:id_user, :date_internal_order, :id_balance, :thirdparty_internal_order, :amount_internal_order, :usd_amount_internal_order,
                                        :symbol_internal_order, :fees_internal_order, :order_key_internal_order, :side_internal_order, :to_internal_order,
                                        :ref_internal_order, :type_internal_order, :status_internal_order, :ordered_price_internal_order)",
                                  [
                                    'id_user' => $this->_getUser()->_getUserID(),
                                    'date_internal_order' => time(),
                                    'id_balance' => $this->_getBalanceID(),
                                    'thirdparty_internal_order' => $exchange->_getExchangeName(),
                                    'amount_internal_order' => ($type == "market" ? $amount : ($side == "BUY" ? ($usd_total * $ordered_price) : $amount)),
                                    'usd_amount_internal_order' => ($type == "market" ? $usd_total : ($side == "SELL" ? ($amount * $ordered_price) : $usd_total)),
                                    'symbol_internal_order' => $symbol,
                                    'side_internal_order' => $side,
                                    'order_key_internal_order' => ($this->_isPractice() ? '' : $order['id']),
                                    'fees_internal_order' => $fees,
                                    'to_internal_order' => $to,
                                    'ref_internal_order' => $this->_generateOrderReference(),
                                    'type_internal_order' => $type,
                                    'status_internal_order' => ($type == "market" ? "1" : "0"),
                                    'ordered_price_internal_order' => (is_null($ordered_price) ? "0" : $ordered_price)
                                  ]);
    if(!$r) throw new Exception("Error : Fail to save internal order", 1);

  }

  public function _updateOrder($id_order, $infos_order){
    $r = parent::querySqlRequest("SELECT * FROM internal_order_krypto WHERE id_internal_order=:id_internal_order AND id_user=:id_user AND id_balance=:id_balance",
                                [
                                  'id_internal_order' => $id_order,
                                  'id_user' => $this->_getUser()->_getUserID(),
                                  'id_balance' => $this->_getBalanceID()
                                ]);

    if(count($r) == 0) throw new Exception('Error when updating order : '.$id_order.' - user : '.$this->_getUser()->_getUserID().' - balance : '.$this->_getBalanceID().', order not exist');

    $r = parent::querySqlRequest("UPDATE internal_order_krypto SET
                                status_internal_order=:status_internal_order,
                                order_key_internal_order=:order_key_internal_order
                                WHERE id_internal_order=:id_internal_order AND id_user=:id_user AND id_balance=:id_balance",
                                [
                                  'id_internal_order' => $id_order,
                                  'id_user' => $this->_getUser()->_getUserID(),
                                  'id_balance' => $this->_getBalanceID(),
                                  'status_internal_order' => 1,
                                  'order_key_internal_order' => $infos_order['id']
                                ]);

    if(!$r) throw new Exception('Error when updating order : '.$id_order.' - user : '.$this->_getUser()->_getUserID().' - balance : '.$this->_getBalanceID().', SQL Error');
  }

  public function _changeActiveBalance($bid){

    $infosBalance = parent::querySqlRequest("SELECT * FROM balance_krypto WHERE id_balance=:id_balance AND id_user=:id_user",
                                [
                                  'id_balance' => $bid,
                                  'id_user' => $this->_getUser()->_getUserID()
                                ]);

    if(count($infosBalance) == 0) throw new Exception("Error : Fail to change balance", 1);

    $r = parent::execSqlRequest("UPDATE balance_krypto SET active_balance=:active_balance WHERE id_user=:id_user AND active_balance=:st_active_balance",
                                [
                                  'active_balance' => 0,
                                  'st_active_balance' => 1,
                                  'id_user' => $this->_getUser()->_getUserID()
                                ]);

    $r = parent::execSqlRequest("UPDATE balance_krypto SET active_balance=:active_balance WHERE id_user=:id_user AND id_balance=:id_balance",
                               [
                                 'active_balance' => 1,
                                 'id_user' => $this->_getUser()->_getUserID(),
                                 'id_balance' => $bid
                               ]);

    if(!$r) throw new Exception("Error : Fail to change status", 1);

    return [
      'id_balance' => $infosBalance[0]['id_balance'],
      'enc_id_balance' => App::encrypt_decrypt('encrypt', $infosBalance[0]['id_balance']),
      'type_balance' => $infosBalance[0]['type_balance'],
      'title' => $infosBalance[0]['type_balance'].' account'
    ];

  }

  public function _getLimits(){
    return $this->BalanceLimit;
  }

  public function _limitReached($showAmountNeeded = false){
    if($showAmountNeeded) return $this->_getLimits()[$this->_getBalanceType()] - $this->_getBalanceValue();
    return $this->_getBalanceValue() >= $this->_getLimits()[$this->_getBalanceType()];
  }

  public function _getBalanceByID($bid){
    $r = parent::querySqlRequest("SELECT * FROM balance_krypto WHERE id_balance=:id_balance AND id_user=:id_user",
                                [
                                  'id_user' => $this->_getUser()->_getUserID(),
                                  'id_balance' => $bid
                                ]);
    if(count($r) == 0) throw new Exception("Error : Balance not found", 1);
    return new Balance($this->_getUser(), $this->_getApp(), $r[0]['type_balance']);
  }

  public function _validateDeposit($keycharge, $status, $amount, $typepayment, $datapayment, $fees = 0){
    $BalanceReal = new Balance($this->_getUser(), $this->_getApp(), 'real');
    $r = parent::execSqlRequest("INSERT INTO deposit_history_krypto (id_user, amount_deposit_history, date_deposit_history, balance_deposit_history, payment_type_deposit_history, payment_data_deposit_history, payment_status_deposit_history, description_deposit_history, fees_deposit_history)
                                  VALUES (:id_user, :amount_deposit_history, :date_deposit_history, :balance_deposit_history, :payment_type_deposit_history, :payment_data_deposit_history, :payment_status_deposit_history, :description_deposit_history, :fees_deposit_history)",
                                  [
                                    'id_user' => $this->_getUser()->_getUserID(),
                                    'amount_deposit_history' => $amount,
                                    'date_deposit_history' => time(),
                                    'balance_deposit_history' => $BalanceReal->_getBalanceID(),
                                    'payment_type_deposit_history' => $typepayment,
                                    'payment_data_deposit_history' => json_encode($datapayment),
                                    'payment_status_deposit_history' => $status,
                                    'description_deposit_history' => ucfirst($typepayment).' payment',
                                    'fees_deposit_history' => $fees
                                  ]);


      if(!$r) throw new Exception("Error SQL : Fail to add deposit in database", 1);

      if($BalanceReal->_getBalanceType() == 'real' && $status == 1){
        $App = new App();
        $Trade = new Trade($this->_getUser(), $this->_getApp());
        $thirdPartyChoosen = $Trade->_getThirdParty($App->_hiddenThirdpartyServiceCfg()[$BalanceReal->_getType()])[$App->_hiddenThirdpartyService()];
        $paymentList = $thirdPartyChoosen->_getApi()->get_payment_methods();
        $paymentSelectedData = null;
        foreach ($paymentList as $paymentData) {
          if($paymentData['allow_deposit'] == true && $paymentData['primary_buy'] == true){
            $paymentSelectedData = $paymentData;
            break;
          }
        }
        if(!is_null($paymentSelectedData)){
          $response = $thirdPartyChoosen->_getApi()->deposit($paymentSelectedData['currency'], $amount, null, ['payment_method_id' => $paymentSelectedData['id']]);
          //error_log(json_encode($response));
        }
      }

      if($BalanceReal->_getBalanceType() == 'real' && $this->_getApp()->_referalEnabled()){
        $r = parent::querySqlRequest("SELECT * FROM deposit_history_krypto WHERE id_user=:id_user AND balance_deposit_history=:balance_deposit_history",
                                    [
                                      'id_user' => $this->_getUser()->_getUserID(),
                                      'balance_deposit_history' => $BalanceReal->_getBalanceID()
                                    ]);


        if(count($r) == 1){

          $AssociateReferal = $BalanceReal->_getUser()->_getAssociateReferall();

          if(!is_null($AssociateReferal)){

            $Balance = new Balance($AssociateReferal, $BalanceReal->_getApp(), 'real');
            $Balance->_addDeposit($this->_getApp()->_getReferalWinAmount(), 'Referal', 'Referal commission ('.$this->_getUser()->_getEmail().')');

          }

        }
      }

  }

  public function _askWidthdraw($symbol, $amount, $method){

    //if(!filter_var($paypal, FILTER_VALIDATE_EMAIL)) throw new Exception("Please enter a valid email address", 1);
    if(!is_numeric($amount)) throw new Exception("Amount not valid", 1);
    $BalanceList = $this->_getBalanceListResum();
    if(!array_key_exists($symbol, $BalanceList)) throw new Exception("Symbol not available in balance", 1);
    if($amount > $BalanceList[$symbol]) throw new Exception("Amount not available on your balance", 1);

    $token = substr(str_shuffle( "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 50);

    $fees = $amount * $this->_getApp()->_getWithdrawFees();

    $WithdrawReference = $this->_generateWithdrawReference();



    $r = parent::execSqlRequest("INSERT INTO widthdraw_history_krypto (id_user, id_balance, amount_widthdraw_history, date_widthdraw_history, status_widthdraw_history, paypal_widthdraw_history, token_widthdraw_history, description_widthdraw_history, symbol_widthdraw_history, method_widthdraw_history, fees_widthdraw_history, ref_widthdraw_history)
                                VALUES (:id_user, :id_balance, :amount_widthdraw_history, :date_widthdraw_history, :status_widthdraw_history, :paypal_widthdraw_history, :token_widthdraw_history, :description_widthdraw_history, :symbol_widthdraw_history, :method_widthdraw_history, :fees_widthdraw_history, :ref_widthdraw_history)",
                                [
                                  'id_user' => $this->_getUser()->_getUserID(),
                                  'id_balance' => $this->_getBalanceID(),
                                  'amount_widthdraw_history' => $amount,
                                  'date_widthdraw_history' => time(),
                                  'status_widthdraw_history' => 0,
                                  'paypal_widthdraw_history' => '',
                                  'token_widthdraw_history' => $token,
                                  'description_widthdraw_history' => 'Widthdraw ('.$this->_getApp()->_formatNumber($amount, ($amount > 10 ? 2 : 6)).' '.$symbol.') ('.$this->_getApp()->_formatNumber($fees, ($fees > 10 ? 2 : 6)).' '.$symbol.' Fees)',
                                  'symbol_widthdraw_history' => $symbol,
                                  'method_widthdraw_history' => $method,
                                  'fees_widthdraw_history' => $fees,
                                  'ref_widthdraw_history' => $WithdrawReference
                                ]);

      if(!$r) throw new Exception("Error : Fail to create widthdraw request (please contact the support)", 1);

      $template = new Liquid\Template();
      $template->parse(file_get_contents(APP_URL.'/app/modules/kr-user/templates/confirmWidthdraw.tpl'));

      $IsRealMoney = $this->_symbolIsMoney($amount);

      $Withdraw = new Widthdraw($this->_getUser());
      $InfosWithdraw = $Withdraw->_getInformationWithdrawMethod($method);

      $infosWithdrawText = "";
      foreach($Withdraw->_getWithdrawData($InfosWithdraw) as $key => $value){
        $infosWithdrawText .= "<li><b>".$key." : </b> ".$value."</li>";
      }


      // Render & send email
      $this->_getApp()->_sendMail($this->_getUser()->_getEmail(), $this->_getApp()->_getAppTitle().' - Withdraw confirmation needed', $template->render([
        'APP_URL' => APP_URL,
        'APP_TITLE' => $this->_getApp()->_getAppTitle(),
        'SUBJECT' => 'Withdraw confirmation needed',
        'LOGO_BLACK' => $this->_getApp()->_getLogoBlackPath(),
        'USER_NAME' => $this->_getUser()->_getName(),
        'CURRENCY' => $symbol,
        'CONFIRM_LINK' => APP_URL.'/app/modules/kr-trade/src/actions/askWidthdrawApprove.php?token='.App::encrypt_decrypt('encrypt', $token),
        'AMOUNT' => $this->_getApp()->_formatNumber($amount, ($IsRealMoney ? 2 : 8)).' '.$symbol,
        'TYPE' => ucfirst($InfosWithdraw['type_user_widthdraw']),
        'INFOS_WITHDRAW' => $infosWithdrawText,
        'DATE' => date('d/m/Y H:i:s', time())
      ]));
      throw new Exception(APP_URL.'/app/modules/kr-trade/src/actions/askWidthdrawApprove.php?token='.App::encrypt_decrypt('encrypt', $token));
  }

  public function _getAskWidthdrawEmail(){
    $r = parent::querySqlRequest("SELECT * FROM widthdraw_history_krypto WHERE id_user=:id_user AND id_balance=:id_balance",
                                [
                                  'id_user' => $this->_getUser()->_getUserID(),
                                  'id_balance' => $this->_getBalanceID()
                                ]);

    if(count($r) == 0) return $this->_getUser()->_getEmail();
    return $r[0]['paypal_widthdraw_history'];
  }

  public function _askWidthdrawApprove($token){
    $r = parent::querySqlRequest("SELECT * FROM widthdraw_history_krypto WHERE id_user=:id_user AND id_balance=:id_balance AND token_widthdraw_history=:token_widthdraw_history",
                                [
                                  'id_user' => $this->_getUser()->_getUserID(),
                                  'id_balance' => $this->_getBalanceID(),
                                  'token_widthdraw_history' => App::encrypt_decrypt('decrypt', $token)
                                ]);

    if(count($r) == 0) throw new Exception("Error : Wrong token", 1);
    if($r[0]['status_widthdraw_history'] != "0") throw new Exception('Withdraw already processed');
    if(time() - $r[0]['date_widthdraw_history'] > 3500) throw new Exception("Error : Widthdraw request has expire", 1);


    $rv = parent::execSqlRequest("UPDATE widthdraw_history_krypto SET status_widthdraw_history=:status_widthdraw_history WHERE id_user=:id_user AND id_balance=:id_balance AND token_widthdraw_history=:token_widthdraw_history",
                                [
                                  'status_widthdraw_history' => 1,
                                  'id_user' => $this->_getUser()->_getUserID(),
                                  'id_balance' => $this->_getBalanceID(),
                                  'token_widthdraw_history' => App::encrypt_decrypt('decrypt', $token)
                                ]);

    if(!$rv) throw new Exception("Error : Fail to change widthdraw status (contact support)", 1);

    if($this->_getApp()->_getEnableAutomaticWithdraw()){
      $Trade = new Trade(null, null);
      $Withdraw = new Widthdraw();

      $WithdrawAssociate = $Withdraw->_getWithrawExchangeAssociate();
      if(array_key_exists($r[0]['symbol_widthdraw_history'], $WithdrawAssociate)){
        $ExchangeFetched = $Trade->_getThirdParty($this->_getApp()->_hiddenThirdpartyServiceCfg()[$WithdrawAssociate[$r[0]['symbol_widthdraw_history']]])[$WithdrawAssociate[$r[0]['symbol_widthdraw_history']]];

        $Infoswithdraw = $Withdraw->_getInformationWithdrawMethod($r[0]['method_widthdraw_history']);
        $valueWithdraw = json_decode($Infoswithdraw['value_user_widthdraw'], true);
        $ExchangeFetched->_execWithdraw($r[0]['symbol_widthdraw_history'], ($r[0]['amount_widthdraw_history'] - $r[0]['fees_widthdraw_history']), $valueWithdraw['address']);

        $this->_setDoneWithdraw($r[0]['id_widthdraw_history']);

      }

    }

    $template = new Liquid\Template();
    $template->parse(file_get_contents(APP_URL.'/app/modules/kr-user/templates/adminEmail.tpl'));

    // Render & send email
    $this->_getApp()->_sendMail($this->_getApp()->_getSupportEmail(), $this->_getApp()->_getAppTitle().' - Withdraw asked ('.$this->_getUser()->_getEmail().')', $template->render([
      'APP_URL' => APP_URL,
      'APP_TITLE' => $this->_getApp()->_getAppTitle(),
      'SUBJECT' => 'Withdraw asked',
      'LOGO_BLACK' => $this->_getApp()->_getLogoBlackPath(),
      'NAME' => $this->_getUser()->_getName(),
      'EMAIL' => $this->_getUser()->_getEmail(),
      'AMOUNT' => $this->_getApp()->_formatNumber($r[0]['amount_widthdraw_history'], 2).' $',
      'DATE' => date('d/m/Y H:i:s', time())
    ]));


  }

  private $TransactionsHistory = null;
  public function _getTransactionsHistory(){
    if(!is_null($this->TransactionsHistory)) return $this->TransactionsHistory;
    $res = [];
    foreach ($this->_getDepositHistory() as $depositData) {
      $depositData['date_histo'] = intval($depositData['date_deposit_history']);
      $depositData['type_histo'] = 'deposit';
      $depositData['description_histo'] = $depositData['description_deposit_history'];
      $depositData['amount_histo'] = $depositData['amount_deposit_history'];
      $depositData['currency'] = $depositData['currency_deposit_history'];
      $depositData['status'] = $depositData['payment_status_deposit_history'];
      $depositData['fees'] = $depositData['fees_deposit_history'];
      $depositData['method'] = ucfirst(str_replace('_', ' ', $depositData['payment_type_deposit_history']));
      $depositData['ref'] = (strlen($depositData['ref_deposit_history']) > 0 ? $depositData['ref_deposit_history'] : $depositData['id_user'].'-'.$depositData['id_deposit_history']);
      $res[] = $depositData;
    }

    $Withdraw = new Widthdraw($this->_getUser());

    foreach ($this->_getWidthdrawHistory(false, true) as $depositData) {

      $WithdrawMethod = "Paypal";
      if(strlen($depositData['method_widthdraw_history']) && $depositData['method_widthdraw_history'] > 0){
        $infosWithdraw = $Withdraw->_getInformationWithdrawMethod($depositData['method_widthdraw_history']);
        if($infosWithdraw === false){
           $WithdrawMethod = "-";
        } else{
          $WithdrawMethod = $infosWithdraw['type_user_widthdraw'];
        }

      }

      $depositData['date_histo'] = intval($depositData['date_widthdraw_history']);
      $depositData['type_histo'] = 'widthdraw';
      $depositData['description_histo'] = $depositData['description_widthdraw_history'];
      $depositData['amount_histo'] = $depositData['amount_widthdraw_history'];
      $depositData['currency'] = $depositData['symbol_widthdraw_history'];
      $depositData['status'] = $depositData['status_widthdraw_history'];
      $depositData['fees'] = $depositData['fees_widthdraw_history'];
      $depositData['method'] = ucfirst(str_replace('_', ' ', $WithdrawMethod));
      $depositData['ref'] = (strlen($depositData['ref_widthdraw_history']) > 0 ? $depositData['ref_widthdraw_history'] : $depositData['id_user'].'-'.$depositData['id_widthdraw_history']);
      $res[] = $depositData;
    }

    function sortTransactionsHisto($a, $b){
      if($a['date_histo'] == $b['date_histo']) return 0;
      return ($a['date_histo'] > $b['date_histo']) ? -1 : 1;
    }

    usort($res, 'sortTransactionsHisto');
    $this->TransactionsHistory = $res;
    return $this->TransactionsHistory;
  }

  public function _getListTrade($symbol = null, $after = 0){
    if(!is_null($symbol)) return parent::querySqlRequest("SELECT * FROM internal_order_krypto WHERE id_user=:id_user AND id_balance=:id_balance AND symbol_internal_order=:symbol_internal_order AND date_internal_order > :date_internal_order ORDER BY id_internal_order DESC LIMIT 100",
                                  [
                                    'id_user' => $this->_getUser()->_getUserID(),
                                    'id_balance' => $this->_getBalanceID(),
                                    'symbol_internal_order' => $symbol,
                                    'date_internal_order' => $after
                                  ]);
    return parent::querySqlRequest("SELECT * FROM internal_order_krypto WHERE id_user=:id_user AND id_balance=:id_balance AND date_internal_order > :date_internal_order ORDER BY id_internal_order DESC",
                                  [
                                    'id_user' => $this->_getUser()->_getUserID(),
                                    'id_balance' => $this->_getBalanceID(),
                                    'date_internal_order' => $after
                                  ]);
  }

  public function _getOrderResumBySymbol($CryptoApi){

    $r = parent::querySqlRequest("SELECT * FROM internal_order_krypto WHERE id_user=:id_user AND id_balance=:id_balance GROUP BY symbol_internal_order",
                                [
                                  'id_user' => $this->_getUser()->_getUserID(),
                                  'id_balance' => $this->_getBalanceID()
                                ]);

    $res = [];
    foreach ($r as $key => $symbolInternalOrder) {
      $resLine = [
        'coin' => new CryptoCoin($CryptoApi, $symbolInternalOrder['symbol_internal_order']),
        'evolv' => $this->_getBalanceEvolution($CryptoApi, $symbolInternalOrder['symbol_internal_order'])
      ];
      $res[] = $resLine;
    }
    return $res;

  }

  public function _checkPaymentResult(){
    if (empty($_GET) || empty($_GET['c']) || empty($_GET['t']) || empty($_GET['v'])) {
        return false;
    }

    if (!is_numeric($_GET['t']) || (time() - intval($_GET['t']) > 5)) {
        return false;
    }

    $listPaymentAvailable = ['paypal', 'mollie'];
    if (!in_array($_GET['c'], $listPaymentAvailable)) {
        return false;
    }


    $dataPayment = parent::querySqlRequest("SELECT * FROM deposit_history_krypto WHERE payment_data_deposit_history LIKE :payment_data_deposit_history AND id_user=:id_user AND payment_type_deposit_history=:payment_type_deposit_history",
                                        [
                                          'payment_data_deposit_history' => '%'.$_GET['v'].'%',
                                          'id_user' => $this->_getUser()->_getUserID(),
                                          'payment_type_deposit_history' => $_GET['c']
                                        ]);

    if(count($dataPayment) == 0){
      return false;
    }

    $dataPayment = $dataPayment[0];

    $keyCharge = null;
    if($_GET['c'] == "paypal"){
      $keyCharge = json_decode($dataPayment['payment_data_deposit_history'], true);
      $keyCharge = json_decode($keyCharge, true);
      $keyCharge = $keyCharge['id'];
    }

    if($_GET['c'] == "mollie"){
      $keyCharge = $_GET['v'];
    }

    echo '<script type="text/javascript">$(document).ready(function(){ showChargePopup("result_'.$_GET['c'].'", {k:"'.$keyCharge.'",t:"deposit"}); });</script>';

  }

  public function _getAmountCrypto($crypto){

    $r = parent::querySqlRequest("SELECT * FROM internal_order_krypto WHERE id_user=:id_user AND id_balance=:id_balance AND symbol_internal_order=:symbol_internal_order",
                                [
                                  'id_user' => $this->_getUser()->_getUserID(),
                                  'id_balance' => $this->_getBalanceID(),
                                  'symbol_internal_order' => $crypto
                                ]);

    $valueAmount = 0;
    foreach ($r as $key => $value) {
      if($value['side_internal_order'] == "BUY"){
        $valueAmount += floatval($value['amount_internal_order']);
      } else {
        $valueAmount -= floatval($value['amount_internal_order']);
      }
    }

    return $valueAmount;

  }

  public function _validDeposit($orderid, $paymentgateway = 'coingate'){

    $r = parent::querySqlRequest("SELECT * FROM deposit_history_krypto WHERE id_user=:id_user AND payment_status_deposit_history=:payment_status_deposit_history AND payment_data_deposit_history LIKE :payment_data_deposit_history",
                                [
                                  'id_user' => $this->_getUser()->_getUserID(),
                                  'payment_status_deposit_history' => 0,
                                  'payment_data_deposit_history' => '%'.$orderid.'%'
                                ]);

    if(count($r) == 0) throw new Exception("Error : Fail to receive order : ".$orderid, 1);


    $r = parent::execSqlRequest("UPDATE deposit_history_krypto SET payment_status_deposit_history=:payment_status_deposit_history WHERE id_deposit_history=:id_deposit_history AND payment_type_deposit_history=:payment_type_deposit_history AND id_user=:id_user",
                                [
                                  'payment_status_deposit_history' => '1',
                                  'id_deposit_history' => $r[0]['id_deposit_history'],
                                  'payment_type_deposit_history' => $paymentgateway,
                                  'id_user' => $this->_getUser()->_getUserID()
                                ]);

    if(!$r){
      throw new Exception("Error : Fail to change order status (".$orderid.")", 1);
    }

  }

  public function _removeDeposit($orderid){
    $r = parent::execSqlRequest("DELETE FROM deposit_history_krypto WHERE payment_data_deposit_history LIKE :payment_data_deposit_history AND payment_status_deposit_history=:payment_status_deposit_history",
                                [
                                  'payment_status_deposit_history' => '0',
                                  'payment_data_deposit_history' => '%'.$orderid.'%'
                                ]);
    if(!$r) throw new Exception("Error : Fail to remove deposit request", 1);

  }

  public function _setDoneWithdraw($request){

    $sv = explode('-', $request);
    if(count($sv) != 2) throw new Exception('Permission denied', 1);

    $request = $sv[1];

    $r = parent::querySqlRequest("SELECT * FROM widthdraw_history_krypto WHERE id_widthdraw_history=:id_widthdraw_history AND status_widthdraw_history=:status_widthdraw_history",
                                [
                                  'id_widthdraw_history' => $request,
                                  'status_widthdraw_history' => 1
                                ]);
    if(count($r) == 0) throw new Exception("Permission denied", 1);

    $rv = parent::execSqlRequest("UPDATE widthdraw_history_krypto SET status_widthdraw_history=:status_widthdraw_history WHERE id_widthdraw_history=:id_widthdraw_history",
                                [
                                  'id_widthdraw_history' => $request,
                                  'status_widthdraw_history' => 2
                                ]);

    if(!$rv) throw new Exception("Error : Fail to change status", 1);

    $template = new Liquid\Template();
    $template->parse(file_get_contents(APP_URL.'/app/modules/kr-user/templates/processWidthdraw.tpl'));

    $UserWithdraw = new User($r[0]['id_user']);

    $NotifictionCenter = new NotificationCenter($UserWithdraw);
    $NotifictionCenter->_sendNotification('Withdraw - '.(strlen($r[0]['ref_widthdraw_history']) > 0 ? $r[0]['ref_widthdraw_history'] : $r[0]['id_user'].'-'.$r[0]['id_widthdraw_history']), 'Your withdraw as been processed', '');

    // Render & send email
    $this->_getApp()->_sendMail($this->_getUser()->_getEmail(), $this->_getApp()->_getAppTitle().' - Your withdraw as been processed', $template->render([
      'APP_URL' => APP_URL,
      'APP_TITLE' => $this->_getApp()->_getAppTitle(),
      'LOGO_BLACK' => $this->_getApp()->_getLogoBlackPath(),
      'SUBJECT' => 'Your withdraw as been processed',
      'USER_NAME' => $UserWithdraw->_getName(),
      'AMOUNT' => $this->_getApp()->_formatNumber($r[0]['amount_widthdraw_history'], 2).' $',
      'PAYPAL_EMAIL' => $r[0]['paypal_widthdraw_history'],
      'DATE' => date('d/m/Y H:i:s', time())
    ]));

    return true;


  }

  public function _setCancelWithdraw($request){

    $sv = explode('-', $request);
    if(count($sv) != 2) throw new Exception('Permission denied', 1);

    $request = $sv[1];

    $r = parent::querySqlRequest("SELECT * FROM widthdraw_history_krypto WHERE id_widthdraw_history=:id_widthdraw_history",
                                [
                                  'id_widthdraw_history' => $request
                                ]);
    if(count($r) == 0) throw new Exception("Permission denied - ".$request, 1);

    $rv = parent::execSqlRequest("UPDATE widthdraw_history_krypto SET status_widthdraw_history=:status_widthdraw_history WHERE id_widthdraw_history=:id_widthdraw_history",
                                [
                                  'id_widthdraw_history' => $request,
                                  'status_widthdraw_history' => -1
                                ]);

    if(!$rv) throw new Exception("Error : Fail to change status", 1);

    $UserWithdraw = new User($r[0]['id_user']);

    $NotifictionCenter = new NotificationCenter($UserWithdraw);
    $NotifictionCenter->_sendNotification('Withdraw - '.(strlen($r[0]['ref_widthdraw_history']) > 0 ? $r[0]['ref_widthdraw_history'] : $r[0]['id_user'].'-'.$r[0]['id_widthdraw_history']), 'Your withdraw as been canceled', '');

    return true;

  }

  private $BalanceListResum = null;

  public function _getBalanceListResum(){

    if(!$this->_getApp()->_hiddenThirdpartyNotConfigured() || is_null($this->_getApp()->_hiddenThirdpartyServiceCfg()) || strlen(json_encode($this->_getApp()->_hiddenThirdpartyServiceCfg())) < 5) return [];
    if(!is_null($this->BalanceListResum)) return $this->BalanceListResum;

    $Trade = new Trade($this->_getUser(), $this->_getApp());

    $r = parent::querySqlRequest("SELECT * FROM internal_order_krypto WHERE id_balance=:id_balance",
                                [
                                  'id_balance' => $this->_getBalanceID()
                                ]);


    $balanceList = [];
    foreach ($Trade->_getThirdParty() as $key => $Exchange) {
      if(array_key_exists($Exchange->_getExchangeName(), $this->_getApp()->_hiddenThirdpartyServiceCfg())){
        foreach ($Exchange->_getSymbolListAvailable() as $keySymbol) {
          if(!array_key_exists($keySymbol, $balanceList)) $balanceList[$keySymbol] = 0;
        }
      }
    }



    $r = parent::querySqlRequest("SELECT * FROM deposit_history_krypto WHERE balance_deposit_history=:balance_deposit_history AND id_user=:id_user",
                                [
                                  'id_user' => $this->_getUser()->_getUserID(),
                                  'balance_deposit_history' => $this->_getBalanceID()
                                ]);

    foreach ($r as $key => $value) {
      if($value['payment_status_deposit_history'] == "0") continue;
      if($this->_getApp()->_getPaymentApproveNeeded()){
        if($value['payment_status_deposit_history'] == "1") continue;
      }
      if(array_key_exists($value['currency_deposit_history'],$balanceList) || array_key_exists($value['wallet_deposit_history'],$balanceList)){
        if($value['currency_deposit_history'] != $value['wallet_deposit_history']){
          $balanceList[$value['wallet_deposit_history']] += floatval($value['amount_deposit_history']) * floatval($value['wallet_convert_m_deposit_history']);
        } else {
          $balanceList[$value['currency_deposit_history']] += floatval($value['amount_deposit_history']);
        }
      }
    }

    $r = parent::querySqlRequest("SELECT * FROM internal_order_krypto WHERE id_balance=:id_balance AND id_user=:id_user",
                                [
                                  'id_balance' => $this->_getBalanceID(),
                                  'id_user' => $this->_getUser()->_getUserID()
                                ]);

    foreach ($r as $key => $value) {

      if(array_key_exists($value['to_internal_order'], $balanceList)){

        if($value['type_internal_order'] == "limit" && $value['status_internal_order'] == "0"){

        } else {
          if($value['side_internal_order'] == "SELL") $balanceList[$value['to_internal_order']] += floatval($value['usd_amount_internal_order']) - floatval($value['fees_internal_order']);
        }

        if($value['side_internal_order'] == "BUY") $balanceList[$value['to_internal_order']] -= floatval($value['amount_internal_order']);
      }

      if(array_key_exists($value['symbol_internal_order'], $balanceList)){
        if($value['side_internal_order'] == "SELL") $balanceList[$value['symbol_internal_order']] -= floatval($value['amount_internal_order']);

        if($value['type_internal_order'] == "limit" && $value['status_internal_order'] == "0"){
        } else {
          if($value['side_internal_order'] == "BUY") $balanceList[$value['symbol_internal_order']] += (floatval($value['usd_amount_internal_order']) - floatval($value['fees_internal_order']));
        }

      }

    }

    $r = parent::querySqlRequest("SELECT * FROM widthdraw_history_krypto WHERE id_user=:id_user AND id_balance=:id_balance AND status_widthdraw_history != :status_widthdraw_history",
                                [
                                  'id_user' => $this->_getUser()->_getUserID(),
                                  'id_balance' => $this->_getBalanceID(),
                                  'status_widthdraw_history' => -1
                                ]);


    foreach ($r as $key => $value) {
      if(array_key_exists($value['symbol_widthdraw_history'], $balanceList)){
        $balanceList[$value['symbol_widthdraw_history']] -= floatval($value['amount_widthdraw_history']);
      }
    }

    arsort($balanceList);

    $this->BalanceListResum = $balanceList;
    return $this->BalanceListResum;

  }

  public function _getListMoney(){
    $r = parent::querySqlRequest("SELECT code_iso_currency FROM currency_krypto");
    $res = [];
    foreach ($r as $key => $value) {
      $res[] = $value['code_iso_currency'];
    }
    return array_values($res);
  }

  public function _getInfosMoney($codeiso){
    $r = parent::querySqlRequest("SELECT * FROM currency_krypto WHERE code_iso_currency=:code_iso_currency", ['code_iso_currency' => $codeiso]);
    if(count($r) == 0) throw new Exception("Error : Currency not found in database", 1);
    return $r[0];
  }

  public function _getInfoCryptoCurrency($codeiso){

    $CryptoApi = new CryptoApi($this->_getUser(), ['USD', 'USD'], $this->_getApp());
    $CryptoCoin = $CryptoApi->_getCoin($codeiso);

    return [
      'code_iso_currency' => $codeiso,
      'symbol_currency' => $codeiso,
      'usd_rate_currency' => 1 / $CryptoCoin->_getPrice()
    ];
  }

  public function _symbolIsMoney($symbol){
    $r = parent::querySqlRequest("SELECT code_iso_currency FROM currency_krypto WHERE code_iso_currency=:code_iso_currency", ['code_iso_currency' => $symbol]);
    return count($r) > 0;
  }

  public function _symbolAbrev($symbol){
    $r = parent::querySqlRequest("SELECT code_iso_currency FROM currency_krypto WHERE code_iso_currency=:code_iso_currency", ['code_iso_currency' => $symbol]);
    if(count($r) == 0) return $symbol;
    return $r[0]['code_iso_currency'];
  }

  public function _getDepositListAvailable(){
    if(is_null($this->_getApp()->_getListCurrencyDepositAvailable())) return [];
    return array_values($this->_getApp()->_getListCurrencyDepositAvailable());
  }

  public function _getEstimationBalance($tosymbol = null){
    $estimatedSymbol =  $this->_getApp()->_getBalanceEstimationSymbol();
    if($this->_getApp()->_getBalanceEstimationUserCurrency()) $estimatedSymbol = $this->_getUser()->_getCurrency();

    $CryptoApi = new CryptoApi($this->_getUser(), (is_null($tosymbol) ? $estimatedSymbol : $tosymbol), $this->_getApp());
    $BalanceData = $this->_getBalanceListResum();

    $SymbolListAvailableBalance = [];
    foreach ($BalanceData as $key => $value) {
      if($value > 0) $SymbolListAvailableBalance[] = $key;
    }
    if(count($SymbolListAvailableBalance) == 0) return 0;
    $conv = $CryptoApi->_getData('price', ['fsym' => (is_null($tosymbol) ? $estimatedSymbol : $tosymbol), 'tsyms' => join(',', $SymbolListAvailableBalance)]);
    $res = 0;
    if(count($SymbolListAvailableBalance) > 0 && count($conv) == 0 && in_array('BTC', $SymbolListAvailableBalance) && !array_key_exists('BTC', $conv)){
      $convBtcEth = $CryptoApi->_getData('price', ['fsym' => 'ETH', 'tsyms' => 'BTC']);
      $convSymbolEth = $CryptoApi->_getData('price', ['fsym' => (is_null($tosymbol) ? $estimatedSymbol : $tosymbol), 'tsyms' => 'ETH']);
      $conv['BTC'] = $convSymbolEth['ETH'] * $convBtcEth['BTC'];
    }
    foreach ($conv as $key => $value) {
      $res += (1 / $value) * $BalanceData[$key];
    }

    return $res;
  }

  public function _getEstimationPayBalance(){
    $tosymbol = 'BTC';
    $estimatedSymbol =  $this->_getApp()->_getBalanceEstimationSymbol();
    if($this->_getApp()->_getBalanceEstimationUserCurrency()) $estimatedSymbol = $this->_getUser()->_getCurrency();

    $CryptoApi = new CryptoApi($this->_getUser(), (is_null($tosymbol) ? $estimatedSymbol : $tosymbol), $this->_getApp());

    $Paid = [];
    foreach(parent::querySqlRequest("SELECT * FROM  internal_order_krypto WHERE id_user=:id_user AND id_balance=:id_balance",
                                  ['id_user' => $this->_getUser()->_getUserID(),
                                    'id_balance' => $this->_getBalanceID()
                                  ]) as $key => $value){
        $Paid[$value['to_internal_order']] += $value['usd_amount_internal_order'];
    }

    if(count($Paid) == 0) return 0;

    $conv = $CryptoApi->_getData('price', ['fsym' => (is_null($tosymbol) ? $estimatedSymbol : $tosymbol), 'tsyms' => join(',', array_keys($Paid))]);
    $res = 0;
    foreach ($conv as $key => $value) {
      $res += (1 / $value) * $Paid[$key];
    }
    return $res;

  }

  private $ConvertedCache = [];
  public function _convertCurrency($amount = 1, $from = "USD", $to = "BTC", $market = "CCCAGG"){
    if(array_key_exists($from.':'.$to, $this->ConvertedCache)) return $amount * $this->ConvertedCache[$from.':'.$to];
    $CryptoApi = new CryptoApi($this->_getUser(), $from, $this->_getApp());

    if($market == "cex") $market = "cexio";

    $infos = $CryptoApi->_getData('price', ['fsym' => $from, 'tsyms' => $to, "e" => $market]);
    if(!is_null($infos) && array_key_exists($to, $infos)){
      $this->ConvertedCache[$from.':'.$to] = $infos[$to];
      return $amount * $infos[$to];
    }
    return $amount;

  }

  public function _getEstimationSymbol($nshowabrev = false){

    $estimatedSymbol =  $this->_getApp()->_getBalanceEstimationSymbol();
    if($this->_getApp()->_getBalanceEstimationUserCurrency()) $estimatedSymbol = $this->_getUser()->_getCurrency();

    if($nshowabrev) return $estimatedSymbol;
    if($this->_symbolIsMoney($estimatedSymbol)){
      $r = parent::querySqlRequest("SELECT * FROM currency_krypto WHERE code_iso_currency=:code_iso_currency", ['code_iso_currency' => $estimatedSymbol]);
      if(count($r) == 0) return $estimatedSymbol;
      return $r[0]['symbol_currency'];
    } else {
      return $estimatedSymbol;
    }

  }


  public function _getTradedPair(){

    $res = [];

    foreach(parent::querySqlRequest("SELECT * FROM  internal_order_krypto WHERE id_user=:id_user AND id_balance=:id_balance",
                                  ['id_user' => $this->_getUser()->_getUserID(),
                                    'id_balance' => $this->_getBalanceID()
                                  ]) as $key => $value){

      $res[$value['thirdparty_internal_order'].':'.$value['symbol_internal_order'].'/'.$value['to_internal_order']] = [
        "symbol" => $value['symbol_internal_order'],
        "currency" => $value['to_internal_order'],
        "market" => $value['thirdparty_internal_order']
      ];

    }

    return $res;

  }
  public function _getPaymentStatus($type, $time){

    $r = parent::querySqlRequest("SELECT * FROM deposit_history_krypto WHERE payment_type_deposit_history=:payment_type_deposit_history
                                  AND balance_deposit_history=:balance_deposit_history AND id_user=:id_user AND date_deposit_history > :date_deposit_history",
                                  [
                                    'payment_type_deposit_history' => strtolower($type),
                                    'balance_deposit_history' => $this->_getBalanceID(),
                                    'id_user' => $this->_getUser()->_getUserID(),
                                    'date_deposit_history' => $time
                                  ]);

    if(count($r) == 0) throw new Exception("Error : Payment not found");

    $r = $r[0];
    return [
      'ref' => $r['ref_deposit_history'],
      'type' => $r['payment_type_deposit_history'],
      'amount' => $r['amount_deposit_history'],
      'fees' => $r['fees_deposit_history'],
      'currency' => $r['currency_deposit_history'],
      'wallet' => $r['wallet_deposit_history'],
      'enc_ref' => App::encrypt_decrypt('encrypt', $r['ref_deposit_history'])
    ];

  }

  public function _cancelOrder($orderid){

    $r = parent::querySqlRequest("SELECT * FROM internal_order_krypto WHERE id_internal_order=:id_internal_order AND id_balance=:id_balance AND id_user=:id_user",
                                [
                                  'id_internal_order' => $orderid,
                                  'id_balance' => $this->_getBalanceID(),
                                  'id_user' => $this->_getUser()->_getUserID()
                                ]);

    if(count($r) == 0) throw new Exception('Order not found');

    $r = parent::execSqlRequest("DELETE FROM internal_order_krypto WHERE id_internal_order=:id_internal_order AND id_balance=:id_balance AND id_user=:id_user",
                                [
                                  'id_internal_order' => $orderid,
                                  'id_balance' => $this->_getBalanceID(),
                                  'id_user' => $this->_getUser()->_getUserID()
                                ]);

    if(!$r) throw new Exception('Fail to cancel order (SQL Error)');

    return true;

  }

}



?>
