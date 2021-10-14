<?php

/**
 * DashboardTopList class
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
class DashboardTopList extends MySQL
{
    /**
     * CryptoApi Object
     * @var CryptoApi
     */
    private $CryptoApi = null;

    /**
     * User object
     * @var User
     */
    private $user = null;

    /**
     * Graph ID
     * @var Int
     */
    private $itemID = null;

    /**
     * Graph Data
     * @var Array
     */
    private $itemData = null;

    private $App = null;

    /**
     * Dashboard graph constructor
     * @param CryptoApi $CryptoApi CryptoApi object
     * @param User $user           User object
     * @param Int $graphID         Graph ID object
     * @param Array $graphData     Graph data
     */
    public function __construct($CryptoApi, $user, $itemID = null, $itemData = null, $App = null)
    {
        $this->CryptoApi = $CryptoApi;
        $this->user = $user;
        $this->App = $App;
        if(!is_null($itemID)){
          $this->itemID = $itemID;
          if (is_null($itemData)) {
              $this->_loadItemdata();
          } else {
              $this->itemData = $itemData;
          }
        }
    }

    /**
     * Get user object
     * @return User User object
     */
    public function _getUser()
    {
        return $this->user;
    }

    public function _getApp(){
      return $this->App;
    }

    /**
     * Get CryptoApi object
     * @return CryptoApi CryptoApi Object
     */
    public function _getCryptoApi()
    {
        return $this->CryptoApi;
    }

    /**
     * Get graph ID
     * @return Int Graph ID
     */
    public function _getItemID()
    {
        return $this->itemID;
    }

    public function _deleteTopList(){
      $r = parent::execSqlRequest("DELETE FROM top_list_krypto WHERE id_top_list=:id_graph AND id_user=:id_user",
                                        [
                                          'id_graph' => $this->_getItemID(),
                                          'id_user' => $this->_getUser()->_getUserID()
                                        ]);
    }

    /**
     * Load graph data
     */
    public function _loadItemdata()
    {
        $itemData = parent::querySqlRequest("SELECT * FROM top_list_krypto WHERE id_top_list=:id_graph AND id_user=:id_user",
                                          [
                                            'id_graph' => $this->_getItemID(),
                                            'id_user' => $this->_getUser()->_getUserID()
                                          ]);

        if (count($itemData) == 0) {
            throw new Exception("Error : Fail to load item data (graph = ".$this->_getItemID()."; user = ".$this->_getUser()->_getUserID().")", 1);
        }
        $this->itemData = $itemData[0];
        return true;
    }

    /**
     * Get graph data by key
     * @param  String $key Key needed
     * @return String      Value associate to the key
     */
    public function _getValueData($key)
    {
        if (!array_key_exists($key, $this->itemData)) {
            throw new Exception("Error : Fail to load <".$key."> in item data", 1);
        }
        if (empty($this->itemData[$key]) || is_null($this->itemData[$key])) {
            return null;
        }
        return $this->itemData[$key];
    }

    /**
     * Get symbol associate to the graph
     * @return String   Symbol (ex : BTC)
     */
    public function _getSymbolItem()
    {
        return $this->_getValueData('symbol_top_list');
    }

    public function _getKeyGraph(){
       if(empty($this->_getValueData('container_top_list'))) return null;
       return $this->_getValueData('container_top_list');
    }

    public function _getCurrency(){
      return $this->_getValueData('currency_top_list');
    }

    public function _getMarket(){
      return $this->_getValueData('market_top_list');
    }

    /**
     * Get coin graph
     * @return CryptoCoin CryptoCoin associate to the graph
     */
    public function _getCoinItem()
    {
        return new CryptoCoin($this->_getCryptoApi(), $this->_getSymbolItem(), null, $this->_getApp());
    }

    /**
     * Change top list item container
     * @param  String $container New container
     */
    public function _changeContainer($container){
      $r = parent::execSqlRequest("UPDATE top_list_krypto SET container_top_list=NULL WHERE container_top_list=:container_top_list AND id_user=:id_user",
                                  [
                                    'container_top_list' => $container,
                                    'id_user' => $this->_getUser()->_getUserID()
                                  ]);

      if(!$r) throw new Exception("Error : Fail to update old top list item container", 1);

      $r = parent::execSqlRequest("UPDATE top_list_krypto SET container_top_list=:container_top_list WHERE id_user=:id_user AND id_top_list=:id_top_list",
                                  [
                                    'id_user' => $this->_getUser()->_getUserID(),
                                    'id_top_list' => $this->_getItemID(),
                                    'container_top_list' => $container
                                  ]);

      if(!$r) throw new Exception("Error : Fail to change top item container", 1);

    }

    /**
     * Change top list item symbol
     * @param  String $symbol New symbol
     */
    public function _changeSymbol($symbol){
      $r = parent::execSqlRequest("UPDATE top_list_krypto SET symbol_top_list=:symbol_top_list  WHERE id_user=:id_user AND id_top_list=:id_top_list",
                                  [
                                    'id_user' => $this->_getUser()->_getUserID(),
                                    'symbol_top_list' => $symbol,
                                    'id_top_list' => $this->_getItemID()
                                  ]);
      if(!$r) throw new Exception("Error : Fail to change symbol", 1);

    }

    public function _changeCurrency($currency){
      $r = parent::execSqlRequest("UPDATE top_list_krypto SET currency_top_list=:currency_top_list  WHERE id_user=:id_user AND id_top_list=:id_top_list",
                                  [
                                    'id_user' => $this->_getUser()->_getUserID(),
                                    'currency_top_list' => $currency,
                                    'id_top_list' => $this->_getItemID()
                                  ]);
      if(!$r) throw new Exception("Error : Fail to change currency", 1);
    }

    public function _changeMarket($market){
      $r = parent::execSqlRequest("UPDATE top_list_krypto SET market_top_list=:market_top_list  WHERE id_user=:id_user AND id_top_list=:id_top_list",
                                  [
                                    'id_user' => $this->_getUser()->_getUserID(),
                                    'market_top_list' => $market,
                                    'id_top_list' => $this->_getItemID()
                                  ]);
      if(!$r) throw new Exception("Error : Fail to change market", 1);
    }

    /**
     * Add new top list item
     * @param String $symbol
     * @return Int Create item ID
     */
    public function _addItem($symbol, $currency, $market){

      $controlKey = uniqid();

      $r = parent::execSqlRequest("INSERT INTO top_list_krypto (symbol_top_list, id_user, control_key_top_list, currency_top_list, market_top_list)
                                   VALUES (:symbol_top_list, :id_user, :control_key_top_list, :currency_top_list, :market_top_list)",
                                   [
                                     'symbol_top_list' => $symbol,
                                     'id_user' => $this->_getUser()->_getUserID(),
                                     'control_key_top_list' => $controlKey,
                                     'currency_top_list' => $currency,
                                     'market_top_list' => $market
                                   ]);

      if(!$r) throw new Exception("Error : Fail to add SQL new top item", 1);

      // Get data created item
      $g = parent::querySqlRequest("SELECT * FROM top_list_krypto WHERE control_key_top_list=:control_key_top_list AND id_user=:id_user",
                                    [
                                      'id_user' => $this->_getUser()->_getUserID(),
                                      'control_key_top_list' => $controlKey
                                    ]);

      if(count($g) == 0) throw new Exception("Error : Fail to fetch created top item", 1);


      return $g[0]['id_top_list'];

    }

    /**
     * Delete current item
     */
    public function _deleteItem(){
      $r = parent::execSqlRequest("DELETE FROM top_list_krypto WHERE id_top_list=:id_top_list AND id_user=:id_user",
                                  [
                                    'id_top_list' => $this->_getItemID(),
                                    'id_user' => $this->_getUser()->_getUserID()
                                  ]);

      if(!$r) throw new Exception("Error : Fail to delete top item", 1);
    }

    /**
     * Delete all item associate to the graph
     * @param  String $container Graph container
     */
    public function _deleteAll($container){

      $r = parent::execSqlRequest("DELETE FROM top_list_krypto WHERE container_top_list=:container_top_list AND id_user=:id_user",
                                  [
                                    'container_top_list' => $container,
                                    'id_user' => $this->_getUser()->_getUserID()
                                  ]);

      if(!$r) throw new Exception("Error : Fail to delete associate graph item", 1);


    }

}
