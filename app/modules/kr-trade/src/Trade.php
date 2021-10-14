<?php

class Trade extends MySQL {

  private $User = null;
  private $App = null;

  private $ThirdParty = null;

  private $listThirdparty = [
    'binance',
    //'bitstamp',
    'gdax',
    'gemini',
    'hitbtc2',
    //'bitfinex2',
    'kraken',
    'kucoin',
    'poloniex',
    'bittrex',
    'cex',
    'yobit',
    //'okex',
    //'gateio',
    //'coinspot',
    'exmo',
    //'quoinex',
    'livecoin',
    'btcmarkets',
    'coinex',
    'luno',
    'bitbank',
    'bitmex',
    'okcoinusd'
    //'ethfinex'
  ];

  private $selectedThirdparty = 'none';

  public function __construct($User, $App){

    $this->User = $User;
    $this->App = $App;

  }

  public function _getUser(){ return $this->User; }
  public function _getApp(){ return $this->App; }

  public function _getThirdPartyConfig(){
    return [
      'gdax' => [
        'key_gdax' => 'GDAX Key',
        'pass_gdax' => 'GDAX Pass',
        'secret_gdax' => 'GDAX Secret',
        'sandbox' => 'live_gdax'
      ],
     /* 'binance' => [
        'key_binance' => 'Binance Key',
        'secret_binance' => 'Binance Secret',
        'sandbox' => null
      ],*/
	   'binance' => [
        'key_binance' => 'Binance Key',
        'secret_binance' => 'Binance Secret',
        'sandbox' => null
      ],
      // 'bitstamp' => [
      //   'key_bitstamp' => 'Bitstamp Key',
      //   'secret_bitstamp' => 'Bitstamp Secret',
      //   'uid_bitstamp' => 'Bitstamp UID',
      //   'sandbox' => null
      // ],
      'gemini' => [
        'key_gemini' => 'Gemini Key',
        'secret_gemini' => 'Gemini Secret',
        'sandbox' => 'live_gemini'
      ],
      'kraken' => [
        'key_kraken' => 'Kraken Key',
        'private_kraken' => 'Kraken Private',
        'sandbox' => null
      ],
      'kucoin' => [
        'key_kucoin' => 'Kucoin Key',
        'secret_kucoin' => 'Kucoin Private',
        'sandbox' => null
      ],
      'bittrex' => [
        'api_key_bittrex' => 'Bittrex Key',
        'api_secret_bittrex' => 'Bittrex Secret',
        'sandbox' => null
      ],
      'cex' => [
        'key_cex' => 'CEX Key',
        'secret_cex' => 'CEX Secret',
        'uid_cex' => 'CEX UID',
        'sandbox' => null
      ],
      'poloniex' => [
        'key_poloniex' => 'Poloniex Key',
        'secret_poloniex' => 'Poloniex Secret',
        'sandbox' => null
      ],
      'hitbtc2' => [
        'key_hitbtc2' => 'Hitbtc Key',
        'secret_hitbtc2' => 'Hitbtc Secret',
        'sandbox' => null
      ],
      'yobit' => [
        'key_yobit' => 'YoBit Key',
        'secret_yobit' => 'YoBit Secret',
        'sandbox' => null
      ],
      // 'okex' => [
      //   'key_okex' => 'YoBit Key',
      //   'secret_okex' => 'YoBit Secret',
      //   'sandbox' => null
      // ],
      // 'gateio' => [
      //   'key_gateio' => 'GateIo Key',
      //   'secret_gateio' => 'GateIo Secret',
      //   'sandbox' => null
      // ],
      // 'coinspot' => [
      //   'key_coinspot' => 'Coinspot Key',
      //   'secret_coinspot' => 'Coinspot Secret',
      //   'sandbox' => null
      // ],
      'exmo' => [
        'key_exmo' => 'Exmo Key',
        'secret_exmo' => 'Exmo Secret',
        'sandbox' => null
      ],
      // 'quoinex' => [
      //   'key_quoinex' => 'Quoinex Token ID',
      //   'secret_quoinex' => 'Quoinex Secret',
      //   'sandbox' => null
      // ],
      'livecoin' => [
        'key_livecoin' => 'Livecoin Key',
        'secret_livecoin' => 'Livecoin Secret',
        'sandbox' => null
      ],
      'btcmarket' => [
        'key_btcmarket' => 'Btcmarket Key',
        'secret_btcmarket' => 'Btcmarket Secret',
        'sandbox' => null
      ],
      'coinex' => [
        'key_coinex' => 'Coinex Key',
        'secret_coinex' => 'Coinex Secret',
        'sandbox' => null
      ],
      'luno' => [
        'key_luno' => 'Luno Key ID',
        'secret_luno' => 'Luno Secret',
        'sandbox' => null
      ],
      'okcoinusd' => [
        'key_okcoinusd' => 'OKCoin Key',
        'secret_okcoinusd' => 'OKCoin Secret',
        'sandbox' => null
      ],
      'bitmex' => [
        'key_bitmex' => 'Bitmex ID',
        'secret_bitmex' => 'Bitmex Secret',
        'sandbox' => null
      ],
      'bitbank' => [
        'key_bitbank' => 'Bitbank Key',
        'secret_bitbank' => 'Bitbank Secret',
        'sandbox' => null
      ]
    ];
  }

  public function _getThirdParty($params = null, $exchange = null){

    if(!is_null($this->ThirdParty) && is_null($params)) return $this->ThirdParty;

    $this->ThirdParty = [
      'binance' => new Binance($this->_getUser(), $this->_getApp(), $params),
      //'bitstamp' => new Bitstamp($this->_getUser(), $this->_getApp()),
      'gdax' => new Gdax($this->_getUser(), $this->_getApp(), $params),
      'gemini' => new Gemini($this->_getUser(), $this->_getApp(), $params),
      //'bitfinex2' => new Bitfinex($this->_getUser(), $this->_getApp()),
      'hitbtc2' => new Hitbtc($this->_getUser(), $this->_getApp(), $params),
      'kraken' => new Kraken($this->_getUser(), $this->_getApp(), $params),
      'kucoin' => new Kucoin($this->_getUser(), $this->_getApp(), $params),
      'poloniex' => new Poloniex($this->_getUser(), $this->_getApp(), $params),
      'bittrex' => new Bittrex($this->_getUser(), $this->_getApp(), $params),
      'cex' => new Cex($this->_getUser(), $this->_getApp(), $params),
      'yobit' => new Yobit($this->_getUser(), $this->_getApp(), $params),
      //'okex' => new Okex($this->_getUser(), $this->_getApp(), $params),
      //'gateio' => new Gateio($this->_getUser(), $this->_getApp(), $params),
      //'coinspot' => new Coinspot($this->_getUser(), $this->_getApp(), $params),
      'exmo' => new Exmo($this->_getUser(), $this->_getApp(), $params),
      //'quoinex' => new Quoinex($this->_getUser(), $this->_getApp(), $params),
      'livecoin' => new Livecoin($this->_getUser(), $this->_getApp(), $params),
      'btcmarket' => new Btcmarket($this->_getUser(), $this->_getApp(), $params),
      'coinex' => new Coinex($this->_getUser(), $this->_getApp(), $params),
      'luno' => new Luno($this->_getUser(), $this->_getApp(), $params),
      'okcoinusd' => new Okcoinusd($this->_getUser(), $this->_getApp(), $params),
      'bitmex' => new Bitmex($this->_getUser(), $this->_getApp(), $params),
      'bitbank' => new Bitbank($this->_getUser(), $this->_getApp(), $params)
      //'ethfinex' => new Ethfinex($this->_getUser(), $this->_getApp())
    ];

    return $this->ThirdParty;

  }

  public function _syncListCrypto(){
    echo '<pre>';
    foreach ($this->listThirdparty as $exchangeName) {
      $exchange = '\\ccxt\\' . $exchangeName;
      $exchange = new $exchange ();
      var_dump($exchangeName);
      foreach ($exchange->fetch_markets() as $ksymbol => $pair) {

        $r = parent::execSqlRequest("INSERT INTO thirdparty_crypto_krypto (symbol_thirdparty_crypto, to_thirdparty_crypto, name_thirdparty_crypto, min_thirdparty_crypto, max_thirdparty_crypto, active_thirdparty_crypto, precision_amount_thirdparty_crypto)
                                    VALUES (:symbol_thirdparty_crypto, :to_thirdparty_crypto, :name_thirdparty_crypto, :min_thirdparty_crypto, :max_thirdparty_crypto, :active_thirdparty_crypto, :precision_amount_thirdparty_crypto)",
                                    [
                                      'symbol_thirdparty_crypto' => strtoupper($pair['base']),
                                      'to_thirdparty_crypto' => strtoupper($pair['quote']),
                                      'name_thirdparty_crypto' => strtolower($exchangeName),
                                      'min_thirdparty_crypto' => (!array_key_exists('limits', $pair) ||
                                                                  !array_key_exists('amount', $pair['limits']) ||
                                                                  !array_key_exists('min', $pair['limits']['amount']) ||
                                                                  is_null($pair['limits']['amount']['min']) ? 0.001 : $pair['limits']['amount']['min']),
                                      'max_thirdparty_crypto' => (!array_key_exists('limits', $pair) ||
                                                                  !array_key_exists('amount', $pair['limits']) ||
                                                                  !array_key_exists('max', $pair['limits']['amount']) ||
                                                                  is_null($pair['limits']['amount']['max']) ? 0.001 : $pair['limits']['amount']['max']),
                                      'active_thirdparty_crypto' => (!array_key_exists('active', $pair) ? true : $pair['active']),
                                      'precision_amount_thirdparty_crypto' => (!array_key_exists('precision', $pair) || !array_key_exists('amount', $pair['precision']) || is_null($pair['precision']['amount']) ? 3 : abs($pair['precision']['amount']))
                                    ]);


      }
    }

  }

  public function _sortThirdpartyListSymbolSelected($a, $b){
    //error_log($a->_getExchangeName().' - '.$this->_getNameSelectedThirdPartyUser());
    if($a->_getExchangeName() == $this->_getNameSelectedThirdPartyUser()) return -1;
    return 1;
  }

  public function _thirdparySymbolTrading($from, $to, $market = null){

    if(strtoupper($market) == "HITBTC") $market = "hitbtc2";



    if(is_null($market)){
      $r = parent::querySqlRequest("SELECT * FROM thirdparty_crypto_krypto WHERE symbol_thirdparty_crypto=:symbol_thirdparty_crypto AND to_thirdparty_crypto=:to_thirdparty_crypto",
                                   [
                                     'symbol_thirdparty_crypto' => $from,
                                     'to_thirdparty_crypto' => $to
                                   ]);
    } else {
      $r = parent::querySqlRequest("SELECT * FROM thirdparty_crypto_krypto WHERE symbol_thirdparty_crypto=:symbol_thirdparty_crypto AND to_thirdparty_crypto=:to_thirdparty_crypto AND name_thirdparty_crypto=:name_thirdparty_crypto",
                                   [
                                     'symbol_thirdparty_crypto' => $from,
                                     'to_thirdparty_crypto' => $to,
                                     'name_thirdparty_crypto' => $market
                                   ]);
    }

    $listThirdparty = [];
    $listThirdpartyActivated = [];
    foreach ($r as $key => $value) {
      if(!array_key_exists($value['name_thirdparty_crypto'], $this->_getThirdParty())) continue;
      $tp = $this->_getThirdParty()[$value['name_thirdparty_crypto']];
      if($tp->_isActivated() != false) $listThirdpartyActivated[] = $tp;
      else $listThirdparty[] = $tp;
    }


    $listThirdpartyOrdered = [];

    usort($listThirdpartyActivated, array($this, '_sortThirdpartyListSymbolSelected'));

    foreach ($listThirdpartyActivated as $tp) { $listThirdpartyOrdered[] = $tp; }
    foreach ($listThirdparty as $tp) { $listThirdpartyOrdered[] = $tp; }

    return $listThirdpartyOrdered;

  }

  public function _getCoinOrderList(){
    $res = [];
    $listCoin = parent::querySqlRequest("SELECT * FROM coinlist_krypto  WHERE order_coinlist < 50 ORDER BY order_coinlist");
    $OrderCoin = [];
    $CoinListSum = [];
    foreach ($listCoin as $key => $value) {
      $OrderCoin[$value['symbol_coinlist']] = intval($value['order_coinlist']);
    }
    foreach ($OrderCoin as $key => $value) {
      foreach ($OrderCoin as $keyS => $valueS) {
        if($key == $keyS) continue;
        $CoinListSum["'".$key.''.$keyS."'"] = $OrderCoin[$key] + $OrderCoin[$keyS];
      }
    }
    asort($CoinListSum);
    return array_slice($CoinListSum, 0, 60);

  }

  public function _getMarketTradeAvailable($CryptoApi, $limit = 30, $search = null){
    $res = [];
    if($this->_getApp()->_hiddenThirdpartyActive()){
      $selectedQuery = "";
      $selectedOrderExchange = "";
      $selectedParams = [];
      foreach (array_keys($this->_getApp()->_hiddenThirdpartyServiceCfg()) as $key => $exchangeName) {
        $selectedQuery.= (strlen($selectedQuery) > 0 ? " OR " : "")." name_thirdparty_crypto = :name_thirdparty_crypto_".$exchangeName." ";
        $selectedParams["name_thirdparty_crypto_".$exchangeName] = strtolower($exchangeName);
        $selectedOrderExchange .= " '".strtolower($exchangeName)."',";
      }
      $selectedOrderExchange = substr($selectedOrderExchange, 0, -1);

      $orderInfos = "";
      foreach (array_keys($this->_getCoinOrderList()) as $key => $value) {
        $orderInfos .= " ".$value.",";
      }
      $orderInfos = substr($orderInfos, 0, -1);

      if(!is_null($search)){
        $selectedParams['query_search'] = '%'.$search.'%';
        $req = parent::querySqlRequest("SELECT symbol_thirdparty_crypto, to_thirdparty_crypto, name_thirdparty_crypto, min_thirdparty_crypto,
                                        max_thirdparty_crypto, active_thirdparty_crypto, precision_amount_thirdparty_crypto,
                                        CONCAT(symbol_thirdparty_crypto, to_thirdparty_crypto) as pair_thirdparty_f FROM thirdparty_crypto_krypto WHERE (".$selectedQuery.")
                                        AND (symbol_thirdparty_crypto LIKE :query_search OR to_thirdparty_crypto LIKE :query_search OR name_thirdparty_crypto LIKE :query_search
                                            OR CONCAT(symbol_thirdparty_crypto, to_thirdparty_crypto) LIKE :query_search OR CONCAT(symbol_thirdparty_crypto, '/', to_thirdparty_crypto) LIKE :query_search
                                            OR CONCAT(name_thirdparty_crypto, ':', symbol_thirdparty_crypto, '/', to_thirdparty_crypto) LIKE :query_search)
                                        ORDER BY FIELD(pair_thirdparty_f, ".$orderInfos.") DESC, FIELD(name_thirdparty_crypto, ".$selectedOrderExchange.") LIMIT ".$limit * 4, $selectedParams);
      } else {
        $req = parent::querySqlRequest("SELECT symbol_thirdparty_crypto, to_thirdparty_crypto, name_thirdparty_crypto, min_thirdparty_crypto,
                                        max_thirdparty_crypto, active_thirdparty_crypto, precision_amount_thirdparty_crypto,
                                        CONCAT(symbol_thirdparty_crypto, to_thirdparty_crypto) as pair_thirdparty_f FROM thirdparty_crypto_krypto WHERE ".$selectedQuery." ORDER BY FIELD(pair_thirdparty_f, ".$orderInfos.") DESC, FIELD(name_thirdparty_crypto, ".$selectedOrderExchange.") LIMIT ".$limit * 4, $selectedParams);
      }


      //error_log(json_encode($req));
    } else {

      if(is_null($search)){
        $req = parent::querySqlRequest("SELECT * FROM thirdparty_crypto_krypto WHERE name_thirdparty_crypto=:name_thirdparty_crypto LIMIT ".$limit,
                                      [
                                        'name_thirdparty_crypto' => $this->_getNameSelectedThirdPartyUser()
                                      ]);

        if(count($req) < $limit){
          $req = array_merge($req, parent::querySqlRequest("SELECT * FROM thirdparty_crypto_krypto LIMIT ".($limit - count($req))));
        }
      } else {
        $infosSearch = explode('/', $search);
        if(count($infosSearch) == 2){
          $req = parent::querySqlRequest("SELECT * FROM thirdparty_crypto_krypto WHERE symbol_thirdparty_crypto=:symbol_thirdparty_crypto AND to_thirdparty_crypto=:to_thirdparty_crypto LIMIT ".$limit,
                                        [
                                          'symbol_thirdparty_crypto' => $infosSearch[0],
                                          'to_thirdparty_crypto' => $infosSearch[1]
                                        ]);
        } else {
          $req = parent::querySqlRequest("SELECT * FROM thirdparty_crypto_krypto WHERE name_thirdparty_crypto=:search_query_second OR symbol_thirdparty_crypto=:search_query OR to_thirdparty_crypto=:search_query LIMIT ".$limit, [
            'search_query' => $search,
            'search_query_second' => strtolower($search)
          ]);
        }
      }

      //$req = parent::querySqlRequest("SELECT * FROM thirdparty_crypto_krypto LIMIT ".$limit);
    }


    $MarketCoinList = [];
    foreach ($req as $key => $dataMarket) {
      $CryptoApi->_setCurrency([$dataMarket['to_thirdparty_crypto'], $dataMarket['to_thirdparty_crypto']]);
      if(array_key_exists($dataMarket['symbol_thirdparty_crypto'].''.$dataMarket['to_thirdparty_crypto'], $res)) continue;
      try {
        $marketS = [];
        if(!array_key_exists($dataMarket['symbol_thirdparty_crypto'], $MarketCoinList)) $MarketCoinList[$dataMarket['symbol_thirdparty_crypto']] = new CryptoCoin($CryptoApi, $dataMarket['symbol_thirdparty_crypto'], null , strtolower($dataMarket['name_thirdparty_crypto']));
        $marketS['coin'] = $MarketCoinList[$dataMarket['symbol_thirdparty_crypto']];
        $marketS['market'] = $dataMarket;
        $marketS['cryptoapi'] = clone $CryptoApi;

        $res[$dataMarket['symbol_thirdparty_crypto'].''.$dataMarket['to_thirdparty_crypto']] = $marketS;
      } catch (Exception $e) {}

    }
    return $res;
  }

  public function _symbolAvailableTrading($from, $to, $market = null){
    return count($this->_thirdparySymbolTrading($from, $to, $market)) > 0;
  }

  public function _getListOrderSymbol($tp){
    $listSymbol = [];
    $r = parent::querySqlRequest("SELECT * FROM order_krypto WHERE id_user=:id_user AND thirdparty_order=:thirdparty_order",
                                [
                                  'id_user' => $this->_getUser()->_getUserID(),
                                  'thirdparty_order' => strtolower($tp)
                                ]);

    foreach ($r as $key => $value) {
      if(!in_array($value['symbol_order'].'/'.$value['currency_order'], $listSymbol)) $listSymbol[] = $value['symbol_order'].'/'.$value['currency_order'];
    }
    return $listSymbol;
  }

  public function _saveOrder($type, $amount, $symbol, $to, $tp){

    $r = parent::execSqlRequest("INSERT INTO order_krypto (id_user, time_order, type_order, amount_order, symbol_order, currency_order, thirdparty_order)
                                 VALUES (:id_user, :time_order, :type_order, :amount_order, :symbol_order, :currency_order, :thirdparty_order)",
                                 [
                                   'id_user' => $this->_getUser()->_getUserID(),
                                   'time_order' => date('d/m/Y H:i:00', time()),
                                   'type_order' => strtoupper($type),
                                   'amount_order' => $amount,
                                   'symbol_order' => $symbol,
                                   'currency_order' => $to,
                                   'thirdparty_order' => $tp
                                 ]);

      if(!$r) throw new Exception("Error SQL : Fail to add order in sql", 1);

      return true;

  }

  public function _getNameSelectedThirdPartyUser(){
    if($this->selectedThirdparty != 'none') return $this->selectedThirdparty;
    $r = parent::querySqlRequest("SELECT * FROM user_thirdparty_selected_krypto WHERE id_user=:id_user", ['id_user' => $this->_getUser()->_getUserID()]);
    if(count($r) == 0) $this->selectedThirdparty = null;
    else $this->selectedThirdparty = $r[0]['name_user_thirdparty_selected'];
    return $this->selectedThirdparty;
  }

  public function _sortListThirdpartyAvailable($a, $b){
    if($this->_getNameSelectedThirdPartyUser() == $a->_getExchangeName()) return -1;
    return 1;
  }

  public function _getThirdPartyListAvailable(){
    $r = [];
    foreach ($this->_getThirdParty() as $Thirdparty) {
      if($Thirdparty->_isActivated()) $r[] = $Thirdparty;
    }
    usort($r, array($this, '_sortListThirdpartyAvailable'));
    return $r;
  }

  public function _getExchange($exchange){
    $exchange = strtolower($exchange);
    if($exchange == "cexio") $exchange = "cex";

    if(!array_key_exists($exchange, $this->_getThirdParty())) return null;
    return $this->_getThirdParty()[$exchange];
  }

  public function _getSelectedThirdparty(){
    $nameSelected = $this->_getNameSelectedThirdPartyUser();
    if(is_null($nameSelected)) return null;
    return $this->_getExchange($nameSelected);
  }

  public function _getInternalOrderList($symbol){

    return parent::querySqlRequest("SELECT * FROM internal_order_krypto WHERE symbol_internal_order=:symbol_internal_order AND date_internal_order > :date_internal_order",
                                  [
                                    'symbol_internal_order' => $symbol,
                                    'date_internal_order' => (time() - 86400)
                                  ]);

  }

  public function _getLeaderBoard(){
    $res = [];
    $rank = 1;
    foreach (parent::querySqlRequest("SELECT * FROM leader_board_krypto ORDER BY benef_leader_board DESC") as $key => $value) {

      $UserRank = new User($value['id_user']);

      $res[] = [
        'id_user' => $UserRank->_getUserID(),
        'benefic' => $value['benef_leader_board'],
        'rank' => $rank,
        'name' => $UserRank->_getName(),
        'country' => $UserRank->_getUserLocation(true)
      ];
      $rank++;
    }
    return $res;
  }

  public function _getLeaderBoardUser($User){
    $rank = 1;
    foreach (parent::querySqlRequest("SELECT * FROM leader_board_krypto ORDER BY benef_leader_board DESC") as $key => $value) {
      if($value['id_user'] == $User->_getUserID()){
        $value['rank'] = $rank;
        return $value;
        break;
      }
      $rank++;
    }
  }

  public function _saveThirdpartySettings($exchange, $rstring, $rargstring, $args, $updateString){
    $r = parent::querySqlRequest("SELECT * FROM ".$exchange."_krypto WHERE id_user=:id_user", ['id_user' => $this->_getUser()->_getUserID()]);
    if(count($r) == 0){
      $r = parent::execSqlRequest("INSERT INTO ".$exchange."_krypto (".$rstring.") VALUES (".$rargstring.")", $args);
      if(!$r) throw new Exception("Error : Fail to save ".$exchange, 1);
    } else {
      $r = parent::execSqlRequest("UPDATE ".$exchange."_krypto SET ".$updateString." WHERE id_user=:id_user", $args);
      if(!$r) throw new Exception("Error : Fail to update ".$exchange, 1);
    }
    return true;

  }

  public function _removeThirdparty($Exchange){
    if(!array_key_exists($Exchange->_getExchangeName(), $this->_getThirdPartyConfig())) throw new Exception("Permission denied", 1);
    $r = parent::execSqlRequest("DELETE FROM ".$Exchange->_getExchangeName().'_krypto WHERE id_user=:id_user',
                                [
                                  'id_user' => $this->_getUser()->_getUserID()
                                ]);
    if(!$r) throw new Exception("Error : Fail to delete", 1);
    if($this->_getNameSelectedThirdPartyUser() == $Exchange->_getExchangeName()){
      $r = parent::execSqlRequest("DELETE FROM user_thirdparty_selected_krypto WHERE id_user=:id_user",
                                  [
                                    'id_user' => $this->_getUser()->_getUserID()
                                  ]);
      if(!$r) throw new Exception("Error : Fail to remove seletect thirdparty", 1);

    }
    return true;
  }

  public function _changeFirstExchange($exchange){
    $r = parent::querySqlRequest("SELECT * FROM user_thirdparty_selected_krypto WHERE id_user=:id_user", ['id_user' => $this->_getUser()->_getUserID()]);
    if(count($r) > 0){
      $r = parent::execSqlRequest("UPDATE user_thirdparty_selected_krypto SET name_user_thirdparty_selected=:name_user_thirdparty_selected WHERE id_user=:id_user",
                                  [
                                    'id_user' => $this->_getUser()->_getUserID(),
                                    'name_user_thirdparty_selected' => $exchange->_getExchangeName()
                                  ]);
    } else {
      $r = parent::execSqlRequest("INSERT INTO user_thirdparty_selected_krypto (id_user, name_user_thirdparty_selected) VALUES (:id_user, :name_user_thirdparty_selected)",
                                  [
                                    'id_user' => $this->_getUser()->_getUserID(),
                                    'name_user_thirdparty_selected' => $exchange->_getExchangeName()
                                  ]);
    }
    if(!$r) throw new Exception("Error : Fail to change exchange", 1);

  }

  public function _generateLeaderBoard($delay = (7 * 24 * 60 * 60)){
    $userList = parent::querySqlRequest("SELECT * FROM internal_order_krypto WHERE date_internal_order > :date_internal_order GROUP BY id_user",
                                [
                                  'date_internal_order' => time() - $delay
                                ]);
    $benefList = [];

    $CryptoApi = new CryptoApi(null, null, $this->_getApp());

    foreach ($userList as $userInfos) {
      try {

        $UserFetched = new User($userInfos['id_user']);
        $BalanceUser = new Balance($UserFetched, $this->_getApp(), 'real');

        $EstimatedValueBalance = $BalanceUser->_getEstimationBalance();
        $EstimatedValueSymbol = $BalanceUser->_getEstimationSymbol();
        $ConvertedEstimateBalanceBTC = $BalanceUser->_convertCurrency($EstimatedValueBalance, 'USD', 'BTC');

        $EstimatedPayBalance = $BalanceUser->_getEstimationPayBalance();
        $EstimatedPaySymbol = $BalanceUser->_getEstimationSymbol();

        $benefList[$userInfos['id_user']] = $ConvertedEstimateBalanceBTC - $EstimatedPayBalance;


      } catch (\Exception $e) {
        continue;
      }
    }

    $User = new User();
    foreach ($User->_getUserList() as $key => $userInfos) {
      if(!array_key_exists($userInfos['id_user'], $benefList)){
        $benefList[$userInfos['id_user']] = 0;
      }
    }

    arsort($benefList);

    $r = parent::execSqlRequest("DELETE FROM leader_board_krypto");
    if(!$r) throw new Exception("Error leader board : Fail to clean table", 1);

    foreach ($benefList as $userID => $benef) {
      $r = parent::execSqlRequest("INSERT INTO leader_board_krypto (id_user, benef_leader_board) VALUES (:id_user, :benef_leader_board)",
                                  [
                                    'id_user' => $userID,
                                    'benef_leader_board' => $benef
                                  ]);
      if(!$r) throw new Exception("Error leader board : Fail to insert (".$userID.", ".$benef.")", 1);

    }

    return true;


  }

  public function _syncTradingAvailable(){

    foreach ($this->listThirdparty as $nEx => $exchange) {
      $classExance = "\ccxt\\".$exchange;
      $marketList = parent::querySqlRequest("SELECT * FROM thirdparty_crypto_krypto WHERE name_thirdparty_crypto=:name_thirdparty_crypto", ['name_thirdparty_crypto' => $exchange]);
      $marketListRes = [];
      foreach ($marketList as $key => $value) {
        $marketListRes[$value['name_thirdparty_crypto'].':'.$value['symbol_thirdparty_crypto'].'/'.$value['to_thirdparty_crypto']] = true;
      }
	  //echo "hello";
      //var_dump($marketListRes); exit;
      $tpmEx = new $classExance();
      foreach ($tpmEx->fetch_markets() as $Market) {
        if(!array_key_exists($exchange.':'.$Market['symbol'], $marketListRes)){
          $r = parent::execSqlRequest("INSERT INTO thirdparty_crypto_krypto (symbol_thirdparty_crypto, to_thirdparty_crypto, name_thirdparty_crypto, min_thirdparty_crypto, max_thirdparty_crypto, precision_amount_thirdparty_crypto, active_thirdparty_crypto)
                                      VALUES (:symbol_thirdparty_crypto, :to_thirdparty_crypto, :name_thirdparty_crypto, :min_thirdparty_crypto, :max_thirdparty_crypto, :precision_amount_thirdparty_crypto, :active_thirdparty_crypto)",
                                      [
                                        'symbol_thirdparty_crypto' => $Market['base'],
                                        'to_thirdparty_crypto' => $Market['quote'],
                                        'name_thirdparty_crypto' => $exchange,
                                        'min_thirdparty_crypto' => $Market['info']['min_price'],
                                        'max_thirdparty_crypto' => $Market['info']['max_price'],
                                        'precision_amount_thirdparty_crypto' => $Market['info']['decimal_places'],
                                        'active_thirdparty_crypto' => 1
                                      ]);

        }
      }
    }
  }

  public function _checkLimitOrderNotCompleted(){
    $r = parent::querySqlRequest("SELECT * FROM internal_order_krypto WHERE type_internal_order=:type_internal_order AND status_internal_order=:status_internal_order",
                                [
                                  'type_internal_order' => 'limit',
                                  'status_internal_order' => 0
                                ]);

    echo '<pre>';
    $CryptoApiCache = [];
    $BalanceUser = [];
    $UserCache = [];
    foreach ($r as $key => $value) {
      if(!array_key_exists($value['to_internal_order'].':'.$value['thirdparty_internal_order'], $CryptoApiCache)){
        $CryptoApiCache[$value['to_internal_order'].':'.$value['thirdparty_internal_order']] = new CryptoApi(null, [$value['to_internal_order'], $value['to_internal_order']], $this->_getApp(), $value['thirdparty_internal_order']);
      }
      $CryptoApi = $CryptoApiCache[$value['to_internal_order'].':'.$value['thirdparty_internal_order']];
      $Coin = $CryptoApi->_getCoin($value['symbol_internal_order']);

      if(!array_key_exists($value['id_user'], $UserCache)) $UserCache[$value['id_user']] = new User($value['id_user']);
      $User = $UserCache[$value['id_user']];

      $OrderdValueWant = (1 / floatval($value['usd_amount_internal_order'])) * floatval($value['amount_internal_order']);

      $Ordered = false;

      if($OrderdValueWant <= $Coin->_getPrice() && $value['ordered_price_internal_order'] <= $Coin->_getPrice()){
        $Ordered = true;
        var_dump('Upper : '.$OrderdValueWant.' - '.$Coin->_getPrice());
      }

      if($Coin->_getPrice() >= $OrderdValueWant && $value['ordered_price_internal_order'] >= $Coin->_getPrice()){
        $Ordered = true;
        var_dump('Under : '.$OrderdValueWant.' - '.$Coin->_getPrice());
      }

      if($Ordered){

        $thirdPartyChoosen = $this->_getThirdParty($this->_getApp()->_hiddenThirdpartyServiceCfg()[strtolower($value['thirdparty_internal_order'])])[strtolower($value['thirdparty_internal_order'])];

        if(is_null($thirdPartyChoosen)){
          error_log('Fail to order limit : '.$value['thirdparty_internal_order'].' exchange is not available');
          continue;
        }

        if(!array_key_exists($value['id_balance'], $BalanceUser)){
          try {
            $BalanceUser[$value['id_balance']] = new Balance($User, $this->_getApp(), null, $value['id_balance']);
          } catch (\Exception $e) {
            error_log('Fail to to load balance in order limit : User : '.$User->_getUserID().' - Balance ID : '.$value['id_balance']);
          }

        }

        if(!array_key_exists($value['id_balance'], $BalanceUser)) continue;
        $Balance = $BalanceUser[$value['id_balance']];

        try {
          $result = $thirdPartyChoosen->_createOrder($thirdPartyChoosen::_formatPair($value['symbol_internal_order'], $value['to_internal_order']), 'market', $value['side_internal_order'], $value['usd_amount_internal_order'], [], $Balance, $value['id_internal_order']);
          var_dump($result);
        } catch (\Exception $e) {
          error_log('Order limit '.$thirdPartyChoosen->_getExchangeName().' exchange fail : '.$e->getMessage());
        }




      }

    }
  }

}

?>
