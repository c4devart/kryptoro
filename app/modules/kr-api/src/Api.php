<?php

class Api extends MySQL {

  private $api_key = 'ZGz3dSbvv8EGhFBX';

  private $App = null;

  private $route = [
    '/coin/list' => ['_getListCoin', 3600],
    '/coin/list/{limit}/{currency}' => ['_getListCoinLimit', 3600],
    '/coin/list/{limit}' => ['_getListCoinLimit', 3600],
    '/coin/{symbol}' => ['_getDataCoin', 1000],
    '/coin/history/{symbol}/{currency}/{duration}' => ['_getDataCoinHistorical', 10800],
    '/news/list/{limit}' => ['_getNewsList', 1000],
    '/news/{id}' => ['_getNewsItem', 1000],
    '/symbols/{from}/{to}' => ['_getSymbol', 1000],
    '/symbols/toppair/{symbol}' => ['_getTopPairSymbol', 6500],
    '/infoscurrency/{currency}' => ['_getInfosCurrency', 172800]
  ];

  public function __construct($App = null, $api_key = null){

    if(is_null($App) || is_null($api_key) || $api_key != $this->api_key) throw new Exception("Permission denied", 1);
    $this->App = $App;

  }

  private function _getApp(){
    return $this->App;
  }

  public function _route($path, $args){

    if(!array_key_exists($path, $this->route)) throw new Exception("Error : ".$path." not defined", 1);

    preg_match_all("/({[a-z]*})/", $path, $argsList);

    $argsRes = [];
    foreach ($argsList[0] as $keyMatched => $keyArgs) {
      $keyArgs = str_replace(['{', '}'], ['', ''], $keyArgs);
      if(!isset($args[$keyArgs])) throw new Exception("Error : Not matching pattern", 1);
      $argsRes[$keyArgs] = $args[$keyArgs];
    }

    return [call_user_func_array(array($this, $this->route[$path][0]), $argsRes), $this->route[$path][1]];
  }

  private function _getDataCoin($symbol){

    var_dump($symbol);

  }

  private function _getListCoin(){
    return ['ddd' => 'ddd'];
  }

  public function _getListCoinLimit($limit, $currency = 'USD'){

    if(!is_numeric($limit) || $limit < 1) throw new Exception("Wrong call", 1);
    $r = parent::querySqlRequest("SELECT * FROM coinlist_krypto ORDER BY order_coinlist ASC LIMIT ".$limit);
    $listCoin = [];
    $CryptoApi = new CryptoApi(null, [$currency, '$']);
    foreach ($r as $key => $datacoin) {
      $CryptoCoin = new CryptoCoin($CryptoApi, $datacoin['symbol_coinlist'], $datacoin);
      $CryptoGraph = new CryptoGraph($CryptoCoin->_getHistoMin(1440));

      $multifullData = $CryptoCoin->_getAllMultiFullData();

      $listCoin[$datacoin['symbol_coinlist']] = $datacoin;
      $listCoin[$datacoin['symbol_coinlist']]['MULTIFULL'] = $multifullData;

      $max = $multifullData['HIGHDAY'] - $multifullData['LOWDAY'];

      $listCoin[$datacoin['symbol_coinlist']]['MULTIFULL']['PCTDAY_V'] = 100 - abs(((($multifullData['PRICE'] - $multifullData['LOWDAY']) - $max) / $max) * 100);

      $HistoCandle = $CryptoGraph->_getCandles();

      $ADX = ADX::run($HistoCandle, 14);
      $RSI = RSI::run($HistoCandle, 14);
      $EMA = EMA::run($HistoCandle, 14);
      $ATR = ATR::run($HistoCandle, 14);

      // $listCoin[$datacoin['symbol_coinlist']]['ADX'] = $ADX[count($ADX) - 1]['val'];
      // $listCoin[$datacoin['symbol_coinlist']]['RSI'] = $RSI[count($RSI) - 1]['val'];
      // $listCoin[$datacoin['symbol_coinlist']]['EMA'] = $EMA[count($EMA) - 1]['val'];
      // $listCoin[$datacoin['symbol_coinlist']]['ATR'] = $ATR[count($ATR) - 1]['val'];

    }

    return $listCoin;

  }

  public function _getDataCoinHistorical($symbol, $currency = 'USD', $duration = '86400'){

    $CryptoApi = new CryptoApi(null, [$currency, '$']);
    $CryptoCoin = new CryptoCoin($CryptoApi, $symbol, null);
    $CryptoGraph = new CryptoGraph($CryptoCoin->_getHistoMin(1440));

    return $CryptoGraph->_getCandles();

  }

  public function _getNewsList($limit = null){

    $News = new News();
    $NewsListRes = [];
    foreach ($News->_getListFeedRSS() as $NewsItem) {
      $NewsListRes[$NewsItem->_getArticleUniq()] = [
        'picture' => $NewsItem->_getPicture(),
        'title' => $NewsItem->_getTitle(),
        'url' => $NewsItem->_getUrl(),
        'from' => $NewsItem->_getFrom(),
        'author' => $NewsItem->_getAuthor(),
        'date' => $NewsItem->_getDatePublish(),
        'time' => $NewsItem->_getTimestamp(),
        'tags' => $NewsItem->_getListTags(),
        'content' => $NewsItem->_getContent(),
        'uniqid' => $NewsItem->_getArticleUniq()
      ];
    }

    if(!is_null($limit)) return array_slice($NewsListRes, 0, $limit);
    return $NewsListRes;

  }

  public function _getNewsItem($id){

    $News = new News();
    $Article = $News->_getArticle($id);

    return [
      'id' => $id,
      'picture' => $Article->_getPicture(),
      'title' => $Article->_getTitle(),
      'url' => $Article->_getUrl(),
      'from' => $Article->_getFrom(),
      'author' => $Article->_getAuthor(),
      'date' => $Article->_getDatePublish(),
      'time' => $Article->_getTimestamp(),
      'tags' => array_slice($Article->_getArticleDataVal('categories'), 0, 5),
      'content' => $Article->_getContent()
    ];


  }

  public function _getSymbol($from, $to){

    $CryptoApi = new CryptoApi(null, [$to, '$']);
    $CryptoCoin = new CryptoCoin($CryptoApi, $from, null);

    $CryptoGraph = new CryptoGraph($CryptoCoin->_getHistoMin(1440));

    $HistoCandle = $CryptoGraph->_getCandles();

    $RSI = RSI::run($HistoCandle, 14);
    $EMA = EMA::run($HistoCandle, 14);
    $ATR = ATR::run($HistoCandle, 14);

    $ADXVAL = [];
    foreach ([14] as $period) {
      $ADXVAL[$period] = [];
      $ADXLIST = ADX::run($HistoCandle, $period);
      foreach ($ADXLIST as $vADX) {
        $ADXVAL[$period]['val'] = $vADX['val'];
      }

      foreach ([15, 60, 240, 1120] as $timevol) {
        $ADXVAL[$period]['evolv'][$timevol] = $ADXLIST[count($ADXLIST) - $timevol]['val'];
      }

    }

    return [
      'symbol' => $CryptoCoin->_getSymbol(),
      'coinname' => $CryptoCoin->_getCoinName(),
      'coinfullname' => $CryptoCoin->_getCoinFullName(),
      'price' => $CryptoCoin->_getPrice(),
      'evol24' => $CryptoCoin->_getCoin24Evolv(),
      'mkcap' => $CryptoCoin->_getMarketCap(),
      'mkcap_human' => $CryptoCoin->_formatNumberCommarization($CryptoCoin->_getMarketCap()),
      'direct24vol' => $CryptoCoin->_getDirectVol24(),
      'direct24vol_human' => $CryptoCoin->_formatNumberCommarization($CryptoCoin->_getDirectVol24()),
      'total24vol' => $CryptoCoin->_getTotalVol24(),
      'total24vol_human' => $CryptoCoin->_formatNumberCommarization($CryptoCoin->_getTotalVol24()),
      'getlow24' => $CryptoCoin->_getLow24Hours(),
      'gethigh24' => $CryptoCoin->_getHigh24Hours(),
      'getpercentagelowhigh' => 100 - $CryptoCoin->_getCurrentPercentagePriceLowHigh(),
      'MUTLIFULLDATA' => $CryptoCoin->_getAllMultiFullData(),
      'ADX' => $ADXVAL
    ];

  }

  public function _getTopPairSymbol($symbol){


    $CryptoApi = new CryptoApi(null, ['USD', '$']);
    $CryptoCoin = new CryptoCoin($CryptoApi, $symbol, null);

    return $CryptoCoin->_getTopPair(true, true);

  }

  public function _getInfosCurrency($currency){

    $r = parent::querySqlRequest("SELECT * FROM currency_krypto WHERE code_iso_currency=:code_iso_currency", ['code_iso_currency' => $currency]);
    if(count($r) > 0){
      return [
        'name' => $r[0]['name_currency'],
        'symbol' => $r[0]['symbol_currency']
      ];
    } else {
      $r = parent::querySqlRequest("SELECT * FROM coinlist_krypto WHERE symbol_coinlist=:symbol_coinlist", ['symbol_coinlist' => $currency]);
      if(count($r) > 0){
        return [
          'name' => $r[0]['coinname_coinlist'],
          'symbol' => $r[0]['symbol_coinlist']
        ];
      }
    }

  }

}

?>
