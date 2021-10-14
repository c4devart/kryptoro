<?php

/**
 * Login user action
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

try {

    // Load app modules
    $App = new App(true);
    $App->_loadModulesControllers();

    // Check if user is already logged
    $User = new User();
    if ($User->_isLogged()) {
      // Redirect user
      die(json_encode([
        'error' => 0,
        'href' => APP_URL.'/dashboard'.($App->_rewriteDashBoardName() ? '' : '.php')
      ]));
    }

    // Init lang object
    $Lang = new Lang(null, $App);


    $tfsCode = null;
    if(isset($_POST['kr_login_code'])) $tfsCode = $_POST['kr_login_code'];

    if(!is_null($tfsCode)){
      $_POST['kr_usr_email'] = App::encrypt_decrypt('decrypt', $_POST['kr_usr_email']);
      $_POST['kr_usr_pwd'] = App::encrypt_decrypt('decrypt', $_POST['kr_usr_pwd']);
    }

    // Check post given
    if (empty($_POST) || (empty($_POST['kr_usr_email']) && empty($_POST['kr_usr_pwd']))) {
        die(json_encode(['error' => 2, 'fields' => ['kr_usr_email' => '', 'kr_usr_pwd' => '']]));
    } elseif (empty($_POST['kr_usr_email'])) {
        die(json_encode(['error' => 2, 'fields' => ['kr_usr_email' => '']]));
    } elseif (empty($_POST['kr_usr_pwd'])) {
        die(json_encode(['error' => 2, 'fields' => ['kr_usr_pwd' => '']]));
    }

    // Check email format
    if (!filter_var($_POST['kr_usr_email'], FILTER_VALIDATE_EMAIL)) {
        die(json_encode(['error' => 2, 'fields' => ['kr_usr_email' => $Lang->tr('Email not valid')]]));
    }



    // Login user
    $loginResult = $User->_login($_POST['kr_usr_email'], $_POST['kr_usr_pwd'], 'standard', $tfsCode);
    if ($loginResult == 1) {
        // Ok --> redirect user
        die(json_encode(['error' => 0, 'href' => APP_URL.'/dashboard'.($App->_rewriteDashBoardName() ? '' : '.php')]));
    } else if($loginResult == 2){
      die(json_encode(['error' => 3, 'user' => App::encrypt_decrypt('encrypt', $_POST['kr_usr_email']), 'pwd' => App::encrypt_decrypt('encrypt', $_POST['kr_usr_pwd'])]));
    } else if($loginResult == 4){
      die(json_encode(['error' => 4, 'msg' => 'Wrong Google Authenticator code']));
    }

    throw new Exception("Error : Login fail", 1);


} catch (Exception $e) {
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}
