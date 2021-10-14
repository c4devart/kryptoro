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

    $User->_updateUserStatus((!empty($_GET) && isset($_GET['chat_user_status']) && !empty($_GET['chat_user_status']) ? $_GET['chat_user_status'] : null ));

    $Chat = new Chat($User);
    $RoomList = $Chat->_getListRoom();


    $listLastMsg = [];
    $lastMsgChat = [];
    $roomList = [];

    foreach ($RoomList as $Room) {
      $listLastMsg[$Room->_getRoomID(true)] = $Room->_getLastMsgSendTime();
      $lastMsgChat[$Room->_getRoomID(true)] = [];

      $roomList[$Room->_getRoomID(true)] = [
        'color' => $Room->_getRoomColor(),
        'picture' => $Room->_getRoomPicture(),
        'name' => $Room->_getRoomName(),
        'last_msg' => $Room->_getLastMsgSendTime()
      ];

      $RoomLastMessageText = $Room->_getLastMsgSendTime(true);

      foreach ($Room->_getLastMsgList(20) as $Message) {
        $lastMsgChat[$Room->_getRoomID(true)][$Message->_getMessageID(true)] = $Message->_getFullMessageData();
        $lastMsgChat[$Room->_getRoomID(true)][$Message->_getMessageID(true)]['me'] = $Message->_getSenderUserID() == $User->_getUserID();
        $lastMsgChat[$Room->_getRoomID(true)][$Message->_getMessageID(true)]['id_encrypted'] = $Message->_getMessageID(true);
        $lastMsgChat[$Room->_getRoomID(true)][$Message->_getMessageID(true)]['hours'] = date('H:i', $Message->_getTimeSended());
        $lastMsgChat[$Room->_getRoomID(true)][$Message->_getMessageID(true)]['date_formated_lm'] = $RoomLastMessageText;

        if($Message->_isFile()){
          $lastMsgChat[$Room->_getRoomID(true)][$Message->_getMessageID(true)]['extension'] = $Message->_getFileExtension();
          $lastMsgChat[$Room->_getRoomID(true)][$Message->_getMessageID(true)]['url_download'] = $Message->_getFileDownloadLink();
          $lastMsgChat[$Room->_getRoomID(true)][$Message->_getMessageID(true)]['file_name'] = $Message->_getFileName();
        }


        $UserSender = new User($Message->_getSenderUserID());

        $lastMsgChat[$Room->_getRoomID(true)][$Message->_getMessageID(true)]['user_data'] = [
          'name' => $UserSender->_getName(),
          'picture' => $UserSender->_getPicture(),
          'associate_color' => $UserSender->_getAssociateColor()
        ];
      }
    }

    $userStatus = [];
    foreach ($RoomList as $Room) {
      if($Room->_isGroup()){
        $userStatus[$Room->_getRoomID(true)] = [
          'type' => 'room',
          'status' => 3
        ];
      } else {
        foreach ($Room->_getListUser() as $distUser) {
          if($distUser->_getUserID() != $User->_getUserID()){
            $userStatus[$Room->_getRoomID(true)] = [
              'type' => 'user',
              'status' => $distUser->_getUserStatus()
            ];
          }
        }
      }
    }

    die(json_encode([
      'error' => 0,
      'last_msg' => $listLastMsg,
      'list_msg' => $lastMsgChat,
      'user_status' => $userStatus,
      'room_list' => $roomList,
      'my_status' => $User->_getUserStatus()
    ]));

} catch (\Exception $e) {
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}
