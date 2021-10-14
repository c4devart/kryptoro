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

    // Check data available
    if (empty($_POST)) {
        throw new Exception("Error : Args not valid", 1);
    }

    $App->_saveCalendarSettings(
      (array_key_exists('kr-adm-chk-enablecalandar', $_POST) && $_POST['kr-adm-chk-enablecalandar'] == "on" ? 1 : 0),
      ($_POST['kr-adm-calendarclientid'] == '**************' ? $App->_getCalendarCientID() : $_POST['kr-adm-calendarclientid']),
      ($_POST['kr-adm-calendarclientsecret'] == '**************' ? $App->_getCalendarClientSecret() : $_POST['kr-adm-calendarclientsecret']),
      (array_key_exists('kr-adm-chk-enablecalandarenablecoin', $_POST) && $_POST['kr-adm-chk-enablecalandarenablecoin'] == "on" ? 1 : 0)
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
