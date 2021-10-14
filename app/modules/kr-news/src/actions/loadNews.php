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
    $New = new News();

    // Article selected
    $ArticleSelected = $New->_getArticle($_POST['uniqnews']);

} catch (Exception $e) {
    die(json_encode([
      'error' => 1,
      'msg' => $e->getMessage()
    ]));
}

?>
<header style="<?php echo(empty($ArticleSelected->_getPicture()) ? 'display:none;' : ''); ?> background-image:url('<?php echo $ArticleSelected->_getPicture(); ?>')">

</header>
<section class="kr-news-detailed-infos">
  <div>
    <div class="kr-news-detailed-infos-data">
      <label><?php echo $ArticleSelected->_getFrom(); ?></label>
      <span><?php echo $ArticleSelected->_getAuthor(); ?></span>
    </div>
  </div>
  <ul>
    <?php
    foreach ($ArticleSelected->_getListTags() as $keyTag => $valTag) {
        echo '<li class="kr-news-tags-'.($keyTag % 5).'">'.$valTag.'</li>';
    }
    ?>
  </ul>
</section>
<h1><?php echo $ArticleSelected->_getTitle(); ?></h1>
<div class="kr-news-content"><?php echo $ArticleSelected->_getContent(); ?></div>
<footer>
  <a href="<?php echo $ArticleSelected->_getUrl(); ?>" target=_bank class="btn btn-orange btn-autowidth"><?php echo $Lang->tr('View the article'); ?></a>
</footer>
