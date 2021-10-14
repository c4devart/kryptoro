<?php

/**
 * Admin dashboard page
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

// Load app modules
$App = new App(true);
$App->_loadModulesControllers();

try {

  $User = new User();
  if(!$User->_isLogged()) throw new Exception("User are not logged", 1);
  if(!$User->_isAdmin() && !$User->_isManager()) throw new Exception("Permission denied", 1);

  if(empty($_POST) || !isset($_POST['kr-manager-modif-balance-idu'])
                   || !isset($_POST['kr-manager-modif-balance-value'])
                   || !isset($_POST['kr-manager-modif-balance-t'])
                   || !isset($_POST['kr-manager-modif-balance-symbol']));


  $Manager = new Manager($App);
  $newBalanceValue = $Manager->_modifiyUserBalance($_POST['kr-manager-modif-balance-idu'],
                                $_POST['kr-manager-modif-balance-value'],
                                $_POST['kr-manager-modif-balance-t'],
                                $_POST['kr-manager-modif-balance-symbol']);

  die(json_encode([
    'error' => 0,
    'title' => 'Balance updated !',
    'msg' => 'New balance ('.$_POST['kr-manager-modif-balance-symbol'].') value : '.$newBalanceValue.' '.$_POST['kr-manager-modif-balance-symbol']
  ]));


} catch (\Exception $e) {
  die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}


?>
