<?php

/**
 * Admin dashboard page
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */

session_start();

require "../../../../../config/config.settings.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/MySQL/MySQL.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/App.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/AppModule.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/User/User.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/Lang/Lang.php";

// Load app modules
$App = new App(true);
$App->_loadModulesControllers();

try {

  $User = new User();
  if(!$User->_isLogged()) throw new Exception("User are not logged", 1);
  if(!$User->_isAdmin()) throw new Exception("Permission denied", 1);


  $Statistics = new Statistics();

  var_dump($Statistics->_generateListDate());

} catch (\Exception $e) {
  die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}


?>
