<?php
/**
 * CryptoNotification class
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
class CryptoNotification extends MySQL {

  /**
   * Symbol notification (ex : BTC)
   * @var String
   */
  private $symbol = null;

  private $currency = null;

  private $market = null;

  /**
   * User notified
   * @var User
   */
  private $user = null;

  /**
   * Notification ID
   * @var Int
   */
  private $notificationid = null;

  /**
   * Notification data
   * @var Array
   */
  private $notificationdata = null;

  /**
   * CryptoNotification constructor
   * @param String $symbol         CryptoNotification symbol
   * @param User $user             User associate to notification
   * @param Int $notificationid    Notification ID
   */
  public function __construct($symbol = null, $currency = null, $market = "CCCAGG", $user = null, $notificationid = null){

    // Check user is given
    if(is_null($user) && $user != "global") throw new Exception("Error : User need to be given in CryptoNotification", 1);
    $this->user = $user;

    if(!is_null($symbol)){ $this->symbol = $symbol; }
    if(!is_null($currency)){ $this->currency = $currency; }
    if(!is_null($market)){ $this->market = $market; }

    // Check if notificationid is given --> load notification data
    if(!is_null($notificationid)){
      $this->notificationid = $notificationid;
      $this->_loadNotificationData();
    }
  }

  /**
   * Get notification symbol
   * @return String Notification symbol
   */
  public function _getSymbol(){
    if(is_null($this->symbol)) throw new Exception("Error : Symbol not init in CryptoNotification", 1);
    return $this->symbol;
  }

  public function _getCurrency(){
    if(is_null($this->currency)) throw new Exception("Error : Currency not init in CryptoNotification", 1);
    return $this->currency;
  }

  public function _getMarket(){
    if(is_null($this->market)) return "CCCAGG";
    return $this->market;
  }

  /**
   * Get user associate to this notification
   * @return User User associate to this notification
   */
  public function _getUser(){
    if(is_null($this->user)) throw new Exception("Error : CryptoNotification, user is null", 1);
    return $this->user;
  }

  /**
   * Set user to this notification
   * @param User $user New user associate
   */
  public function _setUser($user){
    $this->user = $user;
  }

  /**
   * Get notification ID
   * @return Int Notification ID
   */
  public function _getNotificationID(){
    if(is_null($this->notificationid)) throw new Exception("Error : Notification ID is null", 1);
    return $this->notificationid;
  }

  /**
   * Load notification data
   */
  public function _loadNotificationData(){

    // Featch indicator data from SQL
    $this->notificationdata = parent::querySqlRequest("SELECT * FROM notification_krypto WHERE id_notification=:id_notification",
                                                      [
                                                        'id_notification' => $this->_getNotificationID()
                                                      ]);

    // Check if indicator was founded
    if(count($this->notificationdata) == 0) throw new Exception("Error : Unable to load notification data (".$this->_getNotificationID().")", 1);
    $this->notificationdata = $this->notificationdata[0];
  }

  /**
   * Get notification data by key
   * @param  String $key Key data
   * @return String      Data associate to the key
   */
  public function _getNotificationDataValue($key){

    // Check if notification data was loaded
    if(is_null($this->notificationdata)) throw new Exception("Error : Notification data not loaded (".$this->_getNotificationID().")", 1);

    // Check data key was in array
    if(!array_key_exists($key, $this->notificationdata)) throw new Exception("Error : Key not found in notification data (key = ".$key.")", 1);

    // Return associate value to the key
    return $this->notificationdata[$key];
  }

  /**
   * Get notification value
   * @return String Notification value
   */
  public function _getValueNotification(){
    return $this->_getNotificationDataValue('value_notification');
  }

  /**
   * Get notification user id
   * @return Int Notification user id
   */
  public function _getAttribuateUserNotification(){
    return $this->_getNotificationDataValue('id_user');
  }

  /**
   * Get notification compared under
   * @return boolean Notification compared under
   */
  public function _isCompareUnder(){
    return $this->_getNotificationDataValue('compare_notififcation') == 1;
  }

  /**
   * Check if notification need to be sended
   * @param  String $valueCompare Compared value
   * @return Boolean              notification need to be sended
   */
  public function _notificationNeeded($valueCompare){
    if($this->_isCompareUnder()) return floatval($this->_getValueNotification()) > floatval($valueCompare);
    else return floatval($this->_getValueNotification()) < floatval($valueCompare);
  }

  /**
   * Send notication
   * @param  CryptoCoin $Coin  Coin notification
   * @param  String $value     Coin value
   * @return Boolean           Notification status
   */
  public function _sendNotification($Coin, $value){

    // Init notification center object with user
    $NotificationCenter = new NotificationCenter($this->_getUser());

    // Check if CURL extension is available
    if(!function_exists('curl_version')) throw new Exception("Error : CURL extension needed", 1);

    // Update notification was sended
    $r = parent::execSqlRequest("UPDATE notification_krypto SET status_notification=:status_notification WHERE id_notification=:id_notification",
                                [
                                  'id_notification' => $this->_getNotificationID(),
                                  'status_notification' => 1
                                ]);

    // Check update notification
    if(!$r) throw new Exception("Error : Fail to update notification krypto status", 1);

    // Send notification to notification center & return result
    return $NotificationCenter->_sendNotification(
                        $this->_generateTitleNotification($Coin, $value),
                        $this->_generateTextNotification($Coin, $value),
                        file_get_contents($Coin->_getIcon()));

  }

  /**
   * Generate notification text
   * @param  CryptoCoin $Coin  Crypto coin
   * @param  String $value     Actual coin value
   * @return String            Notification text
   */
  public function _generateTextNotification($Coin, $value){
    $diffCoef = (floatval($value) - floatval($this->_getValueNotification())) / floatval($this->_getValueNotification());
    return $Coin->_getCoinFullName().' '.($this->_isCompareUnder() ? 'decreased' : 'increased').' to '.number_format($value, 2, ',', ' ').' '.$Coin->_getApi()->_getCurrency();
  }

  /**
   * Generation notification title
   * @param  CryptoCoin $Coin  Crypto coin
   * @param  String $value     Actual coin value
   * @return String            Notification title
   */
  public function _generateTitleNotification($Coin, $value){
    return $Coin->_getSymbol().'/'.$this->_getCurrency().' '.($this->_isCompareUnder() ? 'decreased' : 'increased').' to '.number_format($value, 2, ',', ' ').' '.$Coin->_getApi()->_getCurrency();
  }

  /**
   * Get list notification associate to this user
   * @return Array Notification list
   */
  public function _getListCryptoNotifications(){
    $listNotifiation = [];
    foreach (parent::querySqlRequest("SELECT * FROM notification_krypto WHERE symbol_notification=:symbol_notification
                                      AND currency_notification=:currency_notification
                                      AND market_notification=:market_notification
                                      AND status_notification=:status_notification AND id_user=:id_user ORDER BY id_notification DESC",
                                      [
                                        'symbol_notification' => $this->_getSymbol(),
                                        'status_notification' => 0,
                                        'id_user' => $this->_getUser()->_getUserID(),
                                        'currency_notification' => $this->_getCurrency(),
                                        'market_notification' => strtoupper($this->_getMarket())
                                      ]) as $keyNotification => $dataNotification) {
      $listNotifiation[] = [
        'id' => $dataNotification['id_notification'],
        'value' => $dataNotification['value_notification'],
        'type' => $dataNotification['compare_notififcation'],
        'currency' => $dataNotification['currency_notification'],
        'market' => $dataNotification['market_notification']
      ];
    }
    return $listNotifiation;
  }

  /**
   * Admin : Fetch all notification created (use for cron)
   * @param  Boolean $onlyavailable Only fetch available notification
   * @return Array                  CryptoNotification object
   */
  public function _admFetchAllNotifications($onlyavailable = true){

    // Fetch SQL Data notifications
    if(!$onlyavailable) $list = parent::querySqlRequest("SELECT * FROM notification_krypto", []);
    else $list = parent::querySqlRequest("SELECT * FROM notification_krypto WHERE status_notification=:status_notification", ['status_notification' => 0]);

    $notificationsList = [];

    // Fetch all notification & create CryptoNotification object
    foreach ($list as $keyNotif => $sqlNotifData) {
      $notificationsList[] = new CryptoNotification($sqlNotifData['symbol_notification'],
                                                    $sqlNotifData['currency_notification'],
                                                    $sqlNotifData['market_notification'],
                                                    new User($sqlNotifData['id_user']),
                                                    $sqlNotifData['id_notification']);
    }
    return $notificationsList;

  }

  /**
   * Create notification
   * @param  String $value  Notify value
   * @param  String $actual Actual value (use for create compare then)
   */
  public function _createNotification($value, $currency, $market, $actual){

    // Check if value & actuel is numeric
    if(!is_numeric($value) || !is_numeric($actual)) throw new Exception("Error : Fail to create notification, value is not number", 1);

    // Create notification in SQL
    $r = parent::execSqlRequest("INSERT INTO notification_krypto (symbol_notification, actual_value_notification, value_notification, compare_notififcation, id_user, market_notification, currency_notification)
                                VALUES (:symbol_notification, :actual_value_notification, :value_notification, :compare_notififcation, :id_user, :market_notification, :currency_notification)",
                                [
                                  'symbol_notification' => $this->_getSymbol(),
                                  'actual_value_notification' => $actual,
                                  'value_notification' => $value,
                                  'compare_notififcation' => ($actual > $value ? '1' : '0'),
                                  'id_user' => $this->_getUser()->_getUserID(),
                                  'currency_notification' => $currency,
                                  'market_notification' => $market
                                ]);

    // Check if notification was created
    if(!$r) throw new Exception("Error : Error SQL, fail to create notification", 1);

  }

  public function _deleteNotification(){
    $r = parent::execSqlRequest("DELETE FROM notification_krypto WHERE id_notification=:id_notification AND id_user=:id_user",
                                [
                                  'id_notification' => $this->_getNotificationID(),
                                  'id_user' => $this->_getUser()->_getUserID()
                                ]);

    if(!$r) throw new Exception("Error SQL : Fail to delete notification (".$this->_getNotificationID().")", 1);
    return true;
  }

}

?>
