<?php

/**
 * Admin mail settings page
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
<form class="kr-admin kr-adm-post-evs" action="<?php echo APP_URL; ?>/app/modules/kr-admin/src/actions/saveSmtpSettings.php" method="post">
  <nav class="kr-admin-nav">
    <ul>
      <?php foreach ($Admin->_getListSection() as $key => $section) { // Get list admin section
        echo '<li type="module" kr-module="admin" kr-view="'.strtolower(str_replace(' ', '', $section)).'" '.($section == 'Mail settings' ? 'class="kr-admin-nav-selected"' : '').'>'.$Lang->tr($section).'</li>';
      } ?>
    </ul>
  </nav>
  <div class="kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Mail engine'); ?></label>
      </div>
      <div>
        <select class="" name="kr-adm-chk-mailtype" onchange="$('.kr-adm-post-evs').submit();">
          <option value="mail" <?php if($App->_getMailType() == "mail") echo 'selected'; ?>>Send emails with default mail function of the server</option>
          <option value="smtp" <?php if($App->_getMailType() == "smtp") echo 'selected'; ?>>Send emails with SMTP credentials</option>
        </select>
      </div>
    </div>
    <?php if($App->_getMailType() == "smtp"): ?>
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Enable SMTP'); ?></label>
        </div>
        <div>
          <div class="ckbx-style-14">
              <input type="checkbox" id="kr-adm-chk-enablesmtp" <?php echo ($App->_smtpEnabled() ? 'checked' : ''); ?> name="kr-adm-chk-enablesmtp">
              <label for="kr-adm-chk-enablesmtp"></label>
          </div>
        </div>
      </div>
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('SMTP Server'); ?></label>
        </div>
        <div>
          <input type="text" placeholder="<?php echo $Lang->tr('Your SMTP host server'); ?>" name="kr-adm-smtpserver" value="<?php echo $App->_getSmtpServer(); ?>">
        </div>
      </div>
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('SMTP Port'); ?></label>
        </div>
        <div>
          <input type="text" placeholder="<?php echo $Lang->tr('Your SMTP port'); ?>" name="kr-adm-smtpport" value="<?php echo $App->_getSmtpPort(); ?>">
        </div>
      </div>
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('SMTP Security'); ?></label>
        </div>
        <div>
          <select name="kr-adm-security">
            <option <?php if($App->_getSmtpSecurity() == "0") echo 'selected="selected"'; ?> value="0">None</option>
            <option <?php if($App->_getSmtpSecurity() == "tls") echo 'selected="selected"'; ?> value="tls">TLS</option>
            <option <?php if($App->_getSmtpSecurity() == "ssl") echo 'selected="selected"'; ?> value="ssl">SSL</option>
          </select>
        </div>
      </div>
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('SMTP User'); ?></label>
        </div>
        <div>
          <input type="text" placeholder="<?php echo $Lang->tr('Your SMTP user'); ?>" name="kr-adm-smtpuser" value="<?php echo $App->_getSmtpUser(); ?>">
        </div>
      </div>
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('SMTP Password'); ?></label>
        </div>
        <div>
          <input type="text" placeholder="<?php echo $Lang->tr('Your SMTP password'); ?>" name="kr-adm-smtppassword" value="<?php echo (!is_null($App->_getSmtpPassword()) ? '********' : ''); ?>">
        </div>
      </div>
    <?php else: ?>
      <div class="kr-admin-field">
        <div>
          <label><?php echo $Lang->tr('Mail from'); ?></label><br/>
          <span>Exemple : no-reply@mycompany.com</span>
        </div>
        <div>
          <input type="text" placeholder="<?php echo $Lang->tr('Your sending mail address'); ?>" name="kr-adm-mailfromaddr" value="<?php echo $App->_getMailSendingAddress(); ?>">
        </div>
      </div>
    <?php endif; ?>
  </div>
  <div class="kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Email sender name'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Email sender name'); ?>" name="kr-adm-emailsendername" value="<?php echo $App->_getSmtpFrom(); ?>">
      </div>
    </div>
  </div>
  <div class="kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Support mail'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Support mail'); ?>" name="kr-adm-supportmail" value="<?php echo $App->_getSupportEmail(); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Support Phone'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Support phone'); ?>" name="kr-adm-supportphone" value="<?php echo $App->_getSupportPhone(); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Support Address'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Support Address'); ?>" name="kr-adm-supportaddress" value="<?php echo $App->_getSupportAddress(); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('DPO mail'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('DPO mail'); ?>" name="kr-adm-dpomail" value="<?php echo $App->_getDPOEmail(); ?>">
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('DPO Phone'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('DPO phone'); ?>" name="kr-adm-dpophone" value="<?php echo $App->_getDPOPhone(); ?>">
      </div>
    </div>
  </div>
  <div class="kr-admin-line kr-admin-line-cls">
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Send welcome mail'); ?></label>
      </div>
      <div>
        <div class="ckbx-style-14">
            <input type="checkbox" id="kr-adm-chk-sendwelcommail" <?php echo ($App->_sendWelcomeEmail() ? 'checked' : ''); ?> name="kr-adm-chk-sendwelcommail">
            <label for="kr-adm-chk-sendwelcommail"></label>
        </div>
      </div>
    </div>
    <div class="kr-admin-field">
      <div>
        <label><?php echo $Lang->tr('Welcome mail subject'); ?></label>
      </div>
      <div>
        <input type="text" placeholder="<?php echo $Lang->tr('Welcome mail subject'); ?>" name="kr-adm-welcomemailsubject" value="<?php echo $App->_getWelcomeSubject(); ?>">
      </div>
    </div>
  </div>
  <div class="kr-admin-action">
    <input type="submit" class="btn btn-orange" name="" value="<?php echo $Lang->tr('Save'); ?>">
  </div>
</form>
