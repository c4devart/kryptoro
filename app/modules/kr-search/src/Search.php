<?php

class Search extends MySQL {

  private $App = null;

  private $SearchListQuery = null;

  public function __construct($App){
    $this->App = $App;
  }

  public function _getApp(){
    return $this->App;
  }

  public function _getServiceName(){

    return "service_cache".($this->_getApp()->_hiddenThirdpartyActive() && $this->_getApp()->_hiddenThirdpartyNotConfigured() ? "_native" : "");
  }

  public function _getFromCache(){
	  
    // if(isset($_SESSION['kr_search_engine']) && isset($_SESSION['kr_search_engine_cache']) && $_SESSION['kr_search_engine_cache'] > time()){
    //   $this->SearchListQuery = json_decode($_SESSION['kr_search_engine'], true);
    // }
    if(!is_null($this->SearchListQuery)) return $this->SearchListQuery;
    $r = parent::querySqlRequest("SELECT * FROM cache_krypto WHERE service_cache LIKE :service_cache",
                                [
                                  'service_cache' => $this->_getServiceName().'_%'
                                ]);


    $mainCache = [];
    if(count($r) > 0){
      foreach ($r as $key => $value) {
        $mainCache = array_merge($mainCache, json_decode($value['value_cache'], true));
      }
    }

    if(count($mainCache) == 0 || count($r) == 0 || $r[0]['last_update_cache'] < time()){
		
      $allElement = $this->_setInCache(count($r) > 0);
      $this->SearchListQuery = $allElement;
    } else {
      $this->SearchListQuery = $mainCache;
    }
    // $_SESSION['kr_search_engine'] = json_encode($this->SearchListQuery);
    // $_SESSION['kr_search_engine_cache'] = time() + 14400;
  }

  public function _setInCache($alreadyexist = false){
    $allElement = $this->_getAllElement();
    $allElementDB = array_chunk($allElement, 200);
    $r = parent::execSqlRequest("DELETE FROM cache_krypto WHERE service_cache LIKE :service_cache",
                                [
                                  'service_cache' => $this->_getServiceName().'_%'
                                ]);

    foreach ($allElementDB as $key => $value) {
      $r = parent::execSqlRequest("INSERT INTO cache_krypto (service_cache, value_cache, last_update_cache) VALUES (:service_cache, :value_cache, :last_update_cache)",
                                  [
                                    'service_cache' => $this->_getServiceName().'_'.$key,
                                    'value_cache' => json_encode($value),
                                    'last_update_cache' => time() + 14400
                                  ]);
    }

    return $allElement;
  }

  public function _getAllElement(){
    $search = "";
    if($this->_getApp()->_hiddenThirdpartyActive() && $this->_getApp()->_hiddenThirdpartyNotConfigured()){
		
		
      $marketConditional = "";

      $orderCase = "";
      $i = 0;
      foreach ($this->_getApp()->_hiddenThirdpartyServiceCfg() as $market => $value) {
        if(strtoupper($market) == "GDAX") $market = "COINBASE";
        if(strtoupper($market) == "HITBTC2") $market = "HITBTC";
        if(strtoupper($market) == "CEX") $market = "CEXIO";
        $marketConditional .= ($marketConditional != "" ? " OR " : "")."`market_exchanges` LIKE '".strtoupper($market)."'";
        $orderCase .= ", '".strtoupper($market)."'";
        $i++;
      }
	

      $r = parent::querySqlRequest("SELECT market_exchanges, symbol_exchanges, currency_exchanges,
                                    (
                                      SELECT coinname_coinlist
                                      FROM coinlist_krypto
                                      WHERE coinlist_krypto.symbol_coinlist = exchanges_krypto.symbol_exchanges
                                    ) AS symbol_longname,
                                    (
                                      SELECT name_currency
                                      FROM currency_krypto
                                      WHERE currency_krypto.code_iso_currency = exchanges_krypto.currency_exchanges
                                    ) AS currency_longname
                                    FROM exchanges_krypto
                                    WHERE (".$marketConditional.")
                                    AND (EXISTS (SELECT id_coinlist FROM coinlist_krypto WHERE coinlist_krypto.symbol_coinlist=exchanges_krypto.symbol_exchanges)
                                        OR EXISTS (SELECT id_currency FROM currency_krypto WHERE currency_krypto.code_iso_currency=exchanges_krypto.symbol_exchanges))
                                    AND (EXISTS (SELECT id_coinlist FROM coinlist_krypto WHERE coinlist_krypto.symbol_coinlist=exchanges_krypto.currency_exchanges)
                                        OR EXISTS (SELECT id_currency FROM currency_krypto WHERE currency_krypto.code_iso_currency=exchanges_krypto.currency_exchanges))
                                    "."ORDER BY FIELD(market_exchanges".$orderCase.")",
                                  [
                                    'query' => '%'.$search.'%'
                                  ]);

    } else {
		
	
      $r = parent::querySqlRequest("SELECT market_exchanges, symbol_exchanges, currency_exchanges,
                                    (
                                      SELECT coinname_coinlist
                                      FROM coinlist_krypto
                                      WHERE coinlist_krypto.symbol_coinlist = exchanges_krypto.symbol_exchanges
                                      LIMIT 1
                                    ) AS symbol_longname,
                                    (
                                      SELECT name_currency
                                      FROM currency_krypto
                                      WHERE currency_krypto.code_iso_currency = exchanges_krypto.currency_exchanges
                                      LIMIT 1
                                    ) AS currency_longname
                                    FROM exchanges_krypto");

    }

    foreach ($r as $key => $value) {
      $value['currency_crypto_longname'] = $value['symbol_longname'];
      $value['order_ratio'] = 1;
      $r[$key] = $value;
    }

    return $r;

  }


  public function _query($search, $limit = 250){
    $resQuery = [];
    $alreadyFetched = [];
    foreach ($this->_getFromCache() as $key => $value) {
      if(count($alreadyFetched) >= 300) break;
      if(in_array($value['market_exchanges'].":".$value['symbol_exchanges'].$value['currency_exchanges'], $alreadyFetched)) continue;
      if(strpos(strtoupper($value['market_exchanges'].":".$value['symbol_exchanges'].$value['currency_exchanges']), strtoupper($search)) === false) continue;
      $alreadyFetched[] = $value['market_exchanges'].":".$value['symbol_exchanges'].$value['currency_exchanges'];
      $resQuery[] = $value;
    }
    return array_slice($resQuery, 0, 300);
  }

}

?>
