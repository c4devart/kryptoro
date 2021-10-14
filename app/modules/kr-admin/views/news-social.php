<?php

/**
 * Admin news social settings
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */

session_start();

require "../../../../config/config.settings.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/vendor/autoload.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/MySQL/MySQL.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/App.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/AppModule.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/User/User.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/Lang/Lang.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoApi.php";

// Load app modules
$App = new App(true);
$App->_loadModulesControllers();

// Check loggin & permission
$User = new User();
if(!$User->_isLogged()) throw new Exception("User are not logged", 1);
if(!$User->_isAdmin()) throw new Exception("Permission denied", 1);

// Init language object
$Lang = new Lang($User->_getLang(), $App);

// Init admin object
$Admin = new Admin();

// Init dashboard object
$Dashboard = new Dashboard(new CryptoApi(null, null, $App), $User);

// Init News & Social object
$News = new News();
$Social = new Social('twitter');

?>
<section class="kr-admin">
  <nav class="kr-admin-nav">
    <ul>
      <?php foreach ($Admin->_getListSection() as $key => $section) { // Get list admin section
        echo '<li type="module" kr-module="admin" kr-view="'.strtolower(str_replace(' ', '', $section)).'" '.($section == 'News - Social' ? 'class="kr-admin-nav-selected"' : '').'>'.$Lang->tr($section).'</li>';
      } ?>
    </ul>
  </nav>

  <h3><?php echo $Lang->tr('Calendar credentials'); ?></h3>
  <form method="post"action="<?php echo APP_URL; ?>/app/modules/kr-admin/src/actions/saveCalendarSettings.php" class="kr-adm-post-evs kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Enable calendar'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-enablecalandar" <?php echo ($App->_getCalendarEnable() ? 'checked' : ''); ?> name="kr-adm-chk-enablecalandar">
            <label for="kr-adm-chk-enablecalandar"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Calendar Client ID'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your Calendar Client ID'); ?>" name="kr-adm-calendarclientid" value="<?php echo (!empty($App->_getCalendarCientID()) ? '**************' : ''); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Calendar Client Secret'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your Calendar Client ID'); ?>" name="kr-adm-calendarclientsecret" value="<?php echo (!empty($App->_getCalendarClientSecret()) ? '**************' : ''); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Only show events in relation to enabled coins'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-enablecalandarenablecoin" <?php echo ($App->_getCalendarEnableCoinsEnabled() ? 'checked' : ''); ?> name="kr-adm-chk-enablecalandarenablecoin">
            <label for="kr-adm-chk-enablecalandarenablecoin"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-action">
      <input type="submit" style="margin-bottom:15px;" class="btn btn-orange" name="" value="<?php echo $Lang->tr('Save'); ?>">
    </div>
  </form>

  <h3><?php echo $Lang->tr('News feed'); ?></h3>

  <form method="post"action="<?php echo APP_URL; ?>/app/modules/kr-admin/src/actions/addRSSFeed.php" class="kr-adm-post-evs kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Add news feed'); ?></label>
      </div>
      <div class="kr-admin-field-ws">
        <input type="text" placeholder="<?php echo $Lang->tr('Your RSS Feed link'); ?>" name="kr-rss-feed" value="">
        <input type="submit" class="btn btn-orange" name="" value="<?php echo $Lang->tr('Add'); ?>">
      </div>
    </div>
  </form>
  <div class="kr-admin-table">
    <table>
      <thead>
        <tr>
          <td><?php echo $Lang->tr('Name feed'); ?></td>
          <td><?php echo $Lang->tr('Url'); ?></td>
          <td></td>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach ($News->_getRssFeedList() as $idRssFeed => $RssFeed) { // Get list RSS Feeds
          ?>
          <tr>
            <td><?php echo $RssFeed->_getFromTitle(); ?></td>
            <td><?php echo $RssFeed->_getUrl(); ?></td>
            <td>
              <form method="post" action="<?php echo APP_URL; ?>/app/modules/kr-admin/src/actions/deleteRSSFeed.php" class="kr-adm-post-evs kr-admin-line kr-admin-line-cls">
                <input type="hidden" name="rssfeed_id" value="<?php echo $idRssFeed; ?>">
                <input type="submit" class="btn btn-small btn-autowidth" name="remove_news" value="Delete">
              </form>
            </td>
          </tr>
          <?php
        }
        ?>
      </tbody>
    </table>
  </div>

  <h3><?php echo $Lang->tr('Twitter accounts'); ?></h3>

  <form method="post" action="<?php echo APP_URL; ?>/app/modules/kr-admin/src/actions/addSocialFeed.php" class="kr-adm-post-evs kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Add twitter account'); ?></label>
      </div>
      <div class="kr-admin-field-ws">
        <input type="text" placeholder="<?php echo $Lang->tr('Twitter name'); ?> (<?php echo $Lang->tr('without'); ?> @) ex : Bitcoin" name="kr-social-feed" value="">
        <input type="submit" class="btn btn-orange" name="" value="<?php echo $Lang->tr('Add'); ?>">
      </div>
    </div>
  </form>
  <div class="kr-admin-table">
    <table>
      <thead>
        <tr>
          <td><?php echo $Lang->tr('Social name'); ?></td>
          <td><?php echo $Lang->tr('Url'); ?></td>
          <td></td>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach ($Social->_getRssFeedList() as $idSocial => $RssFeed) { // Get list Social Feeds
          ?>
          <tr>
            <td><?php echo $RssFeed->_getFromTitle(); ?></td>
            <td><?php echo $RssFeed->_getUrl(); ?></td>
            <td>
              <form method="post" action="<?php echo APP_URL; ?>/app/modules/kr-admin/src/actions/deleteSocialFeed.php" class="kr-adm-post-evs kr-admin-line kr-admin-line-cls">
                <input type="hidden" name="social_id" value="<?php echo $idSocial; ?>">
                <input type="submit" class="btn btn-small btn-autowidth" name="remove_social" value="Delete">
              </form>
            </td>
          </tr>
          <?php
        }
        ?>
      </tbody>
    </table>
  </div>
</section>
