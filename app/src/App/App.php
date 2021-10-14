<?php

/**
 * Main application class
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
class App extends MySQL {

  /**
   * Module list available
   * @var Array Module Array
   */
  private $modulesList = [];

  /**
   * Settings data
   * @var Array List Krypto settings
   */
  private $settingsData = null;

  /**
   * Application constructor
   * @param boolean $loadmodules If load module or just access to config data
   */
  public function __construct($loadmodules = false){

    $this->_loadPlatform();


    if(!defined('MYSQL_HOST') && file_exists('install')) header('Location: '.(defined('FILE_PATH') ? APP_URL : '').'/install/');

    // If loadmodule, load modules
    if($loadmodules) $this->_loadModules();

    // Load application settings in Database
    $this->_loadAppSettings();

  }

  public function _loadPlatform(){
    set_time_limit(180);
    if(true){
      ini_set('display_errors', 0);
      ini_set('display_startup_errors', 0);
      error_reporting(0);
    } else {
      ini_set('display_errors', 1);
      ini_set('display_startup_errors', 1);
      error_reporting(-1);
    }
  }

  public static function _getVersion(){ return base64_encode("4.1.0"); }

  public function _installDirectoryExist(){
    return file_exists('install');
  }

  /**
   * Load module function
   */
  public function _loadModules(){

    // Get list modules available in application
    foreach (scandir($_SERVER['DOCUMENT_ROOT'].FILE_PATH.'/app/modules') as $directory) {

      // Check if file is an file
      if($directory == "." || $directory == "..") continue;

      // Get directory path
      $directoryPath = $_SERVER['DOCUMENT_ROOT'].FILE_PATH.'/app/modules/'.$directory;

      // Check if file parsed is a directory (module need to be a directory)
      if(!is_dir($directoryPath)){

        // Save error in log file
        error_log('Fail to load module : '.$directory.' --> is not a directory');
        continue;
      }

      // Load module
      $ModuleLoad = new AppModule($directory);

      // Check module configuratino file
      if(!$ModuleLoad->_checkConfig()){

        // Save error in log file
        error_log('Fail to load module : '.$directory.' --> wrong configuration');
        continue;
      }

      // Check if module is enabled
      if($ModuleLoad->_isEnable()){
        // If enabled, save in module list
        $this->modulesList[$directory] = $ModuleLoad;
      }
    }
  }

  /**
   * Get assets list for all modules enabled
   * @param  string $typeAssets Type assets loaded (css, js)
   * @return String             Assets path
   */
  public function _getAssetsList($typeAssets = "css"){
    $res = "";
    // Get list modules
    foreach ($this->modulesList as $moduleObject) {
      // Load assets for current module
      foreach ($moduleObject->_loadAssets($typeAssets) as $asset) {
        $res .= $asset."\n\r"; // Add module assets in return data
      }
    }
    return $res;
  }

  /**
   * Load modules controllers
   */
  public function _loadModulesControllers(){

    // Get list modules
    foreach ($this->modulesList as $moduleObject) {
      // Get list modules controllers
      foreach ($moduleObject->_loadControllers() as $controlers) {
        // Require controllers class
        if($controlers == "error_log") continue;
        require $moduleObject->_getModulePath().'/src/'.$controlers;
      }
    }

  }

  /**
   * Load application settings from database
   */
  private function _loadAppSettings(){

    // Get list settings saved in database
    $r = parent::querySqlRequest("SELECT * FROM settings_krypto", []);

    // Reset all settings & set as an array
    $this->settingsData = [];

    // Get list settings
    foreach ($r as $key => $vSettings) {

      // If settings was en encrypted settings ==> decrypt
      if($vSettings['encrypted_settings'] == 1) $vSettings['value_settings'] = App::encrypt_decrypt('decrypt', $vSettings['value_settings']);

      // Save settings in object
      $this->settingsData[$vSettings['key_settings']] = $vSettings['value_settings'];
    }
  }

  /**
   * Change settings attribute
   * @param  String $key Settings key
   * @param  String $val Settings value
   */
  private function _saveSettingsAttribute($key, $val, $encrypt = false){
    if($encrypt) $val = App::encrypt_decrypt('encrypt', $val);
    if(!$encrypt && strlen(App::encrypt_decrypt('decrypt', $val)) > 0) $encrypt = true;
    if(!array_key_exists($key, $this->settingsData)){
      $r = parent::execSqlRequest("INSERT INTO settings_krypto (key_settings, value_settings, encrypted_settings)
                                  VALUES (:key_settings, :value_settings, :encrypted_settings)",
                                  [
                                    'key_settings' => $key,
                                    'value_settings' => $val,
                                    'encrypted_settings' => ($encrypt ? 1 : 0)
                                  ]);
    } else {
      $r = parent::execSqlRequest("UPDATE settings_krypto SET value_settings=:nval WHERE key_settings=:key_settings", ['nval' => $val, 'key_settings' => $key]);
    }

    if(!$r) throw new Exception("Error : Fail to update settings key : ".$key, 1);
    return true;
  }

  /**
   * Get settings attribute from saved
   * @param  String $key Settings key needed
   * @return String      Settings value
   */
  private function _getSettingsAttribute($key){
    // If is null or not exist, return null
    if(is_null($this->settingsData) || !array_key_exists($key, $this->settingsData)) return null;

    // Return associate value
    return $this->settingsData[$key];
  }

  /**
   * Get if app allow signup
   * @return Boolean
   */
  public function _allowSignup(){ return $this->_getSettingsAttribute('allow_signup') == 1; }

  /**
   * Get if the app is in maintenance mode
   * @return Boolean
   */
  public function _isMaintenanceMode(){ return $this->_getSettingsAttribute('maintenance_mode') == 1; }

  /**
   * Get support email
   * @return String Support email
   */
  public function _getSupportEmail(){ return $this->_getSettingsAttribute('support_email'); }

  public function _getSupportPhone(){ return $this->_getSettingsAttribute('support_phone'); }

  public function _getSupportAddress(){ return $this->_getSettingsAttribute('support_address'); }

  public function _getDPOEmail(){ return $this->_getSettingsAttribute('dpo_email'); }

  public function _getDPOPhone(){ return $this->_getSettingsAttribute('dpo_phone'); }

  /**
   * Get if app enable google authentification
   * @return Boolean
   */
  public function _enableGooglOauth(){ return $this->_getSettingsAttribute('google_oauth') == 1; }

  public function _enableFacebookOauth(){ return $this->_getSettingsAttribute('facebook_oauth') == 1; }

  public function _getFacebookAppID(){ return $this->_getSettingsAttribute('facebook_appid'); }
  public function _getFacebookAppSecret(){ return $this->_getSettingsAttribute('facebook_appsecret'); }

  public function _chatIsDisabled(){ return $this->_getSettingsAttribute('chat_disabled') == 1; }

  /**
   * Get app title
   * @return String Application title
   */
  public function _getAppTitle(){ return $this->_getSettingsAttribute('title_app'); }

  /**
   * Get app description
   * @return String Application description
   */
  public function _getAppDescription(){ return $this->_getSettingsAttribute('description_app'); }

  /**
   * Get google analytic code
   * @return String Google analytic
   */
  public function _getGoogleAnalytics(){ return $this->_getSettingsAttribute('google_analytic'); }

  /**
   * Get number format
   * @return String Number format
   */
  public function _getNumberFormat(){ return $this->_getSettingsAttribute('number_format'); }

  /**
   * Get if smtp is enabled
   * @return Boolean
   */
  public function _smtpEnabled(){ return $this->_getSettingsAttribute('smtp_enabled') == 1; }

  /**
   * Get smtp server host
   * @return String Stmp server
   */
  public function _getSmtpServer(){ return $this->_getSettingsAttribute('smtp_server'); }

  /**
   * Get smtp user
   * @return String Smtp user
   */
  public function _getSmtpUser(){ return $this->_getSettingsAttribute('smtp_user'); }

  /**
   * Get smtp password
   * @return String Smtp password
   */
  public function _getSmtpPassword(){ return $this->_getSettingsAttribute('smtp_password'); }

  /**
   * Get smtp port
   * @return String smtp port
   */
  public function _getSmtpPort(){ return $this->_getSettingsAttribute('smtp_port'); }

  /**
   * Get smtp security
   * @return String smtp security
   */
  public function _getSmtpSecurity(){
    $security = $this->_getSettingsAttribute('smtp_security');
    if($security != "0" && $security != "tls" && $security != "ssl") return "0";
    return $security;
  }

  /**
   * Get smtp from name
   * @return String Smtp from name
   */
  public function _getSmtpFrom(){ return $this->_getSettingsAttribute('smtp_from'); }

  public function _getMailType(){
    if(is_null($this->_getSettingsAttribute('mail_type'))) return "smtp";
    return $this->_getSettingsAttribute('mail_type');
  }

  public function _getMailSendingAddress(){
    if(is_null($this->_getSettingsAttribute('mail_sending_email'))) return "no-reply@krypto.com";
    return $this->_getSettingsAttribute('mail_sending_email');
  }

  /**
   * Get if app enable free trial
   * @return Boolean
   */
  public function _freetrialEnabled(){ return intval($this->_getSettingsAttribute('freetrial_enabled')) == 1; }

  /**
   * Get number free trial day
   * @return Int Number day free trial
   */
  public function _getChargeTrialDay(){ return intval($this->_getSettingsAttribute('charge_trial_nbdays')); }

  /**
   * Get if app allow credit card payment
   * @return Boolean
   */
  public function _creditCardEnabled(){
    if(is_null($this->_getPrivateStripeKey()) || empty($this->_getPrivateStripeKey())) return false;
    return intval($this->_getSettingsAttribute('creditcard_enabled')) == 1;
  }

  /**
   * Get if app enabled subscription
   * @return Boolean
   */
  public function _subscriptionEnabled(){ return intval($this->_getSettingsAttribute('subscription_enabled')) == 1; }

  /**
   * Get app premium name
   * @return String premium name
   */
  public function _getPremiumName(){ return $this->_getSettingsAttribute('premium_name'); }

  /**
   * Get app charge currency
   * @return String Charge currency (ex : USD)
   */
  public function _getChargeCurrency(){ return ($this->_getSettingsAttribute('charge_currency') == null ? 'USD' : $this->_getSettingsAttribute('charge_currency')); }

  /**
   * Get app charge currency symbol
   * @return String Charge currency symbol (ex : $)
   */
  public function _getChargeCurrencySymbol(){

    // Search currnecy in database
    $r = parent::querySqlRequest("SELECT * FROM currency_krypto WHERE code_iso_currency=:code_iso_currency", ['code_iso_currency' => $this->_getChargeCurrency()]);

    // If not found return default symbol : $
    if(count($r) == 0) return '$';

    // Return symbol currency
    return $r[0]['symbol_currency'];
  }

  /**
   * Get app charge text features
   * @return String Charge text features
   */
  public function _getChargeText(){ return $this->_getSettingsAttribute('premium_features'); }

  /**
   * Get app payment successfull text
   * @return String payment success text
   */
  public function _getPaymentResultDone(){ return $this->_getSettingsAttribute('payment_success'); }

  /**
   * Get app private Stripe Key
   * @return String Stripe Private Key
   */
  public function _getPrivateStripeKey(){ return $this->_getSettingsAttribute('stripe_privatekey'); }

  /**
   * Get if paypal is enabled
   * @return Boolean
   */
  public function _paypalEnabled(){
    if(is_null($this->_getPaypalClientID()) || empty($this->_getPaypalClientID()) || is_null($this->_getPaypalClientSecret()) || empty($this->_getPaypalClientSecret())) return false;
    return intval($this->_getSettingsAttribute('paypal_enabled')) == 1;
  }

  /**
   * Get if paypal is enabled as live mode
   * @return Boolean
   */
  public function _paypalLiveModeEnabled(){
    return intval($this->_getSettingsAttribute('paypal_live')) == 1;
  }

  /**
   * Get app Paypal client ID
   * @return String Paypal client ID
   */
  public function _getPaypalClientID(){ return $this->_getSettingsAttribute('paypal_clientid'); }

  /**
   * Get app Paypal client Secret
   * @return String Paypal client Secret
   */
  public function _getPaypalClientSecret(){ return $this->_getSettingsAttribute('paypal_secret'); }

  /**
   * Get Fortumo secret key
   * @return String Secret key
   */
  public function _getFortumoSecretKey(){ return $this->_getSettingsAttribute('fortumo_secret'); }

  /**
   * Get Fortumo service key
   * @return String Service key
   */
  public function _getFortumoServiceKey(){ return $this->_getSettingsAttribute('fortumo_service'); }

  /**
   * Get if Fortumo is enabled
   * @return Boolean
   */
  public function _fortumoEnabled(){ return $this->_getSettingsAttribute('fortumo_enabled') == 1; }

  /**
   * Get if CoinGate is enabled
   * @return Boolean
   */
  public function _coingateEnabled(){ return $this->_getSettingsAttribute('coingate_enabled') == 1; }

  /**
   * Get if Coingate is on live mode
   * @return Boolean
   */
  public function _coingateLiveMode(){ return $this->_getSettingsAttribute('coingate_live_mode') == 1; }

  /**
   * Get Coingate app id
   * @return String
   */
  public function _getCoingateAppID(){ return $this->_getSettingsAttribute('coingate_app_id'); }

  /**
   * Get Coingate api secret
   * @return String
   */
  public function _getCoingateApiSecret(){ return $this->_getSettingsAttribute('coingate_api_secret'); }

  public function _getCoingateConvertionTo(){ return $this->_getSettingsAttribute('coingate_paymentconvertion'); }

  /**
   * Get Coingate api key
   * @return String
   */
  public function _getCoingateApiKey(){ return $this->_getSettingsAttribute('coingate_api_key'); }

  public function _getCoinGateAuthToken(){ return $this->_getSettingsAttribute('coingate_authtoken'); }

  public function _paymentReferencePattern(){ return $this->_getSettingsAttribute('payment_ref_pattern'); }



  public function _getCoinGateCryptoCurrencyDepositAllowed(){
    return ['BTC', 'LTC', 'ETH', 'BCH'];
  }

  /**
   * Get if mollie is enabled
   * @return Boolean
   */
  public function _mollieEnabled(){ return $this->_getSettingsAttribute('mollie_enabled') == 1; }

  /**
   * Get Mollie key
   * @return String
   */
  public function _getMollieKey(){ return $this->_getSettingsAttribute('mollie_key'); }

  public function _raveflutterwaveEnabled(){ return $this->_getSettingsAttribute('raveflutterwave_enabled') == 1; }
  public function _getRaveflutterwavePublicKey(){ return $this->_getSettingsAttribute('raveflutterwave_public_key'); }
  public function _getRaveflutterwaveSecretKey(){ return $this->_getSettingsAttribute('raveflutterwave_secret_key'); }
  public function _raveflutterwaveSandboxMode(){ return $this->_getSettingsAttribute('raveflutterwave_sandbox') == 1; }

  public function _getRaveflutterwaveTitle(){ return $this->_getSettingsAttribute('raveflutterwave_title'); }
  public function _getRaveflutterwavePrefix(){ return $this->_getSettingsAttribute('raveflutterwave_prefix'); }

  public function _coinbasecommerceEnabled(){ return $this->_getSettingsAttribute('coinbasecommerce_enabled') == 1; }
  public function _getCoinbaseCommerceAPIKey(){ return $this->_getSettingsAttribute('coinbasecommerce_apikey'); }
  public function _getCoinbaseCommercePaymentTitle(){ return $this->_getSettingsAttribute('coinbasecommerce_paymentitle'); }

  public function _coinpaymentsEnabled(){ return $this->_getSettingsAttribute('coinpayments_enabled') == 1; }
  public function _getCoinpaymentsPublicKey(){ return $this->_getSettingsAttribute('coinpayments_publickey'); }
  public function _getCoinpaymentsPrivateKey(){ return $this->_getSettingsAttribute('coinpayments_privatekey'); }
  public function _getCoinpaymentsMarchandID(){ return $this->_getSettingsAttribute('coinpayments_marchant_id'); }
  public function _getCoinpaymentsIPNSecret(){ return $this->_getSettingsAttribute('coinpayment_ipn_secret'); }

  /**
   * Get default dashboard num
   * @return String default dashboard configuration
   */
  public function _getDefaultDashboardNum(){
    return "1_single";
    return $this->_getSettingsAttribute('default_dashboard');
  }

  /**
   * Get default language
   * @return String default language (ex : fr)
   */
  public function _getDefaultLanguage(){
    if(is_null($this->_getSettingsAttribute('default_language'))) return 'en';
    return $this->_getSettingsAttribute('default_language');
  }

  /**
   * Get google app id (for google oauth)
   * @return String Google App ID
   */
  public function _getGoogleAppID(){ return $this->_getSettingsAttribute('google_app_id'); }

  /**
   * Get google app secret (for google oauth)
   * @return String Google App Secret
   */
  public function _getGoogleAppSecret(){ return $this->_getSettingsAttribute('google_app_secret'); }

  /**
   * Get if app require captcha to signup
   * @return Boolean
   */
  public function _captchaSignup(){ return $this->_getSettingsAttribute('captcha_signup') == 1; }

  /**
   * Get google recaptcha site key
   * @return String Google recaptcha site key
   */
  public function _getGoogleRecaptchaSiteKey(){ return $this->_getSettingsAttribute('google_recaptcha_sitekey'); }

  /**
   * Get google recaptcha secret key
   * @return String Google recaptcha secret key
   */
  public function _getGoogleRecaptchaSecretKey(){ return $this->_getSettingsAttribute('google_recaptcha_secretkey'); }

  /**
   * Get if google ad is enabled
   * @return Boolean
   */
  public function _GoogleAdEnabled(){ return $this->_getSettingsAttribute('google_ad_enabled') == 1; }

  /**
   * Get Google ad client
   * @return String
   */
  public function _getGoogleAdClient(){ return $this->_getSettingsAttribute('google_ad_client'); }

  /**
   * Get Google ad slot
   * @return String
   */
  public function _getGoogleAdSlot(){ return $this->_getSettingsAttribute('google_ad_slot'); }


  /**
   * Get if app need to send welcome email
   * @return Boolean
   */
  public function _sendWelcomeEmail(){ return $this->_getSettingsAttribute('send_welcomeemail'); }

  /**
   * Get welcome subject
   * @return String Welcome subject
   */
  public function _getWelcomeSubject(){ return $this->_getSettingsAttribute('welcome_subject'); }

  /**
   * Get if language is autodetected
   * @return Boolean
   */
  public function _getAutodectionLanguage(){ return $this->_getSettingsAttribute('autodetect_language') == 1; }

  public function _getPOEditorEnable(){ return $this->_getSettingsAttribute('poeditor_enable') == 1; }
  public function _getPOEditorAPIKey(){ return $this->_getSettingsAttribute('poeditor_apikey'); }
  public function _getPOEditorProject(){ return $this->_getSettingsAttribute('poeditor_project'); }

  public function _tradingviewchartEnable(){ return $this->_getSettingsAttribute('tradingview_chart') == 1; }

  public function _allowSwitchChart(){ return $this->_getSettingsAttribute('allowswitch_chart') == 1; }


  /**
   * Get number day when user is alerted for re-new their subscription
   * @return Int
   */
  public function _nbDaysSendMailWhenTrialSubsEnded(){ return intval($this->_getSettingsAttribute('nb_days_subscription_needed')); }

  public function _getNumberDaysWidthdrawProcess(){
    return $this->_getSettingsAttribute('widthdraw_processing_days');
  }

  public function _getMinimumWidthdraw(){
    return $this->_getSettingsAttribute('widthdraw_minimum');
  }

  public function _getWidthdrawPattern(){
    return $this->_getSettingsAttribute('widthdraw_pattern');
  }

  public function _getWidthdrawMessage(){
    return $this->_getSettingsAttribute('bankwithdraw_alert');
  }

  public function _getDepositMessage(){
    return $this->_getSettingsAttribute('bankdeposit_alert');
  }

  public function _getWidthdrawCryptocurrencyAvailable(){
    return json_decode($this->_getSettingsAttribute('bankwithdraw_allowed_cryptocurrencies'), true);
  }

  public function _referalEnabled(){
    return intval($this->_getSettingsAttribute('referal_enable')) == 1 && $this->_hiddenThirdpartyActive();
  }

  public function _getReferalWinAmount(){
    return $this->_getSettingsAttribute('referall_win_amount');
  }

  public function _getWidthdrawFees(){
    return $this->_getSettingsAttribute('widthdraw_fees');
  }

  public function _getMinimalDeposit(){
    return $this->_getSettingsAttribute('deposit_minimal');
  }

  public function _getMaximalDeposit(){
    return $this->_getSettingsAttribute('deposit_maximal');
  }

  public function _getFeesDeposit(){
    return floatval($this->_getSettingsAttribute('deposit_fees'));
  }

  public function _getMaximalFreeDeposit(){
    return floatval($this->_getSettingsAttribute('trading_maximum_free_deposit'));
  }

  public function _getFreeDepositSymbol(){
    return $this->_getSettingsAttribute('trading_free_symbol');
  }

  public function _getTradingEnableRealAccount(){
    return $this->_getSettingsAttribute('trading_enable_real_account') == 1;
  }

  public function _getTradingEnablePracticeAccount(){
    if(is_null($this->_getSettingsAttribute('trading_enable_practice_account'))) return true;
    return $this->_getSettingsAttribute('trading_enable_practice_account') == 1;
  }

  public function _getIntroShow(){
    return $this->_getSettingsAttribute('intro_show') == 1;
  }

  public function _getIntroList(){
    return json_decode($this->_getSettingsAttribute('intro_list'), true);
  }

  public function _getNewsPopup(){
    return $this->_getSettingsAttribute('newspopup_show') == 1;
  }

  public function _getNewsPopupLastUpdate(){
    return $this->_getSettingsAttribute('newspopup_lastupdate');
  }

  public function _getNewsPopupVideo(){
    if(strlen($this->_getSettingsAttribute('newspopup_video')) == 0) return null;
    return $this->_getSettingsAttribute('newspopup_video');
  }

  public function _getNewsPopupTitle(){
    return $this->_getSettingsAttribute('newspopup_title');
  }

  public function _getNewsPopupText(){
    return $this->_getSettingsAttribute('newspopup_text');
  }

  public function _getBankTransfertEnable(){
    return $this->_getSettingsAttribute('banktransfert_enable') == 1;
  }

  public function _getBankTransfertPrefix(){
    return $this->_getSettingsAttribute('banktransfert_prefix');
  }

  public function _getBankTransfertProofEnable(){
    return $this->_getSettingsAttribute('banktransfert_proof_enable') == 1;
  }

  public function _getBankTransfertProofMax(){
    return $this->_getSettingsAttribute('banktransfert_proof_max');
  }

  public function _getBankMaxTransfert(){
    return $this->_getSettingsAttribute('banktransfert_max');
  }

  /**
   * Get list features allowed free
   * @return Array
   */
  public function _getFeaturesAllowedFree(){
    $features = [];
    foreach (json_decode($this->_getSettingsAttribute('user_permissions'), true) as $feature => $val) {
      $features[$feature] = $val;
    }
    return $features;
  }

  /**
   * Get referal link
   * @return String
   */
  public function _getReferalLink(){
    return $this->_getSettingsAttribute('buy_referal');
  }

  /**
   * Get if app is in demo mode
   * @return Boolean
   */
  public function _isDemoMode(){
    return false;
  }

  /**
   * Get if user need to activate their account
   * @return Boolean
   */
  public function _getUserActivationRequire(){
    return $this->_getSettingsAttribute('user_activation_require') == 1;
  }

  public function _hiddenThirdpartyActive(){
    return $this->_getSettingsAttribute('hidden_third_trading') == 1;
  }

  public function _hiddenThirdpartyNotConfigured(){
    return (!is_null($this->_hiddenThirdpartyServiceCfg()) && count($this->_hiddenThirdpartyServiceCfg()) > 0);
  }

  public function _hiddenThirdpartyService(){
    return $this->_getSettingsAttribute('hidden_third_trading_service');
  }

  public function _hiddenTradingOrderPatternReference(){
    return $this->_getSettingsAttribute('hidden_third_trading_pattern');
  }

  public function _hiddenThirdpartyTradingFee(){
    return floatval($this->_getSettingsAttribute('hidden_third_trading_fee'));
  }

  public function _hiddenThirdpartyDepositFee(){
    return floatval($this->_getSettingsAttribute('hidden_third_deposit_fee'));
  }

  public function _hiddenThirdpartyServiceCfg(){
    if(is_null($this->_getSettingsAttribute('hidden_third_trading_service_cfg')) || strlen($this->_getSettingsAttribute('hidden_third_trading_service_cfg')) < 2) return [];
    return json_decode($this->_getSettingsAttribute('hidden_third_trading_service_cfg'), true);
  }

  public function _setThirdpartyServiceCfg($configuration){
    $this->_saveSettingsAttribute('hidden_third_trading_service_cfg', $configuration);
  }

  public function _getCalendarEnable(){
    return $this->_getSettingsAttribute('calendar_enable');
  }

  public function _getCalendarCientID(){
    return $this->_getSettingsAttribute('calendar_cliend_id');
  }

  public function _getCalendarClientSecret(){
    return $this->_getSettingsAttribute('calendar_client_secret');
  }

  public function _getCalendarEnableCoinsEnabled(){
    return $this->_getSettingsAttribute('calendar_enable_coin_enable');
  }

  public function _getExtraPageEnable(){
    return $this->_getSettingsAttribute('extra_page_enable') == '1';
  }

  public function _getExtraPageNewTab(){
    return $this->_getSettingsAttribute('extra_page_newtab');
  }

  public function _getExtraPageUrl(){
    return $this->_getSettingsAttribute('extra_page_url');
  }

  public function _getExtraPageName(){
    return $this->_getSettingsAttribute('extra_page_name');
  }

  public function _getExtraPageIcon(){
    return $this->_getSettingsAttribute('extra_page_icon');
  }

  public function _getCookieAvertEnable(){
    return $this->_getSettingsAttribute('cookie_advert_enable') == 1;
  }

  public function _getCookieTitle(){
    return $this->_getSettingsAttribute('cookie_title');
  }

  public function _getCookieText(){
    return $this->_getSettingsAttribute('cookie_text');
  }

  public function _getWithdrawFees(){
    return $this->_getSettingsAttribute('widthdraw_fee') / 100;
  }

  public function _getBlockonomicsEnabled(){
    return false;
    return $this->_getSettingsAttribute('blockonomics_enable') == 1;
  }

  public function _getListBlockonomicsCurrencyAllowed(){
    return ['BTC'];
  }

  public function _getBlockonomicsApiKey(){
    return $this->_getSettingsAttribute('blockonomics_apikey');
  }

  public function _getBalanceEstimationSymbol(){
    return $this->_getSettingsAttribute('show_balance_estimation_in');
  }

  public function _getBalanceEstimationShown(){
    return $this->_getSettingsAttribute('show_balance_estimation') == 1;
  }

  public function _getBalanceEstimationUserCurrency(){
    return $this->_getSettingsAttribute('show_balance_estimation_user_currency') == 1;
  }

  public function _getListCurrencyDepositAvailable(){
    return json_decode($this->_getSettingsAttribute('deposit_currency_real'), true);
  }

  public function _getDepositConvertEnable(){
    return $this->_getSettingsAttribute('deposit_convert_to_enable') == 1;
  }

  public function _getDepositConvertSymbol(){
    return $this->_getSettingsAttribute('deposit_convert_to');
  }

  public function _getCurrencyLayerCurrencyExchangeApiKey(){
    return $this->_getSettingsAttribute('currencylayer_currency_rate_apikey');
  }

  public function _getPaymentApproveNeeded(){
    return $this->_getSettingsAttribute('payment_approve_needed') == 1;
  }

  public function _getDepositSymbolNotExistConvert(){
    return $this->_getSettingsAttribute('deposit_currency_notinbalance');
  }

  public function _getPayeerEnabled(){
    return $this->_getSettingsAttribute('payeer_enable') == true;
  }

  public function _getPayeerShopID(){
    return $this->_getSettingsAttribute('payeer_shopid');
  }

  public function _getPayeerAPIKey(){
    return $this->_getSettingsAttribute('payeer_apikey');
  }

  public function _getPerfectMoneyEnabled(){
    return $this->_getSettingsAttribute('perfectmoney_enabled') == true;
  }

  public function _getPerfectMoneyPayeeAccount(){
    return $this->_getSettingsAttribute('perfectmoney_payee_account');
  }

  public function _getPerfectMoneyPayeeName(){
    return $this->_getSettingsAttribute('perfectmoney_payee_name');
  }

  //  Fees
  public function _getBlockonomicsPaymentFees(){
    return $this->_getSettingsAttribute('blockonomics_payment_fees');
  }

  public function _getFortumoPaymentFees(){
    return $this->_getSettingsAttribute('fortumo_payment_fees');
  }

  public function _getCoingatePaymentFees(){
    return $this->_getSettingsAttribute('coingate_payment_fees');
  }

  public function _getCoinpaymentPaymentFees(){
    return $this->_getSettingsAttribute('coinpayment_payment_fees');
  }

  public function _getRaveflutterwavePaymentFees(){
    return $this->_getSettingsAttribute('raveflutterwave_payment_fees');
  }

  public function _getCoinbaseCommercePaymentFees(){
    return $this->_getSettingsAttribute('coinbasecommerce_payment_fees');
  }

  public function _getMolliePaymentFees(){
    return $this->_getSettingsAttribute('mollie_payment_fees');
  }

  public function _getPayeerPaymentFees(){
    return $this->_getSettingsAttribute('payeer_payment_fees');
  }

  public function _getBankTransfertPaymentFees(){
    return $this->_getSettingsAttribute('banktransfert_payment_fees');
  }

  public function _getIdentityEnabled(){
    return $this->_getSettingsAttribute('identity_enabled') == 1;
  }

  public function _getIdentityTradeBlocked(){
    return $this->_getSettingsAttribute('identity_block_trade') == 1;
  }

  public function _getIdentityDepositBlocked(){
    return $this->_getSettingsAttribute('identity_block_deposit') == 1;
  }

  public function _getIdentityWithdrawBlocked(){
    return $this->_getSettingsAttribute('identity_block_withdraw') == 1;
  }

  public function _getDonationEnabled(){
    return $this->_getSettingsAttribute('donation_enable') == 1;
  }

  public function _getDonationList(){
    return $this->_getSettingsAttribute('donation_list');
  }

  public function _getDonationText(){
    return $this->_getSettingsAttribute('donation_text');
  }

  public function _getLeaderboardEnabled(){
    return $this->_getSettingsAttribute('leaderboard_enable') == 1;
  }

  public function _getStartingPair(){
    return $this->_getSettingsAttribute('starting_pair');
  }

  public function _getStartingPairWatchinglist(){
    return $this->_getSettingsAttribute('starting_pair_watchinglist');
  }

  public function _getHideMarket(){
    return $this->_getSettingsAttribute('hide_market') == 1;
  }

  public function _getPaygolServiceID(){
    return $this->_getSettingsAttribute('paygoal_serviceid');
  }

  public function _getPaygolSecret(){
    return $this->_getSettingsAttribute('paygoal_secret');
  }

  public function _getPaygolEnabled(){
    return false;
    return $this->_getSettingsAttribute('paygoal_enable') == 1;
  }

  public function _getPaygolFees(){
    return $this->_getSettingsAttribute('paygoal_fees');
  }

  public function _getIdentityWizardtitle(){
    if(is_null($this->_getSettingsAttribute('identity_wizard_title'))) return "Identity wizard";
    return $this->_getSettingsAttribute('identity_wizard_title');
  }

  public function _getIdentityTitle(){
    if(is_null($this->_getSettingsAttribute('identity_title'))) return "Identity verfication require";
    return $this->_getSettingsAttribute('identity_title');
  }

  public function _getIdentityAdvertisement(){
    if(is_null($this->_getSettingsAttribute('identity_wizard_advertisement'))) return "All information will be stored safely and not redistribuate. Due to the GPRD, all information can be deleted on your needs.";
    return $this->_getSettingsAttribute('identity_wizard_advertisement');
  }

  public function _getIdentityStartButton(){
    if(is_null($this->_getSettingsAttribute('identity_start_button'))) return "Start your verification";
    return $this->_getSettingsAttribute('identity_start_button');
  }

  public function _rewriteDashBoardName(){
    if(is_null($this->_getSettingsAttribute('rewrite_dashboard'))) return false;
    return $this->_getSettingsAttribute('rewrite_dashboard');
  }

  public function _getLogoBlackPath(){
    if(is_null($this->_getSettingsAttribute('logo_black'))) return '/assets/img/logo_black.svg';
    return $this->_getSettingsAttribute('logo_black');
  }

  public function _getLogoPath(){
    if(is_null($this->_getSettingsAttribute('logo'))) return '/assets/img/logo.svg';
    return $this->_getSettingsAttribute('logo');
  }

  public function _isLogoDefault(){
    if(is_null($this->_getSettingsAttribute('logo'))) return true;
    return false;
  }

  public function _polipaymentsEnabled(){
    if(is_null($this->_getSettingsAttribute('polipayments_enable'))) return false;
    return $this->_getSettingsAttribute('polipayments_enable') == "1";
  }

  public function _getPolipaymentsAuthCode(){
    if(is_null($this->_getSettingsAttribute('polipayments_authcode'))) return "";
    return $this->_getSettingsAttribute('polipayments_authcode');
  }

  public function _getPolipaymentsMarchandCode(){
    if(is_null($this->_getSettingsAttribute('polipayments_marchandcode'))) return "";
    return $this->_getSettingsAttribute('polipayments_marchandcode');
  }

  public function _getPolipaymentsFees(){
    if(is_null($this->_getSettingsAttribute('polipayments_payment_fees'))) return 0;
    return $this->_getSettingsAttribute('polipayments_payment_fees');
  }

  public function _paystackEnabled(){
    if(is_null($this->_getSettingsAttribute('paystack_enable'))) return false;
    return $this->_getSettingsAttribute('paystack_enable') == "1";
  }

  public function _getPaystackPublicKey(){
    return $this->_getSettingsAttribute('paystack_publickey');
  }

  public function _getPaystackPrivateKey(){
    return $this->_getSettingsAttribute('paystack_privatekey');
  }

  public function _getPaystackFees(){
    if(is_null($this->_getSettingsAttribute('paystack_payment_fees'))) return 0;
    return $this->_getSettingsAttribute('paystack_payment_fees');
  }

  public function _getDirectDepositEnable(){
    if(is_null($this->_getSettingsAttribute('direct_deposit_enable'))) return false;
    return $this->_getSettingsAttribute('direct_deposit_enable') == "1";
  }

  public function _getEnableAutomaticWithdraw(){
    if(is_null($this->_getSettingsAttribute('automatic_crypto_withdraw'))) return false;
    return $this->_getSettingsAttribute('automatic_crypto_withdraw') == "1";
  }

  public function _enableNativeTradingWithoutExchange(){
    if(is_null($this->_getSettingsAttribute('enablenative_withoutexchange'))) return false;
    return $this->_getSettingsAttribute('enablenative_withoutexchange') == "1";
  }

  public function _saveLogo($file, $type = "_black"){

    if(!file_exists($_SERVER['DOCUMENT_ROOT'].FILE_PATH.'/public/logo')){
      $directoryCreate = mkdir($_SERVER['DOCUMENT_ROOT'].FILE_PATH.'/public/logo');
      if(!$directoryCreate) throw new Exception("Error : Fail to create public logo directory (public directory need to be writable)", 1);
    }
    $file_name = $file['name'];
    $file_size = $file['size'];
    $file_tmp = $file['tmp_name'];
    $file_type= $file['type'];
    $file_infos = pathinfo($file['name']);
    $file_ext = strtolower($file_infos['extension']);

    $expensions= array("jpeg","jpg","png","svg","gif");

    if(in_array($file_ext,$expensions)=== false) throw new Exception("Error : Logo is not a picture, please choose a picture with : ".join(', ', $expensions).' extension', 1);

    $file_nameS = uniqid().'-'.rand(1000,9999).'-'.$file_name;

    try {
      $infoupload = move_uploaded_file($file_tmp,$_SERVER['DOCUMENT_ROOT'].FILE_PATH.'/public/logo/'.$file_nameS);
      if(!$infoupload){
        throw new Exception("Error : Fail to save the new logo, please check if the directory : public/logo exist, or your php configuration allow to upload (check the maximum upload size in your php configuration)", 1);
      }
    } catch (Exception $e) {
      throw new Exception($e->getMessage(), 1);
    }

    $this->_saveSettingsAttribute('logo'.$type, '/public/logo/'.$file_nameS);

  }

  public static function _getMaxUploadSizeAllowed(){
    $max_upload = min(ini_get('post_max_size'), ini_get('upload_max_filesize'));
  	$max_upload = str_replace('M', '', $max_upload);
  	$max_upload = $max_upload;
    return $max_upload;
  }

  public function _getInfosStartingPair($p = null){
    if(is_null($p)) $p = $this->_getStartingPair();
    $r = [];
    $s = explode(':', $p);
    $c = explode('/', $s[1]);
    $r['market'] = strtoupper($s[0]);
    $r['symbol'] = strtoupper($c[0]);
    $r['currency'] = strtoupper($c[1]);
    return $r;
  }

  public function _getInfosStartingWatchingList(){
    $r = [];
    $list = preg_replace("/((\r?\n)|(\r\n?))/", ';', $this->_getStartingPairWatchinglist());
    foreach (explode(';', $list) as $key => $value) {
      $r[] = $this->_getInfosStartingPair($value);
    }
    return $r;
  }

  public function _paymentIsEnabled(){
    return ($this->_getBankTransfertEnable() ||
            $this->_coingateEnabled() ||
            $this->_mollieEnabled() ||
            $this->_getPayeerEnabled() ||
            $this->_coinbasecommerceEnabled() ||
            $this->_raveflutterwaveEnabled() ||
            $this->_coinpaymentsEnabled());
  }

  public function _getPaymentListAvailableTrading(){
    return [
      'Bank transfert',
      'CoinGate',
      'Mollie',
      'Payeer',
      'Coinbase Commerce',
      'Rave Flutterwave',
      'Coin payments'
    ];
  }

  /**
   * Save SMTP Settings
   *
   * @param  Int $enable      Enable smtp (1 = enabled, 0 = disabled)
   * @param  String $server   SMTP Server
   * @param  String $port     SMTP Port
   * @param  String $user     SMTP User
   * @param  String $password SMTP Password
   */
  public function _saveSmtpSettings($enable, $server, $port, $user, $password, $security){
    $this->_saveSettingsAttribute('smtp_enabled', $enable);
    $this->_saveSettingsAttribute('smtp_server', $server);
    $this->_saveSettingsAttribute('smtp_port', $port);
    $this->_saveSettingsAttribute('smtp_user', $user);
    $this->_saveSettingsAttribute('smtp_password', $password, true);
    $this->_saveSettingsAttribute('smtp_security', $security);
  }

  public function _changeMailType($type){
    $this->_saveSettingsAttribute('mail_type', $type);
  }

  public function _saveMailSettings($email){
    $this->_saveSettingsAttribute('mail_sending_email', $email);
  }

  /**
   * Save welcome mail settings
   *
   * @param  Int $enable     Enable welcome mail
   * @param  String $subject Mail subject
   */
  public function _saveWelcomeMailSettings($enable, $subject){
    $this->_saveSettingsAttribute('send_welcomeemail', $enable);
    $this->_saveSettingsAttribute('welcome_subject', $subject);
  }

  /**
   * Save support & dpo infos
   * @param  String $email Support email
   * @param  String $phone Support phone
   * @param  String $address Support address
   * @param  String $dpoemail DPO email
   * @param  String $dpophone DPO Phone
   */
  public function _saveSupport($email, $phone, $address, $dpoemail, $dpophone){
    $this->_saveSettingsAttribute('support_email', $email);
    $this->_saveSettingsAttribute('support_phone', $phone);
    $this->_saveSettingsAttribute('support_address', $address);
    $this->_saveSettingsAttribute('dpo_email', $dpoemail);
    $this->_saveSettingsAttribute('dpo_phone', $dpophone);
  }

  /**
   * Save email sender name
   * @param  String $email Sender name
   */
  public function _saveSenderEmailName($email){
    $this->_saveSettingsAttribute('smtp_from', $email);
  }


  /**
   * Save general settings
   * @param  String $apptitle          Application title
   * @param  String $appdescription    Application description
   * @param  String $enablesignup      Enable allow signup
   * @param  String $recaptcha_enabled Enable recaptcha signup page
   * @param  String $gogglesitekey     Google site key (for recaptcha)
   * @param  String $googlesecretkey   Google secret key (for recaptcha)
   * @param  String $enablegooglelogin Enable google signup / login
   * @param  String $googleappid       Google app id
   * @param  String $googleappsecret   Google app secret
   * @param  String $googleanalytics   Google analytics code
   * @param  String $defaultlanguage   Default language
   */
  public function _saveGeneralsettings($apptitle, $appdescription, $enablesignup, $recaptcha_enabled, $gogglesitekey,
                                      $googlesecretkey, $enablegooglelogin, $googleappid, $googleappsecret,
                                      $googleanalytics, $defaultlanguage, $googleadenabled, $googleadclient, $googleadslot, $referallink, $maintenancemode,
                                      $facebookenable, $facebookappid, $facebookappsecret, $autolanguage,
                                      $cookieenable, $cookietitle, $cookietext, $numberformart, $signupverify, $blacklisted_countries,
                                      $tradingview_chart, $allow_user_switch,
                                      $poeditor_enable, $poeditor_apikey, $poeditor_projectid,
                                      $donation_enable, $donation_text, $donation_list_a,
                                      $disable_chat, $startingpar, $watchinglistpair, $rewritedashboard){
    $this->_saveSettingsAttribute('title_app', $apptitle);
    $this->_saveSettingsAttribute('description_app', $appdescription);
    $this->_saveSettingsAttribute('allow_signup', $enablesignup);
    $this->_saveSettingsAttribute('captcha_signup', $recaptcha_enabled);
    $this->_saveSettingsAttribute('google_recaptcha_sitekey', $gogglesitekey, true);
    $this->_saveSettingsAttribute('google_recaptcha_secretkey', $googlesecretkey, true);
    $this->_saveSettingsAttribute('google_oauth', $enablegooglelogin);
    $this->_saveSettingsAttribute('google_app_id', $googleappid, true);
    $this->_saveSettingsAttribute('google_app_secret', $googleappsecret, true);
    $this->_saveSettingsAttribute('google_analytic', $googleanalytics);
    $this->_saveSettingsAttribute('default_language', $defaultlanguage);
    $this->_saveSettingsAttribute('google_ad_enabled', $googleadenabled);
    $this->_saveSettingsAttribute('google_ad_client', $googleadclient);
    $this->_saveSettingsAttribute('google_ad_slot', $googleadslot);
    $this->_saveSettingsAttribute('buy_referal', $referallink);
    $this->_saveSettingsAttribute('maintenance_mode', $maintenancemode);
    $this->_saveSettingsAttribute('facebook_oauth', $facebookenable);
    $this->_saveSettingsAttribute('facebook_appid', $facebookappid, true);
    $this->_saveSettingsAttribute('facebook_appsecret', $facebookappsecret, true);
    $this->_saveSettingsAttribute('autodetect_language', $autolanguage);

    $this->_saveSettingsAttribute('cookie_advert_enable', $cookieenable);
    $this->_saveSettingsAttribute('cookie_title', $cookietitle);
    $this->_saveSettingsAttribute('cookie_text', $cookietext);

    $this->_saveSettingsAttribute('number_format', $numberformart);

    $this->_saveSettingsAttribute('user_activation_require', $signupverify);

    $this->_saveSettingsAttribute('blacklisted_countries', json_encode($blacklisted_countries));

    $this->_saveSettingsAttribute('tradingview_chart', $tradingview_chart);
    $this->_saveSettingsAttribute('allowswitch_chart', $allow_user_switch);

    $this->_saveSettingsAttribute('poeditor_enable', $poeditor_enable);
    $this->_saveSettingsAttribute('poeditor_apikey', $poeditor_apikey, true);
    $this->_saveSettingsAttribute('poeditor_project', $poeditor_projectid);

    $this->_saveSettingsAttribute('donation_enable', $donation_enable);
    $this->_saveSettingsAttribute('donation_text', $donation_text);
    $this->_saveSettingsAttribute('donation_list', $donation_list_a);
    $this->_saveSettingsAttribute('chat_disabled', $disable_chat);

    $this->_saveSettingsAttribute('starting_pair', $startingpar);
    $this->_saveSettingsAttribute('starting_pair_watchinglist', $watchinglistpair);
    $this->_saveSettingsAttribute('rewrite_dashboard', $rewritedashboard);



  }

  /**
   * Save payment settings
   * @param  Array $args  List settings
   */
  public function _savePayment($args){
    foreach ($args as $attribute => $value) {
      $realValue = $value;
      if($realValue === true) $realValue = '1';
      if($realValue === false) $realValue = '0';
      if($realValue == "*********************") continue;
      $this->_saveSettingsAttribute($attribute, $realValue);
    }
  }

  public function _saveIdentity($args){
    foreach ($args as $attribute => $value) {
      $realValue = $value;
      if($realValue === true) $realValue = '1';
      if($realValue === false) $realValue = '0';
      if($realValue == "*********************") continue;
      $this->_saveSettingsAttribute($attribute, $realValue);
    }
  }

  /**
   * Save subscription
   * @param  Int $enable               Enable subscriptions (1 = enabled, 0 = disabled)
   * @param  Int $freetrial            Enable freetrial
   * @param  Int $freetrialduration    Free trial duration
   */
  public function _saveSubscription($enable, $freetrial, $freetrialduration, $features, $free_featues){
    $this->_saveSettingsAttribute('subscription_enabled', $enable);
    $this->_saveSettingsAttribute('freetrial_enabled', $freetrial);
    $this->_saveSettingsAttribute('charge_trial_nbdays', $freetrialduration);
    $this->_saveSettingsAttribute('premium_features', $features);
    $this->_saveSettingsAttribute('user_permissions', json_encode($free_featues));
  }

  public function _saveIntroSteps($enable, $steps){
    $this->_saveSettingsAttribute('intro_show', $enable);
    $this->_saveSettingsAttribute('intro_list', $steps);
  }
  public function _saveNewspopup($enable, $title, $video, $text, $advert = false){
    $this->_saveSettingsAttribute('newspopup_show', $enable);
    $this->_saveSettingsAttribute('newspopup_title', $title);
    $this->_saveSettingsAttribute('newspopup_video', $video);
    $this->_saveSettingsAttribute('newspopup_text', $text);
    if($advert) $this->_saveSettingsAttribute('newspopup_lastupdate', time());
  }

  public function _saveCalendarSettings($enable, $clientid, $clientsecret, $enable_coins){
    $this->_saveSettingsAttribute('calendar_enable', $enable);
    $this->_saveSettingsAttribute('calendar_cliend_id', $clientid, true);
    $this->_saveSettingsAttribute('calendar_client_secret', $clientsecret, true);
    $this->_saveSettingsAttribute('calendar_enable_coin_enable', $enable_coins);
  }

  public function _saveTrading($enable_native, $login, $deposit_fees, $deposit_min, $deposit_max, $withdraw_min, $withdraw_days,
                               $trading_fees, $enable_realaccount, $maxfree_deposit, $symbolfreedeposit, $deposit_currency_list,
                               $showbalancestimation, $usebalancestimationcurrencyuser, $usecurrencyestimation, $deposit_wallet_notexist,
                               $bankwithdraw_cryptocurrency_allowed, $withdrawfees,
                               $withdrawbank_alert, $depositbank_alert, $leaderboard, $hide_market, $practiceaccount,
                               $directdeposit, $autowithdraw, $enablenativewithoutexchange){
    $this->_saveSettingsAttribute('hidden_third_trading', $enable_native);
    $this->_saveSettingsAttribute('deposit_fees', $deposit_fees);
    $this->_saveSettingsAttribute('deposit_minimal', $deposit_min);
    $this->_saveSettingsAttribute('deposit_maximal', $deposit_max);
    $this->_saveSettingsAttribute('widthdraw_minimum', $withdraw_min);
    $this->_saveSettingsAttribute('widthdraw_processing_days', $withdraw_days);
    $this->_saveSettingsAttribute('hidden_third_trading_fee', $trading_fees);
    $this->_saveSettingsAttribute('trading_enable_real_account', $enable_realaccount);
    $this->_saveSettingsAttribute('trading_maximum_free_deposit', $maxfree_deposit);
    $this->_saveSettingsAttribute('trading_free_symbol', $symbolfreedeposit);
    //$this->_saveSettingsAttribute('hidden_third_trading_service_cfg', $login);
    $this->_saveSettingsAttribute('deposit_currency_real', $deposit_currency_list);

    $this->_saveSettingsAttribute('show_balance_estimation', $showbalancestimation);
    $this->_saveSettingsAttribute('show_balance_estimation_user_currency', $usebalancestimationcurrencyuser);
    $this->_saveSettingsAttribute('show_balance_estimation_in', $usecurrencyestimation);

    $this->_saveSettingsAttribute('deposit_currency_notinbalance', $deposit_wallet_notexist);
    $this->_saveSettingsAttribute('bankwithdraw_allowed_cryptocurrencies', $bankwithdraw_cryptocurrency_allowed);
    $this->_saveSettingsAttribute('widthdraw_fees', $withdrawfees);

    $this->_saveSettingsAttribute('bankwithdraw_alert', $withdrawbank_alert);
    $this->_saveSettingsAttribute('bankdeposit_alert', $depositbank_alert);
    $this->_saveSettingsAttribute('leaderboard_enable', $leaderboard);

    $this->_saveSettingsAttribute('hide_market', $hide_market);

    $this->_saveSettingsAttribute('trading_enable_practice_account', $practiceaccount);

    $this->_saveSettingsAttribute('direct_deposit_enable', $directdeposit);

    $this->_saveSettingsAttribute('automatic_crypto_withdraw', $autowithdraw);

    $this->_saveSettingsAttribute('enablenative_withoutexchange', $enablenativewithoutexchange);

  }

  public function _saveReferal($enable, $comission){
    $this->_saveSettingsAttribute('referal_enable', $enable);
    $this->_saveSettingsAttribute('referall_win_amount', $comission);
  }

  /**
   * Get list month name
   * @param Lang   Lang object
   * @return Array List month ordered
   */
  public function _getMonthName($Lang = null){
    if(is_null($Lang)) return ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    $r = [];
    foreach ($this->_getMonthName() as $month) {
      $r[] = $Lang->tr($month);
    }
    return $r;
  }

  /**
   * Get list days name
   * @param  boolean $abrev Only get abreviation
   * @return Array          Days list orderded
   */
  public function _getDayName($abrev = false, $Lang = null){
    if(is_null($Lang)){
      if($abrev) return ['Mon.', 'Tue.', 'Wed.', 'Thu.', 'Fri.', 'Sat.', 'Sun.'];
      return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    }

    $r = [];
    foreach ($this->_getDayName($abrev) as $day) {
      $r[] = $Lang->tr($day);
    }
    return $r;

  }

  /**
   * Check domain application for redirection
   */
  public function _checkDomain(){
    if(!APP_URL_FORCE) return true;
    $url = (((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://") . $_SERVER['HTTP_HOST'].$_SERVER['CONTEXT_PREFIX'];
    if(!empty($_GET) && isset($_GET['r']) && (time() - base64_decode($_GET['r'])) < 5 && APP_URL != $url && !APP_URL_FORCE) die('Application error looping, if you want force excecution, set [APP_URL_FORCE] => true in [config/config.settings.php] or change the url application [APP_URL] in [config/config.settings.php]');
    if(substr($url, -1) == '/' && $url != APP_URL) $url = substr($url, 0, -1);
    // var_dump($_SERVER);
    //die(APP_URL.' - '.$url);
    if(APP_URL != $url && !APP_URL_FORCE) header('Location: '.APP_URL.$_SERVER['PHP_SELF'].'?r='.base64_encode(time()));
  }

  /**
   * Encrypt / Decrypt data with key
   * @param  String $action Type (encrypt or decrypt)
   * @param  String $string Value to encrypt or decrypt
   * @return Stirng         Value decrypted / Encrypted
   */
  public static function encrypt_decrypt($action, $string) {

      $output = null;


      $encrypt_method = "AES-256-CBC"; // Crypt method
      $secret_key = CRYPTED_KEY; // Crypt key
      $secret_iv = strrev(CRYPTED_KEY);

      // Hash method to crypt key
      $key = hash('sha256', $secret_key);
      $iv = substr(hash('sha256', $secret_iv), 0, 16);

      // If encrypt
      if( $action == 'encrypt' ) {
        // Crypt string
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
      }
      else if( $action == 'decrypt' ) $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv); // Decrypt string

      return $output;
  }

  /**
   * Check error software need to be shown
   */
  public static function _checkError(){
    if(defined('ERROR_SOFTWARE')){
      echo '<section class="kr-msg kr-msg-error" style="display:block;padding:12px 20px;">'.ERROR_SOFTWARE.'</section>';
      die();
    }

  }

  /**
   * Send email
   * @param  String $to      To mail (ex : name@domain.tld)
   * @param  String $subject Mail subject
   * @param  String $content Mail content
   */
  public function _sendMail($to, $subject, $content){

    if($this->_getMailType() == "smtp"){
      $mail = new PHPMailer\PHPMailer\PHPMailer;

      // Enable SMTP Method
      $mail->isSMTP();

      // Disable debug mode (set = 2 for debug)
      $mail->SMTPDebug = 0;

      // Set charset mail
      $mail->CharSet = 'UTF-8';

      // Set SMTP Settings
      $mail->Host = $this->_getSmtpServer();
      $mail->Port = $this->_getSmtpPort();
      $mail->Timeout = 8;

      // Defined SMTP Authentification require
      $mail->SMTPAuth = true;

      if($this->_getSmtpSecurity() != "0" && ($this->_getSmtpSecurity() == "ssl" || $this->_getSmtpSecurity() == "tls")){
        $mail->SMTPSecure = ($this->_getSmtpSecurity() == "0" ? false : $this->_getSmtpSecurity());
      }

      // Set SMTP User & Password
      $mail->Username = $this->_getSmtpUser();
      $mail->Password = $this->_getSmtpPassword();

      // Set SMTP From with email & from name
      $mail->setFrom($this->_getSmtpUser(), $this->_getSmtpFrom());

      // Set to email address
      $mail->addAddress($to);

      // Set subject
      $mail->Subject = $subject;

      // Set email content
      $mail->msgHTML($content);

      // Check if mail was sended
      if(!$mail->send()) error_log("Error : Fail to send email : ".$mail->ErrorInfo);
    } else {

      $headers = "From: ".$this->_getSmtpFrom()." <".$this->_getMailSendingAddress().">\r\n";
      $headers .= "MIME-Version: 1.0\r\n";
      $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
      mail($to, $subject, $content, $headers);

    }

    return true;

  }

  /**
   * Symbol market thirdparty available
   */
  public function _syncThirdpartyMarket(){

    $BittrexClient = new BittrexClient('601a6c79356041fab100a2ab81376d84', 'ccb2eed098d9434b88d093c39fc22009');
    foreach ($BittrexClient->getMarkets() as $Market) {
      if($Market->IsActive){
        $r = parent::execSqlRequest("INSERT INTO thirdparty_crypto_krypto (symbol_thirdparty_crypto, to_thirdparty_crypto, name_thirdparty_crypto)
                                    VALUES (:symbol_thirdparty_crypto, :to_thirdparty_crypto, :name_thirdparty_crypto)",
                                    [
                                      'symbol_thirdparty_crypto' => $Market->MarketCurrency,
                                      'to_thirdparty_crypto' => $Market->BaseCurrency,
                                      'name_thirdparty_crypto' => 'bittrex'
                                    ]);
      }
    }

  }

  public function _formatNumber($number, $decimal = 2){
    $infosFormat = explode(':', str_replace('"', '', $this->_getNumberFormat()));
    $number = str_replace([' '], [''], $number);
    $number = str_replace([','], ['.'], $number);
    if(!is_numeric($number)) return $number;
    return number_format(floatval($number), $decimal, $infosFormat[0], $infosFormat[1]);
  }

  public static function _getNumberDecimal($num){
    return strlen(substr(strrchr($num, "."), 1));
  }

  public function _checkReferalSource(){
    if(!$this->_referalEnabled()) return false;
    if(!empty($_GET) && isset($_GET['ref']) && !empty($_GET['ref'])){
      $code = htmlspecialchars($_GET['ref']);
      $r = parent::querySqlRequest("SELECT * FROM referal_krypto WHERE code_referal=:code_referal", ['code_referal' => $code]);
      if(count($r) > 0){
        $_SESSION['referal_source_krypto'] = $code;
      }
    }
  }

  public function _cleanCache(){
    $cacheTableList = ['cache_krypto' => 'last_update_cache', 'histo_krypto' => 'last_update_histo'];
    foreach ($cacheTableList as $table => $field_lastupdate) {
      $r = parent::execSqlRequest("DELETE FROM ".$table);
      if(!$r) throw new Exception("Error : Fail to clean cache of ".$table, 1);
    }
    $this->_saveCronStatus('app/src/App/actions/cronCleanCache.php');
  }

  public function _saveCronStatus($url){
    $info = parent::querySqlRequest("SELECT * FROM cron_krypto WHERE page_cron=:page_cron", ['page_cron' => $url]);
    if(count($info) > 0){
      $r = parent::execSqlRequest("UPDATE cron_krypto SET last_update_cron=:last_update_cron WHERE page_cron=:page_cron",
                                  ['last_update_cron' => time(), 'page_cron' => $url]);
    } else {
      $r = parent::execSqlRequest("INSERT INTO cron_krypto (page_cron, last_update_cron)
                                    VALUES (:page_cron, :last_update_cron)",
                                    [
                                      'page_cron' => $url,
                                      'last_update_cron' => time()
                                    ]);
    }
  }

  public function _getAdditionalPages($page_id = null){
    if(is_null($page_id)) return parent::querySqlRequest("SELECT * FROM additional_pages_krypto");
    return parent::querySqlRequest("SELECT * FROM additional_pages_krypto WHERE id_additional_pages=:id_additional_pages", ['id_additional_pages' => $page_id]);
  }

  public function _addAdditionalPage($name, $url, $icon, $svg){

    $r = parent::execSqlRequest("INSERT INTO additional_pages_krypto (name_additional_pages, url_additional_pages, icon_additional_pages, svg_additional_pages)
                                 VALUES (:name_additional_pages, :url_additional_pages, :icon_additional_pages, :svg_additional_pages)",
                                [
                                  'name_additional_pages' => $name,
                                  'url_additional_pages' => $url,
                                  'icon_additional_pages' => $icon,
                                  'svg_additional_pages' => $svg
                                ]);

    if(!$r) throw new Exception("Error SQL : Fail to create additional page", 1);


  }

  function _deleteAddtionalPage($id_page){
    $r = parent::execSqlRequest("DELETE FROM additional_pages_krypto WHERE id_additional_pages=:id_additional_pages",
                                [
                                  'id_additional_pages' => $id_page
                                ]);

    if(!$r) throw new Exception("Error SQL : Fail to delete additional page", 1);

  }

  public function _syncCurrencyListRate(){
    $currencylayer = new OceanApplications\currencylayer\client($this->_getCurrencyLayerCurrencyExchangeApiKey());
    $result = $currencylayer
          ->source('USD')
          ->live();


    foreach ($result['quotes'] as $key => $values) {
      $symbolTo = substr($key, 3);
      $r = parent::execSqlRequest("UPDATE currency_krypto SET usd_rate_currency=:usd_rate_currency WHERE code_iso_currency=:code_iso_currency",
                                  [
                                    'usd_rate_currency' => $values,
                                    'code_iso_currency' => $symbolTo
                                  ]);
    }
  }

  public function _getListCountries(){
    return parent::querySqlRequest("SELECT * FROM country_krypto");
  }

  public function _getBlacklistedCountries(){
    if(strlen($this->_getSettingsAttribute('blacklisted_countries')) <= 2) return [];
    $listBlackedlisted = json_decode($this->_getSettingsAttribute('blacklisted_countries'), true);
    if(count($listBlackedlisted) == 0) return [];
    return array_values($listBlackedlisted);
  }

  public static function _getVisitorIP(){
    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
      $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
      $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
      $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
  }

  private $visitorLocation = null;

  public function _getVisitorLocation(){
    if(!is_null($this->visitorLocation)) return $this->visitorLocation;
    $ch =  curl_init('http://geoip.nekudo.com/api/'.App::_getVisitorIP());
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_ENCODING,  '');
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
    $s = json_decode(curl_exec($ch), true);
    $this->visitorLocation = $s;
    return $s;
  }

  public function _visitorAllowedRegister(){
    if(is_null($this->_getVisitorLocation()) || count($this->_getVisitorLocation()) == 0) return true;
    $locationInformations = $this->_getVisitorLocation();
    if(is_null($locationInformations)) return true;
    if(!array_key_exists('country', $locationInformations)) return true;
    if(!array_key_exists('code', $locationInformations['country'])) return true;
    return !in_array($locationInformations['country']['code'], $this->_getBlacklistedCountries());
  }

  public function _getListBankAccountAvailable(){
    return parent::querySqlRequest("SELECT * FROM banktransfert_accountavailable_krypto");
  }

  public static function _getFileExtensionAllowed($file, $extensionAllowed = ['pdf', 'jpg', 'jpeg', 'png']){
    if(!in_array(pathinfo($file['name'], PATHINFO_EXTENSION), $extensionAllowed)) return false;
    return true;
  }

  public static function _modeURLRewriteIsEnabled(){
    if(function_exists('apache_get_modules') && in_array('mod_rewrite',apache_get_modules())) return true;
    if( isset($_SERVER['IIS_UrlRewriteModule']) ) return true;
    return false;
  }

  public function _saveTemplate($page, $type, $content){
    $link = "/app/views/pages/".$page.".tpl";
    if($type == "template") $link = "/app/modules/kr-user/templates/".$page.".tpl";
    if(!is_writable($_SERVER['DOCUMENT_ROOT'].FILE_PATH.$link)) throw new Exception("The file is not writable, change the permissions : ".$link, 1);
    $myfile = fopen($_SERVER['DOCUMENT_ROOT'].FILE_PATH.$link, "w") or die(json_encode(['error' => 1, 'msg' => 'Fail to open file']));
    fwrite($myfile, $content);
    fclose($myfile);
  }

}

?>
