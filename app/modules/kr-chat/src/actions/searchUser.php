<?php

/**
 * Search user chat
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

    $Chat = new Chat($User);
    $listUser = [];
    foreach ($Chat->_searchUser(htmlspecialchars($_POST['search_query']), $User) as $UserItem) {
      if($UserItem->_getUserID() == $User->_getUserID()) continue;
      $listUser[] = [
        'name' => $UserItem->_getName(),
        'id'   => $UserItem->_getUserID(),
        'id_encrypted' => $UserItem->_getUserID(true),
        'picture' => $UserItem->_getPicture(),
        'color' => $UserItem->_getAssociateColor()
      ];
    }

    die(json_encode([
      'error' => 0,
      'list' => $listUser
    ]));

} catch (\Exception $e) {
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}
