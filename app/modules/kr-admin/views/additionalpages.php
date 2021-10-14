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


?>
<section class="kr-admin">
  <nav class="kr-admin-nav">
    <ul>
      <?php foreach ($Admin->_getListSection() as $key => $section) { // Get list admin section
        echo '<li type="module" kr-module="admin" kr-view="'.strtolower(str_replace(' ', '', $section)).'" '.($section == 'Additional pages' ? 'class="kr-admin-nav-selected"' : '').'>'.$Lang->tr($section).'</li>';
      } ?>
    </ul>
  </nav>

  <h3><?php echo $Lang->tr('Additional pages'); ?></h3>

  <form method="post"action="<?php echo APP_URL; ?>/app/modules/kr-admin/src/actions/addAddtionalPage.php" class="kr-adm-post-evs kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Add additional page'); ?></label>
      </div>
      <div class="kr-admin-field-ws">
        <input type="text" style="margin-bottom:15px" placeholder="<?php echo $Lang->tr('Page name'); ?>" name="kr-additionalpage-name" value="">
        <input type="text" style="margin-bottom:15px" placeholder="<?php echo $Lang->tr('Page URL'); ?>" name="kr-additionalpage-url" value="">
        <input type="text" style="margin-bottom:8px" placeholder="<?php echo $Lang->tr('Page icon (ex : lnr-home)'); ?>" name="kr-additionalpage-icon" value="">
        <div style="width:100%;margin-bottom:15px;font-size:13px;">
          <span><?php echo $Lang->tr("You can choose beetween 'Page icon' and 'Page icon svg' (Page icon is priority) - List icons available here"); ?> : <a target=_bank href="https://linearicons.com/free">https://linearicons.com/free</a></span>
        </div>
        <input type="text" style="margin-bottom:15px" placeholder="<?php echo $Lang->tr('Or page icon svg'); ?>" name="kr-additionalpage-iconsvg" value="">
        <input type="submit" class="btn btn-orange" name="" value="<?php echo $Lang->tr('Add'); ?>">
      </div>
    </div>
  </form>
  <div class="kr-admin-table">
    <table>
      <thead>
        <tr>
          <td><?php echo $Lang->tr('Name'); ?></td>
          <td><?php echo $Lang->tr('Url'); ?></td>
          <td><?php echo $Lang->tr('Icon'); ?></td>
          <td><?php echo $Lang->tr('SVG Picture'); ?></td>
          <td></td>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach ($App->_getAdditionalPages() as $keyAdditionalPage => $infosAdditionalPage) { // Get list RSS Feeds
          ?>
          <tr>
            <td><?php echo $infosAdditionalPage['name_additional_pages']; ?></td>
            <td><?php echo $infosAdditionalPage['url_additional_pages']; ?></td>
            <td><?php echo (strlen($infosAdditionalPage['icon_additional_pages']) > 0 ? '<svg class="lnr '.$infosAdditionalPage['icon_additional_pages'].'"><use xlink:href="#'.$infosAdditionalPage['icon_additional_pages'].'"></use></svg>' : '-'); ?></td>
            <td><?php echo (strlen($infosAdditionalPage['svg_additional_pages']) > 0 ? $infosAdditionalPage['svg_additional_pages'] : '-'); ?></td>
            <td>
              <form method="post" action="<?php echo APP_URL; ?>/app/modules/kr-admin/src/actions/deleteAdditionalPage.php" class="kr-adm-post-evs kr-admin-line kr-admin-line-cls">
                <input type="hidden" name="id_additional_pages" value="<?php echo App::encrypt_decrypt('encrypt', $infosAdditionalPage['id_additional_pages']); ?>">
                <input type="submit" class="btn btn-small btn-autowidth" name="remove_social" value="<?php echo $Lang->tr('Delete'); ?>">
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
