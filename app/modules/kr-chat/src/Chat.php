<?php

class Chat extends MySQL {

  private $User = null;
  private $ListRoom = null;

  public function __construct($User){

    $this->User = $User;

  }

  public function _getUser(){ return $this->User; }

  public function _getListRoom(){

    if(!is_null($this->ListRoom)) return $this->ListRoom;

    $this->ListRoom = [];
    foreach (parent::querySqlRequest("SELECT * FROM room_chat_krypto WHERE EXISTS (SELECT id_user_room_chat FROM user_room_chat_krypto WHERE id_room_chat=room_chat_krypto.id_room_chat AND id_user=:id_user)",
                                      ['id_user' => $this->_getUser()->_getUserID()]) as $key => $value) {


      $this->ListRoom[] = new ChatRoom($value['id_room_chat'], $this->_getUser());
    }

    function _sortListRoom($a, $b){
      return $a->_getLastMsgSendTime() < $b->_getLastMsgSendTime();
    }

    usort($this->ListRoom, "_sortListRoom");

    return $this->ListRoom;

  }

  public function _searchUser($search, $me){

    $listUser = parent::querySqlRequest("SELECT * FROM user_krypto WHERE name_user LIKE :search_str OR name_user LIKE :search_str",
                                        [
                                          'search_str' => '%'.$search.'%'
                                        ]);

    $listUserReturned = [];
    $listRoomAvailable = $this->_getListRoom();
    foreach ($listUser as $key => $infosUser) {
      $ufind = true;
      foreach ($listRoomAvailable as $chatRoom) {
        if($chatRoom->_isGroup()) continue;
        foreach ($chatRoom->_getListUser() as $UserFetched) {
          if($UserFetched->_getUserID() == $me->_getUserID()) continue;
          if($UserFetched->_getUserID() == $infosUser['id_user']) $ufind = false;
        }
      }
      if($ufind) $listUserReturned[$infosUser['id_user']] = new User($infosUser['id_user']);
    }
    return $listUserReturned;

  }

  public function _clearPictureCron($delay = 5){
    $r = parent::querySqlRequest("SELECT * FROM msg_room_chat_krypto WHERE date_msg_room_chat < :date_msg_room_chat AND type_msg_room_chat=:type_msg_room_chat",
                                [
                                  'type_msg_room_chat' => 'picture',
                                  'date_msg_room_chat' => (time() - (86400 * $delay))
                                ]);
    var_dump($r);
  }

  public function _createNewRoom($User){

    $keyRoom = uniqid().rand(1,999);
    $r = parent::execSqlRequest("INSERT INTO room_chat_krypto (key_room_chat, date_created_room_chat)
                                VALUES (:key_room_chat, :date_created_room_chat)",
                                [
                                  'key_room_chat' => $keyRoom,
                                  'date_created_room_chat' => time()
                                ]);

    if(!$r) throw new Exception("Error : Fail to create room", 1);

    $r = parent::querySqlRequest("SELECT * FROM room_chat_krypto WHERE key_room_chat=:key_room_chat", ['key_room_chat' => $keyRoom]);
    if(count($r) == 0) throw new Exception("Error : Fail to fetch room (".$keyRoom.")", 1);

    $Room = new ChatRoom($r[0]['id_room_chat']);

    $Room->_addUser($this->_getUser());
    $Room->_addUser($User);

    $Room->_sendMessage($User, 'init_room', '');

    return [
      'id_room' => $Room->_getRoomID(),
      'enc_id_room' => $Room->_getRoomID(true)
    ];

  }

  public function _toggleBlock($RemoteUser){

    $r = parent::querySqlRequest("SELECT * FROM blocked_user_chat_krypto WHERE id_user=:id_user AND id_user_blocked=:id_user_blocked",
                                [
                                  'id_user' => $this->_getUser()->_getUserID(),
                                  'id_user_blocked' => $RemoteUser->_getUserID()
                                ]);

    $newStatus = 'blocked';
    if(count($r) == 0){
      $r = parent::execSqlRequest("INSERT INTO blocked_user_chat_krypto (id_user, id_user_blocked, date_blocked_user_chat)
                                  VALUES (:id_user, :id_user_blocked, :date_blocked_user_chat)",
                                  [
                                    'id_user' => $this->_getUser()->_getUserID(),
                                    'id_user_blocked' => $RemoteUser->_getUserID(),
                                    'date_blocked_user_chat' => time()
                                  ]);
    } else {
      $newStatus = 'unblocked';
      $r = parent::execSqlRequest("DELETE FROM blocked_user_chat_krypto WHERE id_user=:id_user AND id_user_blocked=:id_user_blocked",
                                  [
                                    'id_user' => $this->_getUser()->_getUserID(),
                                    'id_user_blocked' => $RemoteUser->_getUserID()
                                  ]);
    }
    if(!$r) throw new Exception("Error SQL : Fail to toggle block user", 1);

    return $newStatus;

  }

  public function _roomAvailable($User){
    $s =  parent::querySqlRequest("SELECT * FROM user_room_chat_krypto user_fetched WHERE id_user=:id_user AND EXISTS (SELECT id_room_chat FROM user_room_chat_krypto WHERE id_user=:id_user_verif AND id_room_chat = user_fetched.id_room_chat)",
                                  [
                                    'id_user' => $this->_getUser()->_getUserID(),
                                    'id_user_verif' => $User->_getUserID()
                                  ]);
    return (count($s) == 0 ? false : $s[0]['id_room_chat']);
  }


}

?>
