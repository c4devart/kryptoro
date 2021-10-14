<?php

/**
 * Init PushBullet action
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

    // Check if user is logged
    $User = new User();
    if (!$User->_isLogged()) {
        throw new Exception("User not logged", 1);
    }

    // Init lang object
    $Lang = new Lang($User->_getLang(), $App);

    if(!isset($_POST['kr_prof_u']) || (!$User->_isAdmin() && $_POST['kr_prof_u'] != $User->_getUserID(true))){
      throw new Exception("Error : Permission denied", 1);
    }

    $adminAction = false;
    if($User->_isAdmin() && $_POST['kr_prof_u'] != $User->_getUserID(true)){
      $User = new User(App::encrypt_decrypt('decrypt', $_POST['kr_prof_u']));
      $adminAction = true;
    }

    // Define PushBullet ket user
    $User->_definePushbulletKey(new NotificationCenter($User), $_POST['pushbullet_key'], $adminAction);

    die(json_encode([
      'error' => 0,
      'msg' => $Lang->tr('Done !')
    ]));

} catch (Exception $e) {
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}
