<?php

/**
 * Save trading settings
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

    if(empty($_POST) || empty($_POST['exchange'])) throw new Exception("Error : Permission denied", 1);


    $exchangeList = json_decode($_POST['exchange'], true);

    $credentialsActive = $App->_hiddenThirdpartyServiceCfg();

    $newHiddenCredentials = [];
    foreach ($exchangeList as $exchangeName) {
      if(array_key_exists($exchangeName, $credentialsActive)) $newHiddenCredentials[$exchangeName] = $credentialsActive[$exchangeName];
    }

    $App->_setThirdpartyServiceCfg(json_encode($newHiddenCredentials));
    
} catch (\Exception $e) { // If throw exception, return error message
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}

?>
