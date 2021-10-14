<?php

class Calendar extends MySQL {

  private $App = null;

  private $Token = null;
  private $EventList = null;

  public function __construct($App){
    $this->App = $App;
  }

  public function _getApp(){
    return $this->App;
  }

  public function _callService($service, $params){

    $paramsString = (count($params) > 0 ? "?" : "");
    $s = 0;
    foreach ($params as $key => $value) {
      $paramsString .= ($s > 0 ? '&' : '').$key."=".$value;
      $s++;
    }

    $ch = curl_init('https://api.coinmarketcal.com/'.$service.$paramsString);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_ENCODING,  '');
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
    $s = json_decode(curl_exec($ch), true);

    if(array_key_exists('error', $s)) throw new Exception("Error Calendar : ".$s['error_description'], 1);

    return $s;

  }

  public function _getToken(){
    if(!is_null($this->Token)) return $this->Token;

    $token = $this->_callService('oauth/v2/token', [
      'grant_type' => 'client_credentials',
      'client_id' => $this->_getApp()->_getCalendarCientID(),
      'client_secret' => $this->_getApp()->_getCalendarClientSecret()
    ]);
    $this->Token = $token['access_token'];
    return $this->Token;
  }

  public function _getEventItem($itemid, $CryptoApi){
    for ($i=1; $i < 5; $i++) {
      foreach ($this->_callService('v1/events', [
        'access_token' => $this->_getToken(),
        'page' => $i,
        'max' => 149,
        'showOnly' => 'hot_events'
      ]) as $key => $value) {
        if($value['id'] == $itemid){
          $DateTime = new DateTime($value['date_event']);
          $value['formate_date'] = $DateTime->format('j M Y');
          if(count($value['coins']) > 0){
            try {
              $value['coins_kr'] = new CryptoCoin($CryptoApi, $value['coins'][0]['symbol']);
            } catch (Exception $esv) {
              $value['coins_kr'] = null;
            }

          } else {
            $value['coins_kr'] = null;
          }
          return $value;
        }
      }
    }
    return null;
  }

  public function _getEvents(){
    if(!is_null($this->EventList)) return $this->EventList;

    $showDate = null;
    for ($i=1; $i < 5; $i++) {
      foreach ($this->_callService('v1/events', [
        'access_token' => $this->_getToken(),
        'page' => $i,
        'max' => 149,
        'showOnly' => 'hot_events'
      ]) as $key => $value) {

        if($value['vote_count'] < 500 || $value['percentage'] < 70) continue;


        $enableCoin = true;
        if($this->_getApp()->_getCalendarEnableCoinsEnabled()){
          $r = parent::querySqlRequest("SELECT * FROM coinlist_krypto WHERE symbol_coinlist=:symbol_coinlist",
                                      [
                                        'symbol_coinlist' => $value['coins'][0]['symbol']
                                      ]);

          if(count($r) > 0 && $r[0]['status_coinslist'] == "0") $enableCoin = false;
        }

        if(!$enableCoin) continue;

        $DateTime = new DateTime($value['date_event']);
        $value['formate_date'] = $DateTime->format('j M');
        $value['month_date'] = $DateTime->format('F');
        $value['this_month'] = (date('F') == $DateTime->format('F'));
        if(count($value['coins']) > 0 && file_exists('../../../../../assets/img/icons/crypto/'.$value['coins'][0]['symbol'].'.svg')){
          $value['coin_picture'] = $value['coins'][0]['symbol'];
        } else {
          $value['coin_picture'] = null;
        }
        if(is_null($showDate) || $DateTime->format('m/Y') != $showDate){
          $value['show_date'] = true;
          $showDate = $DateTime->format('m/Y');
        } else {
          $value['show_date'] = false;
        }

        $this->EventList[] = $value;
      }
    }

    return $this->EventList;
  }

}

?>
