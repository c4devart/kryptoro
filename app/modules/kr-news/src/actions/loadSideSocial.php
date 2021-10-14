<?php

/**
 * Article new view
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

    // Check if user is logged
    $User = new User();
    if (!$User->_isLogged()) {
        throw new Exception("User not logged", 1);
    }

    // Init language object
    $Lang = new Lang($User->_getLang(), $App);

} catch (Exception $e) {
    die(json_encode([
      'error' => 1,
      'msg' => $e->getMessage()
    ]));
}

$Social = new Social('twitter');
foreach ($Social->_getListFeedRSS() as $socialfeed) {
?>
<section class="kr-social-post">
  <header>
    <div class="">
      <div style="background-image:url('<?php echo $Social->_getUserPicture($Social->_formatAccountUserName($socialfeed->_getAuthor())); ?>')"></div>
      <span><?php echo $Social->_formatUserName($socialfeed->_getAuthor()); ?></span>
    </div>
    <span><?php echo $socialfeed->_getPublishSince($Lang); ?></span>
  </header>
  <p><?php echo $socialfeed->_getContent(); ?></p>
</section>
<?php } ?>
