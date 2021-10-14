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


    $ChatRoom = new ChatRoom(App::encrypt_decrypt('decrypt', $_GET['room']), $User);

} catch (\Exception $e) {
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}


?>
<header>
  <div class="kr-chat-c-user">
    <div class="kr-chat-c-user-picture" style="background-color:<?php echo $ChatRoom->_getRoomColor(); ?>;background-image:url('<?php echo $ChatRoom->_getRoomPicture();?>')"></div>
    <div class="kr-chat-c-user-infos">
      <span><?php echo $ChatRoom->_getRoomName(); ?></span>
      <?php if(!$ChatRoom->_isGroup()): ?>
      <div>
        <div class="kr-chat-c-user-infos-status kr-chat-c-user-infos-status-<?php echo $ChatRoom->_getDistantUser()->_getUserStatus(); ?>"></div>
        <span><?php echo $User->_getUserStatusText($ChatRoom->_getDistantUser()->_getUserStatus()); ?></span>
        <?php
        $UserLocation = $ChatRoom->_getDistantUser()->_getUserLocation();
        if(!is_null($UserLocation)):
        ?>
        <span>|</span>
        <span><?php echo $UserLocation; ?></span>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <ul>
    <?php if(!$ChatRoom->_isGroup()): ?>
      <?php if(false): ?>
      <li>
        <a kr-chat-blockuser="<?php echo $ChatRoom->_getDistantUser()->_getUserID(true); ?>" class="btn btn-autowidth <?php if($ChatRoom->_userIsBlocked()) echo 'btn-red'; ?>">
          <svg class="lnr lnr-bug"><use xlink:href="#lnr-bug"></use></svg>
          <span>Block</span>
        </a>
      </li>
    <?php endif; ?>
    <?php else: ?>
      <li>
        <a class="btn btn-autowidth">
          <svg class="lnr lnr-bug"><use xlink:href="#lnr-bug"></use></svg>
          <span>Leave</span>
        </a>
      </li>
    <?php endif; ?>
  </ul>
</header>
<section class="kr-chat-body-room">
  <div class="kr-chat-upload-hover">
    <div>
      <svg class="lnr lnr-cloud"><use xlink:href="#lnr-cloud"></use></svg>
      <span>Drop your file here</span>
    </div>
  </div>
  <section class="kr-chat-room-listmsg" kr-chat-room-id="<?php echo $ChatRoom->_getRoomID(true); ?>">
    <?php $oldDate = null; foreach ($ChatRoom->_getMessageList() as $Message) { ?>

      <?php if($Message->_isText() && (is_null($oldDate) || $Message->_changeDay($oldDate))): ?>
        <div class="kr-chat-chngdate">
          <span><?php echo date('d/m/Y', $Message->_getTimeSended()); ?></span>
        </div>
      <?php
        $oldDate = $Message->_getTimeSended();
      endif; ?>

      <?php if($Message->_isRoomInit()): ?>
        <div class="kr-chat-init-room">
          <ul>
            <?php
            $ziInit = 999999;
            foreach ($ChatRoom->_getListUser() as $InitUser) {
              echo '<li style="background-color:'.$InitUser->_getAssociateColor().';background-image:url(\''.$InitUser->_getPicture().'\');z-index:'.$ziInit.';"></li>';
              $ziInit--;
            }
            ?>
          </ul>
        </div>
      <?php elseif($Message->_isJoinRoom()): ?>
        <div class="kr-chat-nuser-room">
          <span><b><?php echo $Message->_getValueMessage(); ?></b> join the room</span>
        </div>
      <?php elseif($Message->_isText() || $Message->_isPicture() || $Message->_isFile()): ?>
        <div kr-chat-msg-id="<?php echo $Message->_getMessageID(true); ?>" kr-chat-msg-idu="<?php echo $Message->_getSenderUserID(); ?>" kr-chat-msg-time="<?php echo $Message->_getTimeSended(); ?>" class="kr-chat-msg <?php if($Message->_isUser($User)) echo 'kr-chat-msg-me'; ?>">
          <div class="kr-chat-msg-picture">
            <div <?php if(!$Message->_isUser($User)): ?>style="background-color:<?php echo $Message->_getSenderUserObj()->_getAssociateColor(); ?>;background-image:url('<?php echo $Message->_getSenderUserObj()->_getPicture();?>')"<?php endif; ?>>

            </div>
          </div>
          <div class="kr-chat-msg-content">
            <span><?php if(!$Message->_isUser($User)) echo $Message->_getSenderUserObj()->_getName().', '; ?><?php echo date('H:i', $Message->_getTimeSended()); ?></span>
            <div>
              <?php if($Message->_isText()): ?>
                <div><?php echo $Message->_getValueMessage(); ?></div>
              <?php elseif($Message->_isPicture()): ?>
                <img src="<?php echo $Message->_getValueMessage(); ?>" alt="">
              <?php elseif($Message->_isFile()): ?>
                <div class="kr-chat-msg-file" onclick="window.open('<?php echo $Message->_getFileDownloadLink(); ?>', '_blank');">
                  <div>
                    <div class="file-icon" data-type="<?php echo $Message->_getFileExtension(); ?>"></div>
                  </div>
                  <span><?php echo $Message->_getFileName(); ?></span>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endif; ?>

    <?php } ?>

  </section>
  <div class="kr-chat-upload-progress">
    <div></div>
  </div>
  <form method="post" class="chat-room-form" action="<?php echo APP_URL; ?>/app/modules/kr-chat/src/actions/roomSendMessage.php">
    <div>
      <input type="hidden" name="room_id" value="<?php echo $ChatRoom->_getRoomID(true); ?>">
      <input type="text" name="room_msg" placeholder="Write your message ..." <?php echo ((!$ChatRoom->_isGroup() && $ChatRoom->_userIsBlocked()) ? 'disabled style="color:red;" value="You have blocked the user"' : 'value=""'); ?>>
    </div>
      <ul <?php if(!$ChatRoom->_isGroup() && $ChatRoom->_userIsBlocked()) echo 'style="display:none;"'; ?>>
        <li class="kr-chat-upload-btn"><svg class="lnr lnr-file-add"><use xlink:href="#lnr-file-add"></use></svg></li>
      </ul>
      <section class="kr-chat-sendmsg-btn" <?php if(!$ChatRoom->_isGroup() && $ChatRoom->_userIsBlocked()) echo 'style="display:none;"'; ?>>
        <svg class="lnr lnr-location"><use xlink:href="#lnr-location"></use></svg>
      </section>
  </form>
</section>
