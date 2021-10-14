<?php
/**
 * CryptoOrder class
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
class CryptoOrder extends MySQL {

  /**
   * Historic data
   * @var Array
   */
  private $CryptoCoin = null;

  /**
   * CryptoOrder constructor
   * @param CryptoCoin Coin
   */
  public function __construct($CryptoCoin = null){
    if(is_null($CryptoCoin)) throw new Exception("Error : CryptoOrder coin not given", 1);
    $this->CryptoCoin = $CryptoCoin;
  }

  /**
   * Get cryptocoin associate to CrypoOrder
   * @return CryptoCoin
   */
  public function _getCryptoCoin(){
    if(is_null($this->CryptoCoin)) throw new Exception("Error : CryptoOrder, coin is null", 1);
    return $this->CryptoCoin;
  }

  public function _getOrderList($User = null, $Currency){

    $orderList = null;
    if(!is_null($User)){
      $orderList = parent::querySqlRequest("SELECT * FROM order_krypto WHERE symbol_order=:symbol_order AND id_user=:id_user AND currency_order=:currency_order",
                                        [
                                          'symbol_order' => $this->_getCryptoCoin()->_getSymbol(),
                                          'id_user' => $User->_getUserID(),
                                          'currency_order' => $Currency
                                        ]);
    } else {
      $orderList = parent::querySqlRequest("SELECT * FROM order_krypto WHERE symbol_order=:symbol_order AND currency_order=:currency_order",
                                        [
                                          'symbol_order' => $this->_getCryptoCoin()->_getSymbol(),
                                          'currency_order' => $Currency
                                        ]);
    }

    return $orderList;

  }

  public function _createOrder($User, $date, $type, $amount, $currency){

    $r = parent::execSqlRequest("INSERT INTO order_krypto (id_user, time_order, type_order, amount_order, symbol_order, currency_order)
                                  VALUES (:id_user, :time_order, :type_order, :amount_order, :symbol_order, :currency_order)",
                                  [
                                    'id_user' => $User->_getUserID(),
                                    'time_order' => $date,
                                    'type_order' => strtoupper($type),
                                    'amount_order' => $amount,
                                    'symbol_order' => $this->_getCryptoCoin()->_getSymbol(),
                                    'currency_order' => $currency
                                  ]);

    if(!$r) throw new Exception("Error : Fail to save order in database", 1);
    return true;


  }


}

?>
