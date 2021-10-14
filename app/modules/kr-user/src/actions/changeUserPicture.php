<?php

/**
 * Changer user picture action
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

try {

    // Load app modules
    $App = new App(true);
    $App->_loadModulesControllers();

    // Check if user is logged
    $User = new User();
    if (!$User->_isLogged()) {
        throw new Exception("User not logged", 1);
    }

    $reloadPicture = true;
    if($User->_isAdmin() && $_SESSION['kr_account_view_user'] != $User->_getUserID()){
      $User = new User($_SESSION['kr_account_view_user']);
      $reloadPicture = false;
    }

    // Check files given
    if (empty($_FILES)) {
        throw new Exception("Error : Missing file picture", 1);
    }

    // Show json result
    die(json_encode([
      'error' => 0,
      'reload' => $reloadPicture,
      'picture' => $User->_changePicture($_FILES['file'], !$reloadPicture) // Change user picture & get path
    ]));

} catch (Exception $e) {
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}
