<?php

/**
 * Change payment settings
 *
 * This actions permit to admin to change payment settings in Krypto
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

    $BlockExplorer = new BlockExplorer($App, $User);

    $listAddr = [];
    foreach ($_POST as $key => $value) {
      if(strpos($key, 'addr_') !== false){
        $symbol = explode('_', $key)[1];
        $listAddr[$symbol] = ['address' => (strlen($value) == 0 ? null : $value), 'confirmations' => null];
      }

      if(strpos($key, 'confirm_') !== false){
        if(strlen($listAddr[$symbol]['address']) == 0) continue;
        $symbol = explode('_', $key)[1];
        $listAddr[$symbol]['confirmations'] = $value;
      }
    }

    $BlockExplorer->_saveAddr($listAddr);

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
