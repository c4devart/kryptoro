<?php

/**
 * Admin general settings page
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
<form class="kr-admin kr-adm-post-evs" action="<?php echo APP_URL; ?>/app/modules/kr-admin/src/actions/saveIntro.php">
  <nav class="kr-admin-nav">
    <ul>
      <?php foreach ($Admin->_getListSection() as $key => $section) { // Get list admin section
        echo '<li type="module" kr-module="admin" kr-view="'.strtolower(str_replace(' ', '', $section)).'" '.($section == 'Intro' ? 'class="kr-admin-nav-selected"' : '').'>'.$Lang->tr($section).'</li>';
      } ?>
    </ul>
  </nav>
  <div class="kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Enable news popup'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-newspopup" <?php echo ($App->_getNewsPopup() ? 'checked' : ''); ?> name="kr-adm-chk-newspopup">
            <label for="kr-adm-chk-newspopup"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('News popup title'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Your news popup title'); ?>" name="kr-adm-newspopuptitle" value="<?php echo $App->_getNewsPopupTitle(); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('News popup video (put blank for not show video)'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('News popup video (put blank for not show video)'); ?>" name="kr-adm-newspopupvideo" value="<?php echo $App->_getNewsPopupVideo(); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('News popup description'); ?></label>
      </div>
      <div>
        <textarea style="height:150px;" name="kr-adm-newspopuptext"><?php echo $App->_getNewsPopupText(); ?></textarea>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Advert users'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-newspopupadvert" name="kr-adm-chk-newspopupadvert">
            <label for="kr-adm-chk-newspopupadvert"></label>
        </div>
      </div>
    </div>
  </div>
  <div class="kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Enable intro'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-introenable" <?php echo ($App->_getIntroShow() ? 'checked' : ''); ?> name="kr-adm-chk-introenable">
            <label for="kr-adm-chk-introenable"></label>
        </div>
      </div>
    </div>
  </div>
  <div class="kr-admin-line kr-admin-line-cls">
    <?php
    $listIntro = $App->_getIntroList();
    ?>
    <?php for ($i=0; $i < 20; $i++) { ?>
      <div class="kr-admin-field">
        <div style="margin-right:10px;">
          <select style="width:100%;" name="intro_target_<?php echo $i; ?>">
            <option value="none"></option>
            <?php
            foreach ($Admin->_getIntroAvailable() as $key => $value) {
              echo '<option '.(array_key_exists($i, $listIntro) && $listIntro[$i]['attach'] == $key ? 'selected' : '').' value="'.$key.'">'.$value.'</option>';
            }
            ?>
          </select>
        </div>
        <div style="margin-right:10px;width:240px;">
          <input type="text" placeholder="Your title ..." name="intro_title_<?php echo $i; ?>" value="<?php if(array_key_exists($i, $listIntro)) echo $listIntro[$i]['title']; ?>">
        </div>
        <div>

          <input type="text" placeholder="Your message ..." name="intro_text_<?php echo $i; ?>" value="<?php if(array_key_exists($i, $listIntro)) echo $listIntro[$i]['text']; ?>">
        </div>
      </div>
  <?php } ?>
  </div>
  <div class="kr-admin-action">
    <input type="submit" class="btn btn-orange" name="" value="<?php echo $Lang->tr('Save'); ?>">
  </div>
</form>
