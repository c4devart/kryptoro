<?php

/**
 * Signup user action
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
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/Lang/Lang.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoApi.php";

try {

    // Load app modules
    $App = new App(true);
    $App->_loadModulesControllers();

    // Check if app allow signup
    if(!$App->_allowSignup()) throw new Exception("Error : Permission denied", 1);

    // Init user object
    $User = new User();

    // Init lang object
    $Lang = new Lang(null, $App);

    // If signup need captcha, check captcha
    if($App->_captchaSignup()){
      $recaptcha = new \ReCaptcha\ReCaptcha($App->_getGoogleRecaptchaSecretKey());
      $resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
      if (!$resp->isSuccess()) throw new Exception("Error : Recaptcha fail", 1);
    }

    // Check post given
    $fieldNeeded = ['kr_usr_name', 'kr_usr_email', 'kr_usr_pwd', 'kr_usr_rep_pwd', 'kr_usr_agree'];
    $fieldMissing = [];
    foreach ($fieldNeeded as $field) {
      if(!isset($_POST[$field]) || empty($_POST[$field])) $fieldMissing[$field] = '';
    }

    // Check all fields are given
    if(count($fieldMissing) > 0) die(json_encode([
      'error' => 2,
      'fields' => $fieldMissing
    ]));

    // Check email format
    if(!filter_var($_POST['kr_usr_email'], FILTER_VALIDATE_EMAIL)) die(json_encode(['error' => 2, 'fields' => ['kr_usr_email' => $Lang->tr('Email not valid')]]));

    if($_POST['kr_usr_pwd'] != $_POST['kr_usr_rep_pwd'])  die(json_encode(['error' => 2, 'fields' => ['kr_usr_pwd' => '', 'kr_usr_rep_pwd' => $Lang->tr('Password not match')]]));

    // Create user
    $User->_createUser($_POST['kr_usr_email'], $_POST['kr_usr_name'], $_POST['kr_usr_pwd']);

    // Login user
    if(!$App->_getUserActivationRequire()){
      $User->_login($_POST['kr_usr_email'], $_POST['kr_usr_pwd']);
    } else {
      die(json_encode([
        'error' => 3,
        'msg' => "You will receive a confirmation email for enable your account !"
      ]));
    }


    // Redirect user
    die(json_encode([
      'error' => 0,
      'href' => APP_URL.'/dashboard.php'
    ]));


} catch (Exception $e) {
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}
