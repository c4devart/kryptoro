<?php

/**
 * Add plan subscription
 *
 * This actions permit to admin to add an plan to krypto
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
        throw new Exception("Your are not logged");
    }
    if (!$User->_isAdmin()) {
        throw new Exception("Error : Permission denied");
    }

    if($App->_isDemoMode()) throw new Exception("App currently in demo mode", 1);


    // Check data available
    if (empty($_POST)) {
        throw new Exception("Error : Args not valid");
    }
    if (empty($_POST['kr-adm-nameplan']) || empty($_POST['kr-adm-totalprice']) || empty($_POST['kr-adm-durationdays'])) {
        throw new Exception("You need to fill up all field");
    }

    // Get user charge
    $Charge = $User->_getCharge($App);

    // Create plan with : @nameplan, @totalprice, @planduration
    $Charge->_createPlan($_POST['kr-adm-nameplan'], $_POST['kr-adm-totalprice'], $_POST['kr-adm-durationdays']);

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
