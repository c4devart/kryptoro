<?php

/**
 * Add indicator to graph action
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
        throw new Exception("User is not logged", 1);
    }

    if (!$User->_isAdmin()) {
        throw new Exception("Permission denied", 1);
    }

    if (!$User->_isManager()) {
        throw new Exception("Permission denied", 1);
    }

    if(empty($_POST) || !isset($_POST['ididentity']) || !isset($_POST['status'])) throw new Exception("Permission denied", 1);

    $Identity = new Identity();
    $IdentityUser = $Identity->_getIdentityByIdentityID(App::encrypt_decrypt('decrypt', $_POST['ididentity']));
    $IdentityUser->_changeStatus($_POST['status'], (isset($_POST['args']) ? $_POST['args'] : null));

} catch (\Exception $e) {
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}
