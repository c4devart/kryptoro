<?php

/**
 * User Class
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
class User extends MySQL {

  /**
   * User ID
   * @var Int
   */
  private $userid = null;

  /**
   * User Data
   * @var Array
   */
  private $datauser = null;

  /**
   * User currency symbol
   * @var String
   */
  private $currencySymbol = null;

  private $UserSettings = [];

  /**
   * User constructor
   * @param Int $userid User ID
   */
  public function __construct($userid = null){

    // If user id is given -> load user data
    if(!is_null($userid)){
      $this->userid = $userid;
      $this->_loadNewUserData();
      $this->_loadUserSettings();
    } // If user logged, load user data
    else if($this->_isLogged()) {
      $this->_loadUserData();
      $this->_checkReferalLink();
      $this->_loadUserSettings();
    }
  }

  public function _loadUserSettings(){
    $r = parent::querySqlRequest("SELECT * FROM user_settings_krypto WHERE id_user=:id_user",
                                                  [
                                                    'id_user' => $this->_getUserID()
                                                  ]);
    foreach ($r as $key => $vst) {
      $this->UserSettings[$vst['key_user_settings']] = $vst['value_user_settings'];
    }

  }

  public function _getDefaultUserSettings(){
    return [
      'white_mode' => 'false',
      'hide_market' => 'true',
      'show_bar_chat' => 'true',
      'tradingview_chart_library_use' => 'false',
      'orderlist_show' => 'false',
      'orderlist_layer' => 'false'
    ];
  }

  public function _getUserSettingsKey($key){
    if(!array_key_exists($key, $this->UserSettings) && !array_key_exists($key, $this->_getDefaultUserSettings())) return "";
    if(!array_key_exists($key, $this->UserSettings)) return $this->_getDefaultUserSettings()[$key];
    return $this->UserSettings[$key];
  }

  public function _changeUserSettings($k, $v){
    if(!array_key_exists($k, $this->_getDefaultUserSettings())) throw new Exception("Error : Invalid settings", 1);
    $r = parent::querySqlRequest("SELECT * FROM user_settings_krypto WHERE key_user_settings=:key_user_settings AND id_user=:id_user",
                                [
                                  'key_user_settings' => $k,
                                  'id_user' => $this->_getUserID()
                                ]);
    if(count($r) == 0){
      $r = parent::execSqlRequest("INSERT INTO user_settings_krypto (id_user, key_user_settings, value_user_settings)
                                  VALUES (:id_user, :key_user_settings, :value_user_settings)",
                                  [
                                    'id_user' => $this->_getUserID(),
                                    'key_user_settings' => $k,
                                    'value_user_settings' => $v
                                  ]);
    } else {

      $r = parent::execSqlRequest("UPDATE user_settings_krypto SET value_user_settings=:value_user_settings WHERE key_user_settings=:key_user_settings AND id_user=:id_user",
                                  [
                                    'id_user' => $this->_getUserID(),
                                    'key_user_settings' => $k,
                                    'value_user_settings' => $v
                                  ]);

    }

    if(!$r) throw new Exception("Error SQL : Fail to update user settings", 1);

    return true;

  }

  /**
   * Get User ID
   * @param Boolean Get user id encrypted
   * @return Int User ID
   */
  public function _getUserID($encrypted = false){
    // If user id is given mannualy, return user id
    if(!is_null($this->userid)) return ($encrypted ? App::encrypt_decrypt('encrypt', $this->userid) : $this->userid);
    // If user id is not given but data user (by logged) give by data user
    if(!is_null($this->datauser)) return ($encrypted ? App::encrypt_decrypt('encrypt', $this->_getUserDataByKey('id_user')) : $this->_getUserDataByKey('id_user'));

    throw new Exception("Error : User id is undefined", 1);
  }

  /**
   * Check if user is logged
   * @return Boolean
   */
  public function _isLogged(){
    // Check session
    if(empty($_SESSION) || !array_key_exists('kr_login', $_SESSION) || empty($_SESSION['kr_login'])) return false;
    return true;
  }

  /**
   * Load user data by logged
   */
  private function _loadUserData(){
    // Decode json data
    $this->datauser = json_decode($_SESSION['kr_login'], true);
  }

  /**
   * Load new user data
   */
  private function _loadNewUserData(){
    // Fetch database data by user id
    $this->datauser = parent::querySqlRequest("SELECT * FROM user_krypto WHERE id_user=:id_user", ['id_user' => $this->_getUserID()]);
    // Check user found
    if(count($this->datauser) == 0) throw new Exception("Error : Fail to load user (".$this->_getUserID().")", 1);
    $this->datauser = $this->datauser[0];
  }

  /**
   * Get user data by key
   * @param  String $key Key
   * @return String      Data found by key
   */
  private function _getUserDataByKey($key){

    if(!array_key_exists($key, $this->datauser)) throw new Exception("Error : User data not exist for key = ".$key, 1);
    if(empty($this->datauser[$key]) || strlen($this->datauser[$key]) == 0) return null;
    return $this->datauser[$key];
  }

  /**
   * Get user name
   * @return String User name
   */
  public function _getName(){ return $this->_getUserDataByKey('name_user'); }

  /**
   * Get user initial
   * @return String User name
   */
  public function _getInitial(){ return substr($this->_getUserDataByKey('name_user'), 0, 1); }

  /**
   * Get user picture
   * @return String User picture
   */
  public function _getPicture(){
    if(strlen($this->_getUserDataByKey('picture_user')) == 0 || is_null($this->_getUserDataByKey('picture_user'))) return null;
    return str_replace('{{APP_URL}}', APP_URL, $this->_getUserDataByKey('picture_user'));
  }

  /**
   * Get user email
   * @return String User email
   */
  public function _getEmail(){ return $this->_getUserDataByKey('email_user'); }

  /**
   * Get user oauth type
   * @return String User oauth
   */
  public function _getOauth(){ return $this->_getUserDataByKey('oauth_user'); }

  /**
   * Get user two step
   * @return String User two step
   */
  public function _isTwostep(){ return $this->_getUserDataByKey('twostep_user'); }

  /**
   * Get if user is admin
   * @return Boolean
   */
  public function _isAdmin(){ return $this->_getUserDataByKey('admin_user') == 1; }

  /**
   * Get if user is manager
   * @return Boolean
   */
  public function _isManager(){ return $this->_getUserDataByKey('admin_user') == 2 || $this->_isAdmin(); }

  /**
   * Get user currency
   * @return String User currency (ex : USD)
   */
  public function _getCurrency(){ return $this->_getUserDataByKey('currency_user'); }

  /**
   * Get user password
   * @return String User password
   */
  private function _getPassword(){ return $this->_getUserDataByKey('password_user'); }

  /**
   * Get if user is active
   * @return Boolean
   */
  public function _isActive(){ return $this->_getUserDataByKey('status_user') == 1; }

  /**
   * Get user PushBullet
   * @return String PushBullet key
   */
  public function _getPushbulletKey(){
    return $this->_getUserDataByKey('pushbullet_user');
  }

  /**
   * Get user language
   * @return String User language code (ex : fr)
   */
  public function _getLang($onlygetdata = false){
    if(!empty($_SESSION['kr_custom_lang']) && !$onlygetdata) return $_SESSION['kr_custom_lang'];
    return $this->_getUserDataByKey('lang_user');
  }

  /**
   * Get user last login
   * @return Date Last login date
   */
  public function _getLastLogin(){

    $r = parent::querySqlRequest("SELECT * FROM visits_krypto WHERE id_user=:id_user ORDER BY id_visits DESC LIMIT 1", ['id_user' => $this->_getUserID()]);
    if(count($r) == 0) return null;
    $dateLastLogin = new DateTime('now');
    $dateLastLogin->setTimestamp($r[0]['time_visits']);
    return $dateLastLogin;
  }

  /**
   * Get user created date
   * @return String User created date
   */
  public function _getCreatedDate(){
    return $this->_getUserDataByKey('created_date_user');
  }

  public function _marketShow(){
    return $this->_getUserSettingsKey('hide_market') == 'false';
  }

  public function _whiteMode(){
    return $this->_getUserSettingsKey('white_mode') == 'true';
  }

  public function _barChatShow(){
    return $this->_getUserSettingsKey('show_bar_chat') == 'true';
  }

  public function _tradingviewChartLibraryUse(){
    return $this->_getUserSettingsKey('tradingview_chart_library_use') == 'true';
  }

  /**
   * Get user charge
   * @return Charge User charge
   */
  public function _getCharge($App){
    return new Charges($this, $App);
  }

  /**
   * Get user currency symbol
   * @return String User currency symbol (ex : $)
   */
  public function _getCurrencySymbol(){
    if(!is_null($this->currencySymbol)) return $this->currencySymbol;
    $r = parent::querySqlRequest("SELECT * FROM currency_krypto WHERE code_iso_currency=:code_iso_currency", ['code_iso_currency' => $this->_getCurrency()]);
    if(count($r) == 0) return "";
    $this->currencySymbol = $r[0]['symbol_currency'];
    return $r[0]['symbol_currency'];
  }

  /**
   * Check if user already exist by email & oauth
   * @param  String $email User email search
   * @param  String $oauth Type oauth search
   * @return Boolean
   */
  private function _checkUserExist($email, $oauth = "standard"){

    // Fetch database
    $userData = parent::querySqlRequest("SELECT * FROM user_krypto WHERE email_user=:email_user AND oauth_user=:oauth_user",
                                          [
                                            'email_user' => $email,
                                            'oauth_user' => $oauth
                                          ]);
    return count($userData) > 0;
  }

  /**
   * Login function
   * @param  String $email    User email
   * @param  String $password User password (not crypted)
   * @param  String $oauth    User oauth method
   */
  public function _login($email, $password, $oauth = 'standard', $tfscode = null, $setpwd = false){

    $App = new App(false);

    // Check email validity
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception("Email not valid", 1);

    // Get user data
    $r = parent::querySqlRequest("SELECT * FROM user_krypto WHERE email_user=:email_user AND password_user=:password_user AND oauth_user=:oauth_user",
                                    [
                                      'email_user' => $email,
                                      'password_user' => ($oauth == 'standard' ? hash('sha512', $password) : ($setpwd ? $password : $oauth)),
                                      'oauth_user' => $oauth
                                    ]);

    // Check user find
    if(count($r) == 0) throw new Exception("Invalid login", 1);

    if($r[0]['status_user'] == 0 && $r[0]['admin_user'] == "0") throw new Exception("Your account has been disabled", 1);
    if($App->_isMaintenanceMode() && $r[0]['admin_user'] == "0") throw new Exception("Website currenly under maintenance", 1);
    if($r[0]['status_user'] == 2 && $r[0]['admin_user'] == "0" && $oauth == "standard" && $App->_getUserActivationRequire()){
      $this->_sendActivationEmailLink($email);
      throw new Exception("You need to enable your account. A new email have need sended to your email.", 1);
    }



    $authentificatorSet = parent::querySqlRequest("SELECT * FROM googletfs_krypto WHERE id_user=:id_user AND status_googletfs=:status_googletfs",
                                                  [
                                                    'id_user' => $r[0]['id_user'],
                                                    'status_googletfs' => 1
                                                  ]);

    if(count($authentificatorSet) > 0 && is_null($tfscode)) return 2;

    if(!is_null($tfscode) && count($authentificatorSet) > 0){
      if(!$this->_checkGoogleTFS($tfscode, $r[0]['id_user'])) return 4;
    }

    // Set session
    $_SESSION['kr_login'] = json_encode($r[0]);

    $this->_addVisit($r[0]['id_user']);

    $User = new User();
    $User->_saveUserLoginHistory();

    return 1;

  }

  /**
   * Oauth callback
   * @param  Object $oauth Oauth object
   */
  public function _oauthCallback($oauth){

    // Check if user exist
    if(!$this->_checkUserExist($oauth->_getEmail(), $oauth->_getOauthName())){ // Create account
      $this->_createUser($oauth->_getEmail(),
                         $oauth->_getFirstname().' '.$oauth->_getLastName(),
                         $oauth->_getOauthName(),
                         $oauth->_getAvatar(),
                         $oauth->_getOauthName());

      return $this->_oauthCallback($oauth);
    } else { // Login user
      return $this->_login($oauth->_getEmail(), $oauth->_getOauthName(), $oauth->_getOauthName());
    }

  }

  /**
   * Oauth callback by uniq id
   * @param  [type] $oauth [description]
   */
  public function _oauthCallbackID($oauth){

    $r = parent::querySqlRequest("SELECT * FROM user_krypto WHERE oauth_user=:oauth_user AND password_user=:oauth_id",
                                  [
                                    'oauth_user' => $oauth->_getOauthName(),
                                    'oauth_id' => $oauth->_getId()
                                  ]);

    if(count($r) > 0){
      return $this->_login($r[0]['email_user'], $oauth->_getId(), $oauth->_getOauthName(), null, true);
    } else {
      $this->_createUser($oauth->_getEmail(),
                         $oauth->_getName(),
                         $oauth->_getId(),
                         $oauth->_getAvatar(),
                         $oauth->_getOauthName(),
                        "", 0, 0, true);
      return $this->_oauthCallbackID($oauth);
    }

  }

  /**
   * Create new user
   *
   * @param  String  $email      User email
   * @param  String  $name       User name
   * @param  String  $password   User password
   * @param  String  $picture    User picture
   * @param  String  $oauth      User oauth
   * @param  String  $pushbullet User PushBullet
   * @param  Int $twostep        User twostep
   */
  public function _createUser($email = null, $name = null, $password = null, $picture = "", $oauth = 'standard', $pushbullet = "", $twostep = 0, $admin = 0, $setpwd = false){

    $App = new App(false);

    // Check args given
    if(is_null($email)) throw new Exception("Error : User create, email required", 1);
    if(is_null($name)) throw new Exception("Error : User create, name required", 1);
    if(is_null($password)) throw new Exception("Error : User create, password required", 1);

    // Check email validity
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception("Error : User create, email wrong format", 1);

    // Check if user exist
    if($this->_checkUserExist($email, $oauth)) throw new Exception("User email already exist", 1);

    // Add user to database
    $r = parent::execSqlRequest("INSERT INTO user_krypto (email_user, name_user, password_user, picture_user,
                                                          oauth_user, pushbullet_user, twostep_user, created_date_user, admin_user, status_user) VALUES
                                                          (:email_user, :name_user, :password_user, :picture_user,
                                                          :oauth_user, :pushbullet_user, :twostep_user, :created_date_user, :admin_user, :status_user)",
                                                          [
                                                            'email_user' => $email,
                                                            'name_user' => $name,
                                                            'password_user' => ($oauth == 'standard' ? hash('sha512', $password) : ($setpwd ? $password : $oauth)),
                                                            'picture_user' => $picture,
                                                            'oauth_user' => $oauth,
                                                            'pushbullet_user' => $pushbullet,
                                                            'twostep_user' => $twostep,
                                                            'created_date_user' => time(),
                                                            'admin_user' => $admin,
                                                            'status_user' => ($App->_getUserActivationRequire() && $oauth == 'standard' ? '2' : '1')
                                                          ]);

    // Check if sql add database status
    if(!$r) throw new Exception("Error : User fail to create account", 1);

    $infosRegister = parent::querySqlRequest("SELECT * FROM user_krypto WHERE email_user=:email_user", ['email_user' => $email]);

    $App = new App();

    if(!empty($_SESSION) && isset($_SESSION['referal_source_krypto']) && !empty($_SESSION['referal_source_krypto']) && $App->_referalEnabled()){
      $s = parent::execSqlRequest("INSERT INTO referal_histo_krypto (id_user, code_referal, date_referal_histo) VALUES (:id_user, :code_referal, :date_referal_histo)",
                                  [
                                    'id_user' => $infosRegister[0]['id_user'],
                                    'code_referal' => $_SESSION['referal_source_krypto'],
                                    'date_referal_histo' => time()
                                  ]);
      if(!$s) throw new Exception("Error : Fail to add referal history", 1);

    }


    if($App->_sendWelcomeEmail()){

      $template = new Liquid\Template();
      $template->parse(file_get_contents(APP_URL.'/app/modules/kr-user/templates/welcome.tpl'));

      // Render & send email
      $App->_sendMail($email, $App->_getWelcomeSubject(), $template->render([
        'APP_URL' => APP_URL,
        'APP_TITLE' => $App->_getAppTitle(),
        'LOGO_BLACK' => $App->_getLogoBlackPath(),
        'SUBJECT' => $App->_getWelcomeSubject(),
        'CONTACT_EMAIL' => $App->_getSupportEmail(),
        'USER_NAME' => $name
      ]));
    }

    if($oauth == 'standard' && $App->_getUserActivationRequire()){
      $this->_sendActivationEmailLink($email);
    }

    return true;
  }

  /**
   * Define PushBullet token for user
   * @param  NotificationCenter $NotificationCenter Notification center
   * @param  String $pushbulletKey                  PushBullet key
   */
  public function _definePushbulletKey($NotificationCenter, $pushbulletKey, $adminview = false){

    // Set PushBullet key in data
    $this->datauser['pushbullet_user'] = $pushbulletKey;

    // Send PushBullet notification test
    $r = $NotificationCenter->_sendPushbulletNotification('Notification connected', 'Notification setup completed !');

    // Check notification status
    if($r != true) throw new Exception("Fail to send notification", 1);

    // Update user PushBullet key in database
    $update = parent::execSqlRequest("UPDATE user_krypto SET pushbullet_user=:pushbullet_user WHERE id_user=:id_user",
                                      [
                                        'pushbullet_user' => $pushbulletKey,
                                        'id_user' => $this->_getUserID()
                                      ]);

    // Check update status
    if(!$update) throw new Exception("Error : Fail to update PushBullet key in database", 1);

    // Save new user data in session
    if(!$adminview) $_SESSION['kr_login'] = json_encode($this->datauser);

    return true;

  }

  /**
   * Remove PushBullet
   * @param  String $pushbulletKey PushBullet key
   */
  public function _removePushbullet($pushbulletKey, $adminview = false){
    $this->datauser['pushbullet_user'] = NULL;

    // Check given PushBullet
    if(substr($pushbulletKey, 0, 10) != substr($this->_getPushbulletKey(), 0, 10)) throw new Exception("Error : Fail to check current pushbullet", 1);

    // Update PushBullet key in database
    $update = parent::execSqlRequest("UPDATE user_krypto SET pushbullet_user=:pushbullet_user WHERE id_user=:id_user",
                                      [
                                        'pushbullet_user' => '',
                                        'id_user' => $this->_getUserID()
                                      ]);

    // Check sql update state
    if(!$update) throw new Exception("Error : Fail to update PushBullet key in database", 1);

    // Save new user data session
    if(!$adminview) $_SESSION['kr_login'] = json_encode($this->datauser);
    return true;
  }

  /**
   * Change user picture
   * @param  Array $picture  Picture object (file)
   * @return String          Picture path
   */
  public function _changePicture($picture, $adminview = false){

    // Check public directory users
    if(!file_exists($_SERVER['DOCUMENT_ROOT'].FILE_PATH.'/public')) throw new Exception("Error : Public directory not exist", 1);
    if(!file_exists($_SERVER['DOCUMENT_ROOT'].FILE_PATH.'/public/user')) throw new Exception("Error : User directory in public not exist", 1);
    if(!file_exists($_SERVER['DOCUMENT_ROOT'].FILE_PATH.'/public/user/'.$this->_getUserID())){
      // Try to create personnal user directory
      if(!mkdir($_SERVER['DOCUMENT_ROOT'].FILE_PATH.'/public/user/'.$this->_getUserID())) throw new Exception("Error : Fail to create user directory 'public/user/".$this->_getUserID()."'", 1);
    }

    // Generate new filename
    $filename = basename(uniqid(true).'-'.$picture['name']);

    // Check if picture is an valid format
    if(!in_array(pathinfo($picture['name'], PATHINFO_EXTENSION), [
        'jpeg', 'jpg', 'gif', 'png'])) throw new Exception("Error : Image not accepted (jpeg, jpg, gif, png) only", 1);

    // Try to upload file to public directory
    if (!move_uploaded_file($picture['tmp_name'], $_SERVER['DOCUMENT_ROOT'].FILE_PATH.'/public/user/'.$this->_getUserID().'/'.$filename)) throw new Exception("Error : Fail to upload user picture (permissions error)", 1);

    // Update picture in sql user
    $r = parent::execSqlRequest("UPDATE user_krypto SET picture_user=:picture_user WHERE id_user=:id_user",
                                [
                                  'picture_user' => '{{APP_URL}}/public/user/'.$this->_getUserID().'/'.$filename,
                                  'id_user' => $this->_getUserID()
                                ]);

    // Check update status
    if(!$r) throw new Exception("Error SQL : Fail to change user picture", 1);

    // Set new user picture
    $this->datauser['picture_user'] = '{{APP_URL}}/public/user/'.$this->_getUserID().'/'.$filename;
    if(!$adminview) $_SESSION['kr_login'] = json_encode($this->datauser);

    // Return new user picture path
    return $this->_getPicture();
  }

  /**
   * Generate reset password user token
   * @param  String $Email User email
   * @return String        Generated token
   */
  private function _generateUserResetToken($Email){

    // Generate token
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $token = substr( str_shuffle( $chars ), 0, 50);

    // Save new token in SQL
    $r = parent::execSqlRequest("UPDATE user_krypto SET reset_token_user=:reset_token_user WHERE email_user=:email_user AND oauth_user=:oauth_user",
                                [
                                  'email_user' => $Email,
                                  'reset_token_user' => $token,
                                  'oauth_user' => 'standard'
                                ]);

    // Check token update sql
    if(!$r) throw new Exception("Error : Fail to generate token for user", 1);

    // Return token
    return $token;
  }

  /**
   * Reset user password
   * @param String $Email User email
   * @param App    $App   Application object
   */
  public function _resetPassword($Email = null, $App = null){

    // Check args given
    if(is_null($App)) throw new Exception("Error reset password : App object not given", 1);
    if(is_null($Email)) throw new Exception("Error reset password : Email not given", 1);
    if(!filter_var($Email, FILTER_VALIDATE_EMAIL)) throw new Exception("Error reset password : Email not valid", 1);

    // Generate new user token
    $generateResetToken = $this->_generateUserResetToken($Email);

    // Check infos user
    $infosUser = parent::querySqlRequest("SELECT name_user FROM user_krypto WHERE email_user=:email_user AND oauth_user=:oauth_user",
                                          [
                                            'email_user' => $Email,
                                            'oauth_user' => 'standard'
                                          ]);

    // Generate email template sended to user
    $template = new Liquid\Template();
    $template->parse(file_get_contents(APP_URL.'/app/modules/kr-user/templates/resetPassword.tpl'));

    // Render & send email
    $App->_sendMail($Email, $App->_getAppTitle().' - Password reset', $template->render([
      'APP_URL' => APP_URL,
      'APP_TITLE' => $App->_getAppTitle(),
      'LOGO_BLACK' => $App->_getLogoBlackPath(),
      'SUBJECT' => 'Password reset',
      'USER_NAME' => (count($infosUser) > 0 ? $infosUser[0]['name_user'] : ''),
      'USER_RESET_LINK' => APP_URL.'/?a=pwdr&token='.base64_encode(App::encrypt_decrypt('encrypt', $Email.'||--||'.$generateResetToken))
    ]));

    return true;

  }

  /**
   * Check token validity
   * @param  App  $App            Application object
   * @param  String  $token       Token
   * @param  Boolean $deleteToken If token need to be deleted
   * @return Boolean
   * @return Array                Data token
   */
  public function _parseToken($App = null, $token = null, $deleteToken = false){

    // Check args given
    if(is_null($App)) throw new Exception("Error reset password : App object not given", 1);
    if(is_null($token)) return false;

    // Decrypt token given
    $tokenDecode = $App::encrypt_decrypt('decrypt', base64_decode($token));

    // Get token data
    $tokenDecode = explode('||--||', $tokenDecode);

    // Check token validity
    if(count($tokenDecode) != 2 || !filter_var($tokenDecode[0], FILTER_VALIDATE_EMAIL)) return false;

    // Get token data
    $tokenMatch = parent::querySqlRequest("SELECT * FROM user_krypto WHERE email_user=:email_user AND reset_token_user=:reset_token_user AND oauth_user=:oauth_user",
                                          [
                                            'email_user' => $tokenDecode[0],
                                            'reset_token_user' => $tokenDecode[1],
                                            'oauth_user' => 'standard'
                                          ]);

    // Return false if token not found
    if(count($tokenMatch) == 0) return false;

    // If token need to be deleted
    if($deleteToken){
      // Delete token in SQL Database
      $r = parent::execSqlRequest("UPDATE user_krypto SET reset_token_user = NULL WHERE email_user=:email_user AND reset_token_user=:reset_token_user AND oauth_user=:oauth_user",
                                  [
                                    'email_user' => $tokenDecode[0],
                                    'reset_token_user' => $tokenDecode[1],
                                    'oauth_user' => 'standard'
                                  ]);

      // Check delete status
      if(!$r) throw new Exception("Error : Fail to delete use token", 1);

    }

    return $tokenDecode;
  }

  /**
   * Valid reset password
   * @param String $token    Token
   * @param App $App         App object
   * @param String $password New password
   */
  public function _validResetPassword($token, $App = null, $password){

    // Check token given
    $tokenParsed = $this->_parseToken($App, $token, true);
    if(!$tokenParsed) throw new Exception("Error : Fail to parse token", 1);

    // Update new password
    $u = parent::execSqlRequest("UPDATE user_krypto SET password_user=:password_user WHERE email_user=:email_user AND oauth_user=:oauth_user",
                                [
                                  'email_user' => $tokenParsed[0],
                                  'oauth_user' => 'standard',
                                  'password_user' => hash('sha512', $password)
                                ]);

    // Check update status
    if(!$u) throw new Exception("Error SQL : Fail to update user password", 1);

    // Get user infos
    $infosUser = parent::querySqlRequest("SELECT name_user FROM user_krypto WHERE email_user=:email_user AND oauth_user=:oauth_user",
                                          [
                                            'email_user' => $tokenParsed[0],
                                            'oauth_user' => 'standard'
                                          ]);

    // Check if user was found
    if(count($infosUser) == 0) throw new Exception("Error : Fail to get user data in update password", 1);

    // Generate new template
    $template = new Liquid\Template();
    $template->parse(file_get_contents(APP_URL.'/app/modules/kr-user/templates/resetPasswordDone.tpl'));
    // Render & send mail confirmation update password user
    $App->_sendMail($tokenParsed[0], $App->_getAppTitle().' - You password was changed !', $template->render([
      'APP_URL' => APP_URL,
      'APP_TITLE' => $App->_getAppTitle(),
      'LOGO_BLACK' => $App->_getLogoBlackPath(),
      'SUBJECT' => 'You password was changed !',
      'USER_NAME' => (count($infosUser) > 0 ? $infosUser[0]['name_user'] : ''),
      'SUPPORT_EMAIL' => $App->_getSupportEmail()
    ]));

    return true;

  }

  /**
   * Receive current session IP user
   * @return String IP Address
   */
  public function _getUserIP(){
    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
      $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
      $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
      $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
  }

  /**
   * Add user visit
   * @param Int $userid User id
   */
  public function _addVisit($userid = null){
    if(is_null($userid)) $userid = $this->_getUserID();
    $r = parent::execSqlRequest("INSERT INTO visits_krypto (id_user, time_visits, ip_visits)
                                 VALUES (:id_user, :time_visits, :ip_visits)",
                                 [
                                    'id_user' => $userid,
                                    'time_visits' => time(),
                                    'ip_visits' => App::_getVisitorIP()
                                 ]);
    if(!$r) throw new Exception("Error : Fail to add user visit history", 1);
    return true;
  }

  /**
   * Changer user data
   * @param  String $key   Key to change
   * @param  String $value New value
   */
  private function _changeDataKey($key, $value){
    //if(!array_key_exists($key, $this->datauser)) throw new Exception("Error : User data not exist for key = ".$key, 1);
    $this->datauser[$key] = $value;
  }

  /**
   * Change user name
   * @param String $name New name
   */
  public function _setName($name){
    $this->_changeDataKey('name_user', $name);
  }

  /**
   * Change user password
   * @param String $password New password
   */
  public function _setPassword($password){
    $this->_changeDataKey('password_user', hash('sha512', $password));
  }

  /**
   * Change user email
   * @param String $email New email
   */
  public function _setEmail($email){
    if($this->_checkUserExist($email, 'standard')) throw new Exception("User already exist with the email", 1);
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception("Email not valid", 1);
    $this->_changeDataKey('email_user', $email);
  }

  /**
   * Change user language
   * @param String $lang New language
   */
  public function _setLanguage($lang, $LangObject = null){
    if(!$LangObject->languageAvailable($lang)) throw new Exception("Language not available", 1);
    $this->_changeDataKey('lang_user', $lang);
  }

  /**
   * Change user currency
   * @param String $currency New currency
   */
  public function _setCurrency($currency){
    $currenyAvailable = parent::querySqlRequest("SELECT * FROM currency_krypto WHERE code_iso_currency=:code_iso_currency", ['code_iso_currency' => $currency]);
    if(count($currenyAvailable) == 0) throw new Exception("Currency not available", 1);
    $this->_changeDataKey('currency_user', $currency);
  }

  /**
   * Changer user status
   * @param Int New user status
   */
  public function _setStatus($status){
    $this->_changeDataKey('status_user', $status);
  }

  /**
   * Change user premission level
   * @param Int User permission level
   */
  public function _setAdmin($admin){

    $this->_changeDataKey('admin_user', $admin);
  }

  /**
   * Save user change data
   */
  public function _saveChange($reloadsession = true){

    $r = parent::execSqlRequest("UPDATE user_krypto SET email_user=:email_user, name_user=:name_user, lang_user=:lang_user, currency_user=:currency_user, password_user=:password_user, status_user=:status_user, admin_user=:admin_user WHERE id_user=:id_user",
                                [
                                  'email_user' => $this->_getEmail(),
                                  'name_user' => $this->_getName(),
                                  'lang_user' => $this->_getLang(),
                                  'currency_user' => $this->_getCurrency(),
                                  'id_user' => $this->_getUserID(),
                                  'password_user' => $this->_getPassword(),
                                  'status_user' => ($this->_isActive() ? '1' : '0'),
                                  'admin_user' => ($this->_isAdmin() ? '1' : ($this->_isManager() ? '2' : '0'))
                                ]);

    if(!$r) throw new Exception("Error : Fail to change user infos.", 1);

    if($reloadsession) $_SESSION['kr_login'] = json_encode($this->datauser);

  }

  /**
   * Change free trial enddate
   * @param String $expire Ending date
   */
  public function _setFreetrial($expire){
    if($expire < time()) throw new Exception("The expiration date need to be > than actual date ".$expire." - ".time(), 1);
    $r = parent::execSqlRequest("UPDATE user_krypto SET created_date_user=:created_date_user WHERE id_user=:id_user",
                                [
                                  'created_date_user' => $expire,
                                  'id_user' => $this->_getUserID()
                                ]);

    if(!$r) throw new Exception("Error : Fail to update free trial expire date", 1);

  }

  /**
   * Change premium end date (or create)
   * @param String $expire Ending date
   */
  public function _setPremium($expire){
    if($expire < time()) throw new Exception("The expiration date need to be > than actual date", 1);

    $r = parent::querySqlRequest("SELECT * FROM charges_krypto WHERE date_charges < :date_charges AND id_user=:id_user AND status_charges=:status_charges",
                                [
                                  'date_charges' => time(),
                                  'id_user' => $this->_getUserID(),
                                  'status_charges' => 1
                                ]);

    if(count($r) > 0){
      $r = parent::execSqlRequest("UPDATE charges_krypto SET ndays_charges=:ndays_charges WHERE id_user=:id_user AND id_charges=:id_charges",
                                  [
                                    'ndays_charges' => ceil(($expire - $r[0]['date_charges']) / 86400),
                                    'id_user' => $this->_getUserID(),
                                    'id_charges' => $r[0]['id_charges']
                                  ]);
      if(!$r) throw new Exception("Error : Fail to change premium", 1);
    } else {
      $r = parent::execSqlRequest("INSERT INTO charges_krypto (id_user, date_charges, status_charges, ndays_charges) VALUES
                                  (:id_user, :date_charges, :status_charges, :ndays_charges)",
                                  [
                                    'id_user' => $this->_getUserID(),
                                    'date_charges' => time(),
                                    'status_charges' => 1,
                                    'ndays_charges' => ceil(($expire - time()) / 86400)
                                  ]);
      if(!$r) throw new Exception("Error : Fail to add premium", 1);

    }



    if(!$r) throw new Exception("Error : Fail to update free trial expire date", 1);
  }

  /**
   * Generate demo user
   * @return Array  Data user
   */
  public function _generateDemoUser(){

    $password = substr(str_shuffle('123456789'), 0, 5);
    $nameList = ['Rosalinda Jarosz', 'Gertude Terra', 'Janell Figgs', 'Ethelyn Kliebert', 'Jacelyn Magnuson', 'Tawna Closson', 'Marielle Diederich', 'Tamekia Kingsley', 'Lupe Giefer', 'Santa Bergan', 'Maira Eliason', 'Porfirio Bolanos', 'Benjamin Cassidy', 'Odell Finney', 'Ian Mcfarlain', 'Wei Cabezas', 'Bret Aberle', 'Vernita Wheeling', 'Kim Maestas', 'Amina Menter', 'Bulah Harstad', 'Lexie Ference', 'Earnest Skolnick', 'Evelyne Ross', 'Debroah Kerby', 'Janae Maule', 'Margarette Stock',       'Daniell Tulloch', 'Diana Podesta'];
    shuffle($nameList);
    $nameSelected = $nameList[0];

    $email = strtolower(str_replace(' ', '_', $nameSelected)).substr(str_shuffle('123456789'), 0, 8).'@ovrley.com';

    $this->_createUser($email, $nameSelected, $password, '', 'standard', '', 0, 1);

    return ['email' => $email, 'password' => $password];

  }

  public function _accessWallets($source = 'coinbase'){
    $r = parent::querySqlRequest("SELECT * FROM wallets_kryptos WHERE id_user=:id_user AND source_wallets=:source_wallets",
                                [
                                  'id_user' => $this->_getUserID(),
                                  'source_wallets' => $source
                                ]);
    if(count($r) == 0) return null;
    return $r[0]['acces_token_wallets'];
  }

  public function _googleTwoFactorEnable($userId){
    $r = parent::querySqlRequest("SELECT * FROM googletfs_krypto WHERE id_user=:id_user AND status_googletfs=:status_googletfs",
                                  [
                                    'id_user' => $userId,
                                    'status_googletfs' => 1
                                  ]);
    if(count($r) > 0) return true;
    return false;
  }

  public function _generateGoogleTwoFactor($App){
    $g = new \Google\Authenticator\GoogleAuthenticator();
    $secret = $g->generateSecret();

    $d = parent::execSqlRequest("DELETE FROM googletfs_krypto WHERE id_user=:id_user AND status_googletfs=:status_googletfs",
                                [
                                  'id_user' => $this->_getUserID(),
                                  'status_googletfs' => 0
                                ]);

    if(!$d) throw new Exception("Error : Fail to clean google authentification cache", 1);


    $r = parent::execSqlRequest("INSERT INTO googletfs_krypto (id_user, date_googletfs, secret_googletfs)
                                  VALUES (:id_user, :date_googletfs, :secret_googletfs)",
                                  [
                                    'id_user' => $this->_getUserID(),
                                    'date_googletfs' => time(),
                                    'secret_googletfs' => App::encrypt_decrypt('encrypt', $secret)
                                  ]);

    if(!$r) throw new Exception("Error : Fail to save user google authentification", 1);

    return [
      'qrcode' => $g->getURL($this->_getEmail(), $App->_getAppTitle(), $secret)
    ];

  }

  private function _getGoogleTFSSecret($user = null){

    $r = parent::querySqlRequest("SELECT * FROM googletfs_krypto WHERE id_user=:id_user",
                                  [
                                    'id_user' => (!is_null($user) ? $user : $this->_getUserID())
                                  ]);

    if(count($r) == 0) return null;
    return App::encrypt_decrypt('decrypt', $r[0]['secret_googletfs']);

  }

  public function _checkGoogleTFS($code, $user = null){

    $secret = $this->_getGoogleTFSSecret($user);
    if(is_null($secret)) throw new Exception("Error : Invalid secret", 1);

    $g = new \Google\Authenticator\GoogleAuthenticator();

    return $g->checkCode($secret, $code);

  }

  public function _enableGoogleTFS(){
    $r = parent::execSqlRequest("UPDATE googletfs_krypto SET status_googletfs=:status_googletfs WHERE id_user=:id_user",
                                [
                                    'id_user' => $this->_getUserID(),
                                    'status_googletfs' => 1
                                ]);
    if(!$r) throw new Exception("Error : Fail to active Google TFS", 1);
    return true;
  }

  public function _disableGoogleTFS(){
    $r = parent::execSqlRequest("DELETE FROM googletfs_krypto WHERE id_user=:id_user",
                                [
                                  'id_user' => $this->_getUserID()
                                ]);
    if(!$r) throw new Exception("Error : Fail to disable Google TFS", 1);
    return true;
  }

  public function _getAssociateColor(){

    $listColor = ['#e14880', '#267cc9',
                  '#fcad48', '#ed4141',
                  '#39d95f', '#674bee',
                  '#3cacbd', '#4263d0',
                  '#8bd434', '#8f4fe6'];

    return $listColor[$this->_getUserID() % count($listColor)];

  }

  public function _accessAllowedFeature($App, $feature){

    $Charge = $this->_getCharge($App);

    if($Charge->_activeAbo()) return true;

    $featureList = $App->_getFeaturesAllowedFree();
    if(!array_key_exists($feature, $featureList)) return true;
    if($featureList[$feature] == 1) return true;
    return false;

  }

  public function _sendActivationEmailLink($email){
    $App = new App(false);

    $getInfosUser = parent::querySqlRequest("SELECT * FROM user_krypto WHERE email_user=:email_user AND oauth_user=:oauth_user",
                                            [
                                              'email_user' => $email,
                                              'oauth_user' => 'standard'
                                            ]);
    if(count($getInfosUser) == 0) throw new Exception("Error SQL : Fail to retreive create user for check email", 1);
    $activationCode = App::encrypt_decrypt('encrypt', $email.'||--||'.$getInfosUser[0]['id_user']);

    $template = new Liquid\Template();
    $template->parse(file_get_contents(APP_URL.'/app/modules/kr-user/templates/activeAccount.tpl'));

    // Render & send email
    $App->_sendMail($email, $App->_getAppTitle().' - Account activation', $template->render([
      'APP_URL' => APP_URL,
      'APP_TITLE' => $App->_getAppTitle(),
      'LOGO_BLACK' => $App->_getLogoBlackPath(),
      'SUBJECT' => $App->_getAppTitle().' - Account activation',
      'USER_ACTIVE_LINK' => APP_URL.'/?active='.$activationCode,
      'USER_NAME' => $getInfosUser[0]['name_user']
    ]));

  }

  public function _checkParseActivationAccount(){
    if(empty($_GET) || !isset($_GET['active']) || empty($_GET['active'])) return false;
    $activeCode = App::encrypt_decrypt('decrypt', $_GET['active']);
    $activeCode = explode('||--||', $activeCode);
    if(count($activeCode) != 2) return false;
    $r = parent::querySqlRequest("SELECT * FROM user_krypto WHERE email_user=:email_user AND oauth_user=:oauth_user AND id_user=:id_user AND status_user=:status_user",
                                [
                                  'email_user' => $activeCode[0],
                                  'oauth_user' => 'standard',
                                  'id_user'    => $activeCode[1],
                                  'status_user' => 2
                                ]);
    if(count($r) == 0) return false;
    $r = parent::execSqlRequest("UPDATE user_krypto SET status_user=1 WHERE email_user=:email_user AND oauth_user=:oauth_user AND id_user=:id_user AND status_user=:status_user",
                                [
                                  'email_user' => $activeCode[0],
                                  'oauth_user' => 'standard',
                                  'id_user'    => $activeCode[1],
                                  'status_user' => 2
                                ]);
    if(!$r) return false;
    return true;
  }

  public function _generateReferalCode(){

    $code = htmlentities($this->_getName(), ENT_NOQUOTES, 'utf-8');
    $code = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $code);
    $code = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $code);
    $code = preg_replace('#&[^;]+;#', '', $code);
    $code = str_replace([' ', '-', '_'], ['', '', ''], $code);
    $code = strtolower($code);

    $found = count(parent::querySqlRequest("SELECT * FROM referal_krypto WHERE code_referal=:code_referal", ['code_referal' => $code])) > 0;
    $base_code = $code;
    while($found){
      $code = $base_code.rand(0,100000);
      $found = count(parent::querySqlRequest("SELECT * FROM referal_krypto WHERE code_referal=:code_referal", ['code_referal' => $code])) > 0;
    }

    $r = parent::execSqlRequest("INSERT INTO referal_krypto (id_user, code_referal, date_referal)
                                VALUES (:id_user, :code_referal, :date_referal)",
                                [
                                  'id_user' => $this->_getUserID(),
                                  'code_referal' => $code,
                                  'date_referal' => time()
                                ]);
  }

  public function _checkReferalLink(){

    $r = parent::querySqlRequest("SELECT * FROM referal_krypto WHERE id_user=:id_user", ['id_user' => $this->_getUserID()]);
    if(count($r) == 0){
      $this->_generateReferalCode();
      return $this->_checkReferalLink();
    }
    return $r[0]['code_referal'];

  }

  public function _getReferalUrl(){
    return $this->_checkReferalLink();
  }

  public function _getAssociateReferall(){
    $r = parent::querySqlRequest("SELECT * FROM referal_histo_krypto WHERE id_user=:id_user",
                                [
                                  'id_user' => $this->_getUserID()
                                ]);

    if(count($r) == 0) return null;
    $associateCode = parent::querySqlRequest("SELECT * FROM referal_krypto WHERE code_referal=:code_referal",
                                            [
                                              'code_referal' => $r[0]['code_referal']
                                            ]);

    if(count($associateCode) == 0) return null;
    return new User($associateCode[0]['id_user']);
  }

  public function _getUserStatus(){

    $r = parent::querySqlRequest("SELECT * FROM user_status_krypto WHERE id_user=:id_user", ['id_user' => $this->_getUserID()]);
    if(count($r) == 0) return 0;
    if(time() - intval($r[0]['last_update_user_status']) > 10) return 0;
    return $r[0]['type_user_status'];
  }

  public function _getUserStatusText($status){
    if($status == 1) return 'online';
    if($status == 2) return 'busy';
    return 'offline';
  }

  public function _updateUserStatus($newStatus = null){

    $r = parent::querySqlRequest("SELECT * FROM user_status_krypto WHERE id_user=:id_user", ['id_user' => $this->_getUserID()]);
    if(count($r) == 0){
      $r = parent::execSqlRequest("INSERT INTO user_status_krypto (id_user, last_update_user_status, type_user_status) VALUES
                                  (:id_user, :last_update_user_status, :type_user_status)",
                                  [
                                    'id_user' => $this->_getUserID(),
                                    'last_update_user_status' => time(),
                                    'type_user_status' => 1
                                  ]);
      if(!$r) throw new Exception("Error SQL : Fail to add status user", 1);

    } else {
      if(is_null($newStatus)){
        $r = parent::execSqlRequest("UPDATE user_status_krypto SET last_update_user_status=:last_update_user_status WHERE id_user=:id_user",
                                    [
                                      'last_update_user_status' => time(),
                                      'id_user' => $this->_getUserID()
                                    ]);
      } else {
        $r = parent::execSqlRequest("UPDATE user_status_krypto SET last_update_user_status=:last_update_user_status, type_user_status=:type_user_status WHERE id_user=:id_user",
                                    [
                                      'last_update_user_status' => time(),
                                      'id_user' => $this->_getUserID(),
                                      'type_user_status' => $newStatus
                                    ]);
      }

      if(!$r) throw new Exception("Error SQL : Fail to update status user", 1);
    }

  }

  public function _getUserLocation($countryCode = false){
    $r = parent::querySqlRequest("SELECT * FROM user_login_history_krypto WHERE id_user=:id_user ORDER BY id_user_login_history DESC LIMIT 1",
                                [
                                  'id_user' => $this->_getUserID()
                                ]);

    if(count($r) == 0) return null;
    if($countryCode) return $r[0]['country_code_user_login_history'];
    return $r[0]['location_user_login_history'];
  }

  public function _saveUserLoginHistory(){
    return true;
    $CurrentUserIP = App::_getVisitorIP();
    $r = parent::querySqlRequest("SELECT * FROM user_login_history_krypto WHERE id_user=:id_user AND ip_user_login_history=:ip_user_login_history",
                                [
                                  'id_user' => $this->_getUserID(),
                                  'ip_user_login_history' => $CurrentUserIP
                                ]);

    $ipUnknow = count($r) == 0;


    // Get geoip location
    $ch =  curl_init('http://ip-api.com/json/'.$CurrentUserIP);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_ENCODING,  '');
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
    $s = json_decode(curl_exec($ch), true);

    $geoAdd = true;
    if(array_key_exists('type', $s) && $s['status'] == "fail"){
      error_log('Error : Fail to get Geo location : '.$s['msg']);
      $geoAdd = false;
    }

    $r = parent::execSqlRequest("INSERT INTO user_login_history_krypto (id_user, date_user_login_history, ip_user_login_history, device_user_login_history, location_user_login_history, country_code_user_login_history)
                                VALUES (:id_user, :date_user_login_history, :ip_user_login_history, :device_user_login_history, :location_user_login_history, :country_code_user_login_history)",
                                [
                                  'id_user' => $this->_getUserID(),
                                  'date_user_login_history' => time(),
                                  'ip_user_login_history' => $CurrentUserIP,
                                  'device_user_login_history' => $_SERVER['HTTP_USER_AGENT'],
                                  'location_user_login_history' => ($geoAdd ? ($s['city'] != 'false' ? $s['city'].' ('.$s['country'].')' : $s['country']) : ''),
                                  'country_code_user_login_history' => ($geoAdd ? $s['countryCode'] : '')
                                ]);

      if(!$r) throw new Exception("Error : Fail to add user login history", 1);

      if($ipUnknow){
        $App = new App();
        if($App->_smtpEnabled()){
          $template = new Liquid\Template();
          $template->parse(file_get_contents(APP_URL.'/app/modules/kr-user/templates/unknowLogin.tpl'));

          // Render & send email
          $App->_sendMail($this->_getEmail(), $App->_getAppTitle().' - Successful Login From New IP '.$CurrentUserIP.' - '.date('d/m/Y H:i:s', time()), $template->render([
            'APP_URL' => APP_URL,
            'DEVICE' => $_SERVER['HTTP_USER_AGENT'],
            'APP_TITLE' => $App->_getAppTitle(),
            'LOGO_BLACK' => $App->_getLogoBlackPath(),
            'SUBJECT' => $App->_getAppTitle().' - New user logged',
            'USER_NAME' => $this->_getName(),
            'EMAIL' => $this->_getEmail(),
            'IP' => $CurrentUserIP,
            'LOCATION' => ($geoAdd ? ($s['city'] != 'false' ? $s['city'].' ('.$s['country'].')' : $s['country']) : 'Unknow'),
            'DATE' => date('d/m/Y H:i:s', time())
          ]));
        }

      }

      return true;


  }

  public function _getHistoryLoginUser(){
    return parent::querySqlRequest("SELECT * FROM user_login_history_krypto WHERE id_user=:id_user ORDER BY date_user_login_history DESC LIMIT 15",
                                  [
                                    'id_user' => $this->_getUserID()
                                  ]);
  }

  public function _getListUserSubscribeNotification($App = null){

    $UserList = [];

    foreach (parent::querySqlRequest("SELECT * FROM user_krypto WHERE admin_user=:admin_user AND status_user=:status_user", ['admin_user' => 0, 'status_user' => 1]) as $key => $value) {
      $DistantUser = new User($value['id_user']);
      $Charges = new Charges($DistantUser, $App);
      if($Charges->_isTrial() || $Charge->_activeAbo()){

        $sendTitle = null;
        $type = 'trial';
        $expireDate = '';
        if($App->_nbDaysSendMailWhenTrialSubsEnded() == $Charges->_getTrialNumberDay()){
          $expireDate = date('d/m/Y H:i', $Charges->_getTimestampTrialEnd());
          $sendTitle = 'Your trial end in '.$App->_nbDaysSendMailWhenTrialSubsEnded().' day'.($App->_nbDaysSendMailWhenTrialSubsEnded() > 1 ? 's' : '');
        }

        if($App->_nbDaysSendMailWhenTrialSubsEnded() == $Charges->_getTimeRes()){
          $expireDate = date('d/m/Y H:i', $Charges->_getTimestampChargeEnd());
          $sendTitle = 'Your subscription end in '.$App->_nbDaysSendMailWhenTrialSubsEnded().' day'.($App->_nbDaysSendMailWhenTrialSubsEnded() > 1 ? 's' : '');
          $type = 'premium';
        }

        if(!is_null($sendTitle)) {

          $template = new Liquid\Template();
          $template->parse(file_get_contents(APP_URL.'/app/modules/kr-user/templates/subscribeRequest.tpl'));

          // Render & send email
          $App->_sendMail($DistantUser->_getEmail(), $App->_getAppTitle().' - '.$sendTitle, $template->render([
            'APP_URL' => APP_URL,
            'SUB_TYPE' => $type,
            'LOGO_BLACK' => $App->_getLogoBlackPath(),
            'EXPIRE_DATE' => $expireDate,
            'APP_TITLE' => $App->_getAppTitle(),
            'SUBJECT' => $App->_getAppTitle().' - '.$sendTitle,
            'USER_NAME' => $DistantUser->_getName()
          ]));

          echo $DistantUser->_getEmail();

        }

      }

    }

    return $UserList;

  }

  public function _showIntro(){
    $r = parent::querySqlRequest("SELECT * FROM user_intro_krypto WHERE id_user=:id_user", ['id_user' => $this->_getUserID()]);
    if(count($r) == 0){
      $r = parent::execSqlRequest("INSERT INTO user_intro_krypto (id_user, date_user_intro) VALUES (:id_user, :date_user_intro)",
                                 [
                                   'id_user' => $this->_getUserID(),
                                   'date_user_intro' => time()
                                 ]);
      if(!$r) throw new Exception("Error : Fail to save user intro", 1);
      return true;
    }
    return false;
  }

  public function _getAdminList(){
    $res = [];
    foreach (parent::querySqlRequest("SELECT * FROM user_krypto WHERE admin_user=:admin_user ORDER BY status_user DESC",
                                    [
                                      'admin_user' => 1
                                    ]) as $key => $value) {
      if($value['id_user'] == $this->_getUserID()) continue;
      $res[] = new User($value['id_user']);
    }
    return $res;
  }

  public function _showNewsPopupNeeded($App){
    $loginHistory = array_slice($this->_getHistoryLoginUser(), 0, -1);
    $showPopup = true;
    if(count($loginHistory) <= 1){ $showPopup = false;}

    $vs = parent::querySqlRequest("SELECT * FROM user_newspopup WHERE id_user=:id_user",
                                  [
                                    'id_user' => $this->_getUserID()
                                  ]);

    $founded = count($vs) > 0;

    if(count($founded) == 0) return false;

    if($vs[0]['last_newspopup'] >= $App->_getNewsPopupLastUpdate()) return false;

    if(!$founded){
      $r = parent::execSqlRequest("INSERT INTO user_newspopup (id_user, last_newspopup) VALUES (:id_user, :last_newspopup)",
                                  [
                                    'id_user' => $this->_getUserID(),
                                    'last_newspopup' => time()
                                  ]);
    } else {
      $r = parent::execSqlRequest("UPDATE user_newspopup SET last_newspopup=:last_newspopup WHERE id_user=:id_user",
                                  [
                                    'id_user' => $this->_getUserID(),
                                    'last_newspopup' => time()
                                  ]);
    }
    return $showPopup;
  }

  public function _getUserList(){
    return parent::querySqlRequest("SELECT * FROM user_krypto WHERE status_user=:status_user", ['status_user' => '1']);
  }

  public function _delete(){
    $tableList = [
      'balance_krypto' => 'id_user',
      'banktransfert_krypto' => 'id_user',
      'banktransfert_proof_krypto' => 'id_user',
      'blocked_user_chat_krypto' => 'id_user',
      'blockfolio_krypto' => 'id_user',
      'blockonomics_address_krypto' => 'id_user',
      'blockonomics_transactions_krypto' => 'id_user',
      'charges_krypto' => 'id_user',
      'converter_krypto' => 'id_user',
      'dashboard_krypto' => 'id_user',
      'deposit_history_krypto' => 'id_user',
      'deposit_history_proof_krypto' => 'id_user',
      'googletfs_krypto' => 'id_user',
      'graph_krypto' => 'id_user',
      'holding_krypto' => 'id_user',
      'identity_asset_krypto' => 'id_user',
      'identity_krypto' => 'id_user',
      'internal_order_krypto' => 'id_user',
      'leader_board_krypto' => 'id_user',
      'msg_room_chat_krypto' => 'id_user',
      'notification_center_krypto' => 'id_user',
      'notification_krypto' => 'id_user',
      'order_krypto' => 'id_user',
      'referal_histo_krypto' => 'id_user',
      'referal_krypto' => 'id_user',
      'top_list_krypto' => 'id_user',
      'user_intro_krypto' => 'id_user',
      'user_login_history_krypto' => 'id_user',
      'user_newspopup' => 'id_user',
      'user_room_chat_krypto' => 'id_user',
      'user_settings_krypto' => 'id_user',
      'user_status_krypto' => 'id_user',
      'user_thirdparty_selected_krypto' => 'id_user',
      'user_widthdraw_krypto' => 'id_user',
      'visits_krypto' => 'id_user',
      'watching_krypto' => 'id_user',
      'widthdraw_history_krypto' => 'id_user',
      'user_krypto' => 'id_user'
    ];

    foreach ($tableList as $key => $value) {
      $r = parent::execSqlRequest("DELETE FROM ".$key." WHERE ".$value."=:id_user", ['id_user' => $this->_getUserID()]);
      if(!$r) error_log('Fail to delete user informations : table : '.$key);
    }

  }

}

?>
