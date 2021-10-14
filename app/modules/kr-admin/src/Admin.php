<?php

/**
 * Admin class
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
class Admin extends MySQL {

  private $cronPageList = [
    'app/src/CryptoApi/actions/CheckNotification.php' => 60,
    'app/modules/kr-trade/src/actions/generateLeaderBoard.php' => 18000,
    'app/src/App/actions/cronCleanCache.php' => 3600,
    'app/src/CryptoApi/actions/SyncExchanges.php' => 43200,
    'app/src/CryptoApi/actions/SyncCoin.php' => 43200,
    'app/modules/kr-trade/src/actions/CronLimitOrder.php' => 60
  ];

  public function __construct(){ }

  /**
   * Get number visit on the app since an starting date
   * @param  String $from Timestamp
   * @return Array       Visit list
   */
  public function _getVisitNum($from){
    return parent::querySqlRequest("SELECT * FROM visits_krypto WHERE time_visits > :time_visits", ['time_visits' => $from]);
  }

  /**
   * Get user list (order by the last user signup)
   * @return Array List of users
   */
  public function _getUsersList(){
    $listUser = [];
    foreach (parent::querySqlRequest("SELECT * FROM user_krypto ORDER BY id_user DESC", []) as $key => $dataUser) {
      $listUser[] = new User($dataUser['id_user']);
    }
    return $listUser;
  }

  /**
   * Get list coins
   * @return Array Coins list
   */
  public function _getListCoins(){
    return parent::querySqlRequest("SELECT * FROM coinlist_krypto ORDER BY id_coinlist", []);
  }

  /**
   * Get list admin section available
   * @return Array Admin section
   */
  public function _getListSection(){
    return ['Dashboard', 'General settings', 'Coins', 'Currencies', 'Mail settings',
            'Payment', 'Subscriptions', 'News - Social', 'Intro', 'Trading', 'Cron', 'Additional pages',
            'Bank accounts', 'Identity', 'Templates'];
  }

  /**
   * Get all list blocks stats on dashboard
   * @return Array Array blocks
   */
  public function _getListBlockStats(){

    $todayDate = new DateTime();
    $todayDate->setTime(0, 0, 0);
    $sevendayData = new DateTime();
    $sevendayData->sub(new DateInterval('P7D'));

    return [
      [
        "title" => "Today's visits",
        "value" => number_format(count($this->_getVisitNum($todayDate->getTimestamp())), 0, ',', ' ')
      ],
      [
        "title" => "7 days visits",
        "value" => number_format(count($this->_getVisitNum($sevendayData->getTimestamp())), 0, ',', ' ')
      ],
      [
        "title" => "Number of users",
        "value" => number_format(count($this->_getUsersList()), 0, ',', ' ')
      ],
      [
        "title" => "Number of coins",
        "value" => number_format(count($this->_getListCoins()), 0, ',', ' ')
      ]
    ];
  }

  public function _getIntroAvailable(){

    return [
      ".kr-wtchl left" => "Watching list",
      "[kr-module='dashboard'] left" => "Board",
      "[kr-side='kr-orderbook'] left" => "Order book",
      "[kr-module='marketanalysis'] left" => "Market",
      "[kr-module='blockfolio'] left" => "Blockfolio",
      "[kr-side='kr-leaderboard'] leftleft" => "Leader board",
      "[kr-side='kr-calculator'] left" => "Calculator",
      "[kr-side='kr-infosside'] left" => "News",
      ".kr-toggle-white top" => "Theme switch",
      ".kr-current-time top" => "Time",
      ".kr-wallet-top bottom" => "Account trading wallet",
      "[kr-action='kr-notification-center'] bottom" => "Notifications",
      ".kr-change-dashboard bottom" => "Dashboard manage",
      ".kr-addgraph-dashboard bottom" => "Add item to dashboard",
      ".kr-account bottom" => "Account profile",
      ".kr-live-dash-trade top" => "Market history",
      ".kr-chat-right right" => "Chat bar"
    ];

  }

  private $UserFetchedList = [];

  public function _getUserFetched($user_id){
    if(!array_key_exists($user_id, $this->UserFetchedList)) $this->UserFetchedList[$user_id] = new User($user_id);
    return $this->UserFetchedList[$user_id];
  }

  public function _getWithdrawList($query = null){

    $res = [];

    if(!is_null($query)){
      foreach (parent::querySqlRequest("SELECT * FROM widthdraw_history_krypto WHERE
                                        id_user LIKE :query_search OR
                                        ref_widthdraw_history LIKE :query_search OR
                                        symbol_widthdraw_history LIKE :query_search OR
                                        CONCAT(id_user, '-', id_widthdraw_history) LIKE :query_search
                                        ORDER BY status_widthdraw_history, date_widthdraw_history DESC",
                                        [
                                          'query_search' => '%'.$query.'%'
                                        ]) as $key => $value) {
        $itemWith = $value;
        $itemWith['user_details'] = $this->_getUserFetched($value['id_user']);
        $res[] = $itemWith;
      }
    } else {
      foreach (parent::querySqlRequest("SELECT * FROM widthdraw_history_krypto ORDER BY status_widthdraw_history, date_widthdraw_history DESC") as $key => $value) {
        $itemWith = $value;
        $itemWith['user_details'] = $this->_getUserFetched($value['id_user']);
        $res[] = $itemWith;
      }
    }



    return $res;


  }

  public function _getCronListStatus(){
    $r = [];
    foreach ($this->cronPageList as $url => $updatetime) {

      $info = parent::querySqlRequest("SELECT * FROM cron_krypto WHERE page_cron=:page_cron", ['page_cron' => $url]);

      if(count($info) == 0){
        $r[$url] = [
          'url' => $url,
          'last_update' => 'Never',
          'status' => 0,
          'every' => $updatetime
        ];
      } else {

        $r[$url] = [
          'url' => $url,
          'last_update' => date('d/m/Y H:i:s', $info[0]['last_update_cron']),
          'status' => (time() - $info[0]['last_update_cron'] > $updatetime ? 1 : 2),
          'every' => $updatetime
        ];

      }

    }

    return $r;
  }

  public static function _getTemplateList(){
    return [
      "welcome" => "New user account, welcome email",
      "activeAccount" => "Active user account",
      "adminEmail" => "Admin withdraw request",
      "confirmWidthdraw" => "Confirm withdraw request",
      "processWidthdraw" => "Withdraw request processed",
      "resetPassword" => "User reset password",
      "resetPasswordDone" => "User reset password done",
      "subscribeRequest" => "Subscription expiration user",
      "unknowLogin" => "New login IP detected for a user"
    ];
  }

  public static function _getPagesList(){
    return [
      "condition_use" => "Privacy policy",
      "term_use" => "Terms of service"
    ];
  }

  public static function _getPageContent($link){
    $myfile = fopen($_SERVER['DOCUMENT_ROOT'].FILE_PATH.$link, "r") or die('dd');
    $line = "";
    // Output one line until end-of-file
    while(!feof($myfile)) {
      $line .= fgets($myfile);
    }
    fclose($myfile);
    return $line;
  }

}

?>
