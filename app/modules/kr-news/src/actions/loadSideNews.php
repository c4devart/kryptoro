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

    // Init news object
    $News = new News();


} catch (Exception $e) {
    die(json_encode([
      'error' => 1,
      'msg' => $e->getMessage()
    ]));
}

foreach ($News->_getListFeedRSS() as $article) {
  ?>
<article kr-news="<?php echo $article->_getArticleUniq(); ?>">
  <div style="background-image:url('<?php echo $article->_getPicture(); ?>')"></div>
  <header>
    <span><?php echo $article->_getFrom(); ?></span>
    <span><?php echo $article->_getPublishSince($Lang); ?></span>
  </header>
  <h3><?php echo $article->_getTitle(); ?></h3>
</article>
<?php } ?>
