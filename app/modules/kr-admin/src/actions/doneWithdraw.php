<?php

/**
 * Set done withdraw
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
    if (!$User->_isLogged() || (!$User->_isAdmin() && !$User->_isManager())) {
        throw new Exception("Permission denied", 1);
    }

    if($App->_isDemoMode()) throw new Exception("App currently in demo mode", 1);

    // Check data available
    if (empty($_POST) || empty($_POST['request_id'])) {
        throw new Exception("Permission denied", 1);
    }

    $Balance = new Balance($User, $App);
    $Balance->_setDoneWithdraw(App::encrypt_decrypt('decrypt', $_POST['request_id']));

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
