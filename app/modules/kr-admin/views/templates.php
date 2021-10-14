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
        echo '<li type="module" kr-module="admin" kr-view="'.strtolower(str_replace(' ', '', $section)).'" '.($section == 'Templates' ? 'class="kr-admin-nav-selected"' : '').'>'.$Lang->tr($section).'</li>';
      } ?>
    </ul>
  </nav>

  <h3><?php echo $Lang->tr('Page configuration'); ?></h3>

  <?php
  foreach (Admin::_getPagesList() as $pageName => $titlePage) {
  ?>
    <form method="post"action="<?php echo APP_URL; ?>/app/modules/kr-admin/src/actions/saveTemplate.php" class="kr-adm-post-evs kr-admin-line kr-admin-line-cls">
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr($titlePage); ?></label>
        </div>
        <div class="kr-admin-field-ws">
          <textarea name="tpl-content" class="template-adm-coedi" data-editor="html" data-gutter="1" style="width:100%;height:250px;"><?php echo Admin::_getPageContent('/app/views/pages/'.$pageName.'.tpl'); ?></textarea>
          <input type="hidden" name="template-type" value="page">
          <input type="hidden" name="template-name" value="<?php echo $pageName; ?>">
          <input type="submit" class="btn btn-orange" name="" value="<?php echo $Lang->tr('Save'); ?>">
        </div>
      </div>
    </form>
  <?php } ?>

  <h3><?php echo $Lang->tr('Tamplates configuration'); ?></h3>

  <?php
  foreach (Admin::_getTemplateList() as $templateName => $titleTemplate) {
  ?>
    <form method="post"action="<?php echo APP_URL; ?>/app/modules/kr-admin/src/actions/saveTemplate.php" class="kr-adm-post-evs kr-admin-line kr-admin-line-cls">
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr($titleTemplate); ?></label>
        </div>
        <div class="kr-admin-field-ws">
          <textarea class="template-adm-coedi" data-editor="html" data-gutter="1" name="tpl-content" style="width:100%;height:250px;"><?php echo Admin::_getPageContent('/app/modules/kr-user/templates/'.$templateName.'.tpl'); ?></textarea>
          <input type="hidden" name="template-type" value="template">
          <input type="hidden" name="template-name" value="<?php echo $pageName; ?>">
          <input type="submit" class="btn btn-orange" name="" value="<?php echo $Lang->tr('Save'); ?>">
        </div>
      </div>
    </form>
  <?php } ?>

</section>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.1/ace.js" charset="utf-8"></script>*
<script type="text/javascript">
// $(document).ready(function(){
//   $('textarea.template-adm-coedi').each(function() {
//     var textarea = $(this);
//     var mode = textarea.data('editor');
//     var editDiv = $('<div>', {
//       position: 'absolute',
//       width: textarea.width(),
//       height: textarea.height(),
//       'class': textarea.attr('class')
//     }).insertBefore(textarea);
//     textarea.css('display', 'none');
//     var editor = ace.edit(editDiv[0]);
//     editor.renderer.setShowGutter(textarea.data('gutter'));
//     editor.getSession().setValue(textarea.val());
//     editor.getSession().setMode("ace/mode/" + mode);
//     editor.setTheme("ace/theme/idle_fingers");
//
//     // copy back to textarea on form submit...
//     textarea.closest('form').submit(function() {
//       textarea.val(editor.getSession().getValue());
//     })
//   });
// });
</script>
