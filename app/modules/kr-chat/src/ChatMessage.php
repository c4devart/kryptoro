<?php

class ChatMessage extends MySQL {

  private $MessageID = null;
  private $MessageData = null;
  private $SenderUserObj = null;
  private $SmileyList = [
    [ 'symbol' => '(:', 'decal'  => [-595, -595] ],
    [ 'symbol' => '>:(', 'decal'  => [0, -595] ],
    [ 'symbol' => ':(', 'decal'  => [-561, -595] ],
    [ 'symbol' => ':O', 'decal'  => [-238, -595] ],
    [ 'symbol' => ':P', 'decal'  => [-595, -510] ],
    [ 'symbol' => '|:)', 'decal'  => [-595, -170] ],
    [ 'symbol' => ":'D", 'decal'  => [-595, -85] ],
    [ 'symbol' => ":')", 'decal'  => [-595, -85] ],
    [ 'symbol' => ":*", 'decal'  => [-595, -459] ],
    [ 'symbol' => ":|", 'decal'  => [-595, -323] ],
    [ 'symbol' => ":'(", 'decal'  => [-221, -595] ],
    [ 'symbol' => ':D', 'decal'  => [-595, -51] ],
    [ 'symbol' => ':)', 'decal'  => [-578, -595] ]
  ];

  public function __construct($MessageID = null, $MessageData = null){
    $this->MessageID = $MessageID;
    if(!is_null($MessageID) && is_null($MessageData)) $this->_loadMessage();
    elseif(!is_null($MessageID) && !is_null($MessageData)) $this->MessageData = $MessageData;
  }

  public function _getMessageID($encrypted = false){
    if($encrypted) return App::encrypt_decrypt('encrypt', $this->MessageID);
    return $this->MessageID;
  }

  public function _loadMessage(){
    $r = parent::querySqlRequest("SELECT * FROM msg_room_chat_krypto WHERE id_msg_room_chat=:id_msg_room_chat", ['id_msg_room_chat' => $this->_getMessageID()]);
    if(count($r) == 0) throw new Exception("Error : Fail to load message (".$this->_getMessageID().")", 1);
    $this->MessageData = $r[0];
  }

  private function _getMessageDataByKey($key){

    if(!array_key_exists($key, $this->MessageData)) throw new Exception("Error : Message data not exist for key = ".$key, 1);
    if(empty($this->MessageData[$key]) || strlen($this->MessageData[$key]) == 0) return null;
    return $this->MessageData[$key];
  }

  public function _getFullMessageData(){ return $this->MessageData; }

  public function _getType(){ return $this->_getMessageDataByKey('type_msg_room_chat'); }
  public function _getSenderUserID(){ return $this->_getMessageDataByKey('id_user'); }
  public function _getTimeSended(){ return $this->_getMessageDataByKey('date_msg_room_chat'); }

  public function _formatMessageSmiley($base_msg){

    foreach ($this->SmileyList as $keySmiley => $valSmiley) {
      $base_msg = str_replace($valSmiley['symbol'], '<div class="emoji-block" style="background-position:'.$valSmiley['decal'][0].'px '.$valSmiley['decal'][1].'px;"></div>', $base_msg);
    }
    return $base_msg;

  }

  public function _getValueMessage(){
    $message = str_replace('{APP_URL}', APP_URL, $this->_getMessageDataByKey('value_msg_room_chat'));
    return $this->_formatMessageSmiley($message);
  }

  public function _isRoomInit(){ return $this->_getType() == "init_room"; }
  public function _isJoinRoom(){ return $this->_getType() == "join_room"; }
  public function _isText(){ return $this->_getType() == "text"; }
  public function _isPicture(){ return $this->_getType() == "picture"; }
  public function _isFile(){ return $this->_getType() == "file"; }

  public function _getFileName(){
    $basename = basename($this->_getValueMessage());
    $basenameSplit = explode('-', $basename);
    return join('-', array_slice($basenameSplit, 1));
  }

  public function _getFileExtension(){
    $fileNameInfos = explode('.', $this->_getFileName());
    return strtolower($fileNameInfos[count($fileNameInfos) - 1]);
  }

  public function _getFileDownloadLink(){
    return APP_URL.'/app/modules/kr-chat/src/actions/downloadAttachedFile.php?p='.App::encrypt_decrypt('encrypt', $this->_getValueMessage());
  }

  public function _changeDay($current){

    return date('d/m/Y', $this->_getTimeSended()) != date('d/m/Y', $current);
  }

  public function _isUser($User){
    return $User->_getUserID() == $this->_getSenderUserID();
  }

  public function _getSenderUserObj(){
    if(!is_null($this->SenderUserObj)) return $this->SenderUserObj;
    $this->SenderUserObj = new User($this->_getSenderUserID());
    return $this->SenderUserObj;
  }

}

?>
