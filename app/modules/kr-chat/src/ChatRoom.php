<?php

class ChatRoom extends MySQL {

  private $RoomID = null;
  private $CurrentUser = null;
  private $ListUser = null;

  public function __construct($RoomID = null, $CurrentUser = null){
    $this->RoomID = $RoomID;
    $this->CurrentUser = $CurrentUser;
  }

  public function _getRoomID($encrypted = false){
    if($encrypted) return App::encrypt_decrypt('encrypt', $this->RoomID);
    return $this->RoomID;
  }

  public function _getCurrentUser(){ return $this->CurrentUser; }

  public function _getListUser(){

    if(!is_null($this->ListUser)) return $this->ListUser;

    $r = parent::querySqlRequest("SELECT * FROM user_room_chat_krypto WHERE id_room_chat=:id_room_chat", ['id_room_chat' => $this->_getRoomID()]);

    $this->ListUser = [];
    foreach ($r as $key => $value) {
      $this->ListUser[] = new User($value['id_user']);
    }
    return $this->ListUser;

  }

  public function _isGroup(){
    return count($this->_getListUser()) > 2;
  }

  public function _getRoomName(){
    if(!$this->_isGroup()){
      foreach ($this->_getListUser() as $User) {
        if($User->_getUserID() != $this->_getCurrentUser()->_getUserID()) return $User->_getName();
      }
    }

  }

  public function _getDistantUser(){
    foreach ($this->_getListUser() as $User) {
      if($User->_getUserID() != $this->_getCurrentUser()->_getUserID()) return $User;
    }
    return null;
  }


  public function _userIsBlocked(){
    $blocked = false;
    foreach ($this->_getListUser() as $User) {
      if($User->_getUserID() != $this->_getCurrentUser()->_getUserID()){
        $r = parent::querySqlRequest("SELECT * FROM blocked_user_chat_krypto WHERE id_user=:id_user AND id_user_blocked=:id_user_blocked",
                                    [
                                      'id_user' => $this->_getCurrentUser()->_getUserID(),
                                      'id_user_blocked' => $User->_getUserID()
                                    ]);
        if(count($r) > 0) $blocked = true;
      }
    }
    return $blocked;
  }

  public function _getRoomPicture(){
    if(!$this->_isGroup()){
      foreach ($this->_getListUser() as $User) {
        if($User->_getUserID() != $this->_getCurrentUser()->_getUserID()) return $User->_getPicture();
      }
    }
  }

  public function _getRoomColor(){
    if(!$this->_isGroup()){
      foreach ($this->_getListUser() as $User) {
        if($User->_getUserID() != $this->_getCurrentUser()->_getUserID()) return $User->_getAssociateColor();
      }
    }
  }

  public function _getLastMsgSendTime($dlist = false){

    $r = parent::querySqlRequest("SELECT * FROM msg_room_chat_krypto WHERE id_room_chat=:id_room_chat ORDER BY date_msg_room_chat DESC", ['id_room_chat' => $this->_getRoomID()]);
    if(count($r) == 0) return "0";
    if($dlist){

      $DateTimeCurrent = new DateTime('now');
      $DateTimeCurrent->setTime(0,0);

      $DateTimeMsg = new DateTime();
      $DateTimeMsg->setTimestamp($r[0]['date_msg_room_chat']);
      $DateTimeMsg->setTime(0,0);

      if($DateTimeMsg->getTimestamp() != $DateTimeCurrent->getTimestamp()){
        if($DateTimeCurrent->diff($DateTimeMsg)->days < 7){
          return $DateTimeMsg->format('l');
        }
      } else {
        return date('H:i', $r[0]['date_msg_room_chat']);
      }

      return $DateTimeMsg->getTimestamp();

    }
    return $r[0]['date_msg_room_chat'];

  }

  public function _getLastMsgText(){

    $r = parent::querySqlRequest("SELECT * FROM msg_room_chat_krypto WHERE id_room_chat=:id_room_chat AND type_msg_room_chat=:type_msg_room_chat ORDER BY date_msg_room_chat DESC",
                                      ['id_room_chat' => $this->_getRoomID(), 'type_msg_room_chat' => 'text']);
    if(count($r) == 0) return null;
    return $r[0]['value_msg_room_chat'];

  }

  public function _getMessageList(){
    $r = [];
    foreach (parent::querySqlRequest("SELECT * FROM msg_room_chat_krypto WHERE id_room_chat=:id_room_chat ORDER BY date_msg_room_chat ASC", ['id_room_chat' => $this->_getRoomID()]) as $key => $value) {
      $r[] = new ChatMessage($value['id_msg_room_chat'], $value);
    }
    return $r;
  }

  public function _getLastMsgList($limit = 20){
    $r = [];
    foreach (parent::querySqlRequest("SELECT * FROM msg_room_chat_krypto WHERE id_room_chat=:id_room_chat ORDER BY date_msg_room_chat DESC LIMIT ".$limit,
                                      ['id_room_chat' => $this->_getRoomID()]) as $key => $value) {
      $r[] = new ChatMessage($value['id_msg_room_chat'], $value);
    }
    return array_reverse($r);
  }

  public function _sendMessage($User, $type, $value){

    $controlKey = uniqid(true);

    $r = parent::execSqlRequest("INSERT INTO msg_room_chat_krypto (id_room_chat, id_user, type_msg_room_chat, value_msg_room_chat, date_msg_room_chat, control_key_msg_room_chat)
                                  VALUES (:id_room_chat, :id_user, :type_msg_room_chat, :value_msg_room_chat, :date_msg_room_chat, :control_key_msg_room_chat)",
                                  [
                                    'id_room_chat' => $this->_getRoomID(),
                                    'id_user' => $User->_getUserID(),
                                    'type_msg_room_chat' => $type,
                                    'value_msg_room_chat' => $value,
                                    'date_msg_room_chat' => time(),
                                    'control_key_msg_room_chat' => $controlKey
                                  ]);

    if(!$r) throw new Exception("Error SQL : Fail to send message", 1);

    $r = parent::querySqlRequest("SELECT * FROM msg_room_chat_krypto WHERE control_key_msg_room_chat=:control_key_msg_room_chat AND id_user=:id_user AND id_room_chat=:id_room_chat",
                                [
                                  'control_key_msg_room_chat' => $controlKey,
                                  'id_user' => $User->_getUserID(),
                                  'id_room_chat' => $this->_getRoomID()
                                ]);

    if(count($r) == 0) throw new Exception("Error : Fail to receive sended message", 1);

    $r[0]['id_encrypted'] = App::encrypt_decrypt('encrypt', $r[0]['id_msg_room_chat']);
    $r[0]['hours'] = date('H:i', $r[0]['date_msg_room_chat']);

    if($r[0]['type_msg_room_chat'] == "file"){
      $MessageInfos = new ChatMessage($r[0]['id_msg_room_chat']);
      $r[0]['extension'] = $MessageInfos->_getFileExtension();
      $r[0]['url_download'] = $MessageInfos->_getFileDownloadLink();
      $r[0]['file_name'] = $MessageInfos->_getFileName();
    }

    return $r[0];


  }

  public function _addUser($User){

    $r = parent::querySqlRequest("SELECT * FROM user_room_chat_krypto WHERE id_user=:id_user AND id_room_chat=:id_room_chat",
                                [
                                  'id_room_chat' => $this->_getRoomID(),
                                  'id_user' => $User->_getUserID()
                                ]);

    if(count($r) == 0){
      $r = parent::execSqlRequest("INSERT INTO user_room_chat_krypto (id_room_chat, id_user, date_user_room_chat)
                                  VALUES (:id_room_chat, :id_user, :date_user_room_chat)",
                                  [
                                    'id_room_chat' => $this->_getRoomID(),
                                    'id_user' => $User->_getUserID(),
                                    'date_user_room_chat' => time()
                                  ]);

      if(!$r) throw new Exception("Error : Fail to add user (room = ".$this->_getRoomID()."; user = ".$User->_getUserID().")", 1);

    }

  }

}

?>
