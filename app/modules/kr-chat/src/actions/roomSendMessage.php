<?php

/**
 * Fetch new message krypto
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

    if(empty($_POST) || !isset($_POST['room_id']) || empty($_POST['room_id'])) throw new Exception("Permission denied", 1);

    $Chat = new Chat($User);
    $Room = new ChatRoom(App::encrypt_decrypt('decrypt', $_POST['room_id']));
    if(isset($_POST['room_msg'])){
      $msgData = $Room->_sendMessage($User, 'text', $_POST['room_msg']);
    }

    if(!empty($_FILES)){
      $listDirectoryNeeded = ['public/chat', 'public/chat/'.$Room->_getRoomID(true)];
      foreach ($listDirectoryNeeded as $directoryNeeded) {
        if(!file_exists('../../../../../'.$directoryNeeded)){
          //error_log('../../../../../'.$directoryNeeded);
          if(!mkdir('../../../../../'.$directoryNeeded)) throw new Exception("Error : Fail to create directory (".$directoryNeeded.")", 1);
        }
      }
      //error_log(json_encode($_FILES));
      $pictureName = time().'-'.basename($_FILES['file']['name']);
      if (!move_uploaded_file($_FILES['file']['tmp_name'], '../../../../../public/chat/'.$Room->_getRoomID(true).'/'.$pictureName)) throw new Exception("Error : Fail to upload picture", 1);

      $fileNameInfos = explode('.', $pictureName);
      $extension = strtolower($fileNameInfos[count($fileNameInfos) - 1]);

      $fileType = 'file';
      $imgList = ['jpg', 'jpeg', 'gif', 'png'];
      if(in_array($extension, $imgList)) $fileType = "picture";

      $msgData = $Room->_sendMessage($User, $fileType, '{APP_URL}/public/chat/'.$Room->_getRoomID(true).'/'.$pictureName);
    }

    die(json_encode([
      'error' => 0,
      'msg' => $msgData
    ]));

} catch (\Exception $e) {
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}
