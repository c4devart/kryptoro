<?php

class Blockfolio extends MySQL {

  /**
   * User associate to Blockfolio
   * @var User
   */
  private $User = null;

  /**
   * Blockfolio item
   * @var Array
   */
  private $BlockfolioItem = [];

  /**
   * Blockfolio contructor
   * @param User $User
   */
  public function __construct($User = null){
    if(is_null($User)) throw new Exception("Error: User Blockfolio can't be null", 1);
    $this->User = $User;
    $this->_loadBlockfolio();
  }

  public function _getUser(){
    return $this->User;
  }

  private function _loadBlockfolio(){
    $this->BlockfolioItem = parent::querySqlRequest("SELECT * FROM blockfolio_krypto WHERE id_user=:id_user ORDER BY id_blockfolio DESC",
                                                    [
                                                      'id_user' => $this->_getUser()->_getUserID()
                                                    ]);
  }

  public function _getBlockfolioItem(){
    return $this->BlockfolioItem;
  }

  public function _addItem($symbol, $currency, $market){

    $r = parent::execSqlRequest("INSERT INTO blockfolio_krypto (id_user, symbol_blockfolio, currency_blockfolio, market_blockfolio)
                                  VALUES (:id_user, :symbol_blockfolio, :currency_blockfolio, :market_blockfolio)",
                                  [
                                    'id_user' => $this->_getUser()->_getUserID(),
                                    'symbol_blockfolio' => $symbol,
                                    'currency_blockfolio' => $currency,
                                    'market_blockfolio' => $market
                                  ]);

    if(!$r) throw new Exception("Error : Fail to add item to blockfolio", 1);
    return true;
  }

  public function _removeItem($iid){

    $r = parent::execSqlRequest("DELETE FROM blockfolio_krypto WHERE id_blockfolio=:id_blockfolio AND id_user=:id_user",
                                [
                                  'id_blockfolio' => $iid,
                                  'id_user' => $this->_getUser()->_getUserID()
                                ]);

    if(!$r) throw new Exception("Error : Fail to delete blockfolio item (".$iid.")", 1);

    return true;

  }

  public function _addHolding($symbol, $type, $price, $quantiy, $date){
    if($type != "buy" && $type != "sell") throw new Exception("Wrong type holding trading", 1);

    $dateHolding = new DateTime(str_replace('/', '-', $date));
    $r = parent::execSqlRequest("INSERT INTO holding_krypto (id_user, value_holding, type_holding, date_holding, price_holding, symbol_holding)
                                  VALUES (:id_user, :value_holding, :type_holding, :date_holding, :price_holding, :symbol_holding)",
                                  [
                                    'id_user' => $this->_getUser()->_getUserID(),
                                    'value_holding' => $quantiy,
                                    'type_holding' => $type,
                                    'date_holding' => $dateHolding->getTimestamp(),
                                    'price_holding' => $price,
                                    'symbol_holding' => $symbol
                                  ]);
      if(!$r) throw new Exception("Error SQL : Fail to add holding", 1);
      return true;

  }

}

?>
