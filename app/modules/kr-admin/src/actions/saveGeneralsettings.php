<?php

/**
 * Change General settings
 *
 * This actions permit to admin to change SMTP settings in Krypto
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */

session_start();
require "../../../../../config/config.settings.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/vendor/autoload.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/MySQL/MySQL.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/App.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/AppModule.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/User/User.php";

// Load app modules
$App = new App(true);
$App->_loadModulesControllers();

try {

    // Check loggin & permission
    $User = new User();
    if (!$User->_isLogged()) {
        throw new Exception("Your are not logged", 1);
    }
    if (!$User->_isAdmin()) {
        throw new Exception("Error : Permission denied", 1);
    }

    if($App->_isDemoMode()) throw new Exception("App currently in demo mode", 1);

    if(isset($_FILES['kr-logo-black']) && !empty($_FILES['kr-logo-black']) && array_key_exists('size', $_FILES['kr-logo-black']) && $_FILES['kr-logo-black']['size'] > 0){
      $App->_saveLogo($_FILES['kr-logo-black']);
    }

    if(isset($_FILES['kr-logo-white']) && !empty($_FILES['kr-logo-white']) && array_key_exists('size', $_FILES['kr-logo-white']) && $_FILES['kr-logo-white']['size'] > 0){
      $App->_saveLogo($_FILES['kr-logo-white'], '');
    }

    // Check data available
    if (empty($_POST)) {
        throw new Exception("Error : Args not valid", 1);
    }


    // Save general settings
    $App->_saveGeneralsettings(
      $_POST['kr-adm-title'],
      $_POST['kr-adm-description'],
      (array_key_exists('kr-adm-chk-allowsignup', $_POST) && $_POST['kr-adm-chk-allowsignup'] == "on" ? 1 : 0),
      (array_key_exists('kr-adm-chk-captchasignup', $_POST) && $_POST['kr-adm-chk-captchasignup'] == "on" ? 1 : 0),
      ($_POST['kr-adm-recaptcha-sitekey'] == '**************' ? $App->_getGoogleRecaptchaSiteKey() : $_POST['kr-adm-recaptcha-sitekey']),
      ($_POST['kr-adm-recaptcha-secretkey'] == '**************' ? $App->_getGoogleRecaptchaSecretKey() : $_POST['kr-adm-recaptcha-secretkey']),
      (array_key_exists('kr-adm-chk-googleoauth', $_POST) && $_POST['kr-adm-chk-googleoauth'] == "on" ? 1 : 0),
      ($_POST['kr-adm-googleoauth-appid'] == '**************' ? $App->_getGoogleAppID() : $_POST['kr-adm-googleoauth-appid']),
      ($_POST['kr-adm-googleoauth-appsecret'] == '**************' ? $App->_getGoogleAppSecret() : $_POST['kr-adm-googleoauth-appsecret']),
      $_POST['kr-adm-googleanalytics'],
      $_POST['kr-adm-defaultlanguage'],
      (array_key_exists('kr-adm-chk-googleadenabled', $_POST) && $_POST['kr-adm-chk-googleadenabled'] == "on" ? 1 : 0),
      $_POST['kr-adm-googleadenabclient'],
      $_POST['kr-adm-googleadslot'],
      $_POST['kr-adm-referallink'],
      (array_key_exists('kr-adm-chk-maintenancemode', $_POST) && $_POST['kr-adm-chk-maintenancemode'] == "on" ? 1 : 0),
      (array_key_exists('kr-adm-chk-facebookoauth', $_POST) && $_POST['kr-adm-chk-facebookoauth'] == "on" ? 1 : 0),
      ($_POST['kr-adm-facebookoauth-appid'] == '**************' ? $App->_getFacebookAppID() : $_POST['kr-adm-facebookoauth-appid']),
      ($_POST['kr-adm-facebookoauth-appsecret'] == '**************' ? $App->_getFacebookAppSecret() : $_POST['kr-adm-facebookoauth-appsecret']),
      (array_key_exists('kr-adm-chk-autolanguage', $_POST) && $_POST['kr-adm-chk-autolanguage'] == "on" ? 1 : 0),
      (array_key_exists('kr-adm-chk-cookiepopupenable', $_POST) && $_POST['kr-adm-chk-cookiepopupenable'] == "on" ? 1 : 0),
      $_POST['kr-adm-cookietitle'],
      $_POST['kr-adm-cookietext'],
      $_POST['kr-adm-numberformart'],
      (array_key_exists('kr-adm-chk-signupverifiy', $_POST) && $_POST['kr-adm-chk-signupverifiy'] == "on" ? 1 : 0),
      (isset($_POST['blacklisted_countries']) ? $_POST['blacklisted_countries'] : ''),
      (array_key_exists('kr-adm-chk-tradingviewchart', $_POST) && $_POST['kr-adm-chk-tradingviewchart'] == "on" ? 1 : 0),
      (array_key_exists('kr-adm-chk-allowchartswitch', $_POST) && $_POST['kr-adm-chk-allowchartswitch'] == "on" ? 1 : 0),

      (array_key_exists('kr-adm-chk-poeditorenable', $_POST) && $_POST['kr-adm-chk-poeditorenable'] == "on" ? 1 : 0),
      ($_POST['kr-adm-poeditor-apikey'] == '**************' ? $App->_getPOEditorAPIKey() : $_POST['kr-adm-poeditor-apikey']),
      (isset($_POST['kr-adm-poeditor-project']) ? $_POST['kr-adm-poeditor-project'] : ''),

      (array_key_exists('kr-adm-chk-donationenable', $_POST) && $_POST['kr-adm-chk-donationenable'] == "on" ? 1 : 0),
      $_POST['kr-adm-donationtext'],
      $_POST['donation_list_c'],
      (array_key_exists('kr-adm-chk-disablechat', $_POST) && $_POST['kr-adm-chk-disablechat'] == "on" ? 1 : 0),

      $_POST['kr-adm-startingpair'],
      $_POST['kr-adm-startingpairwatchinglist'],
      (array_key_exists('kr-adm-chk-rewritedashboard', $_POST) && $_POST['kr-adm-chk-rewritedashboard'] == "on" ? 1 : 0)
    );

    // Return success message
    die(json_encode([
      'error' => 0,
      'msg' => 'Done',
      'title' => 'Success'
    ]));

} catch (\Exception $e) { // If throw exception, return error message
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}
