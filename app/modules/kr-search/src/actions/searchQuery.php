<?php
/**
 * Search query
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */

session_start();

require "../../../../../config/config.settings.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/vendor/autoload.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/MySQL/MySQL.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/App.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/User/User.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/modules/kr-search/src/Search.php";

$App = new App();

try {

    //Check if user is logged
    $User = new User();
    if (!$User->_isLogged()) {
        throw new Exception("User is not logged", 1);
    }

     

    $Search = new Search($App);
	
	
	

    $AllElements = $Search->_getFromCache();
	
	
    die(json_encode([
      'error' => 0,
      'coinlist' => $Search->_query($_GET['request']),
      'native' => ($App->_getHideMarket() ? 1 : 0)
    ]));

} catch (\Exception $e) {
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}
