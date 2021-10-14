<?php

/**
 * CryptoCoin class
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
class CryptoCoin extends MySQL {

  /**
   * CryptoApi Object
   * @var CryptoApi
   */
  private $CryptoApi = null;

  /**
   * Crypto coin symbol
   * @var String
   */
  private $CryptoCoinSymbol = null;

  /**
   * Crypto coin data
   * @var Array
   */
  private $CryptoCoinData = null;

  /**
   * Crypto coin multifull data
   * @var Array
   */
  private $CryptoMultifull = null;

  private $Market = null;

  /**
   * CryptoCoin construct
   * @param  CryptoApi      $CryptoApi     CryptoApi Object
   * @param  Int      $CryptoCoinSymbol       CryptoCoin ID
   */
  public function __construct($CryptoApi, $CryptoCoinSymbol = null, $CryptoCoinData = null, $market = "CCCAGG"){
    $this->_setSymbol($CryptoCoinSymbol);
    $this->_setMarket($market);
    if(empty($CryptoCoinData)) $this->_loadData();
    else $this->_setData($CryptoCoinData);
    $this->CryptoApi = $CryptoApi;
  }

  /**
   * Get API
   * @return CryptoApi
   */
  public function _getApi(){
    if(is_null($this->CryptoApi)) throw new Exception("Error : CryptoApi is null for (".$this->_getSymbol().") coin", 1);
    return $this->CryptoApi;
  }

  /**
   * Define the crypto Coin id
   * @param  Int            $CryptoCoinSymbol Crypto Coin ID
   */
  public function _setSymbol($CryptoCoinSymbol = null){
    if(is_null($CryptoCoinSymbol)) throw new Exception("Error : You need to specify CryptoCoinSymbol", 1);
    $this->CryptoCoinSymbol = strtoupper($CryptoCoinSymbol);
  }

  public function _setMarket($Market){
    $this->Market = $Market;
  }

  public function _getMarket(){
    return (is_null($this->Market) ? "CCCAGG" : $this->Market);
  }


  /**
   * Get crypto Coin id
   * @return Int            Crypto Coin ID
   */
  public function _getSymbol(){
    if(is_null($this->CryptoCoinSymbol)) throw new Exception("Error : CryptoCoinSymbol is not defined", 1);
    return $this->CryptoCoinSymbol;
  }

  /**
   * Defined crypto coin data
   * @param Array $CryptoCoinData Coin data
   */
  public function _setData($CryptoCoinData = null){
    $this->CryptoCoinData = $CryptoCoinData;
  }

  /**
   * Load crypto coin data
   */
  public function _loadData(){

    // Get coin data in database
    $valCoin = parent::querySqlRequest("SELECT * FROM coinlist_krypto WHERE symbol_coinlist=:symbol_coinlist", ['symbol_coinlist' => $this->_getSymbol()]);
    $isRealMoney = false;
    // Check if coin is founded
    if(count($valCoin) == 0){
      $valCoin = parent::querySqlRequest("SELECT * FROM currency_krypto WHERE code_iso_currency=:code_iso_currency", ['code_iso_currency' => $this->_getSymbol()]);
      $isRealMoney = true;
      if(count($valCoin) == 0) throw new Exception("Error : Unable to load coin (".$this->_getSymbol().")", 1);
    }

    $valCoin = $valCoin[0];

    if($isRealMoney) {
      $this->CryptoCoinData = [
        'Id' => $valCoin['id_currency'],
        'Url' => $valCoin['name_currency'],
        'Name' => $valCoin['code_iso_currency'],
        'CoinName' => $valCoin['name_currency'],
        'FullName' => $valCoin['name_currency'],
        'Algorithm' => '-',
        'ProofType' => '-',
        'SortOrder' => $valCoin['id_currency'],
        'Symbol' => $valCoin['code_iso_currency'],
        'Status' => 1,
        'Source' => 'cryptocompare'
      ];
    } else {
      $this->CryptoCoinData = [
        'Id' => $valCoin['currencyid_coinlist'],
        'Url' => $valCoin['url_coinlist'],
        'Name' => $valCoin['symbol_coinlist'],
        'CoinName' => $valCoin['coinname_coinlist'],
        'FullName' => $valCoin['fullname_coinlist'],
        'Algorithm' => $valCoin['algorithm_coinlist'],
        'ProofType' => $valCoin['prooftype_coinlist'],
        'SortOrder' => $valCoin['order_coinlist'],
        'Symbol' => $valCoin['symbol_coinlist'],
        'Status' => $valCoin['status_coinslist'],
        'Source' => $valCoin['source_coinlist']
      ];
    }
    // Save data in var CryptoCoinData


  }

  /**
   * Get coin data
   * @return Array Coin data
   */
  public function _getData(){
    return $this->CryptoCoinData;
  }

  /**
   * Get data by key
   * @param  String $k Data key
   * @return String    Data result by key
   */
  public function _getDataKey($k){

    // Check if coin data is loaded
    if(is_null($this->CryptoCoinData)) throw new Exception("Error : Data is null for this Coin (".$this->_getSymbol().")", 1);

    // Check if key is founded
    if(!array_key_exists($k, $this->CryptoCoinData)) throw new Exception("Error : ".$k." not exist in Coin data (".$this->_getSymbol().")", 1);

    // Return associate value
    return $this->CryptoCoinData[$k];
  }

  /**
   * Get coin name
   * @return String Coin name
   */
  public function _getCoinName(){
    return $this->_getDataKey('CoinName');
  }

  /**
   * Get coin full name
   * @return String Coin full name
   */
  public function _getCoinFullName(){
    return $this->_getDataKey('FullName');
  }

  /**
   * Get coin source
   * @return String Coin source
   */
  public function _getCoinSource(){
    return $this->_getDataKey('Source');
  }

  /**
   * Get coin order
   * @return String Coin order
   */
  public function _getCoinSortOrder(){
    return $this->_getDataKey('SortOrder');
  }

  /**
   * Get is coin is enabled
   * @return Boolean
   */
  public function _isEnabled(){
    if(!array_key_exists('Status', $this->CryptoCoinData)) return true;
    return $this->_getDataKey('Status') == 1;
  }

  /**
   * Get coin price
   * @return String Coin price
   */
  public function _getPrice(){
    return $this->_getMultiFullData('PRICE');
  }

  /**
   * Get coin evolution in 24H
   * @return String Coin evolution
   */
  public function _getCoin24Evolv(){
    return $this->_getMultiFullData('CHANGEPCT24HOUR');
  }

  public function _getCoin24Change(){
    return $this->_getMultiFullData('CHANGE24HOUR');
  }

  /**
   * Get coin market cap
   * @return String Coin market cap
   */
  public function _getMarketCap(){
    return $this->_getMultiFullData('MKTCAP');
  }

  /**
   * Get coin direct volume in 24H
   * @return String Coin direct volume
   */
  public function _getDirectVol24(){
    return $this->_getMultiFullData('VOLUME24HOURTO');
  }

  /**
   * Get coin total volume in 24H
   * @return String Coin total volume
   */
  public function _getTotalVol24(){
    return $this->_getMultiFullData('TOTALVOLUME24HTO');
  }

  /**
   * Get market from Multifull
   * @return String Market name
   */
  public function _getMarketMultiFull(){
    return $this->_getMultiFullData('MARKET');
  }

  /**
   * Get low day from Multifull
   * @return String Low day value
   */
  public function _getLowDayMultiFull(){
    return $this->_getMultiFullData('LOWDAY');
  }

  /**
   * Get high day from Multifull
   * @return String high day value
   */
  public function _getHighDayMultiFull(){
    return $this->_getMultiFullData('HIGHDAY');
  }

  /**
   * Get open day from Multifull
   * @return String open day value
   */
  public function _getOpenDayMultiFull(){
    return $this->_getMultiFullData('OPEN24HOUR');
  }

  /**
   * Get change day from Multifull
   * @return String change day value
   */
  public function _getChangeDayMultiFill(){
    return $this->_getMultiFullData('CHANGEDAY');
  }

  /**
   * Get change pct day from Multifull
   * @return String change pct day value
   */
  public function _getChangeDayPctMultiFull(){
    return $this->_getMultiFullData('CHANGEPCTDAY');
  }

  public function _getTotal24VolMultiFull(){
    return $this->_getMultiFullData('VOLUME24HOUR');
  }

  /**
   * Get lowest value last 24h
   * @return String
   */
  public function _getLow24Hours($formated = true){
    if(!$formated) return $this->_getMultiFullData('LOW24HOUR');
    if(strlen(substr(strrchr($this->_getMultiFullData('LOW24HOUR'), "."), 1)) > 5) return number_format($this->_getMultiFullData('LOW24HOUR'), 5, ',', ' ');
    return number_format($this->_getMultiFullData('LOW24HOUR'), strlen(substr(strrchr($this->_getMultiFullData('LOW24HOUR'), "."), 1)), ',', ' ');
  }

  /**
   * Get highest value last 24h
   * @return String
   */
  public function _getHigh24Hours($formated = true){
    if(!$formated) return $this->_getMultiFullData('HIGH24HOUR');
    if(strlen(substr(strrchr($this->_getMultiFullData('HIGH24HOUR'), "."), 1)) > 5) return number_format($this->_getMultiFullData('HIGH24HOUR'), 5, ',', ' ');
    return number_format($this->_getMultiFullData('HIGH24HOUR'), strlen(substr(strrchr($this->_getMultiFullData('HIGH24HOUR'), "."), 1)), ',', ' ');
  }

  /**
   * Get percentage actual value high / low 24h
   * @return Float
   */
  public function _getCurrentPercentagePriceLowHigh(){
    $max = $this->_getHigh24Hours(false) - $this->_getLow24Hours(false);
    if($max == 0) return 0;

    return 100 - abs(((($this->_getPrice() - $this->_getLow24Hours(false)) - $max) / $max) * 100);
  }

  /**
   * Get coin icon path
   * @return String Coin icon path
   */
  public function _getIcon($get_path = false){
    if($get_path) return FILE_PATH.'assets/img/icons/crypto/'.$this->_getSymbol().'.svg';
    //if(!@file_get_contents(APP_URL.'/assets/img/icons/crypto/'.$this->_getSymbol().'.svg') || strpos(get_headers(APP_URL.'/assets/img/icons/crypto/'.$this->_getSymbol().'.svg', 1)["Content-Type"], 'text/html') !== false) return null;
    return APP_URL.'/assets/img/icons/crypto/'.$this->_getSymbol().'.svg';
  }

  /**
   * Get CryptoHisto
   * @param  String $type  Type history (minutes / hours / days)
   * @param  Int $limit    Number history limit
   * @return Array         CryptoHisto Array
   */
  private function _getHistoCoin($type, $limit){

    $currentMinute = new DateTime('now');
    $currentMinute->setTime(date('G'), date('i'), 0);

    $getHistoCache = parent::querySqlRequest("SELECT * FROM histo_krypto WHERE coin_histo=:coin_histo AND currency_histo=:currency_histo AND type_histo=:type_histo
                                              AND last_update_histo=:last_update_histo",
                                              [
                                                'coin_histo' => $this->_getSymbol(),
                                                'currency_histo' => $this->_getApi()->_getCurrency(),
                                                'type_histo' => $type.'/'.$this->_getMarket(),
                                                'last_update_histo' => $currentMinute->getTimestamp()
                                              ]);
    //error_log(count($getHistoCache) . ' -> '.$this->_getSymbol().' -> '.$currentMinute->getTimestamp().' -> '.$this->_getApi()->_getCurrency().' -> '.$type);
    if(count($getHistoCache) == 0){
      // Get history in API

      $optHisto = ['fsym' => $this->_getSymbol(), 'tsym' => $this->_getApi()->_getCurrency()];
      if($type == "histoday") $optHisto['limit'] = 365;
      $histoPrice = $this->_getApi()->_getData($type, $optHisto);
      if($type == "histoday") $histoPrice = array_slice($histoPrice, 0, -2);
      if($type == "histohour") $histoPrice = array_slice($histoPrice, 0, -24);


      $updateCache = parent::querySqlRequest("SELECT * FROM histo_krypto WHERE coin_histo=:coin_histo AND currency_histo=:currency_histo AND type_histo=:type_histo",
                                                [
                                                  'coin_histo' => $this->_getSymbol(),
                                                  'currency_histo' => $this->_getApi()->_getCurrency(),
                                                  'type_histo' => $type
                                                ]);

      if(count($updateCache) == 0){
        $addCache = parent::execSqlRequest("INSERT INTO histo_krypto (coin_histo, currency_histo, type_histo, last_update_histo, data_histo) VALUES
                                                    (:coin_histo, :currency_histo, :type_histo, :last_update_histo, :data_histo)",
                                            [
                                              'coin_histo' => $this->_getSymbol(),
                                              'currency_histo' => $this->_getApi()->_getCurrency(),
                                              'type_histo' => $type.'/'.$this->_getMarket(),
                                              'last_update_histo' => $currentMinute->getTimestamp(),
                                              'data_histo' => json_encode($histoPrice)
                                            ]);
      } else {

        $changeCache = parent::execSqlRequest("UPDATE histo_krypto SET data_histo=:data_histo, last_update_histo=:last_update_histo WHERE coin_histo=:coin_histo AND currency_histo=:currency_histo AND type_histo=:type_histo",
                                            [
                                              'data_histo' => json_encode($histoPrice),
                                              'last_update_histo' => $currentMinute->getTimestamp(),
                                              'type_histo' => $type.'/'.$this->_getMarket(),
                                              'coin_histo' => $this->_getSymbol(),
                                              'currency_histo' => $this->_getApi()->_getCurrency()
                                            ]);

      }

    } else {

      $histoPrice = json_decode($getHistoCache[0]['data_histo'], true);

    }

    if(is_null($histoPrice)) return [];
    // Sort histo by time
    usort($histoPrice, function($a, $b){
                        if($a['time'] == $b['time']) return -1;
                        if($a['time'] < $b['time']) return 0;
                        return 1;
                      });

    $res = [];

    // Create & append CryptoHisto object in result array
    foreach ($histoPrice as $histoVal) {
      $res[$histoVal['time']] = new CryptoHisto($histoVal);
      $res[$histoVal['time']]->_getFormatedDate();
    }
    return $res;

  }

  /**
   * Get coin history by minutes
   * @param  Int $limit  History limit
   * @return Array       CryptoHisto array
   */
  public function _getHistoMin($limit = 100){
    return $this->_getHistoCoin('histominute', $limit);
  }

  /**
   * Gist coin history by hours
   * @param  Int $limit     History limit
   * @return Array          CryptoHisto array
   */
  public function _getHistoHour($limit = 1440){
    return $this->_getHistoCoin('histohour', $limit);
  }

  /**
   * Gist coin history by day
   * @param  Int $limit     History limit
   * @return Array          CryptoHisto array
   */
  public function _getHistoDay($limit = 365, $showfirstday = false){
    return $this->_getHistoCoin('histoday', $limit);
  }

  /**
   * Get History short graph
   * @param  Array $data  History data
   * @return Array        History graph data (x, y)
   */
  public function _getHistoShortGraph($data){
    $res = [];
    foreach ($data as $Histo) {
      $res[$Histo->_getTime()] = ['y' => $Histo->_getHigh(), 'x' => $Histo->_getFormatedDate()];
    }
    return $res;
  }

  /**
   * Get history chart value
   * @param  String $cols Get cols name value (x or y)
   * @param  Array $data  History data
   * @return String       History value
   */
  public function _getChartValue($cols, $data){
    $res = [];
    foreach ($data as $vData) {
      $res[] = $vData[$cols];
    }
    return join($res, ',');
  }

  /**
   * Get crypto coin old price
   * @param  String $timestamp Timestamp price
   * @return Float             Price value
   */
  public function _getOldPrice($timestamp = null){

    // If timestamp not given, get current date and sub 1 day
    if(is_null($timestamp)){
      $DateTime = new DateTime('now');
      $DateTime->sub(new DateInterval('P1D'));
      $timestamp = $DateTime->getTimestamp();
    }

    // Get price API
    $listPrice = $this->_getApi()->_getData('pricehistorical', ['fsym' => $this->_getSymbol(), 'tsyms' => $this->_getApi()->_getCurrency(), 'ts' => $timestamp]);

    // Return price
    return floatval($listPrice[$this->_getSymbol()][$this->_getApi()->_getCurrency()]);
  }

  /**
   * Load crypto multifull coin data
   */
  private function _loadCryptoMultifull(){
    if(!is_null($this->CryptoMultifull)) return $this->CryptoMultifull;

    // Get price multifull in API
      $this->CryptoMultifull = $this->_getApi()->_getData('pricemultifull', ['fsyms' => $this->_getSymbol(), 'tsyms' => $this->_getApi()->_getCurrency()]);

      // Check if price multifull is given
      try {
        if(empty($this->CryptoMultifull) || !array_key_exists('RAW', $this->CryptoMultifull)) throw new Exception("Error : Fail to load crypto multifull (".$this->_getSymbol().")", 1);

        // Save multifull
        $this->CryptoMultifull = $this->CryptoMultifull['RAW'][$this->_getSymbol()][$this->_getApi()->_getCurrency()];
        $this->CryptoMultifull['Source'] = 'cryptocompare';
      } catch (Exception $e) {
        $this->CryptoMultifull = json_decode('{"TYPE":"5","MARKET":"CCCAGG","FROMSYMBOL":"'.$this->_getSymbol().'","TOSYMBOL":"'.$this->_getApi()->_getCurrency().'","FLAGS":"4","PRICE":0,"LASTUPDATE":1524838381,"LASTVOLUME":0,"LASTVOLUMETO":0,"LASTTRADEID":"","VOLUMEDAY":0,"VOLUMEDAYTO":0,"VOLUME24HOUR":0,"VOLUME24HOURTO":0,"OPENDAY":0,"HIGHDAY":0,"LOWDAY":0,"OPEN24HOUR":0,"HIGH24HOUR":0,"LOW24HOUR":0,"LASTMARKET":"Kraken","CHANGE24HOUR":0,"CHANGEPCT24HOUR":0,"CHANGEDAY":0,"CHANGEPCTDAY":0,"SUPPLY":0,"MKTCAP":0,"TOTALVOLUME24H":0,"TOTALVOLUME24HTO":0}', true);
        $this->CryptoMultifull['Source'] = "cryptocompare";

      }
  }

  /**
   * Get multifull data from key
   * @param  String $key Key needed
   * @return String      Result from key
   */
  public function _getMultiFullData($key){

    // If pricemultifull not loaded, load it
    if(is_null($this->CryptoMultifull)) $this->_loadCryptoMultifull();

    //error_log(json_encode($this->CryptoMultifull));

    // Check if key exist, else throw exception
    if(!array_key_exists($key, $this->CryptoMultifull)) throw new Exception("Error : ".$key." not found in Multifull data", 1);

    // Return value associate to the key
    return $this->CryptoMultifull[$key];
  }

  /**
   * Get all multifull data
   * @return Array
   */
  public function _getAllMultiFullData(){
    if(is_null($this->CryptoMultifull)) $this->_loadCryptoMultifull();
    return $this->CryptoMultifull;
  }

  /**
   * Format number as human
   * @param  Int $number    Number who need to be formated
   * @return String         Number formated
   */
  public function _formatNumberCommarization($number){
    if($number >= 1000000000) return $this->_getApi()->_getApp()->_formatNumber($number / 1000000000, 2).' B';
    if($number >= 1000000) return $this->_getApi()->_getApp()->_formatNumber($number / 1000000, 2).' M';
    return $this->_getApi()->_getApp()->_formatNumber($number, ($number > 10 ? 2 : 5));
  }

  /**
   * Get market analytic for this coin
   * @param  Array  $compare Crypto to compare
   * @return Array           Analytic data
   */
  public function getMarketAnalystic($compare = ['BTC', 'USD', 'EUR']){

    // List class to compare
    $classCase = [
      40.5 => 'pos_7',
      25.5 => 'pos_6',
      15.5 => 'pos_5',
      9.5 => 'pos_4',
      4.5 => 'pos_3',
      2.5 => 'pos_2',
      1 => 'pos_1',
      0 => 'neutral',
      -1 => 'neg_1',
      -2.5 => 'neg_2',
      -4.5 => 'neg_3',
      -9.5 => 'neg_4',
      -15 => 'neg_5',
      -25.5 => 'neg_6',
      -40.5 => 'neg_7'
    ];

    // Get price evolution
    $priceEvol = $this->_getApi()->_getData('pricemultifull', ['fsyms' => $this->_getSymbol(), 'tsyms' => join(',', $compare)]);

    // Define result array (positive and negative)
    $negative = [];
    $positive = [];

    foreach ($priceEvol['RAW'][$this->_getSymbol()] as $keyData => $marketData) {
      if($marketData['TOSYMBOL'] == $this->_getSymbol()) continue;

      $marketData['CHANGEPCT24HOUR'] = floatVal($marketData['CHANGEPCT24HOUR']);

      $colorCase = 'neg';
      foreach ($classCase as $valMarketColor => $classAttributed) {
        if($marketData['CHANGEPCT24HOUR'] <= $valMarketColor) $colorCase = $classAttributed;
      }

      $marketCompare = [
        'symbol' => $marketData['TOSYMBOL'],
        'evolution' => floatval($marketData['CHANGEPCT24HOUR']),
        'color' => $colorCase
      ];

      if($marketData['CHANGEPCT24HOUR'] >= 0) $positive[$marketData['TOSYMBOL']] = $marketCompare;
      else $negative[$marketData['TOSYMBOL']] = $marketCompare;

    }

    // Sort negative result
    usort($negative, function($a, $b){
      if($a['evolution'] > $b['evolution']) return 1;
      else return -1;
    });

    // Sort positive result
    usort($positive, function($a, $b){
      if($a['evolution'] > $b['evolution']) return 1;
      else return -1;
    });

    return ['positive' => $positive, 'negative' => $negative];

  }

  public function _callHitAPI($service){
    $s = json_decode(@file_get_contents('https://api.hitbtc.com/api/2'.$service), true);
    if(is_null($s)) throw new Exception("Error : Null given for HITBTC : ".$service, 1);

    if($this->_getApi()->_parseDataHeader($http_response_header) > 400) throw new Exception("Error while fetching data (service = ".$service.")", 1);

    return $s;
  }

  public function _getOrderBookList(){
    $listOrder = $this->_callHitAPI('/public/orderbook/'.$this->_getSymbol().''.$this->_getApi()->_getCurrency());
    $sum = 0;
    $nAmunt = 0;
    foreach ($listOrder['ask'] as $key => $order) {
      $sum += $order['price'];
      $nAmunt += $order['size'];
      $listOrder['ask'][$key]['size_sum'] = $nAmunt;
      $listOrder['ask'][$key]['sum'] = $sum;
    }

    foreach ($listOrder['ask'] as $key => $order) {
      $listOrder['ask'][$key]['percentage'] = 100 - abs((($order['size'] - $nAmunt) / $nAmunt) * 100);
    }

    $sum = 0;
    $nAmunt = 0;
    foreach ($listOrder['bid'] as $key => $order) {
      $sum += $order['price'];
      $nAmunt += $order['size'];
      $listOrder['bid'][$key]['size_sum'] = $nAmunt;
      $listOrder['bid'][$key]['sum'] = $sum;
    }

    foreach ($listOrder['bid'] as $key => $order) {
      $listOrder['bid'][$key]['percentage'] = 100 - abs((($order['size'] - $nAmunt) / $nAmunt) * 100);
    }

    return $listOrder;
  }

  public function _getDephGraphValue(){

    $chartValue = [ 'price' => [], 'value' => ['ask' => [], 'bid' => []] ];

    $listOrderBook = $this->_getOrderBookList();
    foreach ($listOrderBook as $keyBook => $listBook) {
      if($keyBook == 'ask') $listBook = array_reverse($listBook);
      foreach ($listBook as $key => $orderValue) {
        $chartValue['price'][] = $orderValue['price'];
        $chartValue['value'][$keyBook][] = $orderValue['size_sum'];
      }
    }

    return $chartValue;

  }

  /**
   * Get top pair
   * @param  boolean $getprice [description]
   * @return [type]            [description]
   */
  public function _getTopPair($getprice = false, $getmultifulldata = false){
    $topPair = $this->_getApi()->_getData('top/pairs', ['fsym' => $this->_getSymbol(), 'limit' => 10]);
    if(!$getprice) return $topPair;
    $listPair = [];
    foreach ($topPair as $pair) {
      $listPair[] = $pair['toSymbol'];
    }

      if($getmultifulldata){
        $multifull = $this->_getApi()->_getData('pricemultifull', ['fsyms' => $this->_getSymbol(), 'tsyms' => join(',', $listPair)], 'min-api');
        foreach ($topPair as $keyPair => $pair) {
          $topPair[$keyPair]['MULTIFULL'] = $multifull['RAW'][$this->_getSymbol()][$pair['toSymbol']];
        }
      } else {
        $price = @$this->_getApi()->_getData('price', ['fsym' => $this->_getSymbol(), 'tsyms' => join(',', $listPair)]);
        foreach ($topPair as $keyPair => $pair) {
          $topPair[$keyPair]['price'] = $price[$pair['toSymbol']];
        }
      }

    return $topPair;
  }

  public function _toggleActive(){
    $r = parent::execSqlRequest("UPDATE coinlist_krypto SET status_coinslist=:status_coinslist WHERE symbol_coinlist=:symbol_coinlist",
                                          [
                                            'symbol_coinlist' => $this->_getSymbol(),
                                            'status_coinslist' => ($this->_isEnabled() ? '0' : '1')
                                          ]);
    if(!$r) throw new Exception("Error : Fail to change coin status", 1);

    $r = parent::execSqlRequest("DELETE FROM blockfolio_krypto WHERE symbol_blockfolio=:symbol_blockfolio", ['symbol_blockfolio' => $this->_getSymbol()]);
    $r = parent::execSqlRequest("DELETE FROM watching_krypto WHERE symbol=:symbol", ['symbol' => $this->_getSymbol()]);
    $r = parent::execSqlRequest("DELETE FROM top_list_krypto WHERE symbol_top_list=:symbol_top_list", ['symbol_top_list' => $this->_getSymbol()]);
    $r = parent::execSqlRequest("DELETE FROM notification_krypto WHERE symbol_notification=:symbol_notification", ['symbol_notification' => $this->_getSymbol()]);
    $r = parent::execSqlRequest("DELETE FROM histo_krypto WHERE coin_histo=:coin_histo", ['coin_histo' => $this->_getSymbol()]);

  }

  public function _tradingAvailable(){

    $r = parent::querySqlRequest("SELECT * FROM thirdparty_crypto_krypto WHERE symbol_thirdparty_crypto=:symbol_thirdparty_crypto",
                                [
                                  'symbol_thirdparty_crypto' => $this->_getSymbol()
                                ]);
    return count($r) > 0;

  }

  public function _convertTo($tosymbol, $value = 1, $fromsymbol = null){

    if($tosymbol == $this->_getSymbol()) return $value;

    $infosConvert = $this->_getApi()->_getData('price', ['fsym' => (is_null($fromsymbol) ? $this->_getSymbol() : $fromsymbol), 'tsyms' => $tosymbol]);

    if(!array_key_exists($tosymbol, $infosConvert)) return $value;
    return $infosConvert[$tosymbol] * $value;

  }


}

?>
