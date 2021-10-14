<?php

/**
 * Remove PushBullet action
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

    if(empty($_POST) || !isset($_POST['google_tfs_code']) || empty($_POST['google_tfs_code'])) throw new Exception("Code not valid.", 1);

    if(!$User->_checkGoogleTFS($_POST['google_tfs_code'])) throw new Exception("Code not valid.", 1);

    $User->_enableGoogleTFS();

    die(json_encode([
      'error' => 0,
      'msg' => 'Done !'
    ]));

} catch (Exception $e) {
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}
