<?php

/**
 * Dashboard class
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
class Dashboard extends MySQL
{

    /**
     * User object
     * @var User
     */
    private $user = null;

    /**
     * CryptoApi object
     * @var CryptoApi
     */
    private $CryptoApi = null;

    /**
     * Dashboard data
     * @var Array
     */
    private $dataDashboard = null;

    /**
     * New dashboard
     * @var Boolean
     */
    private $isNew = false;

    private $App = null;

    /**
     * Dashboard constructor
     * @param CryptoApi $CryptoApi CryptoApi Object
     * @param User $user           User object
     */
    public function __construct($CryptoApi, $user, $App = null)
    {
        $this->user = $user;
        $this->CryptoApi = $CryptoApi;
        $this->App = $App;

        // If can't load dashboard -> init dashboard
        if (!$this->_loadDashboard()) {
            $this->_initDashboard();
        }
    }

    /**
     * Get user object
     * @return User User object associate to the dashboard
     */
    public function _getUser()
    {
        if (is_null($this->user)) {
            throw new Exception("Error : User is null on Dashboard action", 1);
        }
        return $this->user;
    }

    public function _getApp(){
      return $this->App;
    }

    /**
     * Get CryptoApi object
     * @return CryptoApi CryptoApi object associate to the dashboard
     */
    public function _getCryptoApi()
    {
      if (is_null($this->CryptoApi)) {
          throw new Exception("Error : CryptoApi is null on Dashboard action", 1);
      }
      return $this->CryptoApi;
    }

    /**
     * Load dashboard data
     * @return Boolea
     */
    public function _loadDashboard()
    {
        $data = parent::querySqlRequest("SELECT * FROM dashboard_krypto WHERE id_user=:id_user", ['id_user' => $this->_getUser()->_getUserID()]);
        if (count($data) == 0) {
            return false;
        }
        $this->dataDashboard = $data[0];
        return true;
    }

    /**
     * Init new dashboard
     */
    public function _initDashboard()
    {

        $this->isNew = true;

        // Init app object
        $App = new App(true);

        // Insert new dashboard
        $r = parent::execSqlRequest("INSERT INTO dashboard_krypto (id_user, num_graph_dashboard) VALUES
                                                (:id_user, :num_graph_dashboard)",
                                                [
                                                  'id_user' => $this->_getUser()->_getUserID(),
                                                  'num_graph_dashboard' => $App->_getDefaultDashboardNum()
                                                ]);
        // Check dashboard insertion
        if(!$r) throw new Exception("Error : Fail to create new dashboard", 1);

        // Init dashboard associate element
        $nGraph = explode('_', $App->_getDefaultDashboardNum())[0];
        for ($i=0; $i < intval($nGraph); $i++) {
          $r = parent::execSqlRequest("INSERT INTO graph_krypto (id_user, key_graph)
                                  VALUES (:id_user, :key_graph)",
                                  [
                                    'id_user' => $this->_getUser()->_getUserID(),
                                    'key_graph' => uniqid(),
                                  ]);
        }

        if (!$r) {
            throw new Exception("Error : Unable to create dashboard", 1);
        }

        // Try to load dashboard data
        if (!$this->_loadDashboard()) {
            throw new Exception("Error : Unable to load created dashboard", 1);
        }

    }

    /**
     * Get dashboard data value by key
     * @param  String $key Key needed
     * @return String      Data value associate to the key
     */
    public function _getValueData($key)
    {
        if (!array_key_exists($key, $this->dataDashboard)) {
            throw new Exception("Error : Fail to load <".$key."> in dashboard data", 1);
        }
        if (empty($this->dataDashboard[$key]) || is_null($this->dataDashboard[$key])) {
            return null;
        }
        return $this->dataDashboard[$key];
    }

    /**
     * Get number graph dashboard
     * @return Int Number graph
     */
    public function _getNumGraph($withmobile = true)
    {
        // Test if device is an mobile
        $Mobile = new Mobile_Detect();
        if ($Mobile->isMobile() && $withmobile) {
            return 1;
        }
        return explode('_', $this->_getValueData('num_graph_dashboard'))[0];
    }

    /**
     * Get number graph active
     * @return Int Number graph
     */
    public function _countActiveGraph(){
      $r = parent::querySqlRequest("SELECT * FROM graph_krypto WHERE id_user=:id_user",
                                    [
                                      'id_user' => $this->_getUser()->_getUserID()
                                    ]);
      return count($r);
    }

    /**
     * Get dashboard type
     * @return String Dashboard type
     */
    public function _getGraphPos()
    {
        $Mobile = new Mobile_Detect();
        if ($Mobile->isMobile()) {
            return '1_single';
        }
        return $this->_getValueData('num_graph_dashboard');
    }

    /**
     * Get dashboard list graph
     * @param  Boolean $naturalorder Natural order graph
     * @return Array                 DashboardGraph array list
     */
    public function _getDashboardGraphList()
    {

        $graph = parent::querySqlRequest("SELECT * FROM graph_krypto WHERE id_user=:id_user ORDER BY id_graph", ['id_user' => $this->_getUser()->_getUserID()]);
        $graphList = [];
        foreach ($graph as $graphData) {
            $graphList[] = new DashboardGraph($this->_getCryptoApi(), $this->_getUser(), $graphData['id_graph'], $graphData, $this->_getApp());
        }
        return $graphList;
    }

    /**
     * Get all dashboard type available
     * @return Array Dashboard type
     */
    public function _getListDashboardAvailable()
    {
        return [
          '1_single',
          '2_h',
          '2_v',
          '3_tm_ll',
          '3_tl_lm',
          '4_grid',
          '4_tl_lm',
          '4_tm_ll',
          '6_grid'
        ];
    }

    /**
     * Change dashboard type
     * @param  String $format New type (ex : 2_h)
     */
    public function _changeDashboardType($format)
    {
        if (!in_array($format, $this->_getListDashboardAvailable())) {
            return false;
        }


        $this->dataDashboard['num_graph_dashboard'] = $format;
        $r = parent::execSqlRequest("UPDATE dashboard_krypto SET num_graph_dashboard=:num_graph_dashboard WHERE id_user=:id_user",
                                [
                                  'num_graph_dashboard' => $this->_getGraphPos(),
                                  'id_user' => $this->_getUser()->_getUserID()
                                ]);
        if(!$r) throw new Exception("Error : Fail to change dashboard type", 1);



        if($this->_countActiveGraph() > $this->_getNumGraph()){

          $listGraph = parent::querySqlRequest("SELECT * FROM graph_krypto WHERE id_user=:id_user", ['id_user' => $this->_getUser()->_getUserID()]);
          //
          foreach (array_slice($listGraph, $this->_getNumGraph()) as $key => $value) {

            $r = parent::execSqlRequest("DELETE FROM graph_krypto WHERE id_graph=:id_graph AND id_user=:id_user",
                                        [
                                          'id_user' => $this->_getUser()->_getUserID(),
                                          'id_graph' => $value['id_graph']
                                        ]);

            if(!$r) throw new Exception("Error : Fail to update graph container", 1);

            $r = parent::execSqlRequest("UPDATE top_list_krypto SET container_top_list=NULL WHERE id_user=:id_user AND container_top_list=:container_top_list",
                                        [
                                          'id_user' => $this->_getUser()->_getUserID(),
                                          'container_top_list' => $value['key_graph']
                                        ]);

            if(!$r) throw new Exception("Error : Fail to update top list item after delete graph", 1);

          }

        } else {

          for ($i=$this->_countActiveGraph(); $i < $this->_getNumGraph(); $i++) {

            $r = parent::execSqlRequest("INSERT INTO graph_krypto (id_user, key_graph) VALUES (:id_user, :key_graph)",
                                        [
                                          'id_user' => $this->_getUser()->_getUserID(),
                                          'key_graph' => uniqid()
                                        ]);

            if(!$r) throw new Exception("Error : Fail to append new graph", 1);

          }

        }

        $this->_loadDashboard();

    }


    /**
     * Get list currency available
     * @param  Int $max        Maximum currency fetched
     * @param  String  $query  Query currency searched
     */
    public function _getListCurrency($max = 14, $query = null)
    {
        return parent::querySqlRequest("SELECT *, (SELECT count(*) FROM user_krypto WHERE currency_user=currency_krypto.code_iso_currency) as num_user_currency FROM currency_krypto WHERE name_currency LIKE :querys OR code_iso_currency LIKE :querys ORDER BY num_user_currency DESC, name_currency ASC LIMIT ".$max,
                                  [
                                    'querys' => '%'.$query.'%'
                                  ]);
    }


    /**
     * Get top list item
     * @return Array DashboardTopList array
     */
    public function _getTopList(){

      $toplist = parent::querySqlRequest("SELECT * FROM top_list_krypto WHERE id_user=:id_user ORDER BY id_top_list", ['id_user' => $this->_getUser()->_getUserID()]);

      $toplistres = [];
      foreach ($toplist as $toplistData) {
          $toplistres[] = new DashboardTopList($this->_getCryptoApi(), $this->_getUser(), $toplistData['id_top_list'], $toplistData);
      }
      return $toplistres;
    }

    /**
     * If dashboard is just init
     * @return Boolean
     */
    public function _isNew(){
      return $this->isNew;
    }

}
