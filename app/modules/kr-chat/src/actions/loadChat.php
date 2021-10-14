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

    $Chat = new Chat($User);

} catch (\Exception $e) {
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}

?>
<section class="kr-chat kr-ov-nblr">
  <section>
    <header>
      <div>
        <div class="kr-chat-status-select">
          <?php
          $CurrentStatus = $User->_getUserStatus();
          ?>
          <div class="kr-chat-status kr-chat-status-<?php echo $User->_getUserStatusText($CurrentStatus); ?>">

          </div>
          <span><?php echo $User->_getUserStatusText($CurrentStatus); ?></span>
        </div>
      </div>
      <div class="kr-chat-close">
        <svg class="lnr lnr-cross"><use xlink:href="#lnr-cross"></use></svg>
      </div>
    </header>
    <section>
      <aside>
        <input type="text" name="" id="kr-chat-search-user" placeholder="Search someone ..." value="">
        <ul class="kr-chat-ulist">
          <?php
          foreach ($Chat->_getListRoom() as $Room) {
          ?>
          <li kr-chat-room-tmp="false" class="kr-chat-active-room"
            <?php if(!$Room->_isGroup()): ?>
              kr-chat-us="<?php echo $Room->_getDistantUser()->_getUserID(true); ?>"
            <?php endif; ?>
            kr-chat-lastmsg="<?php echo $Room->_getLastMsgSendTime(); ?>"
            kr-chat-type="<?php echo ($Room->_isGroup() ? 'group' : 'single'); ?>" kr-change-chat-rid="<?php echo $Room->_getRoomID(true); ?>">
            <div class="kr-chat-ulist-picture" style="background-color:<?php echo $Room->_getRoomColor(); ?>;background-image:url('<?php echo $Room->_getRoomPicture();?>')"></div>
            <div class="kr-chat-ulist-infos">
              <div>
                <span><?php echo $Room->_getRoomName(); ?></span>
                <span class="kr-chat-ulist-lmd"><?php echo $Room->_getLastMsgSendTime(true); ?></span>
              </div>
              <span><?php echo $Room->_getLastMsgText(); ?></span>
            </div>
          </li>
        <?php } ?>
        </ul>
      </aside>
      <section id="kr-chat-room-content" class="kr-chat-room-content-nl">
        <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 511.999 511.999" style="enable-background:new 0 0 511.999 511.999;" xml:space="preserve"> <g> <g> <path d="M372.29,50.758H28.831C12.933,50.758,0,63.691,0,79.588v206.056c0,15.897,12.933,28.831,28.831,28.831h22.271v76.71 c0,4.884,2.942,9.289,7.456,11.159c1.494,0.62,3.064,0.92,4.62,0.92c3.144,0,6.232-1.228,8.543-3.538l85.251-85.25h17.104 c6.671,0,12.078-5.407,12.078-12.078c0-6.672-5.409-12.079-12.078-12.079c0,0-22.69,0.016-22.927,0.04 c-2.812,0.191-5.572,1.349-7.722,3.498l-68.169,68.169c0,0-0.021-60.392-0.06-60.705c-0.545-6.166-5.717-11.002-12.024-11.002 H28.831c-2.578,0-4.674-2.097-4.674-4.674V79.588c0-2.578,2.097-4.674,4.674-4.674H372.29c2.578,0,4.674,2.097,4.674,4.674v79.055 c0,6.671,5.409,12.078,12.078,12.078s12.078-5.407,12.078-12.078V79.588C401.12,63.691,388.187,50.758,372.29,50.758z"/> </g> </g> <g> <g> <path d="M483.169,198.492H242.754c-15.897,0-28.831,12.933-28.831,28.831v140.57c0,15.897,12.933,28.831,28.831,28.831h150.514 l60.98,60.98c2.311,2.311,5.4,3.538,8.543,3.538c1.556,0,3.126-0.301,4.62-0.92c4.512-1.87,7.456-6.273,7.456-11.159v-52.44h8.301 c15.897,0,28.831-12.933,28.831-28.831V227.322C512,211.425,499.067,198.492,483.169,198.492z M487.844,367.893 c0,2.577-2.097,4.674-4.674,4.674h-20.376c-6.356,0-11.554,4.912-12.031,11.147c-0.031,0.264-0.051,36.29-0.051,36.29 l-43.854-43.855c-0.046-0.046-0.094-0.089-0.14-0.135c-0.172-0.168-0.335-0.314-0.489-0.445c-2.126-1.864-4.903-3.003-7.951-3.003 H242.754c-2.578,0-4.674-2.097-4.674-4.674v-140.57c0-2.578,2.097-4.674,4.674-4.674h240.416c2.577,0,4.674,2.097,4.674,4.674 V367.893z"/> </g> </g> <g> <g> <path d="M362.964,285.53c-6.667,0-12.078,5.411-12.078,12.078c0,6.667,5.411,12.078,12.078,12.078 c6.668,0,12.078-5.411,12.078-12.078C375.042,290.941,369.631,285.53,362.964,285.53z"/> </g> </g> <g> <g> <path d="M310.472,130.611c0,0-219.822,0-219.822,0c-6.67,0-12.078,5.407-12.078,12.078s5.409,12.078,12.078,12.078h219.822 c6.67,0,12.078-5.407,12.078-12.078S317.142,130.611,310.472,130.611z"/> </g> </g> <g> <g> <path d="M174.075,210.465H90.65c-6.67,0-12.078,5.407-12.078,12.078c0,6.671,5.409,12.078,12.078,12.078h83.425 c6.671,0,12.078-5.407,12.078-12.078S180.745,210.465,174.075,210.465z"/> </g> </g> <g> <g> <path d="M306.837,285.53c-6.666,0-12.078,5.411-12.078,12.078c0,6.667,5.412,12.078,12.078,12.078 c6.668,0,12.078-5.411,12.078-12.078C318.915,290.941,313.505,285.53,306.837,285.53z"/> </g> </g> <g> <g> <path d="M419.079,285.53c-6.667,0-12.078,5.411-12.078,12.078c0,6.667,5.411,12.078,12.078,12.078 c6.668,0,12.078-5.411,12.078-12.078C431.157,290.941,425.746,285.53,419.079,285.53z"/> </g> </g> </svg>
        <span>Talk with administrators</span>
        <ul>
          <?php
          foreach ($User->_getAdminList() as $AdminUser) {
            $RoomAvailableAdmin = $Chat->_roomAvailable($AdminUser);
          ?>
          <li onclick="<?php echo ($RoomAvailableAdmin != false ? '_changeRoomView(\''.App::encrypt_decrypt('encrypt', $RoomAvailableAdmin).'\')' : '_createNewRoom(\''.$AdminUser->_getUserID(true).'\')'); ?>">
            <div style="background-image:url('<?php echo $AdminUser->_getPicture(); ?>');background-color:<?php echo $AdminUser->_getAssociateColor(); ?>;')">
              <?php if($AdminUser->_getUserStatus() != 0): ?>
                <div class="kr-admn-chat-st-<?php echo $AdminUser->_getUserStatus(); ?>"></div>
              <?php endif; ?>
            </div>
            <span><?php echo $AdminUser->_getName(); ?></span>
          </li>
          <?php
          }
          ?>
        </ul>
      </section>
    </section>
  </section>
</section>
