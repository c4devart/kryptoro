<?php

/**
 * DashboardGraph class
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
class DashboardGraph extends MySQL
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
    private $graphID = null;

    /**
     * Graph Data
     * @var Array
     */
    private $graphData = null;

    /**
     * Associate top item
     * @var DashboardTopList
     */
    private $associateTopItem = null;

    private $App = null;

    /**
     * Dashboard graph constructor
     * @param CryptoApi $CryptoApi CryptoApi object
     * @param User $user           User object
     * @param Int $graphID         Graph ID object
     * @param Array $graphData     Graph data
     */
    public function __construct($CryptoApi, $user, $graphID = null, $graphData = null, $App = null)
    {
        $this->App = $App;
        $this->CryptoApi = $CryptoApi;
        $this->user = $user;
        $this->graphID = $graphID;
        if (is_null($graphData)) {
            $this->_loadGraphData();
        } else {
            $this->graphData = $graphData;
        }
    }

    public function _loadGraphByKey($key){
      $r = parent::querySqlRequest("SELECT * FROM graph_krypto WHERE key_graph=:key_graph AND id_user=:id_user",
                                  [
                                    'key_graph' => $key,
                                    'id_user' => $this->_getUser()->_getUserID()
                                  ]);
      if(count($r) == 0) throw new Exception("Error : Fail to load graph (".$key.")", 1);
      $this->graphID = $r[0]['id_graph'];

      $this->_loadGraphData();

      return true;

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
    public function _getGraphID($encrypted = false)
    {
        if($encrypted) return App::encrypt_decrypt('encrypt', $this->graphID);
        return $this->graphID;
    }


    /**
     * Get associate top item to graph
     * @return DashboardTopList Item top
     */
    public function _getAssociateItem(){

      $r = parent::querySqlRequest("SELECT * FROM top_list_krypto WHERE container_top_list=:container_top_list AND id_user=:id_user",
                                  [
                                    'container_top_list' => $this->_getKeyGraph(),
                                    'id_user' => $this->_getUser()->_getUserID()
                                  ]);

      if(count($r) == 0) return null;
      else $this->associateTopItem = new DashboardTopList($this->_getCryptoApi(), $this->_getUser(), $r[0]['id_top_list'], $r[0]);

      return $this->associateTopItem;

    }

    /**
     * Load graph data
     */
    public function _loadGraphData()
    {
        $graphData = parent::querySqlRequest("SELECT * FROM graph_krypto WHERE id_graph=:id_graph AND id_user=:id_user",
                                          [
                                            'id_graph' => $this->_getGraphID(),
                                            'id_user' => $this->_getUser()->_getUserID()
                                          ]);

        if (count($graphData) == 0) {
            throw new Exception("Error : Fail to load data graph (graph = ".$this->_getGraphID()."; user = ".$this->_getUser()->_getUserID().")", 1);
        }
        $this->graphData = $graphData[0];
        return true;
    }

    /**
     * Get graph data by key
     * @param  String $key Key needed
     * @return String      Value associate to the key
     */
    public function _getValueData($key)
    {
        if (!array_key_exists($key, $this->graphData)) {
            throw new Exception("Error : Fail to load <".$key."> in graph data", 1);
        }
        if (empty($this->graphData[$key]) || is_null($this->graphData[$key])) {
            return null;
        }
        return $this->graphData[$key];
    }

    /**
     * Get coin graph
     * @return CryptoCoin CryptoCoin associate to the graph
     */
    public function _getCoinGraph()
    {
        return new CryptoCoin($this->_getCryptoApi(), $this->_getSymbolGraph(), null, $this->_getApp());
    }

    /**
     * Get key graph
     * @return String Key container graph
     */
    public function _getKeyGraph()
    {
        return $this->_getValueData('key_graph');
    }

    public function _getTypeGraph(){
      return $this->_getValueData('type_graph');
    }

    public function _changeGraphType($type){
      if($type != "candlestick" && $type != "line") $type = "candlestick";
      $r = parent::execSqlRequest("UPDATE graph_krypto SET type_graph=:type_graph WHERE id_graph=:id_graph",
                                  [
                                    'type_graph' => $type,
                                    'id_graph' => $this->_getGraphID()
                                  ]);
      if(!$r) throw new Exception("Error SQL : Fail to change type graph (".$this->_getGraphID.")", 1);
      return true;
    }

    /**
     * Get if graph is enabled
     * @return Boolean
     */
    public function _isEnable()
    {
        return $this->_getAssociateItem() != null;
    }

    /**
     * Attribute new container to the graph
     * @param  String $container New container ID
     */
    public function _attributeNewContainer($container)
    {
        $this->graphData['key_graph'] = $container;
        $r = parent::execSqlRequest("UPDATE graph_krypto SET key_graph=:key_graph, status_graph=0 WHERE id_graph=:id_graph AND id_user=:id_user",
                                  [
                                    'key_graph' => $container,
                                    'id_graph' => $this->_getGraphID(),
                                    'id_user' => $this->_getUser()->_getUserID()
                                  ]);
        if (!$r) {
            throw new Exception("Error : Fail to update graph container in SQL", 1);
        }
        return true;
    }

    /**
     * Toggle graph enabled
     * @param  Int $status   New graph status
     */
    public function _toggleEnabled($status)
    {
        $r = parent::execSqlRequest("UPDATE graph_krypto SET status_graph=:nstatus WHERE id_graph=:id_graph AND id_user=:id_user",
                                  [
                                    'id_graph' => $this->_getGraphID(),
                                    'nstatus' => $status,
                                    'id_user' => $this->_getUser()->_getUserID()
                                  ]);

        if (!$r) {
            throw new Exception("Error : Fail to change status graph in SQL", 1);
        }
        return true;
    }
}
